<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pilot_reports', function (Blueprint $table) {
            $table->id();
            $table->integer('external_id')->unique();
            $table->string('location');
            $table->string('aircraft');
            $table->string('altitude');
            $table->string('sky')->nullable();
            $table->string('turbulence')->nullable();
            $table->string('icing')->nullable();
            $table->string('visibility')->nullable();
            $table->integer('temperature')->nullable();
            $table->string('wind')->nullable();
            $table->boolean('urgent');
            $table->boolean('manual')->default(false);
            $table->string('raw');
            $table->dateTime('reported_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pilot_reports');
    }
};
