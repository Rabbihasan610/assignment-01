<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Services\UnitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function __construct(
        private readonly UnitService $service
    ) {}

    public function index(): View
    {
        $units = Unit::query()
            ->withCount([
                'products',
                'productUnits',
            ])
            ->orderBy('name')
            ->paginate(15);

        return view('backend.units.index', compact('units'));
    }

    public function create(): View
    {
        return view('backend.units.form', [
            'unit' => new Unit(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:units,name',
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'abbreviation' => [
                'nullable',
                'string',
                'max:50',
                'unique:units,abbreviation',
            ],
        ]);

        $this->service->store($data);

        return redirect()
            ->route('backend.units.index')
            ->with('success', 'Unit created successfully.');
    }

    public function edit(Unit $unit): View
    {
        return view('backend.units.form', compact('unit'));
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'name')->ignore($unit->id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'abbreviation' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('units', 'abbreviation')->ignore($unit->id),
            ],
        ]);

        $this->service->update($unit, $data);

        return redirect()
            ->route('backend.units.index')
            ->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        try {
            $this->service->delete($unit);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Unit deleted successfully.');
    }
}
