<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'deliveryman'])
            ->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customer) use ($search) {
                        $customer->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        $deliverymen = User::whereHas('roles', function ($q) {
            $q->where('name', 'delivery_man');
        })
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view(
            'backend.delivery-assignments.index',
            compact('orders', 'deliverymen')
        );
    }

    public function assign(Request $request, Order $order)
    {
        $data = $request->validate([
            'delivery_man_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
        ]);

        DB::transaction(function () use ($data, $order) {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($lockedOrder->status, [
                'ACCEPTED',
                'ASSIGNED',
            ], true)) {
                throw ValidationException::withMessages([
                    'delivery_man_id' =>
                    'Only accepted or already assigned orders can be assigned.',
                ]);
            }

            $deliveryman = User::whereKey($data['delivery_man_id'])
                ->where('is_active', true)
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'delivery_man');
                })
                ->first();

            if (!$deliveryman) {
                throw ValidationException::withMessages([
                    'delivery_man_id' =>
                    'Please select an active deliveryman.',
                ]);
            }

            $lockedOrder->delivery_man_id = $deliveryman->id;
            $lockedOrder->status = 'ASSIGNED';
            $lockedOrder->save();
        });

        return redirect()
            ->route('backend.delivery-assignments.index')
            ->with('success', 'Deliveryman assigned successfully.');
    }
}
