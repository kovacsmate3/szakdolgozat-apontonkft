<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tasks = [
            // Projekt 1 – Podmaniczky u 14
            [
                'project_id' => 1,
                'parent_id' => null,
                'name' => 'Épület alaprajzi felmérés',
                'priority' => 'normál',
                'status' => 'folyamatban lévő',
                'surveying_instrument' => 'Leica DISTO D2',
                'description' => 'Teljes alaprajzi felmérés koordinálása.'
            ],
            [
                'project_id' => 1,
                'parent_id' => 1,
                'name' => 'Helyszíni előkészítés',
                'priority' => 'SOS',
                'status' => 'befejezett',
                'surveying_instrument' => 'GPS készülék',
                'description' => 'Előzetes helyszíni szemle, GPS-koordináták ellenőrzése.'
            ],
            [
                'project_id' => 1,
                'parent_id' => 1,
                'name' => 'Alaprajzi felmérés elvégzése',
                'priority' => 'kiemelt',
                'status' => 'folyamatban lévő',
                'surveying_instrument' => 'Trimble X7 3D lézerszkenner',
                'description' => 'Belső helyiségek felmérése.'
            ],

            // Projekt 2 – Budakalász: Épületfeltüntetés
            [
                'project_id' => 2,
                'parent_id' => null,
                'name' => 'Épületfeltüntetés koordinálása',
                'priority' => 'alacsony',
                'status' => 'nem megkezdett',
                'surveying_instrument' => 'Laptop',
                'description' => 'Földhivatali ügyintézés előkészítése.'
            ],
            [
                'project_id' => 2,
                'parent_id' => 4,
                'name' => 'Külső épületfelmérés',
                'priority' => 'alacsony',
                'status' => 'nem megkezdett',
                'surveying_instrument' => 'Trimble R12 GPS',
                'description' => 'Homlokzat méretezése GPS-szel.'
            ],
            [
                'project_id' => 2,
                'parent_id' => 4,
                'name' => 'Adatok feldolgozása',
                'priority' => 'alacsony',
                'status' => 'nem megkezdett',
                'surveying_instrument' => 'Laptop',
                'description' => 'Adatok rendszerezése térinformatikai szoftverrel.'
            ],

            // Projekt 3 – Budapest 02 Gábor Áron u 26: Társasházi alaprajz
            [
                'project_id' => 3,
                'parent_id' => null,
                'name' => 'Társasházi alaprajz készítése',
                'priority' => 'normál',
                'status' => 'folyamatban lévő',
                'surveying_instrument' => 'Leica BLK360',
                'description' => 'Lakóegységek és közös területek alaprajza.'
            ],
            [
                'project_id' => 3,
                'parent_id' => 7,
                'name' => 'Lakóegységek felmérése',
                'priority' => 'SOS',
                'status' => 'folyamatban lévő',
                'surveying_instrument' => 'Leica DISTO S910',
                'description' => 'Lakások precíz belső távolságmérései.'
            ],
            [
                'project_id' => 3,
                'parent_id' => 7,
                'name' => 'Közös területek dokumentálása',
                'priority' => 'kiemelt',
                'status' => 'felfüggesztett',
                'surveying_instrument' => 'Trimble X7 3D lézerszkenner',
                'description' => 'Lépcsőház és közösségi terek mérése.'
            ],

            // Projekt 4 – Szigetszentmiklós: Felmérési munkák
            [
                'project_id' => 4,
                'parent_id' => null,
                'name' => 'Általános helyszíni felmérés koordinálása',
                'priority' => 'alacsony',
                'status' => 'nem megkezdett',
                'surveying_instrument' => 'Laptop',
                'description' => 'Koordináció és tervezés a terepmérésekhez.'
            ],
            [
                'project_id' => 4,
                'parent_id' => 10,
                'name' => 'Szintezési munkák',
                'priority' => 'alacsony',
                'status' => 'nem megkezdett',
                'surveying_instrument' => 'Nikon AX-2S szintező',
                'description' => 'Magassági értékek ellenőrzése.'
            ],
            [
                'project_id' => 4,
                'parent_id' => 10,
                'name' => 'GPS koordináták mérése',
                'priority' => 'alacsony',
                'status' => 'nem megkezdett',
                'surveying_instrument' => 'Trimble R10 GPS',
                'description' => 'GPS adatok gyűjtése terepen.'
            ],

            // Projekt 5 – Csömör: Társasházi alaprajz
            [
                'project_id' => 5,
                'parent_id' => null,
                'name' => 'Társasházi alaprajz készítése',
                'priority' => 'normál',
                'status' => 'folyamatban lévő',
                'surveying_instrument' => 'Laptop',
                'description' => 'Társasház alaprajzának teljes dokumentálása.'
            ],
            [
                'project_id' => 5,
                'parent_id' => 13,
                'name' => 'Terepi adatgyűjtés',
                'priority' => 'SOS',
                'status' => 'folyamatban lévő',
                'surveying_instrument' => 'Leica TS16 mérőállomás',
                'description' => 'Épület pontos helyszíni mérése.'
            ],
            [
                'project_id' => 5,
                'parent_id' => 13,
                'name' => 'Digitális alaprajz szerkesztés',
                'priority' => 'kiemelt',
                'status' => 'folyamatban lévő',
                'surveying_instrument' => 'Laptop',
                'description' => 'Digitális vázlat készítése a felmért adatok alapján.'
            ]
        ];

        foreach ($tasks as $task) {
            Task::create($task);
        }
    }
}
