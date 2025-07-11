<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => fake()->optional()->secondaryAddress(),
            'city' => fake()->city(),
            'state' => fake()->randomElement([
                'Maharashtra', 'Gujarat', 'Karnataka', 'Tamil Nadu', 
                'Delhi', 'Uttar Pradesh', 'West Bengal', 'Rajasthan'
            ]),
            'postal_code' => fake()->numerify('######'),
            'country' => 'India',
            'tax_id' => fake()->optional()->numerify('##############'),
            'contact_person' => fake()->name(),
        ];
    }
}