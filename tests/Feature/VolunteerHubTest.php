<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use App\Models\Task;
use App\Models\Assignment;
use App\Models\Certificate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VolunteerHubTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test role-based dashboard access redirection for authenticated users.
     */
    public function test_authenticated_dashboard_redirections(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'approved',
        ]);
        $response = $this->actingAs($admin)->get('/');
        $response->assertRedirect(route('admin.dashboard'));

        $org = User::factory()->create([
            'role' => 'organization',
            'status' => 'approved',
        ]);
        $response = $this->actingAs($org)->get('/');
        $response->assertRedirect(route('org.dashboard'));

        $volunteer = User::factory()->create([
            'role' => 'volunteer',
            'status' => 'approved',
        ]);
        $response = $this->actingAs($volunteer)->get('/');
        $response->assertRedirect(route('volunteer.dashboard'));
    }

    /**
     * Test that pending and rejected organizations cannot access dashboards.
     */
    public function test_inactive_organization_cannot_access_dashboard(): void
    {
        $orgPending = User::factory()->create([
            'role' => 'organization',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($orgPending)->get(route('org.dashboard'));
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);

        $orgRejected = User::factory()->create([
            'role' => 'organization',
            'status' => 'rejected',
        ]);

        $response = $this->actingAs($orgRejected)->get(route('org.dashboard'));
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Test that pending/rejected volunteers are also restricted.
     */
    public function test_inactive_volunteer_cannot_access_dashboard(): void
    {
        $volPending = User::factory()->create([
            'role' => 'volunteer',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($volPending)->get(route('volunteer.dashboard'));
        $response->assertRedirect(route('login'));

        $volRejected = User::factory()->create([
            'role' => 'volunteer',
            'status' => 'rejected',
        ]);

        $response = $this->actingAs($volRejected)->get(route('volunteer.dashboard'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test that a volunteer cannot mark an assignment complete if it is not approved.
     */
    public function test_volunteer_cannot_complete_unapproved_task(): void
    {
        $volunteer = User::factory()->create([
            'role' => 'volunteer',
            'status' => 'approved',
        ]);

        $org = User::factory()->create([
            'role' => 'organization',
            'status' => 'approved',
        ]);

        $event = Event::create([
            'organization_id' => $org->id,
            'title' => 'Test Event',
            'description' => 'Test Description',
            'location' => 'Test Location',
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHours(2),
        ]);

        $task = Task::create([
            'event_id' => $event->id,
            'title' => 'Test Task',
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        $assignment = Assignment::create([
            'user_id' => $volunteer->id,
            'event_id' => $event->id,
            'task_id' => $task->id,
            'status' => 'pending', // NOT approved
        ]);

        $response = $this->actingAs($volunteer)->post(route('volunteer.tasks.complete', $assignment->id));
        $response->assertRedirect(route('volunteer.dashboard'));
        $response->assertSessionHas('error', 'You cannot complete a duty that has not been approved.');

        $this->assertEquals('pending', $assignment->fresh()->status);
        $this->assertEquals(0, Certificate::count());
    }

    /**
     * Test that a volunteer cannot complete an already completed assignment (no duplicate certificates).
     */
    public function test_volunteer_cannot_duplicate_task_completion(): void
    {
        $volunteer = User::factory()->create([
            'role' => 'volunteer',
            'status' => 'approved',
        ]);

        $org = User::factory()->create([
            'role' => 'organization',
            'status' => 'approved',
        ]);

        $event = Event::create([
            'organization_id' => $org->id,
            'title' => 'Test Event',
            'description' => 'Test Description',
            'location' => 'Test Location',
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHours(2),
        ]);

        $task = Task::create([
            'event_id' => $event->id,
            'title' => 'Test Task',
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        $assignment = Assignment::create([
            'user_id' => $volunteer->id,
            'event_id' => $event->id,
            'task_id' => $task->id,
            'status' => 'approved',
        ]);

        // Complete the first time (should succeed)
        $response1 = $this->actingAs($volunteer)->post(route('volunteer.tasks.complete', $assignment->id));
        $response1->assertRedirect(route('volunteer.dashboard'));
        $response1->assertSessionHas('success');
        
        $this->assertEquals('completed', $assignment->fresh()->status);
        $this->assertEquals(1, Certificate::count());

        // Attempt to complete the second time (should fail/redirect with error)
        $response2 = $this->actingAs($volunteer)->post(route('volunteer.tasks.complete', $assignment->id));
        $response2->assertRedirect(route('volunteer.dashboard'));
        $response2->assertSessionHas('error', 'This duty has already been completed.');

        $this->assertEquals(1, Certificate::count()); // Still 1 certificate, not 2
    }

    /**
     * Test notification read status update.
     */
    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create([
            'role' => 'volunteer',
            'status' => 'approved',
        ]);

        $notificationId = \Illuminate\Support\Str::uuid()->toString();
        \Illuminate\Support\Facades\DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => 'App\\Notifications\\GenericNotification',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $user->id,
            'data' => json_encode(['title' => 'Test Notification']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('notifications.read', $notificationId));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $notification = \Illuminate\Support\Facades\DB::table('notifications')
            ->where('id', $notificationId)
            ->first();
        $this->assertNotNull($notification->read_at);
    }

    /**
     * Test notification is created when a task is completed.
     */
    public function test_notification_is_created_on_task_completion(): void
    {
        $volunteer = User::factory()->create([
            'role' => 'volunteer',
            'status' => 'approved',
        ]);

        $org = User::factory()->create([
            'role' => 'organization',
            'status' => 'approved',
        ]);

        $event = Event::create([
            'organization_id' => $org->id,
            'title' => 'Test Event',
            'description' => 'Test Description',
            'location' => 'Test Location',
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addHours(2),
        ]);

        $task = Task::create([
            'event_id' => $event->id,
            'title' => 'Test Task',
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        $assignment = Assignment::create([
            'user_id' => $volunteer->id,
            'event_id' => $event->id,
            'task_id' => $task->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($volunteer)->post(route('volunteer.tasks.complete', $assignment->id));
        $response->assertRedirect(route('volunteer.dashboard'));

        $this->assertEquals(1, \Illuminate\Support\Facades\DB::table('notifications')
            ->where('notifiable_id', $volunteer->id)
            ->count());
    }

    /**
     * Test notification is created for admin when an organization uploads a document.
     */
    public function test_admin_is_notified_on_org_document_upload(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'approved',
        ]);

        $org = User::factory()->create([
            'role' => 'organization',
            'status' => 'approved',
        ]);

        // Mock upload file
        $file = \Illuminate\Http\UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($org)->post(route('org.documents.store'), [
            'document_file' => $file
        ]);

        $response->assertRedirect(route('org.dashboard'));

        $this->assertEquals(1, \Illuminate\Support\Facades\DB::table('notifications')
            ->where('notifiable_id', $admin->id)
            ->count());
    }
}
