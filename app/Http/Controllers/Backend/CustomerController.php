<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->withCount('orders');

        $query->whereDoesntHave('roles', function ($q) {
            $q->whereIn('name', ['admin', 'delivery_man']);
        });

        // Search by name, email, or phone.
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");

                if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'phone')) {
                    $q->orWhere('phone', 'like', "%{$search}%");
                }
            });
        }

        $customers = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('backend.customers.index', compact('customers'));
    }
}
