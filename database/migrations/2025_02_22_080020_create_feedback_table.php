<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('critic_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('controller_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('position');
            $table->integer('rating');
            $table->string('comment');
            $table->ipAddress();
            $table->boolean('anonymous')->default(false);
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
