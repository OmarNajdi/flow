<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Program::factory()->create([
            'name'       => 'PIEC',
            'level'      => 'pre-incubation',
            'open_date'  => '2024-05-01',
            'close_date' => '2024-05-31',
        ]);
    }
}
