<?php

namespace Database\Factories;

use App\Models\Consultant;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class ConsultantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Create a user factorly like admin in seeder
        // Admin::create([
        //     'name' => 'Admin',
        //     'username' => generateUniqueId(Admin::class),
        //     'email' => 'admin@admin.com',
        //     'phone' => '1234567890',
        //     'password' => bcrypt('Admin@123'),
        //     'status' => true,
        //     'is_admin' => true,
        //     'profile' => json_encode([
        //         'phone' => '1234567890',
        //         'address' => 'Admin Address',
        //         'city' => 'Admin City',
        //         'state' => 'Admin State',
        //         'country' => 'Admin Country',
        //         'zip' => '123456',
        //     ]),
        //     'remember_token' => Str::random(10),
        // ]);

        return [
            'name' => $this->faker->name(),
            'username' => generateUniqueId(Consultant::class),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'password' => bcrypt('Admin@123'),
            'status' => true,
            'is_consultant' => true,
            //Create array or json of specialization like ['Fema compliance,GST,Income Tax'] and store in database should be random
            'specialization' => [$this->faker->word(), $this->faker->word(), $this->faker->word()],
            // Type of consultanta like ['CA','CS','Lawyer'] and store in database should be random Randomaly choose between CA, CS, Lawyer, CMA, Advocate
            'type' => $this->faker->randomElement(['CA', 'CS', 'Lawyer', 'CMA', 'Advocate']),
            'registration_no' => $this->faker->randomNumber(8),
            'is_verified' => rand(0, 1), //Randomly set verified
            'is_twofactor_enabled' => rand(0, 1), //Randomly set two factor enabled
            'profile' => json_encode([
                'phone' => $this->faker->phoneNumber(),
                'address' => $this->faker->address(),
                'city' => $this->faker->city(),
                'state' => $this->faker->state(),
                'country' => $this->faker->country(),
                'zip' => $this->faker->postcode(),
            ]),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
