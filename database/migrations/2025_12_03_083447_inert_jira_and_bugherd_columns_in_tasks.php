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
        // Add columns to tasks table if they don't exist
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'jira_key')) {
                $table->string('jira_key')->nullable()->after('task_short_code');
            }

            if (!Schema::hasColumn('tasks', 'bugherd_task_id')) {
                $table->unsignedBigInteger('bugherd_task_id')->nullable()->after('jira_key');
            }
        });

        // Add Jira project key to projects table if doesn't exist
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'jira_project_key')) {
                $table->string('jira_project_key')->nullable()->after('project_short_code');
            }

            if (!Schema::hasColumn('projects', 'bugherd_project_id')) {
                $table->unsignedBigInteger('bugherd_project_id')->nullable()->after('jira_project_key');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['jira_key', 'bugherd_task_id']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['jira_project_key', 'bugherd_project_id']);
        });
    }
};
