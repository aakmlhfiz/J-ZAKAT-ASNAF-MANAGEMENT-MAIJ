<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_uid')->nullable()->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('ic_no', 12)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('phone', 15)->nullable();
            $table->text('address')->nullable();
            $table->string('district', 100)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('postcode', 5)->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('status')->default('active');
            $table->string('dark_mode')->default('system');
            $table->string('language')->default('ms');
            $table->boolean('notification_email')->default(true);
            $table->boolean('notification_push')->default(true);
            $table->boolean('notification_sms')->default(false);
            $table->rememberToken();
            $table->timestamps();

            $table->index('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
