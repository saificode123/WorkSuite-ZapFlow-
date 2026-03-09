<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JiraService
{
    protected $host;
    protected $user;
    protected $token;

    public function __construct()
    {
        $this->host  = rtrim(env('JIRA_HOST'), '/');
        $this->user  = env('JIRA_USER');
        $this->token = env('JIRA_API_KEY');
    }

    /**
     * Create a new Issue in Jira for the given Task.
     */
    public function createIssue(Task $task)
    {
        if ($this->isConfigMissing()) {
            return null;
        }

        $projectKey = $task->project->jira_project_key ?? null;

        if (empty($projectKey)) {
            Log::warning("Jira: Task #{$task->id} - Project has no Jira project key configured.");
            return null;
        }

        $endpoint = $this->host . '/rest/api/2/issue';
        $payload  = $this->preparePayload($task, $projectKey);

        try {
            $response = Http::withBasicAuth($this->user, $this->token)
                ->withHeaders([
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, $payload);

            if ($response->successful()) {
                $data    = $response->json();
                $jiraKey = $data['key'] ?? null;
                Log::info("Jira: Issue created successfully. Key: {$jiraKey} for Task #{$task->id} in Project {$projectKey}");
                return $jiraKey;
            }

            Log::error("Jira Create Failed ({$response->status()}): " . $response->body());

        } catch (\Exception $e) {
            Log::error("Jira Create Exception: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Update an existing Jira issue with full sync support.
     */
    public function updateIssue($jiraKey, Task $task)
    {
        if ($this->isConfigMissing() || empty($jiraKey)) {
            return;
        }

        $endpoint = $this->host . '/rest/api/2/issue/' . $jiraKey;

        $payload = [
            'fields' => [
                'summary'     => $task->heading,
                'description' => $this->formatDescription($task),
            ],
        ];

        // Update priority if changed
        if ($task->priority) {
            $priorityMap = [
                'critical' => 'Highest',
                'high'     => 'High',
                'medium'   => 'Medium',
                'low'      => 'Low',
            ];

            if (isset($priorityMap[$task->priority])) {
                $payload['fields']['priority'] = ['name' => $priorityMap[$task->priority]];
            }
        }

        // Update due date if changed
        if ($task->due_date) {
            $payload['fields']['duedate'] = $task->due_date->format('Y-m-d');
        }

        try {
            $response = Http::withBasicAuth($this->user, $this->token)
                ->withHeaders([
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->put($endpoint, $payload);

            if ($response->successful()) {
                Log::info("Jira: Updated Issue {$jiraKey} for Task #{$task->id}");

                // Update status/transition if board column changed
                if ($task->isDirty('board_column_id')) {
                    $this->updateIssueStatus($jiraKey, $task);
                }
            } else {
                Log::error("Jira Update Failed: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Jira Update Exception: " . $e->getMessage());
        }
    }

    /**
     * Update Jira issue status via transitions
     */
    protected function updateIssueStatus($jiraKey, Task $task)
    {
        if (!$task->boardColumn) {
            return;
        }

        // Map your board columns to Jira transition names
        $statusMap = [
            'incomplete'       => 'To Do',
            'to_do'           => 'To Do',
            'in_progress'     => 'In Progress',
            'doing'           => 'In Progress',
            'completed'       => 'Done',
            'waiting_approval'=> 'In Review', // Adjust based on your Jira workflow
        ];

        $columnSlug = $task->boardColumn->slug;
        $targetStatus = $statusMap[$columnSlug] ?? null;

        if (!$targetStatus) {
            Log::warning("Jira: No status mapping for column '{$columnSlug}'");
            return;
        }

        try {
            // Get available transitions
            $transitionsEndpoint = $this->host . '/rest/api/2/issue/' . $jiraKey . '/transitions';
            $response = Http::withBasicAuth($this->user, $this->token)->get($transitionsEndpoint);

            if ($response->successful()) {
                $transitions = $response->json()['transitions'] ?? [];

                // Find the transition ID that matches our target status
                $transitionId = null;
                foreach ($transitions as $transition) {
                    if ($transition['to']['name'] === $targetStatus) {
                        $transitionId = $transition['id'];
                        break;
                    }
                }

                if ($transitionId) {
                    // Execute the transition
                    $transitionResponse = Http::withBasicAuth($this->user, $this->token)
                        ->withHeaders([
                            'Accept'       => 'application/json',
                            'Content-Type' => 'application/json',
                        ])
                        ->post($transitionsEndpoint, [
                            'transition' => ['id' => $transitionId]
                        ]);

                    if ($transitionResponse->successful()) {
                        Log::info("Jira: Transitioned {$jiraKey} to '{$targetStatus}'");
                    } else {
                        Log::error("Jira: Transition failed: " . $transitionResponse->body());
                    }
                } else {
                    Log::warning("Jira: No transition found to '{$targetStatus}' for {$jiraKey}");
                }
            }
        } catch (\Exception $e) {
            Log::error("Jira: Status update exception: " . $e->getMessage());
        }
    }

    /**
     * Delete an existing Jira issue.
     */
    public function deleteIssue($jiraKey)
    {
        if ($this->isConfigMissing() || empty($jiraKey)) {
            return;
        }

        $endpoint = $this->host . '/rest/api/2/issue/' . $jiraKey;

        try {
            $response = Http::withBasicAuth($this->user, $this->token)->delete($endpoint);

            if ($response->successful() || $response->status() === 204) {
                Log::info("Jira: Deleted Issue {$jiraKey}");
            } else {
                Log::error("Jira Delete Failed: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Jira Delete Exception: " . $e->getMessage());
        }
    }

    protected function isConfigMissing()
    {
        if (empty($this->host) || empty($this->user) || empty($this->token)) {
            Log::error('Jira Error: Missing .env configuration (JIRA_HOST, JIRA_USER, JIRA_API_KEY).');
            return true;
        }
        return false;
    }

    protected function preparePayload(Task $task, $projectKey)
    {
        return [
            'fields' => [
                'project'     => ['key' => $projectKey],
                'summary'     => $task->heading,
                'description' => $this->formatDescription($task),
                'issuetype'   => ['name' => 'Task'],
            ],
        ];
    }

    protected function formatDescription(Task $task)
    {
        $cleanDesc = strip_tags($task->description) ?: 'No description provided.';
        $taskLink  = route('tasks.show', $task->id);
        return $cleanDesc . "\n\n----------------\nView in Worksuite: " . $taskLink;
    }
}
