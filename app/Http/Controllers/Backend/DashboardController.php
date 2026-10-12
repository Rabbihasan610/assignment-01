<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index()
    {
        $user = auth()->user();

        abort_if(!$user, 401);

        $metrics = $this->dashboardService->getDashboardMetrics($user);

        $products = Product::orderBy('name')
            ->take(8)
            ->get();

        $recentOrders = Order::with([
            'customer',
            'deliveryman',
            'items.product',
        ])
            ->latest()
            ->take(10)
            ->get();

        $deliverymen = User::whereHas('roles', function ($query) {
            $query->where('name', 'deliveryman');
        })->paginate(10);

        return view('backend.dashboard', compact(
            'metrics',
            'products',
            'recentOrders',
            'deliverymen'
        ));
    }
}
