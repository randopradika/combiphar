<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // firstOrCreate: re-seeding must never reset an existing admin's
        // password. Outside local the initial password is random — set a real
        // one via tinker when provisioning a new environment.
        User::firstOrCreate(
            ['email' => 'admin@combiphar.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make(app()->environment('local') ? 'password' : Str::random(40)),
            ]
        );

        $this->call([
            CmsSeeder::class,
            MockupContentSeeder::class,
            OfficesSeeder::class,
        ]);
    }
}
