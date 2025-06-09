<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => null,
            'parent_id'  => null,
            'name'       => fake()->randomElement(['Helyszíni felmérés', 'Telekmegosztási vázrajz készítése', 'Épületfeltüntetési vázrajz készítése', 'Terület kitűzése', 'Toronyfüggőlegesség mérése', 'Földhivatali ügyintézés', 'Digitális ortofotó generálás', 'Kiértékelő jelentés készítése', 'Területbejárás',
            'Földhivatali adatok beszerzése', 'Épület kontúr felmérése',
            'Helyiségek felmérése', 'Használati megállapodás előkészítése', '3D modell készítése','GPS mérés', 'Földhivatali nyomtatvány kitöltése', 'Adatellenőrzés', 'Megrendelői konzultáció']),
            'surveying_instrument' => fake()->randomElement(['mérőállomás', 'lézerszkenner', 'RTK GPS', 'UAV drón', 'lézeres szintező', 'digitális szintező', null]),
            'priority'   => fake()->randomElement(['SOS', 'kiemelt', 'normál', 'alacsony']),
            'status'     => fake()->randomElement(['nem megkezdett','folyamatban lévő','befejezett','felfüggesztett']),
            'description'=> fake()->optional()->paragraph(),
        ];
    }
}
