<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    /**
     * Show the welcome page.
     */
    public function welcome()
    {
        return view('welcome');
    }


    public function index(Request $request): View
    {
        $products = Product::query()
            ->with([
                'unit',
                'productUnits.unit',
            ])
            ->where('stock_quantity', '>', 0)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();

                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->paginate(12);

        return view('customer.products.index', compact('products'));
    }

    public function show(Product $product): View
    {
        $product->load([
            'unit',
            'productUnits.unit',
        ]);

        return view('customer.products.show', compact('product'));
    }
}