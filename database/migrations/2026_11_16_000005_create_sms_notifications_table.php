<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone', 32)->nullable();
            $table->string('notification_type', 80);
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->text('message');
            $table->string('provider', 40)->nullable();
            $table->string('provider_reference')->nullable()->index();
            $table->string('provider_status', 40)->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->string('error_code', 80)->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('retryable')->default(false);
            $table->unsignedSmallInteger('attempt_count')->default(0);
            $table->timestamp('consent_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('dedupe_key')->unique();
            $table->timestamps();

            $table->index(['related_type', 'related_id']);
            $table->index(['notification_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_notifications');
    }
};
