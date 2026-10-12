<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\StockAdjustmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockAdjustmentController extends Controller
{
    public function __construct(
        private readonly StockAdjustmentService $service
    ) {}

    public function index(): View
    {
        return view('backend.stock-adjustments.index', [
            'adjustments' => $this->service->paginate(),
        ]);
    }

    public function create(): View
    {
        return view('backend.stock-adjustments.create', [
            'products' => Product::query()
                ->with(['unit', 'productUnits.unit'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'product_unit_id' => [
                'required',
                'integer',
                'exists:product_units,id',
            ],

            'adjustment_type' => [
                'required',
                Rule::in([
                    'IN',
                    'OUT',
                    'DAMAGE',
                    'RETURN',
                    'ADJUSTMENT',
                ]),
            ],

            'quantity' => [
                'required',
                'numeric',
                'gt:0',
                'max:999999999',
            ],

            'reason' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $this->service->store($data);

        return redirect()
            ->route('backend.stock-adjustments.index')
            ->with('success', 'Stock adjusted successfully.');
    }
}