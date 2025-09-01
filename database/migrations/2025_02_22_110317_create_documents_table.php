<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category');
            $table->string('description', 1024)->nullable();
            $table->text('content')->nullable();
            $table->string('file_path', 2048)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('ALTER TABLE documents ADD CONSTRAINT content_or_path_not_null CHECK ((content IS NOT NULL) OR (file_path IS NOT NULL));');
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
