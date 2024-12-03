<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'tenant_id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        User::factory()->create([
            'tenant_id' => 2,
            'name' => 'Test User2',
            'email' => 'test@example2.com',
            'password' => bcrypt('password'),
        ]);

        User::factory()->create([
            'tenant_id' => 3,
            'name' => 'Test User3',
            'email' => 'test@example3.com',
            'password' => bcrypt('password'),
        ]);
    }
}
