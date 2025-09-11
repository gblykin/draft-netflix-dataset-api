<?php

use App\Enums\Device;
use App\Enums\Gender;
use App\Enums\SubscriptionPlan;
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
            $table->string('external_user_id')->nullable()->unique(); 
            $table->string('email')->unique(); 
            $table->string('first_name');
            $table->string('last_name');
            $table->integer('age')->nullable();
            $table->enum('gender', Gender::values())->nullable();
            $table->string('country')->nullable();
            $table->string('state_province')->nullable();
            $table->string('city')->nullable();
            $table->enum('subscription_plan', SubscriptionPlan::values());
            $table->date('subscription_start_date');
            $table->boolean('is_active')->default(true);
            $table->decimal('monthly_spend', 8, 2)->default(0.00);
            $table->enum('primary_device', Device::values())->nullable();
            $table->integer('household_size')->nullable()->default(1);
            $table->timestamp('source_created_at')->nullable();
            $table->timestamps();

            $table->index('gender');
            $table->index('subscription_plan');
            $table->index('subscription_start_date');
            $table->index('is_active');
            $table->index('primary_device');
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
