<?php
namespace App\Services;

use App\Models\Unit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UnitService
{
    public function paginate(): LengthAwarePaginator
    {
        return Unit::query()
            ->withCount('productUnits')
            ->latest()
            ->paginate(15);
    }

    public function store(array $data): Unit
    {
        return Unit::create($data);
    }

    public function update(Unit $unit, array $data): Unit
    {
        $unit->update($data);

        return $unit->refresh();
    }

    public function delete(Unit $unit): void
    {
        DB::transaction(function () use ($unit) {
            $unit = Unit::query()
                ->whereKey($unit->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($unit->products()->exists()) {
                throw new RuntimeException(
                    'This unit is being used as a product base unit.'
                );
            }

            if ($unit->productUnits()->exists()) {
                throw new RuntimeException(
                    'This unit is assigned to products. Remove those product-unit assignments first.'
                );
            }

            $unit->delete();
        });
    }
}
