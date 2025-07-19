<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        // Creating the SuperAdmin User

        Role::create(['name' => 'superadmin', 'guard_name' => 'admin']);
        $admin = Admin::create([
            'name' => 'Admin',
            'username' => generateUniqueId(Admin::class),
            'email' => 'admin@admin.com',
            'phone' => '1234567890',
            'password' => bcrypt('Admin@123'),
            'status' => true,
            'is_admin' => true,
            'is_email_verified' => true,
            'is_phone_verified' => true,
            'country' => 'India',
            'state' => 'Uttarakhand',
            'city' => 'Dehradun',
            'address' => 'Dehradun',
            'pincode' => '248001',
            'remember_token' => Str::random(10),
        ]);

        $admin->assignRole('Superadmin');
        // Creating Users and Consultants
        // \App\Models\User::factory(10)->create();
        // \App\Models\Consultant::factory(10)->create();
    }
}
