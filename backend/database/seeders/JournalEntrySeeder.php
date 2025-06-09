<?php

namespace Database\Seeders;

use App\Models\JournalEntry;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JournalEntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employeeIds = [3, 4, 5, 6, 7];

        $validTaskIds = Task::whereIn('status', ['folyamatban lévő', 'befejezett'])
        ->pluck('id')
        ->toArray();


        foreach ($employeeIds as $empId) {
            JournalEntry::factory()
                ->state([
                    'user_id' => $empId,
                    'task_id' => fake()->randomElement($validTaskIds),
                ])
                ->create();
        }

        $acceptedLeaveRequests = LeaveRequest::where('status', 'jóváhagyott')->get();
        foreach ($acceptedLeaveRequests as $leaveRequest) {
            JournalEntry::factory()->createLeaveEntriesForRequest($leaveRequest);
        }

        $acceptedOvertimeRequests = OvertimeRequest::where('status', 'jóváhagyott')->get();
        foreach ($acceptedOvertimeRequests as $overtimeRequest) {
            JournalEntry::factory()
                ->withOvertimeRequest($overtimeRequest)
                ->create();
        }
    }
}
