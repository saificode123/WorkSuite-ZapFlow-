<?php

namespace App\Observers;

use App\Events\TaskEvent;
use App\Events\TaskUpdated as EventsTaskUpdated;
use App\Http\Controllers\AccountBaseController;
use App\Models\GoogleCalendarModule;
use App\Models\MentionUser;
use App\Models\Notification;
use App\Models\ProjectTimeLog;
use App\Models\Task;
use App\Models\TaskboardColumn;
use App\Models\TaskUser;
use App\Traits\ProjectProgress;
use App\Models\UniversalSearch;
use App\Models\User;
use App\Services\Google;
use Carbon\Carbon;
use Google\Service\Exception;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventAttendee;
use Google_Service_Calendar_EventDateTime;
use Illuminate\Support\Facades\Config;
use App\Traits\EmployeeActivityTrait;
use App\Helper\Files;
use App\Models\TaskFile;

// --- JIRA INTEGRATION IMPORTS ---
use App\Services\JiraService;
use App\Services\Bugherd;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TaskObserver
{
    use ProjectProgress;
    use EmployeeActivityTrait;

    protected $jiraService;
    protected $bugherdService;

    public function __construct(JiraService $jiraService, Bugherd $bugherdService)
    {
        $this->jiraService = $jiraService;
        $this->bugherdService = $bugherdService;
    }


    public function saving(Task $task)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $task->last_updated_by = user()->id;

            /* Add/Update google calendar event */
            if (!request()->has('repeat') || request()->repeat == 'no' && !is_null($task->due_date)) {
                $task->event_id = $this->googleCalendarEvent($task);
            }
        }
    }

    public function saved(Task $task)
    {
        /* Add/Update google calendar event */
        if (!request()->has('repeat') || request()->repeat == 'no' && !is_null($task->due_date)) {
            $this->googleCalendarEvent($task);
        }
    }

    public function creating(Task $task)
    {
        $task->hash = md5(microtime());

        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                $task->created_by = user()->id;
                $task->added_by = user()->id;
            }

            if (request()->has('board_column_id')) {
                $task->board_column_id = request()->board_column_id;
            } else if (isset(company()->default_task_status)) {
                $task->board_column_id = company()->default_task_status;
            } else {
                $taskBoard = TaskboardColumn::where('slug', 'incomplete')->first();
                $task->board_column_id = $taskBoard->id;
            }
        }

        if (company()) {
            $task->company_id = company()->id;
        }
    }

    public function created(Task $task)
    {
        if (!isRunningInConsoleOrSeeding()) {

            if (user()) {
                self::createEmployeeActivity(user()->id, 'task-created', $task->id, 'task');
            }

            $mentionIds = [];
            $mentionDescriptionMembers = null;
            $unmentionIds = null;
            $unmentionDescriptionMember = null;

            if (request()->mention_user_ids != null || request()->mention_user_ids != '' || request()->has('mention_user_ids')) {

                $task->mentionUser()->sync(request()->mention_user_ids);
                $mentionIds = explode(',', request()->mention_user_ids);
                $mentionDescriptionMembers = User::whereIn('id', $mentionIds)->get();
            }

            if (request()->user_id != null || request()->user_id != '' || request()->has('user_id')) {

                $memberIds = User::whereIn('id',  request()->user_id)->get();
                event(new TaskEvent($task, $memberIds, 'NewTask'));
            }

            if (request()->has('project_id') && request()->project_id != 'all' && request()->project_id != '') {
                if ((request()->mention_user_id) != null || request()->mention_user_id != '' || $mentionIds != null && $mentionIds != '') {

                    event(new TaskEvent($task, $mentionDescriptionMembers, 'TaskMention'));
                    event(new TaskEvent($task, $mentionDescriptionMembers, 'TaskMentionSms'));
                } else {

                    if ($task->project->client_id != null && $task->project->allow_client_notification == 'enable' && $task->project->client->status != 'deactive') {
                        event(new TaskEvent($task, $task->project->client, 'NewClientTask'));
                    }
                }
            } else {

                if ((request()->mention_user_id) != null || request()->mention_user_id != '') {

                    event(new TaskEvent($task, $mentionDescriptionMembers, 'TaskMention'));
                    event(new TaskEvent($task, $mentionDescriptionMembers, 'TaskMentionSms'));
                }
            }

            $log = new AccountBaseController();

            if (\user()) {
                $log->logTaskActivity($task->id, user()->id, 'createActivity', $task->board_column_id);
            }

            if ($task->project_id) {

                // Calculate project progress if enabled
                $log->logProjectActivity($task->project_id, 'messages.newTaskAddedToTheProject');
                $this->calculateProjectProgress($task->project_id);
            }

            // Log search
            $log->logSearchEntry($task->id, $task->heading, 'tasks.edit', 'task');

            // Sync task users
            if (!empty(request()->user_id) && request()->template_id == '') {

                $task->users()->sync(request()->user_id);
            }

            // --- JIRA INTEGRATION START ---
            try {
                // Only create Jira issue if the project has a Jira project key configured
                if ($task->project_id && $task->project && $task->project->jira_project_key) {
                    Log::info("Observer: Task #{$task->id} created in project with Jira key: {$task->project->jira_project_key}");
                    $jiraKey = $this->jiraService->createIssue($task);

                    if ($jiraKey && Schema::hasColumn('tasks', 'jira_key')) {
                        $task->jira_key = $jiraKey;
                        $task->saveQuietly();
                        Log::info("Observer: Jira Key {$jiraKey} saved to Task #{$task->id}");
                    }
                } else {
                    Log::info("Observer: Task #{$task->id} - No Jira project key configured for this project, skipping Jira creation");
                }
            } catch (\Exception $e) {
                Log::error("Observer Jira Create Error for Task #{$task->id}: " . $e->getMessage());
            }
            // --- JIRA INTEGRATION END ---

        }
    }

    public function updating(Task $task)
    {

        $mentionedUser = MentionUser::where('task_id', $task->id)->pluck('user_id');
        $requestMentionIds = explode(',', request()->mention_user_ids);
        $newMention = [];
        $task->mentionUser()->sync(request()->mention_user_ids);

        if ($requestMentionIds != null) {
            foreach ($requestMentionIds as $value) {

                if (($mentionedUser) != null) {

                    if (!in_array($value, json_decode($mentionedUser))) {

                        $newMention[] = $value;
                    }
                } else {

                    $newMention[] = $value;
                }
            }

            $newMentionMembers = User::whereIn('id', $newMention)->get();

            if (!empty($newMention)) {

                event(new TaskEvent($task, $newMentionMembers, 'TaskMention'));
                event(new TaskEvent($task, $newMentionMembers, 'TaskMentionSms'));
            }
        }
    }

    // phpcs:ignore
    public function updated(Task $task)
    {
        $movingTaskId = request()->has('movingTaskId');

        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                self::createEmployeeActivity(user()->id, 'task-updated', $task->id, 'task');
            }

            if ($task->isDirty('board_column_id')) {
                if ($task->boardColumn->slug == 'completed') {
                    $notification = 'TaskCompleted';
                } elseif ($task->boardColumn->slug == 'waiting_approval') {
                    $notification = 'TaskApproval';
                } else {
                    $notification = 'TaskStatusUpdated';
                }

                if ($task->boardColumn->slug == 'waiting_approval') {

                    $admins = User::allAdmins($task->company->id);

                    $users = $task->taskUsers;

                    $projectAdmintask = Task::with('project')
                        ->whereHas('project', function ($query) {
                            $query->whereNotNull('project_admin');
                        })
                        ->get()
                        ->pluck('project.projectAdmin');

                    $admins = $users->merge($admins);

                    if (!$projectAdmintask->isEmpty()) {
                        $admins = $admins->merge($projectAdmintask);
                    }
                } else {
                    $admins = User::allAdmins($task->company->id);
                }

                // send task complete notification
                event(new TaskEvent($task, $admins, $notification));

                if ($task->addedByUser) {
                    $addedByUserRole = $task->addedByUser->roles->pluck('name')->toArray();

                    if (!is_null($task->added_by) && !in_array('client', $addedByUserRole) && !in_array($task->added_by, $admins->pluck('id')->toArray())) {
                        event(new TaskEvent($task, $task->addedByUser, $notification));
                    }
                }

                $taskUser = $task->users->whereNotIn('id', $admins->pluck('id'))->whereNotIn('id', [$task->added_by]);
                event(new TaskEvent($task, $taskUser, $notification));

                $timeLogs = ProjectTimeLog::with('user')->whereNull('end_time')
                    ->where('task_id', $task->id)
                    ->get();

                if ($timeLogs && ($task->boardColumn->slug == 'completed' || $task->boardColumn->slug == 'waiting_approval')) {
                    foreach ($timeLogs as $timeLog) {

                        $timeLog->end_time = now();
                        $timeLog->edited_by_user = (user()) ? user()->id : null;
                        $timeLog->save();

                        /** @phpstan-ignore-next-line */
                        $timeLog->total_hours = ($timeLog->end_time->diff($timeLog->start_time)->format('%d') * 24) + ($timeLog->end_time->diff($timeLog->start_time)->format('%H'));

                        if ($timeLog->total_hours == 0) {
                            /** @phpstan-ignore-next-line */
                            $timeLog->total_hours = round(($timeLog->end_time->diff($timeLog->start_time)->format('%i') / 60), 2);
                        }

                        /** @phpstan-ignore-next-line */
                        $timeLog->total_minutes = ($timeLog->total_hours * 60) + ($timeLog->end_time->diff($timeLog->start_time)->format('%i'));

                        $timeLog->save();

                        if (!is_null($timeLog->activeBreak)) {
                            /** @phpstan-ignore-next-line */
                            $activeBreak = $timeLog->activeBreak;
                            $activeBreak->end_time = $timeLog->end_time;
                            $activeBreak->save();
                        }
                    }
                }

                if ((request()->project_id && request()->project_id != 'all') || (!is_null($task->project_id))) {
                    $project = $task->project;

                    if ($project->client_id != null && $project->allow_client_notification == 'enable' && $project->client->status != 'deactive') {
                        event(new TaskEvent($task, $project->client, 'TaskCompletedClient'));
                    }
                }
            }

            if (request('user_id')) {
                if (($movingTaskId != '' && $task->id == $movingTaskId) || $movingTaskId == '') {
                    event(new TaskEvent($task, $task->users, 'TaskUpdated'));
                }
            }
        }

        /* Add/Update google calendar event */
        if (!request()->has('repeat') || request()->repeat == 'no' && !is_null($task->due_date)) {
            $task->event_id = $this->googleCalendarEvent($task);
        }

        if (pusher_settings()->status == 1 && pusher_settings()->taskboard == 1) {
            Config::set('queue.default', 'sync');
            Config::set('broadcasting.default', 'pusher');
        }

        event(new EventsTaskUpdated());

        if (\user()) {
            if (($movingTaskId != '' && $task->id == $movingTaskId) || $movingTaskId == '') {
                $log = new AccountBaseController();
                $log->logTaskActivity($task->id, user()->id, 'statusActivity', $task->board_column_id);
            }
        }

        if ($task->project_id) {
            if (($movingTaskId != '' && $task->id == $movingTaskId) || $movingTaskId == '') {
                $this->calculateProjectProgress($task->project_id);
            }
        }

        // --- JIRA INTEGRATION START ---
        if ($task->isDirty(['heading', 'description']) && !empty($task->jira_key)) {
            // Check if project still has Jira configured
            if ($task->project && $task->project->jira_project_key) {
                try {
                    Log::info("Observer: Updating Jira Ticket {$task->jira_key} for Task #{$task->id}");
                    $this->jiraService->updateIssue($task->jira_key, $task);
                } catch (\Exception $e) {
                    Log::error("Observer Jira Update Error: " . $e->getMessage());
                }
            } else {
                Log::warning("Observer: Task #{$task->id} has jira_key but project has no jira_project_key configured");
            }
        }
        // --- JIRA INTEGRATION END ---
        // --- JIRA SYNC ---
        $this->syncToJira($task);

        // --- BUGHERD SYNC ---
        $this->syncToBugherd($task);
    }

    /**
     * Sync task updates to Jira
     */
    protected function syncToJira(Task $task)
    {
        if (!$task->project || !$task->project->jira_project_key) {
            return;
        }

        try {
            // If task has jira_key, update it
            if (!empty($task->jira_key)) {
                // Check what fields changed
                if ($task->isDirty(['heading', 'description', 'due_date', 'board_column_id', 'priority'])) {
                    Log::info("Syncing Task #{$task->id} updates to Jira {$task->jira_key}");
                    $this->jiraService->updateIssue($task->jira_key, $task);
                }
            }
            // If no jira_key but project has jira config, create new issue
            else if (Schema::hasColumn('tasks', 'jira_key')) {
                Log::info("Creating Jira issue for Task #{$task->id}");
                $jiraKey = $this->jiraService->createIssue($task);

                if ($jiraKey) {
                    $task->jira_key = $jiraKey;
                    $task->saveQuietly();
                    Log::info("Jira Key {$jiraKey} saved to Task #{$task->id}");
                }
            }
        } catch (\Exception $e) {
            Log::error("Jira Sync Error for Task #{$task->id}: " . $e->getMessage());
        }
    }

    /**
     * Sync task updates to BugHerd
     */
    protected function syncToBugherd(Task $task)
    {
        if (!$task->project || !$task->project->bugherd_project_id) {
            return;
        }

        try {
            $bugherdProjectId = (int) $task->project->bugherd_project_id;

            // Prepare payload
            $payload = $this->prepareBugherdPayload($task);

            // If task has bugherd_task_id, update it
            if (Schema::hasColumn('tasks', 'bugherd_task_id') && !empty($task->bugherd_task_id)) {
                // Check what fields changed
                if ($task->isDirty(['heading', 'description', 'due_date', 'board_column_id', 'priority'])) {
                    Log::info("Syncing Task #{$task->id} updates to BugHerd {$task->bugherd_task_id}");

                    $resp = $this->bugherdService->updateTask(
                        $bugherdProjectId,
                        (int) $task->bugherd_task_id,
                        $payload
                    );

                    Log::info("BugHerd task updated successfully for Task #{$task->id}");
                }
            }
            // If no bugherd_task_id but project has bugherd config, create new task
            else if (Schema::hasColumn('tasks', 'bugherd_task_id')) {
                Log::info("Creating BugHerd task for Task #{$task->id}");

                $resp = $this->bugherdService->createTask($bugherdProjectId, $payload);

                if ($bugherdId = data_get($resp, 'task.id')) {
                    $task->forceFill(['bugherd_task_id' => $bugherdId])->saveQuietly();
                    Log::info("BugHerd task ID {$bugherdId} saved to Task #{$task->id}");
                }
            }
        } catch (\Exception $e) {
            Log::error("BugHerd Sync Error for Task #{$task->id}: " . $e->getMessage());
        }
    }

    /**
     * Prepare BugHerd payload from Task
     */
    protected function prepareBugherdPayload(Task $task): array
    {
        $payload = [
            'description' => $this->formatBugherdDescription($task),
        ];

        // Add due date if present
        if ($task->due_date) {
            $payload['due_at'] = $task->due_date->clone()->utc()->format('Y-m-d\TH:i:s\Z');
        }

        // Map priority (if your app uses priority)
        if ($task->priority) {
            $priorityMap = [
                'high'     => 'critical',
                'medium'   => 'normal',
                'low'      => 'low',
                'critical' => 'critical',
            ];
            $payload['priority'] = $priorityMap[$task->priority] ?? 'normal';
        }

        // Map status from board column
        if ($task->boardColumn) {
            $statusMap = [
                'incomplete'       => 'backlog',
                'to_do'           => 'backlog',
                'in_progress'     => 'doing',
                'doing'           => 'doing',
                'completed'       => 'done',
                'waiting_approval'=> 'doing',
            ];
            $columnSlug = $task->boardColumn->slug;
            $payload['status'] = $statusMap[$columnSlug] ?? 'backlog';
        }

        // Add labels/tags if available
        if ($task->labels && $task->labels->isNotEmpty()) {
            $payload['tag_names'] = $task->labels->pluck('label_name')->filter()->values()->all();
        }

        return $payload;
    }

    /**
     * Format description for BugHerd
     */
    protected function formatBugherdDescription(Task $task): string
    {
        $heading = trim((string) $task->heading);
        $html = (string) ($task->description ?? '');
        $plain = trim(strip_tags($html));

        return trim($heading . (strlen($plain) ? "\n\n{$plain}" : ''));
    }

    public function deleting(Task $task)
    {
        $universalSearches = UniversalSearch::where('searchable_id', $task->id)->where('module_type', 'task')->get();

        if ($universalSearches) {
            foreach ($universalSearches as $universalSearch) {
                UniversalSearch::destroy($universalSearch->id);
            }
        }

        $notifyData = [
            'App\Notifications\NewTask',
            'App\Notifications\TaskUpdated',
            'App\Notifications\TaskComment',
            'App\Notifications\TaskCommentClient',
            'App\Notifications\TaskCompleted',
            'App\Notifications\TaskStatusUpdated',
            'App\Notifications\TaskApproval',
            'App\Notifications\NewClientTask',
            'App\Notifications\TaskCompletedClient',
            'App\Notifications\TaskNote',
            'App\Notifications\TaskNoteClient',
            'App\Notifications\TaskReminder',
            'App\Notifications\TaskUpdatedClient',
            'App\Notifications\SubTaskCreated',
            'App\Notifications\SubTaskCompleted'
        ];

        Notification::whereIn('type', $notifyData)
            ->whereNull('read_at')
            ->where(
                function ($q) use ($task) {
                    $q->where('data', 'like', '{"id":' . $task->id . ',%');
                    $q->orWhere('data', 'like', '%,"task_id":' . $task->id . ',%');
                }
            )->delete();

        /* Start of deleting event from google calendar */
        $google = new Google();
        $googleAccount = company();

        if (company()->google_calendar_status == 'active' && $googleAccount->google_calendar_verification_status == 'verified' && $googleAccount->token) {
            $google->connectUsing($googleAccount->token);
            try {
                if ($task->event_id) {
                    $google->service('Calendar')->events->delete('primary', $task->event_id);
                }
            } catch (Exception $error) {
                if (is_null($error->getErrors())) {
                    // Delete google calendar connection data i.e. token, name, google_id
                    $googleAccount->name = null;
                    $googleAccount->token = null;
                    $googleAccount->google_id = null;
                    $googleAccount->google_calendar_verification_status = 'non_verified';
                    $googleAccount->save();
                }
            }
        }

        $task->files()->each(function ($file) {
            Files::deleteFile($file->hashname, TaskFile::FILE_PATH);
            Files::deleteDirectory(TaskFile::FILE_PATH . '/' . $file->task_id);
            $file->delete();
        });

        /* End of deleting event from google calendar */

        // --- JIRA INTEGRATION START ---
        if (!empty($task->jira_key)) {
            try {
                Log::info("Observer: Deleting Jira Ticket {$task->jira_key} for Task #{$task->id}");
                $this->jiraService->deleteIssue($task->jira_key);
            } catch (\Exception $e) {
                Log::error("Observer Jira Delete Error: " . $e->getMessage());
            }
        }
        // --- JIRA INTEGRATION END ---
    }

    /**
     * @param Task $task
     */
    public function deleted(Task $task)
    {

        if (user()) {
            self::createEmployeeActivity(user()->id, 'task-deleted');
        }

        if (!is_null($task->project_id)) {
            // Calculate project progress if enabled
            $this->calculateProjectProgress($task->project_id);
        }
    }

    protected function googleCalendarEvent($event)
    {
        $module = GoogleCalendarModule::first();
        $googleAccount = company();

        if (!company()) {
            return $event->event_id;
        }

        if (company()->google_calendar_status == 'active' && $googleAccount->google_calendar_verification_status == 'verified' && $googleAccount->token && $module->task_status == 1) {

            $google = new Google();
            $attendiesData = [];

            $attendees = TaskUser::with(['user'])->whereHas(
                'user',
                function ($query) {
                    $query->where('status', 'active')->where('google_calendar_status', true);
                }
            )->where('task_id', $event->id)->get();

            foreach ($attendees as $attend) {
                if (!is_null($attend->user) && !is_null($attend->user->email)) {
                    $attendiesData[] = ['email' => $attend->user->email];
                }
            }

            if ($event->start_date && $event->due_date) {
                $start_date = Carbon::parse($event->start_date)->shiftTimezone($googleAccount->timezone);
                $due_date = Carbon::parse($event->due_date)->shiftTimezone($googleAccount->timezone);

                // Create event
                $google = $google->connectUsing($googleAccount->token);

                $eventData = new Google_Service_Calendar_Event(
                    array(
                        'summary' => $event->heading,
                        'location' => $googleAccount->address,
                        'description' => $event->description,
                        'colorId' => 7,
                        'start' => array(
                            'dateTime' => $start_date,
                            'timeZone' => $googleAccount->timezone,
                        ),
                        'end' => array(
                            'dateTime' => $due_date,
                            'timeZone' => $googleAccount->timezone,
                        ),
                        'attendees' => $attendiesData,
                        'reminders' => array(
                            'useDefault' => false,
                            'overrides' => array(
                                array('method' => 'email', 'minutes' => 24 * 60),
                                array('method' => 'popup', 'minutes' => 10),
                            ),
                        ),
                    )
                );

                try {
                    if ($event->event_id) {
                        $results = $google->service('Calendar')->events->patch('primary', $event->event_id, $eventData);
                    } else {
                        $results = $google->service('Calendar')->events->insert('primary', $eventData);
                    }

                    return $results->id;
                } catch (Exception $error) {
                    if (is_null($error->getErrors())) {
                        // Delete google calendar connection data i.e. token, name, google_id
                        $googleAccount->name = null;
                        $googleAccount->token = null;
                        $googleAccount->google_id = null;
                        $googleAccount->google_calendar_verification_status = 'non_verified';
                        $googleAccount->save();
                    }
                }
            }
        }

        return $event->event_id;
    }

    // Google calendar for multiple events
    protected function googleCalendarEventMulti($eventIds)
    {
        $googleAccount = company();

        if (company()->google_calendar_status == 'active' && $googleAccount->google_calendar_verification_status == 'verified' && $googleAccount->token) {
            $google = new Google();
            $events = Task::whereIn('id', $eventIds)->get();
            $event = $events->first();

            $frq = ['day' => 'DAILY', 'week' => 'WEEKLY', 'month', 'MONTHLY', 'year' => 'YEARLY'];
            $frequency = $frq[$event->repeat_type];

            $eventData = new Google_Service_Calendar_Event();
            $eventData->setSummary($event->heading);
            $eventData->setLocation('');

            $start = new Google_Service_Calendar_EventDateTime();
            $start->setDateTime($event->start_date->toAtomString());
            $start->setTimeZone($googleAccount->timezone);

            $eventData->setStart($start);
            $end = new Google_Service_Calendar_EventDateTime();
            $end->setDateTime($event->due_date->toAtomString());
            $end->setTimeZone($googleAccount->timezone);

            $eventData->setEnd($end);
            /** @phpstan-ignore-next-line */
            $eventData->setRecurrence(array('RRULE:FREQ=' . $frequency . ';INTERVAL=' . $event->repeat_every . ';COUNT=' . $event->repeat_cycles . ';'));

            $attendees = TaskUser::with(['user'])->whereHas(
                'user',
                function ($query) {
                    $query->where('status', 'active')->where('google_calendar_status', true);
                }
            )->where('task_id', $event->id)->get();

            $attendiesData = [];

            foreach ($attendees as $attend) {
                if (!is_null($attend->user) && !is_null($attend->user->email)) {
                    $attendee1 = new Google_Service_Calendar_EventAttendee();
                    $attendee1->setEmail($attend->user->email);
                    $attendiesData[] = $attendee1;
                }
            }

            /** @phpstan-ignore-next-line */
            $eventData->attendees = $attendiesData;

            // Create event
            $google->connectUsing($googleAccount->token);

            try {
                if ($event->event_id) {
                    $results = $google->service('Calendar')->events->patch('primary', $event->event_id, $eventData);
                } else {
                    $results = $google->service('Calendar')->events->insert('primary', $eventData);
                }

                foreach ($events as $event) {
                    $event->event_id = $results->id;
                    $event->save();
                }

                return;
            } catch (Exception $error) {
                if (is_null($error->getErrors())) {
                    // Delete google calendar connection data i.e. token, name, google_id
                    $googleAccount->name = null;
                    $googleAccount->token = null;
                    $googleAccount->google_id = null;
                    $googleAccount->google_calendar_verification_status = 'non_verified';
                    $googleAccount->save();
                }
            }

            foreach ($events as $event) {
                $event->event_id = $event->event_id;
                $event->save();
            }
        }
    }
}
