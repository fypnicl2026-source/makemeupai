<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class ClientUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('SEED_CLIENT_PASSWORD');

        if (empty($password)) {
            return;
        }

        $email = env('SEED_CLIENT_EMAIL', 'mominamughal200@gmail.com');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Momina Client',
                'password' => $password,
                'city' => 'Lahore',
            ]
        );
    }
}
