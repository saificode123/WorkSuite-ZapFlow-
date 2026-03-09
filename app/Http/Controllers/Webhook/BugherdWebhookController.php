<?php
namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\BugherdTask;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class BugherdWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Simple token check (webhook URL: /api/webhooks/bugherd?token=xxx)
        abort_unless($request->query('token') === config('app.bugherd_webhook_token', env('BUGHERD_WEBHOOK_TOKEN')), 401);

        $event = $request->input('event');          // e.g., "task.created", "task.updated"
        $data  = $request->input('task') ?? $request->input('data.task');

        if (!$data || empty($data['id']) || empty($data['project_id'])) {
            return response()->json(['ok' => true]); // ignore unknown payloads
        }

        // map BugHerd project_id -> local project
        $project = Project::where('bugherd_project_id', $data['project_id'])->first();
        if (!$project) return response()->json(['ok' => true]);

        BugherdTask::updateOrCreate(
            ['project_id' => $project->id, 'bugherd_task_id' => $data['id']],
            [
                'status'      => Arr::get($data, 'status'),
                'priority'    => Arr::get($data, 'priority'),
                'description' => Arr::get($data, 'description'),
                'tags'        => Arr::get($data, 'tag_names', []),
                'raw'         => $data,
                'synced_at'   => now(),
            ]
        );

        return response()->json(['ok' => true], Response::HTTP_OK);
    }
}
