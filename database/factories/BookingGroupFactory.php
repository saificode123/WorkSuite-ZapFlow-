<?php

namespace Database\Factories;

use App\Models\BookingGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingGroupFactory extends Factory
{
    protected $model = BookingGroup::class;

    public function definition(): array
    {
        return [
            'customer_id' => User::factory(),
            'group_name' => 'Test Group',
        ];
    }
}
