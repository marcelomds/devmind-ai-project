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
        Schema::create('analyses', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('repository_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('analyzer');
            $table->string('status')->default('pending');
            $table->string('source_type');
            $table->integer('pr_number')->nullable();
            $table->string('commit_sha')->nullable();
            $table->text('input_code')->nullable();
            $table->text('summary')->nullable();
            $table->smallInteger('score')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['repository_id', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analyses');
    }
};
