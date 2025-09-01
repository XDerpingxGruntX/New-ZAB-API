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
            $table->integer('cid')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();

            $table->integer('rating');
            $table->string('operating_initials')->unique();
            $table->string('home_facility');
            $table->json('roles')->nullable();

            $table->boolean('broadcast_opt_in');
            $table->boolean('member');
            $table->boolean('visitor');

            $table->dateTime('next_activity_check_at')->nullable();
            $table->dateTime('warning_delivered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
