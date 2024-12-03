<?php

namespace Database\Factories;

use App\Models\public_group;
use App\Models\MessagePublicGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class MessagePublicGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sender_id' => 1,
            'group_id' => public_group::inRandomOrder()->first()->id,
            'message' => $this->faker->sentence,
            'reply_to_message_id' => MessagePublicGroup::inRandomOrder()->first()?->id,
        ];
    }
}
