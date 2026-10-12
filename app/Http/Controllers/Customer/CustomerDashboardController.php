<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        $customerId = auth()->id();

        $orders = Order::where('user_id', $customerId);

        $totalOrders = (clone $orders)->count();

        $pendingOrders = (clone $orders)
            ->where('status', 'PENDING')
            ->count();

        $activeOrders = (clone $orders)
            ->whereIn('status', [
                'ACCEPTED',
                'ASSIGNED',
                'PICKED_UP',
                'OUT_FOR_DELIVERY',
            ])
            ->count();

        $deliveredOrders = (clone $orders)
            ->where('status', 'DELIVERED')
            ->count();

        $cancelledOrders = (clone $orders)
            ->where('status', 'CANCELLED')
            ->count();

        $totalSpent = (clone $orders)
            ->where('status', 'DELIVERED')
            ->sum('total_amount');

        $recentOrders = Order::where('user_id', $customerId)
            ->with(['items.product', 'deliveryman'])
            ->latest()
            ->take(5)
            ->get();

        return view('customer.dashboard', compact(
            'totalOrders',
            'pendingOrders',
            'activeOrders',
            'deliveredOrders',
            'cancelledOrders',
            'totalSpent',
            'recentOrders'
        ));
    }
}
