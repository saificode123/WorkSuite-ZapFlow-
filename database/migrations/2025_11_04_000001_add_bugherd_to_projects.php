<?php
// database/migrations/2025_11_04_000001_add_bugherd_to_projects.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('bugherd_project_id')->nullable()->index();
        });
    }
    public function down(): void {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('bugherd_project_id');
        });
    }
};
