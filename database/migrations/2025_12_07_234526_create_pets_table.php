<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // ...
        public function up(): void
        {
            Schema::create('pets', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('species', ['Dog', 'Cat', 'Rabbit', 'Bird', 'Other']);
                
                
                $table->unsignedSmallInteger('age')->nullable(); 

                $table->enum('size', ['small', 'medium', 'large']);
                $table->enum('gender', ['Male', 'Female','Unknown']);
                $table->string('breed')->nullable();
                
                $table->enum('status', ['Available', 'Adopted', 'Pending', 'Sick', 'Fostered'])
                    ->default('Available');
                    
                $table->string('image_path')->nullable(); 
                $table->text('description')->nullable();
                $table->date('date_received')->nullable();
                $table->timestamps();
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};