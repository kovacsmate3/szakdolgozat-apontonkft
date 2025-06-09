<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = [
            [
                'job_number'                  => '2025.001',
                'project_name'                => 'Budapest 06 Podmaniczky u 14',
                'location'                    => 'belterület',
                'parcel_identification_number'=> '',
                'deadline'                    => '2025-03-28 17:00:00',
                'description'                 => "Épület alaprajzi felmérése\nMappa neve: 2025.001_Budapest 06_Podmaniczky u 14_ hrsz_épület alaprajzi felmérése
                                                    \nFTP jelszó: 2025.001felm",
                'status'                      => 'folyamatban lévő',
                'address_id'                  => 9,
            ],
            [
                'job_number'                  => '2025.013',
                'project_name'                => 'Budakalász Petőfi Sándor u 9',
                'location'                    => 'belterület',
                'parcel_identification_number'=> '9-1124',
                'deadline'                    => '2025-04-03 17:00:00',
                'description'                 => "Épületfeltüntetés\nMappa neve: 2025.013_Budakalász_Petőfi Sándor u 9_1124 hrsz_épületfeltüntetés\nFTP jelszó: 2025.013epfel",
                'status'                      => 'folyamatban lévő',
                'address_id'                  => 10,
            ],
            [
                'job_number'                  => '2025.029',
                'project_name'                => 'Budapest 02 Gábor Áron u 26',
                'location'                    => 'belterület',
                'parcel_identification_number'=> '12291-4',
                'deadline'                    => '2025-05-05 14:59:59',
                'description'                 => "Társasházi alaprajz\nMappa neve: 2025.029_Budapest 02_Gábor Áron u 26_12291-4 hrsz_társasházi alaprajz\nFTP jelszó: 2025.029thaz",
                'status'                      => 'folyamatban lévő',
                'address_id'                  => 11,
            ],
            [
                'job_number'                  => '2025.004',
                'project_name'                => 'Szigetszentmiklós Diósgyőri u 1',
                'location'                    => 'belterület',
                'parcel_identification_number'=> '13251-5',
                'deadline'                    => '2025-05-20 12:00:00',
                'description'                 => "Felmérési munkák\nMappa neve: 2025.004_Szigetszentmiklós_Diósgyőri u 1_13251-5 hrsz_felmérési munkák\nFTP jelszó: 2025.004felm",
                'status'                      => 'folyamatban lévő',
                'address_id'                  => 12,
            ],
            [
                'job_number'                  => '2025.007',
                'project_name'                => 'Csömör Szőlő köz 1',
                'location'                    => 'belterület',
                'parcel_identification_number'=> '232-12',
                'deadline'                    => '2025-06-06 17:00:00',
                'description'                 => "Társasházi alaprajz\nMappa neve: 2025.007_Csömör_Szõlõ köz 1_hrsz 232-12 hrsz_társasházi alaprajz\nFTP jelszó: 2025.007thaz",
                'status'                      => 'folyamatban lévő',
                'address_id'                  => 13,
            ]
        ];

        foreach ($projects as $project) {
            Project::create($project);
        }
    }
}
