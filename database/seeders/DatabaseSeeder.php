<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Organisation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Organisation::firstOrCreate(
            ['organisation_id' => 'ORG001'],
            [
                'name'              => 'Inbizsys',
                'business_location' => 'SriLanka',
                'industry'          => 'Software Development',
                'date_format'       => 'dd/mm/yyyy',
                'field_separator'   => '/',
                'address_line_1'    => 'No. 607/51, The City Jayanthi Road',
                'city'              => 'Athurugiriya',
                'state'             => 'Western',
                'pincode'           => '10150',
                'country'           => 'Sri Lanka',
            ]
        );
        // 2. Main Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'              => 'Admin',
                'password'          => Hash::make('password'), 
                'email_verified_at' => now(),
            ]
        );
    }
}
