<?php

namespace Tests\Feature\WorkLog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\LeaveRequest;
use App\Models\JournalEntry;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class LeaveRequestTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $regularUser;
    protected $webdevUser;

    public function setUp(): void
    {
        parent::setUp();

        // Create roles
        $adminRole = Role::create([
            'slug' => 'admin',
            'title' => 'Adminisztrátor',
            'description' => 'Admin jogosultságok'
        ]);

        $employeeRole = Role::create([
            'slug' => 'employee',
            'title' => 'Alkalmazott',
            'description' => 'Korlátozott jogosultságok'
        ]);

        $webdevRole = Role::create([
            'slug' => 'webdev',
            'title' => 'Webfejlesztő',
            'description' => 'Fejlesztői jogosultságok'
        ]);

        // Create users with specific roles
        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $this->regularUser = User::factory()->create([
            'role_id' => $employeeRole->id,
        ]);

        $this->webdevUser = User::factory()->create([
            'role_id' => $webdevRole->id,
        ]);
    }

    #[Test]
    public function test_user_can_create_leave_request()
    {
        $leaveRequestData = [
            'start_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'reason' => 'Annual vacation',
        ];

        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/leave-requests', $leaveRequestData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'A szabadság kérelem sikeresen létrehozva.'
            ]);

        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $this->regularUser->id,
            'reason' => 'Annual vacation',
            'status' => 'függőben lévő'
        ]);
    }

    #[Test]
    public function test_admin_can_create_leave_request_for_other_users()
    {
        $leaveRequestData = [
            'user_id' => $this->regularUser->id,
            'start_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'reason' => 'Admin created leave',
            'status' => 'jóváhagyott'
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/leave-requests', $leaveRequestData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'A szabadság kérelem sikeresen létrehozva.'
            ]);

        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $this->regularUser->id,
            'reason' => 'Admin created leave',
            'status' => 'jóváhagyott',
            'processed_by' => $this->adminUser->id
        ]);

        // Verify journal entries were created
        $this->assertDatabaseHas('journal_entries', [
            'user_id' => $this->regularUser->id,
            'work_type' => 'szabadság'
        ]);
    }

    #[Test]
    public function test_admin_cannot_create_leave_request_for_self()
    {
        $leaveRequestData = [
            'user_id' => $this->adminUser->id, // Admin's own ID
            'start_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'reason' => 'Admin self leave',
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/leave-requests', $leaveRequestData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    }

    #[Test]
    public function test_cannot_create_overlapping_leave_requests()
    {
        // Create an initial leave request
        LeaveRequest::create([
            'user_id' => $this->regularUser->id,
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(10),
            'reason' => 'First vacation',
            'status' => 'függőben lévő'
        ]);

        // Try to create an overlapping request
        $leaveRequestData = [
            'start_date' => Carbon::now()->addDays(8)->format('Y-m-d'), // Overlaps with existing request
            'end_date' => Carbon::now()->addDays(15)->format('Y-m-d'),
            'reason' => 'Overlapping vacation',
        ];

        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/leave-requests', $leaveRequestData);

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'A megadott időszakra már létezik szabadság kérelem a felhasználóhoz.',
            ]);
    }

    #[Test]
    public function test_validation_rules_for_leave_request_creation()
    {
        $invalidLeaveRequestData = [
            'start_date' => Carbon::now()->addDays(10)->format('Y-m-d'), // Start date after end date
            'end_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'reason' => '', // Empty reason
        ];

        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/leave-requests', $invalidLeaveRequestData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date', 'reason']);
    }

    #[Test]
    public function test_user_can_view_own_leave_requests()
    {
        // Create some leave requests for the user
        LeaveRequest::create([
            'user_id' => $this->regularUser->id,
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(10),
            'reason' => 'First vacation',
            'status' => 'függőben lévő'
        ]);

        LeaveRequest::create([
            'user_id' => $this->regularUser->id,
            'start_date' => Carbon::now()->addDays(20),
            'end_date' => Carbon::now()->addDays(25),
            'reason' => 'Second vacation',
            'status' => 'függőben lévő'
        ]);

        $response = $this->actingAs($this->regularUser)
            ->getJson('/api/leave-requests?user_id=' . $this->regularUser->id);

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    #[Test]
    public function test_admin_can_view_all_leave_requests()
    {
        // Create leave requests for multiple users
        LeaveRequest::create([
            'user_id' => $this->regularUser->id,
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(10),
            'reason' => 'Regular user vacation',
            'status' => 'függőben lévő'
        ]);

        LeaveRequest::create([
            'user_id' => $this->webdevUser->id,
            'start_date' => Carbon::now()->addDays(15),
            'end_date' => Carbon::now()->addDays(20),
            'reason' => 'Webdev vacation',
            'status' => 'függőben lévő'
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/leave-requests');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    #[Test]
    public function test_user_can_update_own_pending_leave_request()
    {
        $leaveRequest = LeaveRequest::create([
            'user_id' => $this->regularUser->id,
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(10),
            'reason' => 'Original reason',
            'status' => 'függőben lévő'
        ]);

        $updateData = [
            'reason' => 'Updated reason',
            'end_date' => Carbon::now()->addDays(12)->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->regularUser)
            ->putJson("/api/leave-requests/{$leaveRequest->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'A szabadság kérelem adatai sikeresen frissítve lettek.'
            ]);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'reason' => 'Updated reason',
            'status' => 'függőben lévő'
        ]);
    }

    #[Test]
    public function test_user_cannot_update_approved_leave_request()
    {
        $leaveRequest = LeaveRequest::create([
            'user_id' => $this->regularUser->id,
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(10),
            'reason' => 'Approved leave',
            'status' => 'jóváhagyott',
            'processed_at' => now(),
            'processed_by' => $this->adminUser->id
        ]);

        $updateData = [
            'reason' => 'Trying to update approved leave',
        ];

        $response = $this->actingAs($this->regularUser)
            ->putJson("/api/leave-requests/{$leaveRequest->id}", $updateData);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Már feldolgozott szabadság kérelmet csak adminisztrátor módosíthat.'
            ]);
    }

    #[Test]
    public function test_admin_can_update_any_leave_request()
    {
        $leaveRequest = LeaveRequest::create([
            'user_id' => $this->regularUser->id,
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(10),
            'reason' => 'Original reason',
            'status' => 'jóváhagyott',
            'processed_at' => now(),
            'processed_by' => $this->adminUser->id
        ]);

        // Create some journal entries for this leave request
        JournalEntry::create([
            'user_id' => $this->regularUser->id,
            'work_date' => Carbon::now()->addDays(5),
            'hours' => '0:00:00',
            'work_type' => 'szabadság',
            'leaverequest_id' => $leaveRequest->id,
            'note' => 'SZABADSÁG: ' . $this->regularUser->firstname . ' - Original reason'
        ]);

        $updateData = [
            'reason' => 'Admin updated',
            'status' => 'elutasított',
            'decision_comment' => 'Changed decision'
        ];

        $response = $this->actingAs($this->adminUser)
            ->putJson("/api/leave-requests/{$leaveRequest->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'reason' => 'Admin updated',
            'status' => 'elutasított',
            'decision_comment' => 'Changed decision'
        ]);

        // Check that journal entries were deleted when status changed from approved to rejected
        $this->assertDatabaseMissing('journal_entries', [
            'leaverequest_id' => $leaveRequest->id
        ]);
    }

    #[Test]
    public function test_admin_can_approve_leave_request()
    {
        $leaveRequest = LeaveRequest::create([
            'user_id' => $this->regularUser->id,
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(7), // 3 days
            'reason' => 'Pending approval',
            'status' => 'függőben lévő'
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/leave-requests/{$leaveRequest->id}/approve", [
                'decision_comment' => 'Approved by admin'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'A szabadság kérelem sikeresen jóváhagyva.'
            ]);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'jóváhagyott',
            'processed_by' => $this->adminUser->id,
            'decision_comment' => 'Approved by admin'
        ]);

        // Verify that journal entries were created (one for each day)
        $journalEntries = JournalEntry::where('leaverequest_id', $leaveRequest->id)->get();
        $this->assertEquals(3, $journalEntries->count());
    }

    #[Test]
    public function test_admin_can_reject_leave_request()
    {
        $leaveRequest = LeaveRequest::create([
            'user_id' => $this->regularUser->id,
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(10),
            'reason' => 'Pending rejection',
            'status' => 'függőben lévő'
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/leave-requests/{$leaveRequest->id}/reject", [
                'decision_comment' => 'Rejected due to workload'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'A szabadság kérelem elutasítva.'
            ]);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'elutasított',
            'processed_by' => $this->adminUser->id,
            'decision_comment' => 'Rejected due to workload'
        ]);

        // Verify no journal entries were created
        $this->assertEquals(0, JournalEntry::where('leaverequest_id', $leaveRequest->id)->count());
    }

    #[Test]
    public function test_non_admin_cannot_approve_leave_request()
    {
        $leaveRequest = LeaveRequest::create([
            'user_id' => $this->webdevUser->id,
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(10),
            'reason' => 'Pending approval',
            'status' => 'függőben lévő'
        ]);

        // Regular user tries to approve
        $response = $this->actingAs($this->regularUser)
            ->postJson("/api/leave-requests/{$leaveRequest->id}/approve", [
                'decision_comment' => 'Trying to approve'
            ]);

        $response->assertStatus(403);

        // Webdev tries to approve
        $response = $this->actingAs($this->webdevUser)
            ->postJson("/api/leave-requests/{$leaveRequest->id}/approve", [
                'decision_comment' => 'Trying to approve'
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function test_user_can_delete_own_pending_leave_request()
    {
        $leaveRequest = LeaveRequest::create([
            'user_id' => $this->regularUser->id,
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(10),
            'reason' => 'Delete test',
            'status' => 'függőben lévő'
        ]);

        $response = $this->actingAs($this->regularUser)
            ->deleteJson("/api/leave-requests/{$leaveRequest->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('leave_requests', [
            'id' => $leaveRequest->id
        ]);
    }

    #[Test]
    public function test_user_cannot_delete_approved_leave_request()
    {
        $leaveRequest = LeaveRequest::create([
            'user_id' => $this->regularUser->id,
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(10),
            'reason' => 'Approved leave',
            'status' => 'jóváhagyott',
            'processed_at' => now(),
            'processed_by' => $this->adminUser->id
        ]);

        // Create a journal entry for this leave request
        JournalEntry::create([
            'user_id' => $this->regularUser->id,
            'work_date' => Carbon::now()->addDays(5),
            'hours' => '0:00:00',
            'work_type' => 'szabadság',
            'leaverequest_id' => $leaveRequest->id,
            'note' => 'SZABADSÁG'
        ]);

        $response = $this->actingAs($this->regularUser)
            ->deleteJson("/api/leave-requests/{$leaveRequest->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id
        ]);
    }

    #[Test]
    public function test_admin_can_delete_any_leave_request_without_journal_entries()
    {
        $leaveRequest = LeaveRequest::create([
            'user_id' => $this->regularUser->id,
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(10),
            'reason' => 'Admin delete test',
            'status' => 'elutasított',
            'processed_at' => now(),
            'processed_by' => $this->adminUser->id
        ]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/api/leave-requests/{$leaveRequest->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('leave_requests', [
            'id' => $leaveRequest->id
        ]);
    }

    #[Test]
    public function test_cannot_delete_leave_request_with_journal_entries()
    {
        $leaveRequest = LeaveRequest::create([
            'user_id' => $this->regularUser->id,
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(10),
            'reason' => 'Cannot delete',
            'status' => 'jóváhagyott',
            'processed_at' => now(),
            'processed_by' => $this->adminUser->id
        ]);

        // Create a journal entry for this leave request
        JournalEntry::create([
            'user_id' => $this->regularUser->id,
            'work_date' => Carbon::now()->addDays(5),
            'hours' => '0:00:00',
            'work_type' => 'szabadság',
            'leaverequest_id' => $leaveRequest->id,
            'note' => 'SZABADSÁG'
        ]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/api/leave-requests/{$leaveRequest->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Ez a szabadság kérelem már rögzítve van a munkanaplóban, ezért nem törölhető.'
            ]);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id
        ]);
    }
}
