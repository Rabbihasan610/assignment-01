<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Milk',
                'base_unit' => 'Liter',
                'units' => [
                    ['unit' => 'Liter', 'factor' => 1, 'price' => 90, 'default' => true],
                ],
            ],
            [
                'name' => 'Potato',
                'base_unit' => 'Kilogram',
                'units' => [
                    ['unit' => 'Kilogram', 'factor' => 1, 'price' => 35, 'default' => true],
                    ['unit' => 'Sack', 'factor' => 25, 'price' => 800, 'default' => false],
                ],
            ],
            [
                'name' => 'Onion',
                'base_unit' => 'Kilogram',
                'units' => [
                    ['unit' => 'Kilogram', 'factor' => 1, 'price' => 60, 'default' => true],
                    ['unit' => 'Sack', 'factor' => 25, 'price' => 1400, 'default' => false],
                ],
            ],
            [
                'name' => 'Rice',
                'base_unit' => 'Kilogram',
                'units' => [
                    ['unit' => 'Kilogram', 'factor' => 1, 'price' => 70, 'default' => true],
                    ['unit' => 'Sack', 'factor' => 25, 'price' => 1650, 'default' => false],
                ],
            ],
            [
                'name' => 'Egg',
                'base_unit' => 'Piece',
                'units' => [
                    ['unit' => 'Piece', 'factor' => 1, 'price' => 12, 'default' => true],
                    ['unit' => 'Tray', 'factor' => 30, 'price' => 330, 'default' => false],
                ],
            ],
            [
                'name' => 'Sugar',
                'base_unit' => 'Kilogram',
                'units' => [
                    ['unit' => 'Kilogram', 'factor' => 1, 'price' => 120, 'default' => true],
                    ['unit' => 'Sack', 'factor' => 25, 'price' => 2900, 'default' => false],
                ],
            ],
            [
                'name' => 'Cooking Oil',
                'base_unit' => 'Liter',
                'units' => [
                    ['unit' => 'Liter', 'factor' => 1, 'price' => 175, 'default' => true],
                    ['unit' => 'Can', 'factor' => 5, 'price' => 850, 'default' => false],
                ],
            ],
            [
                'name' => 'Bread',
                'base_unit' => 'Packet',
                'units' => [
                    ['unit' => 'Packet', 'factor' => 1, 'price' => 60, 'default' => true],
                    ['unit' => 'Carton', 'factor' => 12, 'price' => 660, 'default' => false],
                ],
            ],
        ];

        DB::transaction(function () use ($products) {
            foreach ($products as $item) {
                $baseUnit = Unit::where('name', $item['base_unit'])->first();

                if (! $baseUnit) {
                    throw new RuntimeException(
                        "Base unit '{$item['base_unit']}' not found. Seed units first."
                    );
                }

                $product = Product::firstOrNew([
                    'name' => $item['name'],
                ]);

                $product->unit_id = $baseUnit->id;

                // Only initialize stock for a new product.
                if (! $product->exists) {
                    $product->stock_quantity = 0;
                }

                $baseUnitPrice = collect($item['units'])->first(
                    fn (array $unit) => $unit['unit'] === $item['base_unit']
                )['price'];

                $product->price = $baseUnitPrice;
                $product->save();

                foreach ($item['units'] as $unitData) {
                    $unit = Unit::where('name', $unitData['unit'])->first();

                    if (! $unit) {
                        throw new RuntimeException(
                            "Selling unit '{$unitData['unit']}' not found. Seed units first."
                        );
                    }

                    ProductUnit::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'unit_id' => $unit->id,
                        ],
                        [
                            'conversion_factor' => $unitData['factor'],
                            'price' => $unitData['price'],
                            'is_default' => $unitData['default'],
                        ]
                    );
                }
            }
        });
    }
}
