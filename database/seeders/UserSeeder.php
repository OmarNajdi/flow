<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'first_name'        => 'Omar',
            'last_name'         => 'Najdi',
            'email'             => 'omar@bees.to',
            'password'          => bcrypt('123456'),
            'dob'               => '1997-03-19',
            'phone'             => '0595935043',
            'whatsapp'          => '+972526912278',
            'gender'            => 'Male',
            'residence'         => 'Jerusalem',
            'educational_level' => 'Master',
            'description'       => 'Entrepreneur',
            'occupation'        => 'Software Engineer',
        ]);
    }
}
