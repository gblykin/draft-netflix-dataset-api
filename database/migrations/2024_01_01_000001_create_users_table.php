<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->unique();
            $table->string('email')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->integer('age')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Prefer not to say', 'Other'])->nullable();
            $table->string('country');
            $table->string('state_province')->nullable();
            $table->string('city');
            $table->string('subscription_plan');
            $table->date('subscription_start_date');
            $table->boolean('is_active')->default(true);
            $table->decimal('monthly_spend', 8, 2)->nullable()->default(0.00);
            $table->string('primary_device')->nullable();
            $table->integer('household_size')->nullable()->default(1);
            $table->timestamps();
            
            $table->index(['subscription_plan', 'country']);
            $table->index(['is_active', 'subscription_start_date']);
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
