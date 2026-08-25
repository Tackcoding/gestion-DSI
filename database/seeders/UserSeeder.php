<?php

namespace Database\Seeders;

use App\Enums\RoleUtilisateur;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'tack@midsp.mg'],
            [
                'name'     => 'Tack',
                'password' => Hash::make('password'),
                'role'     => RoleUtilisateur::Administrateur,
                'actif'    => true,
            ]
        );
    }
}