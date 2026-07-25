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
        Schema::create('findings', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('analysis_id')->constrained()->cascadeOnDelete();
            $table->string('severity');
            $table->string('category');
            $table->string('title');
            $table->text('message');
            $table->text('suggestion')->nullable();
            $table->string('file_path')->nullable();
            $table->integer('line_start')->nullable();
            $table->integer('line_end')->nullable();
            $table->timestamps();

            $table->index('analysis_id');
            $table->index('severity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('findings');
    }
};
