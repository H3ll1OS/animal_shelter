<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('post_adoption_visits', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('adoption_id')->constrained()->restrictOnDelete();
            $table->foreignId('pet_id')->constrained()->restrictOnDelete();
            $table->foreignId('adopter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('scheduled_date');
            $table->date('actual_visit_date')->nullable();
            $table->string('monitoring_method', 30);
            $table->string('purpose')->nullable();
            $table->string('schedule_status', 20)->default('Pending');
            $table->string('pet_status', 40)->nullable();
            $table->string('health_condition', 40)->nullable();
            $table->string('living_condition', 40)->nullable();
            $table->string('ownership_status', 50)->nullable();
            $table->text('findings')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('notes')->nullable();
            $table->date('next_follow_up_date')->nullable();
            $table->timestamps();
            $table->index(['schedule_status', 'scheduled_date']);
            $table->index(['pet_id', 'scheduled_date']);
        });

        Schema::create('post_adoption_visit_attachments', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('post_adoption_visit_id')->constrained('post_adoption_visits')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_filename');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_adoption_visit_attachments');
        Schema::dropIfExists('post_adoption_visits');
    }
};
