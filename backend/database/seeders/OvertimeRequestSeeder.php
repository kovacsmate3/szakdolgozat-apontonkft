<?php

namespace Database\Seeders;

use App\Models\OvertimeRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OvertimeRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $overtimeRequests = [
            [
                'date'             => '2025-03-03',
                'hours'            => '02:00:00',
                'status'           => 'jóváhagyott',
                'reason'           => 'Sürgős projekt befejezése',
                'processed_at'     => '2025-03-10 09:00:00',
                'processed_by'     => 1,
                'decision_comment' => 'Túlóra bejelentés elfogadva.',
            ],
            [
                'date'             => '2025-04-10',
                'hours'            => '01:30:00',
                'status'           => 'függőben lévő',
                'reason'           => 'Konferencia előkészítés',
                'processed_at'     => null,
                'processed_by'     => null,
                'decision_comment' => null,
            ],
            [
                'date'             => '2025-03-07',
                'hours'            => '02:00:00',
                'status'           => 'elutasított',
                'reason'           => 'Rendszerkarbantartás',
                'processed_at'     => '2025-03-10 10:00:00',
                'processed_by'     => 1,
                'decision_comment' => 'Kérelem elutasítva, mert nem megfelelő napra lett rögzítve a túlórázás.',
            ],
        ];

        $employeeIds = [3, 4, 5, 6, 7];

        shuffle($employeeIds);
        $selectedEmployeeIds = array_slice($employeeIds, 0, 3);

        foreach ($selectedEmployeeIds as $empId) {
            foreach ($overtimeRequests as $ot) {
                $ot['user_id'] = $empId;
                OvertimeRequest::create($ot);
            }
        }
    }
}
