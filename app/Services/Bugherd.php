<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

class Bugherd
{
    public function __construct(
        protected string $base = '',
        protected string $key  = ''
    ) {
        $this->base = rtrim(config('services.bugherd.base'), '/');
        $this->key  = config('services.bugherd.key');
    }

    protected function client()
    {
        return Http::asJson()
            ->acceptJson()
            ->withBasicAuth($this->key, 'x'); // password can be any char
    }

    /** List tasks for a BugHerd project (with simple paging passthrough) */
    public function listTasks(int $bugherdProjectId, array $params = []): array
    {
        $resp = $this->client()->get("{$this->base}/projects/{$bugherdProjectId}/tasks.json", $params);
        $resp->throw();
        return $resp->json(); // array of tasks
    }

    /** Create a task in a specific BugHerd project */
    public function createTask(int $bugherdProjectId, array $task): array
    {
        $body = ['task' => $task];
        $resp = $this->client()->post("{$this->base}/projects/{$bugherdProjectId}/tasks.json", $body);
        $resp->throw();
        return $resp->json();
    }

    /** Fetch a single task */
    public function getTask(int $bugherdProjectId, int $bugherdTaskId): array
    {
        $resp = $this->client()->get("{$this->base}/projects/{$bugherdProjectId}/tasks/{$bugherdTaskId}.json");
        $resp->throw();
        return $resp->json();
    }

    /** Update an existing BugHerd task */
    public function updateTask(int $bugherdProjectId, int $bugherdTaskId, array $task): array
    {
        $body = ['task' => $task];
        $resp = $this->client()->put("{$this->base}/projects/{$bugherdProjectId}/tasks/{$bugherdTaskId}.json", $body);
        $resp->throw();
        return $resp->json();
    }

    /** Delete a BugHerd task */
    public function deleteTask(int $bugherdProjectId, int $bugherdTaskId): bool
    {
        $resp = $this->client()->delete("{$this->base}/projects/{$bugherdProjectId}/tasks/{$bugherdTaskId}.json");
        $resp->throw();
        return $resp->successful();
    }
}
