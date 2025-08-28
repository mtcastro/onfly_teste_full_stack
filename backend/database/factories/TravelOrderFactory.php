<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TravelOrder;
use App\Models\User;

class TravelOrderFactory extends Factory
{
    protected $model = TravelOrder::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'requester_name' => $this->faker->name,
            'destination' => $this->faker->city,
            'departure_date' => $this->faker->dateTimeBetween('+1 days', '+10 days')->format('Y-m-d'),
            'return_date' => $this->faker->dateTimeBetween('+11 days', '+20 days')->format('Y-m-d'),
            'status' => 'requested'
        ];
    }
}
