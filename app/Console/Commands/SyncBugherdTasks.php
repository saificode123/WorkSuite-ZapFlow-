<?php
namespace App\Console\Commands;

use App\Models\Project;
use App\Models\BugherdTask;
use App\Services\Bugherd;
use Illuminate\Console\Command;

class SyncBugherdTasks extends Command
{
    protected $signature = 'bugherd:sync {projectId?}';
    protected $description = 'Sync tasks from BugHerd to local cache';

    public function handle(Bugherd $api)
    {
        $query = Project::query()->whereNotNull('bugherd_project_id');
        if ($pid = $this->argument('projectId')) $query->where('id', $pid);

        $query->chunk(50, function ($projects) use ($api) {
            foreach ($projects as $project) {
                $tasks = $api->listTasks($project->bugherd_project_id);
                foreach ($tasks as $t) {
                    BugherdTask::updateOrCreate(
                        ['project_id' => $project->id, 'bugherd_task_id' => $t['id']],
                        [
                            'status'      => $t['status'] ?? null,
                            'priority'    => $t['priority'] ?? null,
                            'description' => $t['description'] ?? null,
                            'tags'        => $t['tag_names'] ?? [],
                            'raw'         => $t,
                            'synced_at'   => now(),
                        ]
                    );
                }
            }
        });

        $this->info('Sync completed.');
    }
}
