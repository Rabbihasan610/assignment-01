<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryController extends Controller
{
    private function assignedOrderQuery()
    {
        return Order::query()
            ->where('delivery_man_id', auth()->id());
    }

    public function index()
    {
        $orders = $this->assignedOrderQuery()
            ->with('customer:id,name,phone,latitude,longitude')
            ->whereIn('status', [
                'ASSIGNED',
                'ACCEPTED',
                'PICKED_UP',
                'OUT_FOR_DELIVERY',
            ])
            ->latest()
            ->paginate(10);

        return view('deliveryman.assigned-drops.index', compact('orders'));
    }

    public function show($assignment)
    {
        $order = $this->assignedOrderQuery()
            ->with([
                'customer:id,name,phone,latitude,longitude',
                'items.product',
                'items.productUnit',
            ])
            ->findOrFail($assignment);

        return view('deliveryman.assigned-drops.show', compact('order'));
    }

    public function partialDelivery(Request $request, $assignment)
    {
        $validated = $request->validate([
            'request_key' => ['required', 'uuid'],

            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
        ]);

        $hasPositiveQuantity = collect($validated['items'])
            ->contains(fn($item) => (float) $item['quantity'] > 0);

        if (!$hasPositiveQuantity) {
            throw ValidationException::withMessages([
                'items' => 'Enter a delivery quantity for at least one product.',
            ]);
        }

        $order = $this->assignedOrderQuery()
            ->with('customer:id,name,latitude,longitude')
            ->findOrFail($assignment);

        if (in_array($order->status, ['DELIVERED', 'CANCELLED'], true)) {
            return back()->with('error', 'This order is already completed or cancelled.');
        }

        $customer = $order->customer;

        if (
            !$customer ||
            !is_numeric($customer->latitude) ||
            !is_numeric($customer->longitude)
        ) {
            return back()->with(
                'error',
                'Customer location is not available.'
            );
        }

        $withinGeofence = is_within_delivery_geofence(
            (float) $customer->latitude,
            (float) $customer->longitude,
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            0.100
        );

        if (!$withinGeofence) {
            return back()->with(
                'error',
                'You must be within 100 metres of the customer to record delivery.'
            );
        }

        $result = DB::transaction(function () use (
            $assignment,
            $validated
        ) {
            $order = $this->assignedOrderQuery()
                ->lockForUpdate()
                ->findOrFail($assignment);

            $existingRecord = DB::table('delivery_records')
                ->where('request_key', $validated['request_key'])
                ->first();

            if ($existingRecord) {
                if (
                    (int) $existingRecord->order_id !== (int) $order->id ||
                    (int) $existingRecord->delivery_man_id !== (int) auth()->id()
                ) {
                    throw ValidationException::withMessages([
                        'request_key' => 'Invalid delivery request key.',
                    ]);
                }

                return 'duplicate';
            }

            if (in_array($order->status, ['DELIVERED', 'CANCELLED'], true)) {
                throw ValidationException::withMessages([
                    'items' => 'This order cannot receive another delivery.',
                ]);
            }

            $recordId = DB::table('delivery_records')->insertGetId([
                'order_id' => $order->id,
                'delivery_man_id' => auth()->id(),
                'request_key' => $validated['request_key'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($validated['items'] as $input) {
                $deliveryQty = round((float) $input['quantity'], 2);

                if ($deliveryQty <= 0) {
                    continue;
                }

                $item = OrderItem::query()
                    ->where('order_id', $order->id)
                    ->whereKey($input['item_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $alreadyDelivered = (float) $item->delivered_quantity;
                $remaining = round(
                    (float) $item->quantity - $alreadyDelivered,
                    2
                );

                if ($deliveryQty > $remaining) {
                    throw ValidationException::withMessages([
                        'items' => "Delivery quantity exceeds the remaining quantity for order item #{$item->id}.",
                    ]);
                }

                $conversionFactor = (float) $item->conversion_factor;

                if ($conversionFactor <= 0) {
                    throw ValidationException::withMessages([
                        'items' => "Invalid unit conversion for order item #{$item->id}.",
                    ]);
                }

                $baseQty = round($deliveryQty * $conversionFactor, 4);

                $product = Product::query()
                    ->whereKey($item->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $availableStock = (float) $product->stock_quantity;

                if ($availableStock < $baseQty) {
                    throw ValidationException::withMessages([
                        'items' => "Insufficient stock for {$product->name}. Available: {$availableStock}. Required: {$baseQty}.",
                    ]);
                }

                $product->stock_quantity = round(
                    $availableStock - $baseQty,
                    4
                );
                $product->save();

                $item->delivered_quantity = round(
                    $alreadyDelivered + $deliveryQty,
                    2
                );
                $item->save();

                DB::table('delivery_record_items')->insert([
                    'delivery_record_id' => $recordId,
                    'order_item_id' => $item->id,
                    'quantity' => $deliveryQty,
                    'base_quantity' => $baseQty,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $hasRemaining = OrderItem::query()
                ->where('order_id', $order->id)
                ->whereRaw('delivered_quantity < quantity')
                ->exists();

            $order->status = $hasRemaining
                ? 'OUT_FOR_DELIVERY'
                : 'DELIVERED';

            $order->save();

            return $hasRemaining ? 'partial' : 'complete';
        }, 3);

        if ($result === 'duplicate') {
            return redirect()
                ->route('delivery.assigned-drops.show', $assignment)
                ->with('success', 'This delivery request was already processed.');
        }

        return redirect()
            ->route('deliveryman.assigned-drops.show', $assignment)
            ->with(
                'success',
                $result === 'complete'
                    ? 'All items have been delivered successfully.'
                    : 'Partial delivery saved and stock updated successfully.'
            );
    }

    public function completeDelivery(Request $request, $assignment)
    {
        
        $order = $this->assignedOrderQuery()
            ->with('items')
            ->findOrFail($assignment);

        if (in_array($order->status, ['DELIVERED', 'CANCELLED'], true)) {
            return back()->with(
                'error',
                'This order is already completed or cancelled.'
            );
        }

        $hasRemaining = $order->items->contains(
            fn($item) =>
            (float) $item->delivered_quantity < (float) $item->quantity
        );

        if ($hasRemaining) {
            return back()->with(
                'error',
                'Some items are still pending. Record their delivered quantities first.'
            );
        }

        $order->status = 'DELIVERED';
        $order->save();

        return redirect()
            ->route('deliveryman.assigned-drops.index')
            ->with('success', "Order #{$order->id} successfully delivered.");
    }

    public function history()
    {
        $orders = $this->assignedOrderQuery()
            ->with('customer:id,name,phone')
            ->whereIn('status', ['DELIVERED', 'CANCELLED'])
            ->latest('updated_at')
            ->paginate(15);

        return view('deliveryman.history', compact('orders'));
    }
}
