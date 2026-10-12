<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CartReservation;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
  
    private const RESERVATION_MINUTES = 15;

 
    private function deliveryFee(): float
    {
        return 30.00;
    }

    public function index(): View
    {
        $userId = auth()->id();

        CartReservation::where('user_id', $userId)
            ->where('expires_at', '<=', now())
            ->delete();

        $items = CartReservation::with([
            'product',
            'product.unit',
            'productUnit',
            'productUnit.unit',
        ])
            ->where('user_id', $userId)
            ->where('expires_at', '>', now())
            ->orderBy('id')
            ->get();

        $subtotal = $items->sum(function ($item) {
            return (float) $item->quantity * (float) $item->unit_price;
        });

        $deliveryFee = $this->deliveryFee();


        $total = $subtotal + $deliveryFee;

        return view('customer.cart.index', compact(
            'items',
            'subtotal',
            'deliveryFee',
            'total'
        ));
    }

  
    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_unit_id' => [
                'required',
                'integer',
                'exists:product_units,id',
            ],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:999999'],
        ]);

        $userId = auth()->id();

        DB::transaction(function () use ($validated, $userId) {
            $product = Product::whereKey($validated['product_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $productUnit = ProductUnit::whereKey($validated['product_unit_id'])
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();

            if (!$productUnit) {
                throw ValidationException::withMessages([
                    'product_unit_id' => 'Selected unit does not belong to this product.',
                ]);
            }

            $factor = (float) $productUnit->conversion_factor;
            $price = (float) $productUnit->price;
            $requestedQuantity = (float) $validated['quantity'];

            if ($factor <= 0 || $price < 0) {
                throw ValidationException::withMessages([
                    'product_unit_id' => 'This selling unit has an invalid price or conversion factor.',
                ]);
            }

            $reservation = CartReservation::where('user_id', $userId)
                ->where('product_unit_id', $productUnit->id)
                ->lockForUpdate()
                ->first();

            $newQuantity = $requestedQuantity
                + (float) ($reservation?->quantity ?? 0);

            $newBaseQuantity = $newQuantity * $factor;

            $reservedByCarts = CartReservation::where(
                'product_id',
                $product->id
            )
                ->where('expires_at', '>', now())
                ->when($reservation, function ($query) use ($reservation) {
                    $query->where('id', '!=', $reservation->id);
                })
                ->sum('base_quantity');

            $reservedByPendingOrders = $this->pendingOrderBaseQuantity(
                $product->id
            );

            $available = (float) $product->stock_quantity
                - (float) $reservedByCarts
                - $reservedByPendingOrders;

            if ($newBaseQuantity > $available + 0.000001) {
                throw ValidationException::withMessages([
                    'quantity' => sprintf(
                        'Insufficient stock. Available quantity is approximately %s %s.',
                        number_format(max(0, $available) / $factor, 4),
                        $productUnit->unit?->name ?? 'units'
                    ),
                ]);
            }

            CartReservation::updateOrCreate(
                [
                    'user_id' => $userId,
                    'product_unit_id' => $productUnit->id,
                ],
                [
                    'product_id' => $product->id,
                    'quantity' => $newQuantity,
                    'base_quantity' => $newBaseQuantity,
                    'unit_price' => $price,
                    'expires_at' => now()->addMinutes(
                        self::RESERVATION_MINUTES
                    ),
                ]
            );
        }, 3);

        return redirect()
            ->route('customer.cart.index')
            ->with('success', 'Product added to cart successfully.');
    }


    public function update(Request $request, CartReservation $reservation): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'numeric', 'gt:0', 'max:999999'],
        ]);

        abort_unless((int) $reservation->user_id === (int) auth()->id(), 404);

        DB::transaction(function () use ($reservation, $validated) {
            $lockedReservation = CartReservation::whereKey($reservation->id)
                ->where('user_id', auth()->id())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedReservation->expires_at->isPast()) {
                $lockedReservation->delete();

                throw ValidationException::withMessages([
                    'quantity' => 'Your cart reservation has expired. Please add the product again.',
                ]);
            }

            $product = Product::whereKey($lockedReservation->product_id)
                ->lockForUpdate()
                ->firstOrFail();

            $productUnit = ProductUnit::whereKey(
                $lockedReservation->product_unit_id
            )
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->firstOrFail();

            $factor = (float) $productUnit->conversion_factor;
            $price = (float) $productUnit->price;
            $quantity = (float) $validated['quantity'];

            if ($factor <= 0 || $price < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'This product unit has an invalid configuration.',
                ]);
            }

            $baseQuantity = $quantity * $factor;

            $reservedByCarts = CartReservation::where(
                'product_id',
                $product->id
            )
                ->where('expires_at', '>', now())
                ->where('id', '!=', $lockedReservation->id)
                ->sum('base_quantity');

            $reservedByPendingOrders = $this->pendingOrderBaseQuantity(
                $product->id
            );

            $available = (float) $product->stock_quantity
                - (float) $reservedByCarts
                - $reservedByPendingOrders;

            if ($baseQuantity > $available + 0.000001) {
                throw ValidationException::withMessages([
                    'quantity' => 'Requested quantity exceeds available stock.',
                ]);
            }

            $lockedReservation->update([
                'quantity' => $quantity,
                'base_quantity' => $baseQuantity,
                'unit_price' => $price,
                'expires_at' => now()->addMinutes(self::RESERVATION_MINUTES),
            ]);
        }, 3);

        return redirect()
            ->route('customer.cart.index')
            ->with('success', 'Cart quantity updated.');
    }


    public function remove(CartReservation $reservation): RedirectResponse
    {
        abort_unless((int) $reservation->user_id === (int) auth()->id(), 404);

        $reservation->delete();

        return redirect()
            ->route('customer.cart.index')
            ->with('success', 'Item removed from cart.');
    }


    public function checkout(): View|RedirectResponse
    {
        $userId = auth()->id();

        CartReservation::where('user_id', $userId)
            ->where('expires_at', '<=', now())
            ->delete();

        $items = CartReservation::with([
            'product',
            'product.unit',
            'productUnit',
            'productUnit.unit',
        ])
            ->where('user_id', $userId)
            ->where('expires_at', '>', now())
            ->orderBy('id')
            ->get();

        if ($items->isEmpty()) {
            return redirect()
                ->route('customer.cart.index')
                ->with('error', 'Your cart is empty or your reservations have expired.');
        }

        $subtotal = $items->sum(function ($item) {
            return (float) $item->quantity * (float) $item->unit_price;
        });

        $deliveryFee = $this->deliveryFee();
        $total = $subtotal + $deliveryFee;

        return view('customer.checkout', compact(
            'items',
            'subtotal',
            'deliveryFee',
            'total'
        ));
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delivery_address' => [
                'required',
                'string',
                'max:5000',
            ],

            'delivery_instructions' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'delivery_latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],

            'delivery_longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],
        ]);

        $userId = auth()->id();

        $order = DB::transaction(function () use ($validated, $userId) {

            $reservations = CartReservation::where('user_id', $userId)
                ->where('expires_at', '>', now())
                ->orderBy('product_id')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($reservations->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Your cart is empty or the reservations have expired. Please add products again.',
                ]);
            }

            $productIds = $reservations->pluck('product_id')
                ->unique()
                ->sort()
                ->values();

            $products = Product::whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $unitIds = $reservations->pluck('product_unit_id')
                ->unique()
                ->sort()
                ->values();

            $units = ProductUnit::with('unit')
                ->whereIn('id', $unitIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $preparedItems = [];

            $ownReservationIds = $reservations->pluck('id')->all();

            foreach ($reservations as $reservation) {

                $product = $products->get($reservation->product_id);

                $productUnit = $units->get($reservation->product_unit_id);

                if (
                    !$product ||
                    !$productUnit ||
                    (int) $productUnit->product_id !== (int) $product->id
                ) {
                    throw ValidationException::withMessages([
                        'cart' => 'A product or unit in your cart is no longer available.',
                    ]);
                }

                if ($reservation->expires_at->isPast()) {
                    throw ValidationException::withMessages([
                        'cart' => 'A cart reservation has expired. Please review your cart.',
                    ]);
                }

                $quantity = (float) $reservation->quantity;
                $factor = (float) $productUnit->conversion_factor;
                $price = (float) $productUnit->price;

                if (
                    $quantity <= 0 ||
                    $factor <= 0 ||
                    $price < 0
                ) {
                    throw ValidationException::withMessages([
                        'cart' => 'A cart item has an invalid quantity, unit or price.',
                    ]);
                }

                $baseQuantity = $quantity * $factor;

                $otherCartReservations = CartReservation::where(
                    'product_id',
                    $product->id
                )
                    ->where('expires_at', '>', now())
                    ->whereNotIn('id', $ownReservationIds)
                    ->sum('base_quantity');

                $pendingOrderQuantity = $this->pendingOrderBaseQuantity(
                    $product->id
                );

                $available = (float) $product->stock_quantity
                    - (float) $otherCartReservations
                    - (float) $pendingOrderQuantity;

                $thisProductBaseQuantity = $reservations
                    ->where('product_id', $product->id)
                    ->sum(function ($line) use ($units) {
                        $lineUnit = $units->get($line->product_unit_id);

                        return (float) $line->quantity
                            * (float) ($lineUnit?->conversion_factor ?? 0);
                    });

                if ($thisProductBaseQuantity > $available + 0.000001) {
                    throw ValidationException::withMessages([
                        'cart' => "Insufficient stock for {$product->name}. Please update your cart.",
                    ]);
                }

                $lineTotal = round($quantity * $price, 2);

                $subtotal += $lineTotal;

                $unitName = $productUnit->unit?->abbreviation
                    ?? $productUnit->unit?->name
                    ?? 'Unit';

                $preparedItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'sub_total' => $lineTotal,
                    'product_unit_id' => $productUnit->id,
                    'conversion_factor' => $factor,
                    'unit_name' => $unitName,
                ];
            }

            $subtotal = round($subtotal, 2);

            $deliveryFee = $this->deliveryFee();

            $total = round($subtotal + $deliveryFee, 2);

            $order = Order::create([
                'user_id' => $userId,
                'status' => 'PENDING',
                'total_amount' => $total,
                'refund_amount' => 0,
                'delivery_fee' => $deliveryFee,

                'delivery_address' => $validated['delivery_address'],
                'delivery_instructions' => $validated['delivery_instructions'] ?? null,

                'delivery_latitude' => $validated['delivery_latitude'],
                'delivery_longitude' => $validated['delivery_longitude'],
            ]);

            foreach ($preparedItems as $item) {
                $order->items()->create($item);
            }

            CartReservation::whereIn('id', $ownReservationIds)->delete();

            return $order;

        }, 3);

        return redirect()
            ->route('customer.products.index')
            ->with('success', "Order #{$order->id} placed successfully.");
    }

    private function pendingOrderBaseQuantity(int $productId): float
    {
        return (float) DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.product_id', $productId)
            ->where('orders.status', 'PENDING')
            ->selectRaw(
                'COALESCE(SUM(order_items.quantity * order_items.conversion_factor), 0) AS reserved_quantity'
            )
            ->value('reserved_quantity');
    }
}
