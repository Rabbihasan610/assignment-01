<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DeliverymanController extends Controller
{
    
    private function deliverymanQuery()
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'delivery_man');
        });
    }


    public function index(Request $request)
    {
        $query = $this->deliverymanQuery();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $deliverymen = $query
            ->withCount('deliveryOrders')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('backend.deliverymen.index', compact('deliverymen'));
    }

 
    public function create()
    {
        return view('backend.deliverymen.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $role = Role::where('name', 'delivery_man')->first();

        if (!$role) {
            return back()
                ->withInput($request->except(
                    'password',
                    'password_confirmation'
                ))
                ->withErrors([
                    'role' => 'Deliveryman role was not found. Create this role first.',
                ]);
        }

        DB::transaction(function () use ($data, $role) {
            $deliveryman = new User();

            $deliveryman->name = $data['name'];
            $deliveryman->email = $data['email'];
            $deliveryman->password = Hash::make($data['password']);
            $deliveryman->is_active = true;
            $deliveryman->save();

            $deliveryman->roles()->syncWithoutDetaching([
                $role->id,
            ]);
        });

        return redirect()
            ->route('backend.deliverymen.index')
            ->with('success', 'Deliveryman created successfully.');
    }


    public function edit(User $deliveryman)
    {
        abort_unless(
            $deliveryman->roles()
                ->where('name', 'delivery_man')
                ->exists(),
            404
        );

        return view(
            'backend.deliverymen.form',
            compact('deliveryman')
        );
    }

 
    public function update(Request $request, User $deliveryman)
    {
        abort_unless(
            $deliveryman->roles()
                ->where('name', 'delivery_man')
                ->exists(),
            404
        );

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($deliveryman->id),
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $deliveryman->name = $data['name'];
        $deliveryman->email = $data['email'];

        if (!empty($data['password'])) {
            $deliveryman->password = Hash::make(
                $data['password']
            );
        }

        $deliveryman->save();

        return redirect()
            ->route('backend.deliverymen.index')
            ->with('success', 'Deliveryman updated successfully.');
    }

 
    public function toggleStatus(User $deliveryman)
    {
        abort_unless(
            $deliveryman->roles()
                ->where('name', 'delivery_man')
                ->exists(),
            404
        );

        if ($deliveryman->is_active) {
            $hasActiveOrders = Order::where(
                'delivery_man_id',
                $deliveryman->id
            )
                ->whereIn('status', [
                    'ASSIGNED',
                    'PICKED_UP',
                    'OUT_FOR_DELIVERY',
                ])
                ->exists();

            if ($hasActiveOrders) {
                return back()->with(
                    'error',
                    'This deliveryman has active orders. Reassign them before deactivating.'
                );
            }
        }

        $deliveryman->is_active = !$deliveryman->is_active;
        $deliveryman->save();

        return back()->with(
            'success',
            'Deliveryman status updated successfully.'
        );
    }

  
    public function destroy(User $deliveryman)
    {
        abort_unless(
            $deliveryman->roles()
                ->where('name', 'delivery_man')
                ->exists(),
            404
        );

        $hasOrders = Order::where(
            'delivery_man_id',
            $deliveryman->id
        )->exists();

        if ($hasOrders) {
            return back()->with(
                'error',
                'This deliveryman has order history. Deactivate the account instead of deleting it.'
            );
        }

        $deliveryman->roles()
            ->detach();

        $deliveryman->delete();

        return redirect()
            ->route('backend.deliverymen.index')
            ->with('success', 'Deliveryman deleted successfully.');
    }
}
