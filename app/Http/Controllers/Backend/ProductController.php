<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Unit;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $service
    ) {}

    public function index(): View
    {
        return view('backend.products.index', [
            'products' => $this->service->paginate(),
        ]);
    }

    public function create(): View
    {
        return view('backend.products.form', [
            'product' => new Product(),
            'units' => Unit::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request, true);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('products', 'public');
        }

        $this->service->store($data);

        return redirect()
            ->route('backend.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $product->load('productUnits.unit');

        return view('backend.products.form', [
            'product' => $product,
            'units' => Unit::query()->orderBy('name')->get(),
        ]);
    }

    public function update(
        Request $request,
        Product $product
    ): RedirectResponse {
        $data = $this->validateProduct($request, false);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('products', 'public');
        }

        $this->service->update($product, $data);

        return redirect()
            ->route('backend.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        try {
            $this->service->delete($product);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('backend.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    private function validateProduct(
        Request $request,
        bool $creating
    ): array {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'image' => ['nullable', 'image', 'max:2048'],

            'units' => ['required', 'array', 'min:1'],
            'units.*.unit_id' => [
                'required',
                'integer',
                'distinct',
                'exists:units,id',
            ],
            'units.*.conversion_factor' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'units.*.price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'units.*.is_default' => ['nullable', 'boolean'],
        ];

    
        $data = $request->validate($rules);

        $baseUnitId = (int) $data['unit_id'];

        $baseUnit = collect($data['units'])->first(
            fn (array $item) => (int) $item['unit_id'] === $baseUnitId
        );

        if (!$baseUnit) {
            throw ValidationException::withMessages([
                'units' => 'The base unit must be included in the product selling units.',
            ]);
        }

        if (abs((float) $baseUnit['conversion_factor'] - 1.0) > 0.000001) {
            throw ValidationException::withMessages([
                'units' => 'The base unit conversion factor must be 1.',
            ]);
        }

        $defaultCount = collect($data['units'])->filter(
            fn (array $item) => !empty($item['is_default'])
        )->count();

        if ($defaultCount > 1) {
            throw ValidationException::withMessages([
                'units' => 'Only one selling unit can be the default unit.',
            ]);
        }

        if ($defaultCount === 0) {
            foreach ($data['units'] as &$item) {
                $item['is_default'] =
                    (int) $item['unit_id'] === $baseUnitId;
            }

            unset($item);
        }

        $data['price'] = (float) $baseUnit['price'];

        return $data;
    }
}
