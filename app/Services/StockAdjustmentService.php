<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\StockAdjustment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockAdjustmentService
{
    public function paginate(): LengthAwarePaginator
    {
        return StockAdjustment::query()
            ->with([
                'product.unit',
                'productUnit.unit',
                'user',
            ])
            ->latest()
            ->paginate(15);
    }

    public function store(array $data): StockAdjustment
    {
        return DB::transaction(function () use ($data) {

            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($data['product_id']);

            $productUnit = ProductUnit::query()
                ->where('product_id', $product->id)
                ->whereKey($data['product_unit_id'])
                ->lockForUpdate()
                ->first();

            if (! $productUnit) {
                throw ValidationException::withMessages([
                    'product_unit_id' =>
                        'The selected selling unit does not belong to this product.',
                ]);
            }

            $quantity = (float) $data['quantity'];
            $conversionFactor = (float) $productUnit->conversion_factor;

            if (! is_finite($quantity) || $quantity <= 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Quantity must be greater than zero.',
                ]);
            }

            if (! is_finite($conversionFactor) || $conversionFactor <= 0) {
                throw ValidationException::withMessages([
                    'product_unit_id' => 'Invalid unit conversion factor.',
                ]);
            }

            $baseQuantity = round(
                $quantity * $conversionFactor,
                4
            );

            $currentStock = (float) $product->stock_quantity;
            $type = $data['adjustment_type'];

            if (! in_array($type, [
                'IN',
                'OUT',
                'DAMAGE',
                'RETURN',
                'ADJUSTMENT',
            ], true)) {
                throw ValidationException::withMessages([
                    'adjustment_type' => 'Invalid stock adjustment type.',
                ]);
            }


            if (in_array($type, ['IN', 'RETURN', 'ADJUSTMENT'], true)) {
                $newStock = round($currentStock + $baseQuantity, 4);
            } else {
                if ($baseQuantity > $currentStock) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Insufficient stock for this adjustment.',
                    ]);
                }

                $newStock = round($currentStock - $baseQuantity, 4);
            }

            $product->stock_quantity = $newStock;
            $product->save();

            return StockAdjustment::create([
                'product_id' => $product->id,
                'product_unit_id' => $productUnit->id,
                'adjustment_type' => $type,
                'quantity' => $quantity,
                'conversion_factor' => $conversionFactor,
                'base_quantity' => $baseQuantity,
                'reason' => $data['reason'] ?? null,
                'user_id' => auth()->id(),
            ]);
        });
    }
}
