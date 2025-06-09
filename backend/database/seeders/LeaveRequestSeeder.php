<?php

namespace Database\Seeders;

use App\Models\LeaveRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaveRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveRequests = [
            [
                'start_date'       => '2025-07-10',
                'end_date'         => '2025-07-15',
                'status'           => 'jóváhagyott',
                'reason'           => 'Nyári pihenés',
                'processed_at'     => '2025-05-01 10:00:00',
                'processed_by'     => 1,
                'decision_comment' => 'Kérelem elfogadva.',
            ],
            [
                'start_date'       => '2025-12-20',
                'end_date'         => '2025-12-31',
                'status'           => 'függőben lévő',
                'reason'           => 'Karácsonyi szabadság',
                'processed_at'     => null,
                'processed_by'     => null,
                'decision_comment' => null,
            ],
            [
                'start_date'       => '2025-02-03',
                'end_date'         => '2025-02-12',
                'status'           => 'elutasított',
                'reason'           => 'Téli síelés',
                'processed_at'     => '2025-01-25 14:30:00',
                'processed_by'     => 1,
                'decision_comment' => 'Kérelem elutasítva, mert a szabadság túl hosszú lenne.',
            ],
        ];

        $employeeIds = [3, 4, 5, 6, 7];
        shuffle($employeeIds);
        $selectedEmployeeIds = array_slice($employeeIds, 0, 3);

        foreach ($selectedEmployeeIds as $empId) {
            foreach ($leaveRequests as $lr) {
                $lr['user_id'] = $empId;
                LeaveRequest::create($lr);
            }
        }
    }
}
