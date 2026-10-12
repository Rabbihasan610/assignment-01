<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Unit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ProductService
{
    public function paginate(): LengthAwarePaginator
    {
        return Product::query()
            ->with(['unit', 'productUnits.unit'])
            ->latest()
            ->paginate(10);
    }

    public function store(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $units = $data['units'];

            unset($data['units']);

            $product = Product::create($data);

            $this->syncProductUnits($product, $units);

            return $product->load(['unit', 'productUnits.unit']);
        });
    }

    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($product->id);

            $units = $data['units'];

            unset($data['units'], $data['stock_quantity']);

            $newBaseUnitId = (int) $data['unit_id'];

            if ($newBaseUnitId !== (int) $product->unit_id) {
                $hasOrders = DB::table('order_items')
                    ->where('product_id', $product->id)
                    ->exists();

                $hasReservations = $this->activeReservationsExist($product);

                if (
                    (float) $product->stock_quantity > 0
                    || $hasOrders
                    || $hasReservations
                ) {
                    throw ValidationException::withMessages([
                        'unit_id' => 'The base unit cannot be changed because this product has stock, order history, or active reservations.',
                    ]);
                }
            }

            $product->update($data);

            $this->syncProductUnits($product, $units);

            return $product->refresh()
                ->load(['unit', 'productUnits.unit']);
        });
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($product->id);

            $hasOrders = DB::table('order_items')
                ->where('product_id', $product->id)
                ->exists();

            if ($hasOrders) {
                throw new RuntimeException(
                    'This product has order history and cannot be deleted.'
                );
            }

            if ($this->activeReservationsExist($product)) {
                throw new RuntimeException(
                    'This product has active cart reservations and cannot be deleted.'
                );
            }

            $product->productUnits()->delete();

            $product->delete();
        });
    }

    private function syncProductUnits(Product $product, array $units): void
    {
        $unitIds = collect($units)
            ->pluck('unit_id')
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($unitIds->unique()->count() !== $unitIds->count()) {
            throw ValidationException::withMessages([
                'units' => 'A unit cannot be added more than once.',
            ]);
        }

        if (!$unitIds->contains((int) $product->unit_id)) {
            throw ValidationException::withMessages([
                'units' => 'The product base unit must be included in its selling units.',
            ]);
        }

        $baseUnit = collect($units)->first(
            fn (array $unit) =>
                (int) $unit['unit_id'] === (int) $product->unit_id
        );

        if (
            !$baseUnit
            || abs((float) $baseUnit['conversion_factor'] - 1.0) > 0.000001
        ) {
            throw ValidationException::withMessages([
                'units' => 'The base unit conversion factor must be 1.',
            ]);
        }

        $defaultCount = collect($units)->filter(
            fn (array $unit) => !empty($unit['is_default'])
        )->count();

        if ($defaultCount !== 1) {
            throw ValidationException::withMessages([
                'units' => 'Exactly one selling unit must be selected as default.',
            ]);
        }

        $existingUnits = $product->productUnits()
            ->lockForUpdate()
            ->get()
            ->keyBy('unit_id');

        $removedUnits = $existingUnits->keys()
            ->diff($unitIds);

        foreach ($removedUnits as $removedUnitId) {
            $existingUnit = $existingUnits->get($removedUnitId);

            if ($this->activeReservationsExist($product, $existingUnit->id)) {
                throw ValidationException::withMessages([
                    'units' => 'A selling unit with an active reservation cannot be removed.',
                ]);
            }
        }

        foreach ($units as $unitData) {
            $unitId = (int) $unitData['unit_id'];

            $existingUnit = $existingUnits->get($unitId);

            if ($existingUnit) {
                $factorChanged = abs(
                    (float) $existingUnit->conversion_factor
                    - (float) $unitData['conversion_factor']
                ) > 0.000001;

                if (
                    $factorChanged
                    && $this->activeReservationsExist($product, $existingUnit->id)
                ) {
                    throw ValidationException::withMessages([
                        'units' => 'The conversion factor cannot be changed while this unit has active reservations.',
                    ]);
                }
            }

            ProductUnit::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'unit_id' => $unitId,
                ],
                [
                    'conversion_factor' => $unitData['conversion_factor'],
                    'price' => $unitData['price'],
                    'is_default' => (bool) ($unitData['is_default'] ?? false),
                ]
            );
        }

        if ($removedUnits->isNotEmpty()) {
            $product->productUnits()
                ->whereIn('unit_id', $removedUnits)
                ->delete();
        }

        $basePrice = collect($units)->first(
            fn (array $unit) =>
                (int) $unit['unit_id'] === (int) $product->unit_id
        )['price'];

        $product->update([
            'price' => $basePrice,
        ]);
    }

    private function activeReservationsExist(
        Product $product,
        ?int $productUnitId = null
    ): bool {
        $query = DB::table('cart_reservations')
            ->where('product_id', $product->id)
            ->where('expires_at', '>', now());

        if ($productUnitId !== null) {
            $query->where('product_unit_id', $productUnitId);
        }

        return $query->exists();
    }
}
