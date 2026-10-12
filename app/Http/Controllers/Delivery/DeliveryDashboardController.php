<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Order;

class DeliveryDashboardController extends Controller
{
    public function index()
    {
        $deliveryManId = auth()->id();

        $orders = Order::query()
            ->where('delivery_man_id', $deliveryManId);

        $activeStatuses = [
            'ASSIGNED',
            'ACCEPTED',
            'PICKED_UP',
            'OUT_FOR_DELIVERY',
        ];

        $activeOrders = (clone $orders)
            ->whereIn('status', $activeStatuses);

        $stats = [
            'total_assigned' => (clone $orders)->count(),

            'active_deliveries' => (clone $activeOrders)->count(),

            'pending_deliveries' => (clone $orders)
                ->whereIn('status', ['PENDING', 'ASSIGNED'])
                ->count(),

            'completed_deliveries' => (clone $orders)
                ->where('status', 'DELIVERED')
                ->count(),

            'today_completed' => (clone $orders)
                ->where('status', 'DELIVERED')
                ->whereDate('updated_at', today())
                ->count(),
        ];

        $recentOrders = (clone $orders)
            ->with('customer:id,name,phone,latitude,longitude')
            ->whereNotIn('status', ['DELIVERED', 'CANCELLED'])
            ->latest()
            ->take(8)
            ->get();

        $todayCompletedOrders = (clone $orders)
            ->with('customer:id,name,phone')
            ->where('status', 'DELIVERED')
            ->whereDate('updated_at', today())
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('deliveryman.dashboard', compact(
            'stats',
            'recentOrders',
            'todayCompletedOrders'
        ));
    }
}
