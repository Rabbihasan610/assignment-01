<?php
namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Exception;

class DashboardService
{
    public function getDashboardMetrics(User $user): array
    {
        $roleNames = $user->roles()->pluck('name')->toArray();

        if (in_array('admin', $roleNames, true)) {
            return [
                'total_orders' => Order::count(),

                'pending_orders' => Order::where(
                    'status',
                    'PENDING'
                )->count(),

                'active_deliveries' => Order::whereIn('status', [
                    'ASSIGNED',
                    'PICKED_UP',
                    'OUT_FOR_DELIVERY',
                ])->count(),

                'low_stock_products' => Product::where(
                    'stock_quantity',
                    '<=',
                    10
                )->count(),
            ];
        }

        if (in_array('customer', $roleNames, true)) {
            return [
                'my_orders' => Order::where(
                    'user_id',
                    $user->id
                )->count(),

                'active_orders' => Order::where(
                    'user_id',
                    $user->id
                )->whereNotIn('status', [
                    'DELIVERED',
                    'CANCELLED',
                ])->count(),

                'completed_orders' => Order::where(
                    'user_id',
                    $user->id
                )->where('status', 'DELIVERED')->count(),
            ];
        }

        if (in_array('deliveryman', $roleNames, true)) {
            return [
                'assigned_jobs' => Order::where(
                    'delivery_man_id',
                    $user->id
                )->whereIn('status', [
                    'ASSIGNED',
                    'PICKED_UP',
                ])->count(),

                'delivered_today' => Order::where(
                    'delivery_man_id',
                    $user->id
                )->where('status', 'DELIVERED')
                    ->whereDate('updated_at', today())
                    ->count(),
            ];
        }

        throw new Exception('Unauthorized role profile');
    }

    public function checkPermission(
        User $user,
        string $ability
    ): bool {
        $matrix = [
            'admin' => [
                'manage_inventory',
                'assign_order',
                'view_all_orders',
                'adjust_stock',
            ],

            'customer' => [
                'create_order',
                'view_own_orders',
                'request_return',
            ],

            'deliveryman' => [
                'view_assigned_orders',
                'pickup_parcel',
                'complete_delivery',
            ],
        ];

        $roleNames = $user->roles()->pluck('name')->toArray();

        foreach ($roleNames as $roleName) {
            if (in_array(
                $ability,
                $matrix[$roleName] ?? [],
                true
            )) {
                return true;
            }
        }

        return false;
    }
}
