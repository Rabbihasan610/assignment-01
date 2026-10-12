<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\DeliveryRouteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class RoutePlannerController extends Controller
{
    public function __construct(
        private DeliveryRouteService $routeService
    ) {}

 
    public function index()
    {
        $orders = Order::query()
            ->where('delivery_man_id', auth()->id())
            ->whereNotIn('status', ['DELIVERED', 'CANCELLED'])
            ->with('customer:id,name,latitude,longitude,phone')
            ->orderBy('id')
            ->get();

        return view('deliveryman.route-planner', [
            'orders' => $orders,
            'mapsKey' => config('services.google_maps.key'),
        ]);
    }


    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],
            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],
        ]);

        $orders = Order::query()
            ->where('delivery_man_id', auth()->id())
            ->whereNotIn('status', ['DELIVERED', 'CANCELLED'])
            ->with('customer:id,name,latitude,longitude,phone')
            ->orderBy('id')
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'message' => 'You have no pending orders to calculate a delivery route.',
            ], 422);
        }

        $stops = [];

        foreach ($orders as $order) {
            $customer = $order->customer;

            if (
                !$customer ||
                !is_numeric($customer->latitude) ||
                !is_numeric($customer->longitude)
            ) {
                return response()->json([
                    'message' => "Order #{$order->id}-এর customer location পাওয়া যায়নি।",
                ], 422);
            }

            $stops[] = [
                'order_id' => $order->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'latitude' => (float) $customer->latitude,
                'longitude' => (float) $customer->longitude,
            ];
        }

        try {
            $route = $this->routeService->optimizeRoute(
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                $stops
            );

            $route['route_source'] = 'google_routes_api';
            $route['estimated'] = false;

            $message = 'Google Routes API.';
        } catch (\Throwable $exception) {
            \Log::warning(
                'Google route failed; using local fallback.',
                [
                    'delivery_man_id' => auth()->id(),
                    'error' => $exception->getMessage(),
                ]
            );

            $route = $this->routeService->optimizeRouteLocally(
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                $stops
            );

            $message = 'Google API unavailable. Estimated fallback route.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $route,
        ]);
    }
}
