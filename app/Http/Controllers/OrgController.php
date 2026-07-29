<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use App\Models\Task;
use App\Models\Skill;
use App\Models\Assignment;
use App\Models\RecordArchive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrgController extends Controller
{
    /**
     * Display the Organization Dashboard.
     */
    public function dashboard(Request $request)
    {
        $orgId = Auth::id();

        $myEventsCount = Event::where('organization_id', $orgId)->count();

        // Count unique volunteers assigned to tasks/events of this organization
        $eventIds = Event::where('organization_id', $orgId)->pluck('id')->toArray();
        $assignedVolunteersCount = Assignment::whereIn('event_id', $eventIds)
            ->distinct('user_id')
            ->count('user_id');

        $events = Event::where('organization_id', $orgId)
            ->with(['tasks.skills', 'assignments.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $complianceDocuments = RecordArchive::where('table_name', 'compliance_documents')
            ->where('archived_by', $orgId)
            ->orderBy('id', 'desc')
            ->get();

        // Tasks list for the dropdown in the Skill Engine (only pending/in_progress tasks)
        $allOrgTasks = Task::whereIn('event_id', $eventIds)
            ->whereIn('status', ['pending', 'in_progress'])
            ->with('skills')
            ->get();

        // Run Skill Engine if a task is selected
        $selectedTaskId = $request->query('task_id');
        if (!$selectedTaskId && $allOrgTasks->count() > 0) {
            $selectedTaskId = $allOrgTasks->first()->id;
        }

        $selectedTask = null;
        $skillMatches = [];

        if ($selectedTaskId) {
            $selectedTask = Task::with('skills')->find($selectedTaskId);
            if ($selectedTask) {
                $requiredSkills = $selectedTask->skills;
                $requiredSkillIds = $requiredSkills->pluck('id')->toArray();

                if (count($requiredSkillIds) > 0) {
                    $volunteers = User::where('role', 'volunteer')
                        ->where('status', 'approved')
                        ->with('skills')
                        ->get();

                    foreach ($volunteers as $vol) {
                        $volSkillIds = $vol->skills->pluck('id')->toArray();
                        $matchingSkills = array_intersect($requiredSkillIds, $volSkillIds);
                        $score = round((count($matchingSkills) / count($requiredSkillIds)) * 100);

                        $skillMatches[] = [
                            'volunteer' => $vol,
                            'score' => $score,
                            'matched_skills' => $requiredSkills->filter(fn($sk) => in_array($sk->id, $volSkillIds)),
                        ];
                    }

                    // Sort by score descending, then by volunteer name
                    usort($skillMatches, function ($a, $b) {
                        if ($b['score'] === $a['score']) {
                            return strcmp($a['volunteer']->name, $b['volunteer']->name);
                        }
                        return $b['score'] <=> $a['score'];
                    });
                }
            }
        }

        // Fetch all available skills to display when creating tasks
        $skills = Skill::all();

        return view('org.dashboard', compact(
            'myEventsCount',
            'assignedVolunteersCount',
            'events',
            'complianceDocuments',
            'allOrgTasks',
            'selectedTaskId',
            'selectedTask',
            'skillMatches',
            'skills'
        ));
    }

    /**
     * Launch a new Event and define initial tasks.
     */
    public function storeEvent(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'capacity' => ['nullable', 'integer', 'min:1'],

            // Task inputs
            'task_title' => ['nullable', 'array'],
            'task_title.*' => ['required', 'string', 'max:255'],
            'task_priority' => ['nullable', 'array'],
            'task_priority.*' => ['required', 'in:low,medium,high'],
            'task_skills' => ['nullable', 'array'], // key is task index, value is array of skill IDs
        ]);

        $event = Event::create([
            'organization_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'start_time' => Carbon::parse($request->start_time),
            'end_time' => Carbon::parse($request->end_time),
            'capacity' => $request->capacity,
            'status' => 'published', // Automatically published for prototype simplicity
        ]);

        // Process associated tasks
        if ($request->has('task_title')) {
            foreach ($request->task_title as $index => $taskTitle) {
                $priority = $request->task_priority[$index] ?? 'medium';
                $task = Task::create([
                    'event_id' => $event->id,
                    'title' => $taskTitle,
                    'description' => 'Initial event setup task.',
                    'priority' => $priority,
                    'status' => 'pending',
                    'due_date' => $event->start_time,
                ]);

                // Bind skills
                if ($request->has("task_skills.{$index}")) {
                    $task->skills()->attach($request->task_skills[$index]);
                }
            }
        }

        return redirect()->route('org.dashboard')
            ->with('success', "Event '{$event->title}' has been successfully launched.");
    }

    /**
     * Simulate upload of a compliance document.
     */
    public function uploadDocument(Request $request)
    {
        $request->validate([
            'document_file' => ['required', 'file', 'mimes:pdf,docx,xlsx', 'max:10240'], // 10MB limit
        ]);

        $file = $request->file('document_file');
        $filename = $file->getClientOriginalName();
        $sizeBytes = $file->getSize();

        // Convert size to human readable
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $sizeBytes > 0 ? floor(log($sizeBytes, 1024)) : 0;
        $sizeStr = number_format($sizeBytes / pow(1024, $power), 2) . ' ' . $units[$power];

        // Simulate save
        // We will store it in the public/uploads folder in real life, but here we just record it in database
        $simulatedPath = 'uploads/compliance/' . time() . '_' . $filename;

        // Save to record_archives
        RecordArchive::create([
            'table_name' => 'compliance_documents',
            'record_id' => time(), // simulated record id
            'original_data' => [
                'filename' => $filename,
                'size' => $sizeStr,
                'uploaded_at' => Carbon::now()->toDateString(),
                'file_path' => $simulatedPath,
            ],
            'archived_by' => Auth::id(),
            'reason' => 'Multi-year compliance audit upload (Simulated).'
        ]);

        // Create notification for admins
        $orgName = Auth::user()->name;
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            DB::table('notifications')->insert([
                'id' => Str::uuid(),
                'type' => 'App\\Notifications\\GenericNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $admin->id,
                'data' => json_encode([
                    'title' => 'Compliance Doc Uploaded',
                    'message' => "{$orgName} uploaded a new compliance document: '{$filename}'.",
                    'icon' => 'fa-file-shield',
                ]),
                'read_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        return redirect()->route('org.dashboard')
            ->with('success', "Document '{$filename}' uploaded and archived successfully.");
    }
}
