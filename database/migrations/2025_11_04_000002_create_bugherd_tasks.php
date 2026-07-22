<?php
// database/migrations/2025_11_04_000002_create_bugherd_tasks.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bugherd_tasks', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('project_id')->unsigned()->nullable();
            $t->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $t->unsignedBigInteger('bugherd_task_id')->index();     // remote id
            $t->string('status')->nullable();
            $t->string('priority')->nullable();
            $t->string('description', 10000)->nullable();
            $t->json('tags')->nullable();
            $t->json('raw')->nullable();           // full remote payload
            $t->timestamp('synced_at')->nullable();
            $t->timestamps();
            $t->unique(['project_id','bugherd_task_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('bugherd_tasks');
    }
};

