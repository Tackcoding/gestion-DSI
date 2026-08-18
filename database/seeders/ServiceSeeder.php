<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        // A CONFIRMER avec la DSI : decoupage reel en services.
        $services = [
            ['code' => 'DVEC', 'libelle' => 'Direction de la Veille et de la Communication'],
        ];

        foreach ($services as $s) {
            Service::updateOrCreate(['code' => $s['code']], $s);
        }
    }
}
