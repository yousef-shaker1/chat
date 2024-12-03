<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\MessageChat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MessageChat>
 */
class MessageChatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sender_id' => User::inRandomOrder()->first()->id,
            'receiver_id' => User::inRandomOrder()->first()->id,
            'message' => $this->faker->sentence,
            'reply_to_message_id' => MessageChat::inRandomOrder()->first()?->id,
        ];
    }
}
