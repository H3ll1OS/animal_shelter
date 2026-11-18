<?php

namespace Tests\Feature;

use App\Jobs\SendSmsNotification;
use App\Http\Controllers\Admin\AdoptionController;
use App\Http\Controllers\Admin\PostAdoptionMonitoringController;
use App\Models\Adoption;
use App\Models\Pet;
use App\Models\PostAdoptionVisit;
use App\Models\SmsNotification;
use App\Models\User;
use App\Console\Commands\SendMonitoringSmsReminders;
use App\Services\Sms\PhilippineMobileNumber;
use App\Services\Sms\SmsNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Tests\TestCase;

class SmsNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_philippine_mobile_numbers_are_normalized_to_e164(): void
    {
        $this->assertSame('+639171234567', PhilippineMobileNumber::normalize('0917 123 4567'));
        $this->assertSame('+639171234567', PhilippineMobileNumber::normalize('+63 917 123 4567'));
        $this->assertNull(PhilippineMobileNumber::normalize('12345'));
        $this->assertNull(PhilippineMobileNumber::normalize(null));
    }

    public function test_sms_is_skipped_when_consent_is_missing_or_number_is_invalid(): void
    {
        Queue::fake();
        config([
            'services.sms.provider' => 'twilio',
            'services.sms.twilio.account_sid' => 'AC' . str_repeat('a', 32),
            'services.sms.twilio.auth_token' => 'test-token',
            'services.sms.twilio.messaging_service_sid' => 'MG' . str_repeat('b', 32),
        ]);

        $service = app(SmsNotificationService::class);
        $noConsentUser = User::factory()->create(['phone' => '09171234567']);
        $noConsent = $service->queue(
            'test:no-consent',
            'adoption.submitted',
            'Test message',
            $noConsentUser,
            $noConsentUser->phone,
            null,
            null
        );

        $optedInUser = User::factory()->create([
            'phone' => 'not-a-mobile',
            'sms_opted_in_at' => now(),
        ]);
        $invalidNumber = $service->queue(
            'test:invalid-number',
            'adoption.submitted',
            'Test message',
            $optedInUser,
            $optedInUser->phone,
            null,
            $optedInUser->sms_opted_in_at
        );

        $this->assertSame('skipped', $noConsent?->status);
        $this->assertSame('consent_missing', $noConsent?->error_code);
        $this->assertSame('skipped', $invalidNumber?->status);
        $this->assertSame('invalid_mobile_number', $invalidNumber?->error_code);
        Queue::assertNothingPushed();
    }

    public function test_missing_provider_credentials_are_recorded_as_skipped(): void
    {
        Queue::fake();
        config([
            'services.sms.provider' => 'twilio',
            'services.sms.twilio.account_sid' => null,
            'services.sms.twilio.auth_token' => null,
            'services.sms.twilio.messaging_service_sid' => null,
            'services.sms.twilio.from' => null,
        ]);
        $user = User::factory()->create([
            'phone' => '09171234567',
            'sms_opted_in_at' => now(),
        ]);

        $notification = app(SmsNotificationService::class)->queue(
            'test:no-provider-credentials',
            'adoption.submitted',
            'Test message',
            $user,
            $user->phone,
            null,
            $user->sms_opted_in_at
        );

        $this->assertSame('skipped', $notification?->status);
        $this->assertSame('provider_not_configured', $notification?->error_code);
        Queue::assertNothingPushed();
    }

    public function test_adoption_submission_queues_sms_after_the_application_is_saved(): void
    {
        Queue::fake();
        Storage::fake('public');
        $this->configureTwilio();

        $user = User::factory()->create([
            'phone' => '09171234567',
            'sms_opted_in_at' => now(),
        ]);
        $pet = Pet::create([
            'name' => 'Milo',
            'species' => 'Dog',
            'size' => 'small',
            'gender' => 'Male',
            'status' => 'Available',
        ]);

        $this->actingAs($user)->post(route('pets.adopt', $pet), [
            'full_name' => $user->name,
            'email' => $user->email,
            'phone' => '09171234567',
            'message' => '',
            'documents' => [UploadedFile::fake()->image('proof.jpg')],
        ])->assertSessionHas(
            'success',
            'Your adoption application has been successfully submitted. Our shelter staff will review your application and contact you through your provided contact information regarding your application status and the next steps in the adoption process.'
        );

        $adoption = Adoption::query()->firstOrFail();
        $this->assertDatabaseHas('sms_notifications', [
            'dedupe_key' => 'adoption:' . $adoption->id . ':submitted',
            'notification_type' => 'adoption.submitted',
            'status' => 'pending',
            'recipient_phone' => '+639171234567',
        ]);
        Queue::assertPushed(SendSmsNotification::class);
    }

    public function test_mobile_adoption_submission_uses_the_same_sms_service(): void
    {
        Queue::fake();
        $this->configureTwilio();
        $user = User::factory()->create([
            'phone' => '09171234567',
            'sms_opted_in_at' => now(),
        ]);
        $pet = Pet::create([
            'name' => 'Milo',
            'species' => 'Dog',
            'size' => 'small',
            'gender' => 'Male',
            'status' => 'Available',
        ]);
        $token = $user->issueApiToken();

        $this->withToken($token)->postJson('/api/mobile/pets/' . $pet->id . '/adoptions', [
            'full_name' => $user->name,
            'email' => $user->email,
            'phone' => '09171234567',
            'message' => '',
            'documents' => ['uploaded-document.pdf'],
        ])->assertCreated()->assertJsonPath(
            'message',
            'Adoption Application Submitted Successfully! Your adoption application has been successfully submitted. Our shelter staff will review your application and contact you through your provided contact information regarding your application status and the next steps in the adoption process. Please wait for confirmation from our staff. Submission does not guarantee adoption approval.'
        );

        $this->assertDatabaseHas('sms_notifications', [
            'notification_type' => 'adoption.submitted',
            'recipient_phone' => '+639171234567',
            'status' => 'pending',
        ]);
    }

    public function test_approval_and_rejection_sms_are_queued_only_after_decision_updates(): void
    {
        Queue::fake();
        $this->configureTwilio();
        $user = User::factory()->create([
            'phone' => '09171234567',
            'sms_opted_in_at' => now(),
        ]);
        $pet = Pet::create([
            'name' => 'Luna',
            'species' => 'Cat',
            'size' => 'small',
            'gender' => 'Female',
            'status' => 'Available',
        ]);
        $adoption = Adoption::create([
            'pet_id' => $pet->id,
            'user_id' => $user->id,
            'full_name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'message' => '',
            'document_path' => '[]',
            'status' => 'Pending',
        ]);

        app(AdoptionController::class)->approve($adoption);
        $this->assertDatabaseHas('sms_notifications', [
            'dedupe_key' => 'adoption:' . $adoption->id . ':approved',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('pets', ['id' => $pet->id, 'status' => 'Adopted']);

        $adoption->status = 'Pending';
        $adoption->save();
        app(AdoptionController::class)->reject($adoption);
        $this->assertDatabaseHas('sms_notifications', [
            'dedupe_key' => 'adoption:' . $adoption->id . ':rejected',
            'status' => 'pending',
        ]);

        Queue::assertPushed(SendSmsNotification::class, 2);
    }

    public function test_monitoring_schedule_create_and_update_queue_sms(): void
    {
        Queue::fake();
        $this->configureTwilio();
        $adopter = User::factory()->create([
            'phone' => '09171234567',
            'sms_opted_in_at' => now(),
        ]);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Active']);
        $adoption = $this->approvedAdoption($adopter);
        $controller = app(PostAdoptionMonitoringController::class);
        $scheduledDate = today()->addDays(2)->format('Y-m-d');

        $storeRequest = Request::create('/admin/post-adoption/schedules', 'POST', [
            'adoption_id' => $adoption->id,
            'scheduled_date' => $scheduledDate,
            'monitoring_method' => 'Home Visit',
        ]);
        $storeRequest->setUserResolver(fn () => $admin);
        $controller->store($storeRequest);

        $visit = PostAdoptionVisit::query()->firstOrFail();
        $this->assertDatabaseHas('sms_notifications', [
            'dedupe_key' => 'monitoring:' . $visit->id . ':created',
            'notification_type' => 'monitoring.scheduled',
            'status' => 'pending',
        ]);

        $updateRequest = Request::create('/admin/post-adoption/schedules/' . $visit->id, 'PUT', [
            'adoption_id' => $adoption->id,
            'scheduled_date' => today()->addDays(3)->format('Y-m-d'),
            'monitoring_method' => 'Phone Call',
        ]);
        $updateRequest->setUserResolver(fn () => $admin);
        $controller->update($updateRequest, $visit);

        $this->assertDatabaseHas('sms_notifications', [
            'notification_type' => 'monitoring.updated',
            'related_type' => PostAdoptionVisit::class,
            'related_id' => $visit->id,
            'status' => 'pending',
        ]);
        Queue::assertPushed(SendSmsNotification::class, 2);
    }

    public function test_monitoring_reminder_command_uses_unique_event_key(): void
    {
        Queue::fake();
        $this->configureTwilio();
        $adopter = User::factory()->create([
            'phone' => '09171234567',
            'sms_opted_in_at' => now(),
        ]);
        $adoption = $this->approvedAdoption($adopter);
        PostAdoptionVisit::create([
            'adoption_id' => $adoption->id,
            'pet_id' => $adoption->pet_id,
            'adopter_id' => $adopter->id,
            'created_by' => $adopter->id,
            'scheduled_date' => today()->addDay(),
            'monitoring_method' => 'Home Visit',
            'schedule_status' => 'Pending',
        ]);

        $command = app(SendMonitoringSmsReminders::class);
        $command->handle(app(SmsNotificationService::class));
        $command->handle(app(SmsNotificationService::class));

        $this->assertDatabaseCount('sms_notifications', 1);
        $this->assertDatabaseHas('sms_notifications', [
            'notification_type' => 'monitoring.reminder',
            'status' => 'pending',
        ]);
        Queue::assertPushed(SendSmsNotification::class, 1);
    }

    public function test_users_can_explicitly_opt_in_and_opt_out_in_account_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/account/settings', [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '09171234567',
            'sms_notifications_enabled' => true,
        ])->assertRedirect(route('account.settings'));

        $this->assertNotNull($user->fresh()->sms_opted_in_at);

        $this->actingAs($user)->patch('/account/settings', [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '09171234567',
            'sms_notifications_enabled' => false,
        ])->assertRedirect(route('account.settings'));

        $this->assertNull($user->fresh()->sms_opted_in_at);
    }

    public function test_changing_a_phone_number_invalidates_previously_queued_sms_consent(): void
    {
        $oldConsent = now()->subDay()->startOfSecond();
        $user = User::factory()->create([
            'phone' => '09171234567',
            'sms_opted_in_at' => $oldConsent,
        ]);
        $notification = SmsNotification::create([
            'user_id' => $user->id,
            'recipient_phone' => '+639171234567',
            'notification_type' => 'adoption.submitted',
            'message' => 'Test message',
            'status' => 'pending',
            'consent_at' => $oldConsent,
            'dedupe_key' => 'test:old-number-consent',
        ]);

        $this->actingAs($user)->patch('/account/settings', [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '09981234567',
            'sms_notifications_enabled' => true,
        ])->assertRedirect(route('account.settings'));

        $this->assertNotSame(
            $oldConsent->format('U'),
            $user->fresh()->sms_opted_in_at->format('U')
        );
        (new SendSmsNotification($notification->id))->handle();
        $this->assertSame('skipped', $notification->fresh()->status);
    }

    public function test_opted_in_notification_is_recorded_once_and_queued(): void
    {
        Queue::fake();
        config([
            'services.sms.provider' => 'twilio',
            'services.sms.twilio.account_sid' => 'AC' . str_repeat('a', 32),
            'services.sms.twilio.auth_token' => 'test-token',
            'services.sms.twilio.messaging_service_sid' => 'MG' . str_repeat('b', 32),
        ]);

        $user = User::factory()->create([
            'phone' => '09171234567',
            'sms_opted_in_at' => now(),
        ]);
        $service = app(SmsNotificationService::class);

        $first = $service->queue(
            'test:dedupe',
            'adoption.submitted',
            'Test message',
            $user,
            $user->phone,
            null,
            $user->sms_opted_in_at
        );
        $second = $service->queue(
            'test:dedupe',
            'adoption.submitted',
            'Duplicate test message',
            $user,
            $user->phone,
            null,
            $user->sms_opted_in_at
        );

        $this->assertSame($first?->id, $second?->id);
        $this->assertSame('+639171234567', $first?->recipient_phone);
        $this->assertSame('pending', $first?->status);
        $this->assertDatabaseCount('sms_notifications', 1);
        Queue::assertPushed(SendSmsNotification::class);
    }

    public function test_queued_messages_and_retries_respect_current_consent(): void
    {
        Queue::fake();
        $consentedAt = now()->startOfSecond();
        $user = User::factory()->create([
            'phone' => '09171234567',
            'sms_opted_in_at' => $consentedAt,
        ]);
        $notification = SmsNotification::create([
            'user_id' => $user->id,
            'recipient_name' => $user->name,
            'recipient_phone' => '+639171234567',
            'notification_type' => 'adoption.submitted',
            'message' => 'Test message',
            'status' => 'pending',
            'consent_at' => $consentedAt,
            'dedupe_key' => 'test:revoked-before-send',
        ]);

        $user->update(['sms_opted_in_at' => null]);
        (new SendSmsNotification($notification->id))->handle();
        $this->assertSame('skipped', $notification->fresh()->status);
        $this->assertSame('consent_revoked', $notification->fresh()->error_code);

        $retry = SmsNotification::create([
            'user_id' => $user->id,
            'recipient_phone' => '+639171234567',
            'notification_type' => 'adoption.submitted',
            'message' => 'Test message',
            'status' => 'failed',
            'retryable' => true,
            'consent_at' => $consentedAt,
            'dedupe_key' => 'test:revoked-before-retry',
        ]);

        $this->assertFalse(app(SmsNotificationService::class)->retry($retry));
        $this->assertSame('skipped', $retry->fresh()->status);
        Queue::assertNothingPushed();
    }

    public function test_only_provider_confirmed_rate_limits_are_safe_to_retry(): void
    {
        $this->configureTwilio();
        Http::fake([
            'api.twilio.com/*' => Http::response(['code' => 20429], 429),
        ]);
        $consentedAt = now()->startOfSecond();
        $user = User::factory()->create([
            'phone' => '09171234567',
            'sms_opted_in_at' => $consentedAt,
        ]);
        $notification = SmsNotification::create([
            'user_id' => $user->id,
            'recipient_phone' => '+639171234567',
            'notification_type' => 'adoption.submitted',
            'message' => 'Test message',
            'status' => 'pending',
            'consent_at' => $consentedAt,
            'dedupe_key' => 'test:provider-rate-limit',
        ]);
        $job = new SendSmsNotification($notification->id);

        try {
            $job->handle();
            $this->fail('A Twilio rate limit should be retryable.');
        } catch (\App\Services\Sms\SmsProviderException $exception) {
            $this->assertTrue($exception->retryable);
            $job->failed($exception);
        }

        $notification->refresh();
        $this->assertSame('failed', $notification->status);
        $this->assertTrue($notification->retryable);
        $this->assertNull($notification->provider_reference);

        Queue::fake();
        $this->assertTrue(app(SmsNotificationService::class)->retry($notification));
        $this->assertSame('pending', $notification->fresh()->status);
        Queue::assertPushed(SendSmsNotification::class);
    }

    public function test_twilio_acceptance_and_delivery_callback_are_tracked_separately(): void
    {
        config([
            'services.sms.provider' => 'twilio',
            'services.sms.twilio.account_sid' => 'AC' . str_repeat('a', 32),
            'services.sms.twilio.auth_token' => 'test-token',
            'services.sms.twilio.messaging_service_sid' => 'MG' . str_repeat('b', 32),
        ]);

        Http::fake([
            'api.twilio.com/*' => Http::response([
                'sid' => 'SM' . str_repeat('c', 32),
                'status' => 'queued',
            ], 201),
        ]);

        $user = User::factory()->create([
            'phone' => '09171234567',
            'sms_opted_in_at' => now()->startOfSecond(),
        ]);
        $notification = SmsNotification::create([
            'user_id' => $user->id,
            'recipient_name' => 'Test Donor',
            'recipient_phone' => '+639171234567',
            'notification_type' => 'adoption.submitted',
            'message' => 'A concise test message.',
            'provider' => 'twilio',
            'status' => 'pending',
            'consent_at' => $user->sms_opted_in_at,
            'dedupe_key' => 'test:twilio',
        ]);

        (new SendSmsNotification($notification->id))->handle();

        $notification->refresh();
        $this->assertSame('accepted', $notification->status);
        $this->assertSame('queued', $notification->provider_status);
        $this->assertNull($notification->delivered_at);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.twilio.com/2010-04-01/Accounts/AC' . str_repeat('a', 32) . '/Messages.json'
            && $request['To'] === '+639171234567'
            && $request['MessagingServiceSid'] === 'MG' . str_repeat('b', 32));

        $fields = [
            'AccountSid' => 'AC' . str_repeat('a', 32),
            'MessageSid' => 'SM' . str_repeat('c', 32),
            'MessageStatus' => 'delivered',
        ];
        $url = route('api.sms.twilio.status');
        ksort($fields);
        $signedPayload = $url;
        foreach ($fields as $key => $value) {
            $signedPayload .= $key . $value;
        }
        $signature = base64_encode(hash_hmac('sha1', $signedPayload, 'test-token', true));

        $this->post($url, $fields, ['X-Twilio-Signature' => $signature])->assertNoContent();

        $notification->refresh();
        $this->assertSame('delivered', $notification->status);
        $this->assertNotNull($notification->delivered_at);
    }

    public function test_invalid_twilio_callback_signature_is_rejected(): void
    {
        config(['services.sms.twilio.auth_token' => 'test-token']);

        $this->post(route('api.sms.twilio.status'), [
            'MessageSid' => 'SM' . str_repeat('c', 32),
            'MessageStatus' => 'delivered',
        ], ['X-Twilio-Signature' => 'invalid'])->assertForbidden();
    }

    private function configureTwilio(): void
    {
        config([
            'services.sms.provider' => 'twilio',
            'services.sms.twilio.account_sid' => 'AC' . str_repeat('a', 32),
            'services.sms.twilio.auth_token' => 'test-token',
            'services.sms.twilio.messaging_service_sid' => 'MG' . str_repeat('b', 32),
        ]);
    }

    private function approvedAdoption(User $adopter): Adoption
    {
        $pet = Pet::create([
            'name' => 'Milo',
            'species' => 'Dog',
            'size' => 'small',
            'gender' => 'Male',
            'status' => 'Adopted',
        ]);

        return Adoption::create([
            'pet_id' => $pet->id,
            'user_id' => $adopter->id,
            'full_name' => $adopter->name,
            'email' => $adopter->email,
            'phone' => $adopter->phone,
            'message' => '',
            'document_path' => '[]',
            'status' => 'Approved',
        ]);
    }
}
