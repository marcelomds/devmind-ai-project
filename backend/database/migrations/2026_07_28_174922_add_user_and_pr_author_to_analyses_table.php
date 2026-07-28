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
        Schema::table('analyses', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('repository_id')->constrained()->nullOnDelete();
            $table->string('pr_author_login')->nullable()->after('commit_sha');
            $table->string('pr_author_avatar_url')->nullable()->after('pr_author_login');
            $table->bigInteger('pr_author_github_id')->nullable()->after('pr_author_avatar_url');

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analyses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['pr_author_login', 'pr_author_avatar_url', 'pr_author_github_id']);
        });
    }
};
