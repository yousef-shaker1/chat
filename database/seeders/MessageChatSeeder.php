<?php

namespace Database\Seeders;

use App\Models\MessageChat;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MessageChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MessageChat::factory()->count(10)->create();
    }
}
