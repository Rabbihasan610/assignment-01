@php
	$user = auth()->user();

	$roleNames = $user?->roles?->pluck('name')->all() ?? [];
	$role = $roleNames[0] ?? 'customer';

	$menu = [
	    [
	        'label' => 'Dashboard',
	        'icon' => 'bi-speedometer2',
	        'route' => match ($role) {
	            'admin' => 'backend.dashboard',
	            'deliveryman' => 'delivery.dashboard',
	            default => 'customer.dashboard',
	        },
	        'roles' => ['admin', 'customer', 'deliveryman'],
	    ],
	    [
	        'label' => 'Products',
	        'icon' => 'bi-box-seam',
	        'route' => 'backend.products.index',
	        'roles' => ['admin'],
	    ],
	    [
	        'label' => 'Units',
	        'icon' => 'bi-rulers',
	        'route' => 'backend.units.index',
	        'roles' => ['admin'],
	    ],
	    [
	        'label' => 'Stock Adjustments',
	        'icon' => 'bi-arrow-left-right',
	        'route' => 'backend.stock-adjustments.index',
	        'roles' => ['admin'],
	    ],
	    [
	        'label' => 'Customers',
	        'icon' => 'bi-people',
	        'route' => 'backend.customers.index',
	        'roles' => ['admin'],
	    ],
	    [
	        'label' => 'Delivery Men',
	        'icon' => 'bi-person-badge',
	        'route' => 'backend.deliverymen.index',
	        'roles' => ['admin'],
	    ],
	    [
	        'label' => 'Orders',
	        'icon' => 'bi-bag-check',
	        'route' => 'backend.orders.index',
	        'roles' => ['admin'],
	    ],
	    [
	        'label' => 'Delivery Assignments',
	        'icon' => 'bi-truck',
	        'route' => 'backend.delivery-assignments.index',
	        'roles' => ['admin'],
	    ],
	    [
	        'label' => 'Dashboard',
	        'icon' => 'bi-speedometer2',
	        'route' => 'deliveryman.dashboard',
	        'roles' => ['delivery_man'],
	    ],
	    [
	        'label' => 'Find Best Route',
	        'icon' => 'bi-sign-turn-right',
	        'route' => 'deliveryman.route-planner',
	        'roles' => ['delivery_man'],
	    ],
	    [
	        'label' => 'Assigned Drops',
	        'icon' => 'bi-geo-alt',
	        'route' => 'deliveryman.assigned-drops.index',
	        'roles' => ['delivery_man'],
	    ],
	    [
	        'label' => 'Delivery History',
	        'icon' => 'bi-clock-history',
	        'route' => 'deliveryman.history',
	        'roles' => ['delivery_man'],
	    ],
	    [
	        'label' => 'Products',
	        'icon' => 'bi-box-seam',
	        'route' => 'customer.products.index',
	        'roles' => ['customer'],
	    ],

	    [
	        'label' => 'My Cart',
	        'icon' => 'bi-cart',
	        'route' => 'customer.cart.index',
	        'roles' => ['customer'],
	    ],

	    [
	        'label' => 'My Orders',
	        'icon' => 'bi-bag',
	        'route' => 'customer.orders.index',
	        'roles' => ['customer'],
	    ],
	];
@endphp

<div class="d-flex flex-column h-100 p-3 bg-white">
	<div class="d-flex align-items-center gap-2 pb-3 mb-3 border-bottom">
		<i class="bi bi-box-seam text-primary fs-4"></i>
		<span class="fs-5 fw-bold">Panel</span>
	</div>

	<ul class="nav flex-column mb-auto">
		@foreach ($menu as $item)
			@if (array_intersect($roleNames, $item['roles']))
				<li class="nav-item mb-1">
					<a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
						class="nav-link text-secondary rounded px-3 py-2">
						<i class="bi {{ $item['icon'] }} me-2"></i>
						{{ $item['label'] }}
					</a>
				</li>
			@endif
		@endforeach
	</ul>

	<div class="border-top pt-3">
		<div class="d-flex align-items-center justify-content-between gap-2">
			<div class="small">
				<div class="fw-bold text-truncate" style="max-width: 140px;">
					{{ $user?->name }}
				</div>

				<span class="badge bg-secondary-subtle text-secondary">
					{{ strtoupper($role) }}
				</span>
			</div>

			<form action="{{ route('logout') }}" method="POST">
				@csrf

				<button type="submit" class="btn btn-sm btn-outline-danger" title="Logout">
					<i class="bi bi-box-arrow-right"></i>
				</button>
			</form>
		</div>
	</div>
</div>
