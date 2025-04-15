<?php

namespace Tests\Feature\WorkLog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\OvertimeRequest;
use App\Models\JournalEntry;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class OvertimeRequestTest extends TestCase
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
    public function test_user_can_create_overtime_request()
    {
        $overtimeData = [
            'date' => Carbon::now()->subDays(1)->format('Y-m-d'),
            'hours' => '02:30',
            'reason' => 'Project deadline',
        ];

        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/overtime-requests', $overtimeData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'A túlóra igény sikeresen létrehozva.'
            ]);

        $this->assertDatabaseHas('overtime_requests', [
            'user_id' => $this->regularUser->id,
            'reason' => 'Project deadline',
            'status' => 'függőben lévő'
        ]);
    }

    #[Test]
    public function test_admin_can_create_overtime_request_for_other_users()
    {
        $overtimeData = [
            'user_id' => $this->regularUser->id,
            'date' => Carbon::now()->subDays(1)->format('Y-m-d'),
            'hours' => '01:45',
            'reason' => 'Admin created overtime',
            'status' => 'jóváhagyott'
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/overtime-requests', $overtimeData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'A túlóra igény sikeresen létrehozva.'
            ]);

        $this->assertDatabaseHas('overtime_requests', [
            'user_id' => $this->regularUser->id,
            'reason' => 'Admin created overtime',
            'status' => 'jóváhagyott',
            'processed_by' => $this->adminUser->id
        ]);

        // Verify journal entry was created
        $this->assertDatabaseHas('journal_entries', [
            'user_id' => $this->regularUser->id,
            'work_type' => 'túlóra'
        ]);
    }

    #[Test]
    public function test_admin_cannot_create_overtime_request_for_self()
    {
        $overtimeData = [
            'user_id' => $this->adminUser->id, // Admin's own ID
            'date' => Carbon::now()->subDays(1)->format('Y-m-d'),
            'hours' => '01:00',
            'reason' => 'Admin self overtime',
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/overtime-requests', $overtimeData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    }

    #[Test]
    public function test_cannot_create_duplicate_date_overtime_request()
    {
        $this->actingAs($this->regularUser);

        $testDate = Carbon::today()->subDays(1)->startOfDay()->toDateString();

        // Create an initial overtime request
        $firstRequest = OvertimeRequest::create([
            'user_id' => $this->regularUser->id,
            'date' => $testDate,
            'hours' => '02:00',
            'reason' => 'First overtime',
            'status' => 'függőben lévő'
        ]);

        $this->assertDatabaseHas('overtime_requests', [
            'id' => $firstRequest->id
        ]);

        $overtimeData = [
            'date' => $testDate,
            'hours' => '01:30',
            'reason' => 'Duplicate date overtime',
        ];

        $response = $this->postJson('/api/overtime-requests', $overtimeData);

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Erre a napra már létezik túlóraigény a megadott felhasználóhoz.'
            ]);
    }

    #[Test]
    public function test_validation_rules_for_overtime_request_creation()
    {
        $invalidOvertimeData = [
            'date' => null, // Missing required field
            'hours' => '25:00', // Invalid time format
            'reason' => '', // Empty reason
        ];

        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/overtime-requests', $invalidOvertimeData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date', 'hours', 'reason']);
    }

    #[Test]
    public function test_user_can_view_own_overtime_requests()
    {
        // Create some overtime requests for the user
        OvertimeRequest::create([
            'user_id' => $this->regularUser->id,
            'date' => Carbon::today()->subDays(2),
            'hours' => '02:00',
            'reason' => 'First overtime',
            'status' => 'függőben lévő'
        ]);

        OvertimeRequest::create([
            'user_id' => $this->regularUser->id,
            'date' => Carbon::today()->subDays(1),
            'hours' => '01:30',
            'reason' => 'Second overtime',
            'status' => 'függőben lévő'
        ]);

        $response = $this->actingAs($this->regularUser)
            ->getJson('/api/overtime-requests?user_id=' . $this->regularUser->id);

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    #[Test]
    public function test_admin_can_view_all_overtime_requests()
    {
        // Create overtime requests for multiple users
        OvertimeRequest::create([
            'user_id' => $this->regularUser->id,
            'date' => Carbon::today()->subDays(2),
            'hours' => '02:00',
            'reason' => 'Regular user overtime',
            'status' => 'függőben lévő'
        ]);

        OvertimeRequest::create([
            'user_id' => $this->webdevUser->id,
            'date' => Carbon::today()->subDays(1),
            'hours' => '01:30',
            'reason' => 'Webdev overtime',
            'status' => 'függőben lévő'
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/overtime-requests');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    #[Test]
    public function test_user_can_update_own_pending_overtime_request()
    {
        $overtimeRequest = OvertimeRequest::create([
            'user_id' => $this->regularUser->id,
            'date' => Carbon::today()->subDays(2),
            'hours' => '02:00',
            'reason' => 'Original reason',
            'status' => 'függőben lévő'
        ]);

        $updateData = [
            'reason' => 'Updated reason',
            'hours' => '02:30',
        ];

        $response = $this->actingAs($this->regularUser)
            ->putJson("/api/overtime-requests/{$overtimeRequest->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'A túlóra igény adatai sikeresen frissítve lettek.'
            ]);

        $this->assertDatabaseHas('overtime_requests', [
            'id' => $overtimeRequest->id,
            'reason' => 'Updated reason',
            'status' => 'függőben lévő'
        ]);
    }

    #[Test]
    public function test_user_cannot_update_approved_overtime_request()
    {
        $overtimeRequest = OvertimeRequest::create([
            'user_id' => $this->regularUser->id,
            'date' => Carbon::today()->subDays(2),
            'hours' => '02:00',
            'reason' => 'Approved overtime',
            'status' => 'jóváhagyott',
            'processed_at' => now(),
            'processed_by' => $this->adminUser->id
        ]);

        $updateData = [
            'reason' => 'Trying to update approved overtime',
        ];

        $response = $this->actingAs($this->regularUser)
            ->putJson("/api/overtime-requests/{$overtimeRequest->id}", $updateData);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Már feldolgozott túlóra igényt csak adminisztrátor módosíthat.'
            ]);
    }

    #[Test]
    public function test_admin_can_update_any_overtime_request()
    {
        $overtimeRequest = OvertimeRequest::create([
            'user_id' => $this->regularUser->id,
            'date' => Carbon::today()->subDays(2),
            'hours' => '02:00',
            'reason' => 'Original reason',
            'status' => 'jóváhagyott',
            'processed_at' => now(),
            'processed_by' => $this->adminUser->id
        ]);

        // Create a journal entry for this overtime request
        JournalEntry::create([
            'user_id' => $this->regularUser->id,
            'work_date' => Carbon::today()->subDays(2),
            'hours' => '02:00:00',
            'work_type' => 'túlóra',
            'overtimerequest_id' => $overtimeRequest->id,
            'note' => 'TÚLÓRA: ' . $this->regularUser->firstname . ' - Original reason'
        ]);

        $updateData = [
            'reason' => 'Admin updated',
            'status' => 'elutasított',
            'decision_comment' => 'Changed decision'
        ];

        $response = $this->actingAs($this->adminUser)
            ->putJson("/api/overtime-requests/{$overtimeRequest->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('overtime_requests', [
            'id' => $overtimeRequest->id,
            'reason' => 'Admin updated',
            'status' => 'elutasított',
            'decision_comment' => 'Changed decision'
        ]);

        // Check that journal entry was deleted when status changed from approved to rejected
        $this->assertDatabaseMissing('journal_entries', [
            'overtimerequest_id' => $overtimeRequest->id
        ]);
    }

    #[Test]
    public function test_admin_can_approve_overtime_request()
    {
        $overtimeRequest = OvertimeRequest::create([
            'user_id' => $this->regularUser->id,
            'date' => Carbon::today()->subDays(1),
            'hours' => '01:45',
            'reason' => 'Pending approval',
            'status' => 'függőben lévő'
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/overtime-requests/{$overtimeRequest->id}/approve", [
                'decision_comment' => 'Approved by admin'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'A túlóra igény sikeresen jóváhagyva.'
            ]);

        $this->assertDatabaseHas('overtime_requests', [
            'id' => $overtimeRequest->id,
            'status' => 'jóváhagyott',
            'processed_by' => $this->adminUser->id,
            'decision_comment' => 'Approved by admin'
        ]);

        // Verify that a journal entry was created
        $this->assertDatabaseHas('journal_entries', [
            'user_id' => $this->regularUser->id,
            'work_type' => 'túlóra',
            'overtimerequest_id' => $overtimeRequest->id
        ]);
    }

    #[Test]
    public function test_admin_can_reject_overtime_request()
    {
        $overtimeRequest = OvertimeRequest::create([
            'user_id' => $this->regularUser->id,
            'date' => Carbon::today()->subDays(1),
            'hours' => '02:00',
            'reason' => 'Pending rejection',
            'status' => 'függőben lévő'
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/overtime-requests/{$overtimeRequest->id}/reject", [
                'decision_comment' => 'Rejected due to policy'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'A túlóra igény elutasítva.'
            ]);

        $this->assertDatabaseHas('overtime_requests', [
            'id' => $overtimeRequest->id,
            'status' => 'elutasított',
            'processed_by' => $this->adminUser->id,
            'decision_comment' => 'Rejected due to policy'
        ]);

        // Verify no journal entry was created
        $this->assertEquals(0, JournalEntry::where('overtimerequest_id', $overtimeRequest->id)->count());
    }

    #[Test]
    public function test_non_admin_cannot_approve_overtime_request()
    {
        $overtimeRequest = OvertimeRequest::create([
            'user_id' => $this->webdevUser->id,
            'date' => Carbon::today()->subDays(1),
            'hours' => '01:30',
            'reason' => 'Pending approval',
            'status' => 'függőben lévő'
        ]);

        // Regular user tries to approve
        $response = $this->actingAs($this->regularUser)
            ->postJson("/api/overtime-requests/{$overtimeRequest->id}/approve", [
                'decision_comment' => 'Trying to approve'
            ]);

        $response->assertStatus(403);

        // Webdev tries to approve
        $response = $this->actingAs($this->webdevUser)
            ->postJson("/api/overtime-requests/{$overtimeRequest->id}/approve", [
                'decision_comment' => 'Trying to approve'
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function test_user_can_delete_own_pending_overtime_request()
    {
        $overtimeRequest = OvertimeRequest::create([
            'user_id' => $this->regularUser->id,
            'date' => Carbon::today()->subDays(1),
            'hours' => '02:00',
            'reason' => 'Delete test',
            'status' => 'függőben lévő'
        ]);

        $response = $this->actingAs($this->regularUser)
            ->deleteJson("/api/overtime-requests/{$overtimeRequest->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('overtime_requests', [
            'id' => $overtimeRequest->id
        ]);
    }

    #[Test]
    public function test_user_cannot_delete_approved_overtime_request()
    {
        $overtimeRequest = OvertimeRequest::create([
            'user_id' => $this->regularUser->id,
            'date' => Carbon::today()->subDays(1),
            'hours' => '02:00',
            'reason' => 'Approved overtime',
            'status' => 'jóváhagyott',
            'processed_at' => now(),
            'processed_by' => $this->adminUser->id
        ]);

        $response = $this->actingAs($this->regularUser)
            ->deleteJson("/api/overtime-requests/{$overtimeRequest->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('overtime_requests', [
            'id' => $overtimeRequest->id
        ]);
    }

    #[Test]
    public function test_admin_can_delete_any_overtime_request()
    {
        $overtimeRequest = OvertimeRequest::create([
            'user_id' => $this->regularUser->id,
            'date' => Carbon::today()->subDays(1),
            'hours' => '02:00',
            'reason' => 'Admin delete test',
            'status' => 'elutasított',
            'processed_at' => now(),
            'processed_by' => $this->adminUser->id
        ]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/api/overtime-requests/{$overtimeRequest->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('overtime_requests', [
            'id' => $overtimeRequest->id
        ]);
    }

    #[Test]
    public function test_cannot_delete_approved_overtime_request()
    {
        $overtimeRequest = OvertimeRequest::create([
            'user_id' => $this->regularUser->id,
            'date' => Carbon::today()->subDays(1),
            'hours' => '02:00',
            'reason' => 'Approved overtime',
            'status' => 'jóváhagyott', // Not in pending state
            'processed_at' => now(),
            'processed_by' => $this->adminUser->id
        ]);

        // Create a journal entry for this request (this happens automatically in your app)
        JournalEntry::create([
            'user_id' => $this->regularUser->id,
            'work_date' => Carbon::today()->subDays(1),
            'hours' => '02:00:00',
            'work_type' => 'túlóra',
            'overtimerequest_id' => $overtimeRequest->id,
            'note' => 'TÚLÓRA'
        ]);

        // Regular user should not be able to delete approved request
        $response = $this->actingAs($this->regularUser)
            ->deleteJson("/api/overtime-requests/{$overtimeRequest->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Nem megfelelő jogosultság a törléshez. Csak saját függőben lévő túlóra igényeket törölhet, vagy adminisztrátor jogosultság szükséges.'
            ]);
    }
}
