<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controller_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->integer('cid');
            $table->string('rating');
            $table->string('callsign');
            $table->string('airport');
            $table->string('position');
            $table->decimal('frequency', places: 3);
            $table->string('atis')->nullable();
            $table->dateTime('connected_at');
            $table->dateTime('disconnected_at')->nullable();
            $table->dateTime('last_fetched_at');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['cid', 'connected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controller_sessions');
    }
};
