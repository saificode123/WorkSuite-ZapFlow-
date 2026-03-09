<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\BugherdTask;
use App\Http\Requests\StoreBugherdTask;
use App\Services\Bugherd;
use Illuminate\Http\JsonResponse;

class ProjectBugherdController extends Controller
{
    public function index(Project $project, Bugherd $bugherd): JsonResponse
    {
        abort_unless($project->bugherd_project_id, 404, 'BugHerd not linked.');

        // Option A: live list from BugHerd (no cache)
        $tasks = $bugherd->listTasks($project->bugherd_project_id);

        // Option B (uncomment): use local cache
        // $tasks = $project->bugherdTasks()->latest()->paginate(50);

        return response()->json(['ok' => true, 'tasks' => $tasks]);
    }

    public function store(StoreBugherdTask $request, Project $project, Bugherd $bugherd): JsonResponse
    {
        abort_unless($project->bugherd_project_id, 422, 'BugHerd not linked.');

        $created = $bugherd->createTask($project->bugherd_project_id, $request->validated());

        // Optional: persist a local record
        BugherdTask::updateOrCreate(
            ['project_id' => $project->id, 'bugherd_task_id' => $created['id']],
            [
                'status'      => $created['status'] ?? null,
                'priority'    => $created['priority'] ?? null,
                'description' => $created['description'] ?? null,
                'tags'        => $created['tag_names'] ?? [],
                'raw'         => $created,
                'synced_at'   => now(),
            ]
        );

        return response()->json(['ok' => true, 'task' => $created], 201);
    }
}
