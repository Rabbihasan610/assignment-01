<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{

    public function index()
    {
        $orders = Order::with([
            'customer',
            'items.product',
            'deliveryman',
        ])
            ->latest()
            ->paginate(15);

        return view('backend.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load([
            'customer',
            'items.product',
            'items.productUnit.unit',
            'deliveryman',
        ]);

        $deliverymen = User::whereHas('roles', function ($q) {
            $q->where('name', 'delivery_man');
        })
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('backend.orders.show', compact('order', 'deliverymen'));
    }


    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => [
                'required',
                Rule::in(['ACCEPTED', 'CANCELLED']),
            ],
            'cancellation_reason' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        DB::transaction(function () use ($order, $data) {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status !== 'PENDING') {
                abort(422, 'Only pending orders can be reviewed.');
            }

            if ($data['status'] === 'CANCELLED') {
                $lockedOrder->update([
                    'status' => 'CANCELLED',
                    'cancellation_reason' =>
                    $data['cancellation_reason'] ?? 'Rejected by admin',
                ]);

                return;
            }

            $lockedOrder->update([
                'status' => 'ACCEPTED',
                'accepted_by' => auth()->id(),
                'accepted_at' => now(),
                'cancellation_reason' => null,
            ]);
        }, 3);

        return back()->with(
            'success',
            $data['status'] === 'ACCEPTED'
                ? 'Order accepted successfully. You can now assign a deliveryman.'
                : 'Order cancelled successfully.'
        );
    }


    public function assignDeliveryman(Request $request, Order $order)
    {
        $data = $request->validate([
            'delivery_man_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
        ]);

        DB::transaction(function () use ($order, $data) {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($lockedOrder->status, ['ACCEPTED', 'ASSIGNED'], true)) {
                abort(
                    422,
                    'Only accepted or already assigned orders can be assigned.'
                );
            }

            $deliveryman = User::whereKey($data['delivery_man_id'])
                ->whereHas('role', function ($query) {
                    $query->where('name', 'deliveryman');
                })
                ->first();

            if (!$deliveryman) {
                abort(422, 'The selected user is not a deliveryman.');
            }

            $lockedOrder->update([
                'delivery_man_id' => $deliveryman->id,
                'status' => 'ASSIGNED',
            ]);
        }, 3);

        return back()->with(
            'success',
            'Deliveryman assigned successfully.'
        );
    }
}
