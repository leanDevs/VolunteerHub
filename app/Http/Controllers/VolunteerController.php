<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Skill;
use App\Models\Event;
use App\Models\Task;
use App\Models\Assignment;
use App\Models\Certificate;
use App\Models\ChatbotRule;
use App\Models\ChatbotResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VolunteerController extends Controller
{
    /**
     * Display the Volunteer Dashboard.
     */
    public function dashboard()
    {
        $volunteer = Auth::user();
        $mySkills = $volunteer->skills;

        // Get all available skills for registration dropdown
        $mySkillIds = $mySkills->pluck('id')->toArray();
        $availableSkills = Skill::whereNotIn('id', $mySkillIds)->get();

        // Get volunteer's task assignments (tasks they are assigned to)
        $assignments = Assignment::where('user_id', $volunteer->id)
            ->with(['event', 'task.skills'])
            ->get();

        // Get issued certificates
        $certificates = Certificate::where('user_id', $volunteer->id)
            ->with('event')
            ->orderBy('issued_at', 'desc')
            ->get();

        // AI Recommended Skills to Learn:
        // Skills needed in upcoming events/tasks that this volunteer doesn't have yet
        $recommendedSkills = [];
        $upcomingTasks = Task::whereIn('status', ['pending', 'in_progress'])
            ->with(['skills', 'event'])
            ->whereHas('event', function ($query) {
                $query->where('start_time', '>', Carbon::now());
            })
            ->get();

        $neededSkillWeights = [];
        foreach ($upcomingTasks as $task) {
            foreach ($task->skills as $skill) {
                if (!in_array($skill->id, $mySkillIds)) {
                    if (!isset($neededSkillWeights[$skill->id])) {
                        $neededSkillWeights[$skill->id] = [
                            'skill' => $skill,
                            'count' => 0,
                            'event_title' => $task->event->title
                        ];
                    }
                    $neededSkillWeights[$skill->id]['count']++;
                }
            }
        }

        // Sort by count descending and take top 3
        uasort($neededSkillWeights, fn($a, $b) => $b['count'] <=> $a['count']);
        $recommendedSkills = array_slice($neededSkillWeights, 0, 3);

        return view('volunteer.dashboard', compact(
            'volunteer',
            'mySkills',
            'availableSkills',
            'assignments',
            'certificates',
            'recommendedSkills'
        ));
    }

    /**
     * Add a skill to the volunteer's profile.
     */
    public function addSkill(Request $request)
    {
        $volunteer = Auth::user();

        if ($request->has('skill_id')) {
            $request->validate(['skill_id' => 'required|exists:skills,id']);
            $volunteer->skills()->syncWithoutDetaching([$request->skill_id]);
        } elseif ($request->has('skill_name')) {
            $request->validate(['skill_name' => 'required|string|max:255']);
            $skill = Skill::firstOrCreate([
                'name' => Str::title(trim($request->skill_name))
            ]);
            $volunteer->skills()->syncWithoutDetaching([$skill->id]);
        }

        return redirect()->route('volunteer.dashboard')
            ->with('success', 'Profile skills updated successfully.');
    }

    /**
     * Remove a skill from the volunteer's profile.
     */
    public function removeSkill($id)
    {
        $volunteer = Auth::user();
        $volunteer->skills()->detach($id);

        return redirect()->route('volunteer.dashboard')
            ->with('success', 'Skill removed from profile.');
    }

    /**
     * Toggle availability status.
     */
    public function toggleAvailability(Request $request)
    {
        $request->validate([
            'availability' => 'required|in:active,inactive'
        ]);

        $volunteer = User::findOrFail(Auth::id());
        $volunteer->availability = $request->availability;
        $volunteer->save();

        $statusStr = $request->availability === 'active' ? 'Available to Volunteer' : 'On Hold';
        return redirect()->route('volunteer.dashboard')
            ->with('success', "Availability status updated to: {$statusStr}.");
    }

    /**
     * Mark assignment/task completed and issue certificate.
     */
    public function completeTask($id)
    {
        $assignment = Assignment::where('user_id', Auth::id())
            ->where('id', $id)
            ->with('event')
            ->firstOrFail();

        // Check if assignment is already completed
        if ($assignment->status === 'completed') {
            return redirect()->route('volunteer.dashboard')
                ->with('error', 'This duty has already been completed.');
        }

        // Check if assignment is approved
        if ($assignment->status !== 'approved') {
            return redirect()->route('volunteer.dashboard')
                ->with('error', 'You cannot complete a duty that has not been approved.');
        }

        // 1. Mark assignment as completed
        $assignment->status = 'completed';
        $assignment->hours_logged = 4.00; // Mock 4 hours logged
        $assignment->save();

        // 2. Mark the associated task as completed
        if ($assignment->task) {
            $assignment->task->status = 'completed';
            $assignment->task->save();
        }

        // 3. Auto-issue certificate
        $certCode = 'JCI-WENSIES-' . strtoupper(Str::random(4)) . '-' . time();
        Certificate::create([
            'user_id' => Auth::id(),
            'event_id' => $assignment->event_id,
            'certificate_code' => $certCode,
            'issued_at' => Carbon::now(),
            'file_path' => 'certificates/' . $certCode . '.pdf'
        ]);

        // 4. Create Notification
        DB::table('notifications')->insert([
            'id' => Str::uuid(),
            'type' => 'App\\Notifications\\GenericNotification',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => Auth::id(),
            'data' => json_encode([
                'title' => 'Certificate Issued',
                'message' => 'Congratulations! You earned a Certificate of Appreciation for "' . ($assignment->event ? $assignment->event->title : 'JCI Event') . '".',
                'icon' => 'fa-award',
            ]),
            'read_at' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return redirect()->route('volunteer.dashboard')
            ->with('success', "Task marked completed! Certificate of appreciation issued.");
    }

    /**
     * View/Print generated certificate.
     */
    public function downloadCertificate($id)
    {
        $certificate = Certificate::where('user_id', Auth::id())
            ->where('id', $id)
            ->with('event')
            ->firstOrFail();

        return view('volunteer.certificate', compact('certificate'));
    }

    /**
     * Process Chatbot Question for Volunteer.
     */
    public function askChatbot(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500'
        ]);

        $volunteer = Auth::user();
        $message = trim($request->message);
        $lowerMsg = strtolower($message);

        $rules = ChatbotRule::all();
        $matchedRule = null;

        foreach ($rules as $rule) {
            $keyword = strtolower($rule->keyword);
            if (!empty($keyword) && str_contains($lowerMsg, $keyword)) {
                $matchedRule = $rule;
                break;
            }
        }

        if ($matchedRule) {
            $response = $matchedRule->response;
            $intent = $matchedRule->keyword;
            $confidence = 0.95;
        } else {
            // Dynamic Contextual Responses based on user intent
            if (str_contains($lowerMsg, 'skill') || str_contains($lowerMsg, 'learn')) {
                $skillCount = $volunteer->skills()->count();
                $skillList = $volunteer->skills()->pluck('name')->join(', ');
                $response = "You currently have {$skillCount} skill(s) on record" . ($skillList ? " ({$skillList})" : "") . ". You can add new skills or check recommended skills directly from your dashboard!";
                $intent = 'skills_info';
                $confidence = 0.85;
            } elseif (str_contains($lowerMsg, 'duty') || str_contains($lowerMsg, 'task') || str_contains($lowerMsg, 'assignment')) {
                $assignmentCount = Assignment::where('user_id', $volunteer->id)->count();
                $pendingCount = Assignment::where('user_id', $volunteer->id)->where('status', 'approved')->count();
                $response = "You have {$assignmentCount} total assignment(s) logged, with {$pendingCount} active duty task(s) ready to perform. Click 'Mark Completed' on your dashboard when done!";
                $intent = 'tasks_info';
                $confidence = 0.85;
            } elseif (str_contains($lowerMsg, 'certific') || str_contains($lowerMsg, 'cert') || str_contains($lowerMsg, 'award')) {
                $certCount = Certificate::where('user_id', $volunteer->id)->count();
                $response = "You have earned {$certCount} Certificate(s) of Appreciation. Official certificates are automatically generated as print-ready PDFs when you mark completed duties.";
                $intent = 'certificates_info';
                $confidence = 0.85;
            } elseif (str_contains($lowerMsg, 'hour') || str_contains($lowerMsg, 'time') || str_contains($lowerMsg, 'log')) {
                $completedCount = Assignment::where('user_id', $volunteer->id)->where('status', 'completed')->count();
                $loggedHours = $completedCount * 4.0;
                $response = "You have logged approximately {$loggedHours} volunteer hours across completed community projects. Keep up the great work!";
                $intent = 'hours_info';
                $confidence = 0.85;
            } elseif (str_contains($lowerMsg, 'jci') || str_contains($lowerMsg, 'surigao') || str_contains($lowerMsg, 'wensie') || str_contains($lowerMsg, 'about')) {
                $response = "JCI Surigao Wensies is a premier socio-civic leadership chapter in Surigao City dedicated to community development, environmental protection, and empowering local leaders.";
                $intent = 'jci_info';
                $confidence = 0.90;
            } else {
                $response = "Hello! I am your JCI VolunteerHub Assistant. I can help you check your certificates, task duties, skills, or answer questions about JCI Surigao Wensies projects. Try asking: 'How do I get certificates?' or 'What are my current tasks?'";
                $intent = 'general_faq';
                $confidence = 0.60;
            }
        }

        // Save conversation history to database
        $chatLog = ChatbotResponse::create([
            'user_id' => $volunteer->id,
            'message' => $message,
            'response' => $response,
            'intent' => $intent,
            'confidence' => $confidence,
        ]);

        return response()->json([
            'success' => true,
            'message' => $chatLog->message,
            'response' => $chatLog->response,
            'intent' => $chatLog->intent,
            'time' => $chatLog->created_at->format('g:i A'),
        ]);
    }

    /**
     * Retrieve Chatbot Conversation History for Volunteer.
     */
    public function chatbotHistory()
    {
        $history = ChatbotResponse::where('user_id', Auth::id())
            ->orderBy('created_at', 'asc')
            ->take(30)
            ->get()
            ->map(function ($item) {
                return [
                    'message' => $item->message,
                    'response' => $item->response,
                    'intent' => $item->intent,
                    'time' => $item->created_at->format('g:i A'),
                ];
            });

        return response()->json([
            'success' => true,
            'history' => $history
        ]);
    }
}
