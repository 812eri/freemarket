<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ConditionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // unique() を使って重複を防ぐか、lexifyでランダム文字列を生成します
            'name' => $this->faker->unique()->word . '_' . $this->faker->lexify('????'),
        ];
    }
}
