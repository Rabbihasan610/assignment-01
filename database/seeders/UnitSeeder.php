<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Piece', 'abbreviation' => 'pcs'],
            ['name' => 'Kilogram', 'abbreviation' => 'kg'],
            ['name' => 'Gram', 'abbreviation' => 'gm'],
            ['name' => 'Liter', 'abbreviation' => 'L'],
            ['name' => 'Milliliter', 'abbreviation' => 'ml'],
            ['name' => 'Dozen', 'abbreviation' => 'dz'],
            ['name' => 'Packet', 'abbreviation' => 'pkt'],
            ['name' => 'Sack', 'abbreviation' => 'sack'],
            ['name' => 'Tray', 'abbreviation' => 'tray'],
            ['name' => 'Can', 'abbreviation' => 'can'],
            ['name' => 'Carton', 'abbreviation' => 'ctn'],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['name' => $unit['name']],
                ['abbreviation' => $unit['abbreviation']]
            );
        }
    }
}
