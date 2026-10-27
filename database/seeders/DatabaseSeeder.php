<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Tasas sintéticas para la prueba; no son cotizaciones actuales.
        foreach ([
            ['name' => 'DOP', 'symbol' => 'RD$', 'exchange_rate' => '1.000000'],
            ['name' => 'USD', 'symbol' => 'US$', 'exchange_rate' => '60.000000'],
            ['name' => 'EUR', 'symbol' => '€', 'exchange_rate' => '65.000000'],
        ] as $currency) {
            Currency::query()->firstOrCreate(['name' => $currency['name']], $currency);
        }
    }
}
