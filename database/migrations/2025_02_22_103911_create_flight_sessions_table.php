<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flight_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnUpdate();
            $table->integer('cid');
            $table->string('callsign');
            $table->string('aircraft')->nullable();
            $table->string('departure_airport')->nullable();
            $table->string('arrival_airport')->nullable();
            $table->decimal('latitude', 10, 6);
            $table->decimal('longitude', 10, 6);
            $table->integer('heading');
            $table->integer('altitude');
            $table->integer('planned_altitude')->nullable();
            $table->integer('speed');
            $table->string('route')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['cid', 'callsign']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flight_sessions');
    }
};
