<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class HorusAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@horus.local')],
            [
                'name' => 'Administrador Horus',
                'password' => env('ADMIN_PASSWORD', 'ChangeMe2026!'),
            ],
        );
    }
}
