<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MessagePublicGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PublicMessageGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MessagePublicGroup::factory()->count(10)->create();
    }
}
