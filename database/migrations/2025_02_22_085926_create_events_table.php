<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->dateTime('registration_opens_at');
            $table->dateTime('registration_closes_at');
            $table->dateTime('emails_sent_at')->nullable();
            $table->string('banner_path', 2048)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
