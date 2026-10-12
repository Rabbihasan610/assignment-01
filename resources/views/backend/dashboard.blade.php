@extends('backend.layouts.app', [
    'title' => 'Dashboard',
    'page' => 'dashboard',
])

@php
	$user = auth()->user();

	$roleNames = $user?->roles?->pluck('name')->all() ?? [];

	$isAdmin = in_array('admin', $roleNames, true);
	$isCustomer = in_array('customer', $roleNames, true);
	$isDeliveryman = in_array('deliveryman', $roleNames, true);
@endphp

@section('content')
	<div class="container-fluid px-0">

		<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
			<div>
				<h4 class="fw-bold mb-1">Dashboard</h4>
				<p class="text-muted mb-0">
					Welcome back, {{ $user?->name }}!
				</p>
			</div>

			<div class="d-flex gap-2">
				@if ($isAdmin)
					<x-button variant="outline" size="sm">
						<i class="bi bi-boxes me-1"></i>
						Inventory
					</x-button>
				@endif

				@if ($isAdmin || $isCustomer)
					<x-button variant="primary" size="sm">
						<i class="bi bi-plus-lg me-1"></i>
						Create Order
					</x-button>
				@endif
			</div>
		</div>

		<div class="row g-3 mb-4">

			@php
				$metricsList = [];

				if ($isAdmin) {
				    $metricsList = [
				        [
				            'label' => 'Pending Orders',
				            'value' => $metrics['pending_orders'] ?? 0,
				            'description' => 'Awaiting rider',
				            'color' => 'warning',
				            'icon' => 'clock-history',
				        ],
				        [
				            'label' => 'Active Deliveries',
				            'value' => $metrics['active_deliveries'] ?? 0,
				            'description' => 'En route',
				            'color' => 'primary',
				            'icon' => 'bicycle',
				        ],
				        [
				            'label' => 'Total Orders',
				            'value' => $metrics['total_orders'] ?? 0,
				            'description' => 'System lifetime',
				            'color' => 'success',
				            'icon' => 'cart-check',
				        ],
				        [
				            'label' => 'Low Stock Alert',
				            'value' => $metrics['low_stock_products'] ?? 0,
				            'description' => '10 units or less',
				            'color' => 'danger',
				            'icon' => 'exclamation-triangle',
				        ],
				    ];
				} elseif ($isCustomer) {
				    $metricsList = [
				        [
				            'label' => 'My Orders',
				            'value' => $metrics['my_orders'] ?? 0,
				            'description' => 'All my orders',
				            'color' => 'primary',
				            'icon' => 'cart',
				        ],
				        [
				            'label' => 'Active Orders',
				            'value' => $metrics['active_orders'] ?? 0,
				            'description' => 'Currently processing',
				            'color' => 'warning',
				            'icon' => 'clock-history',
				        ],
				        [
				            'label' => 'Completed Orders',
				            'value' => $metrics['completed_orders'] ?? 0,
				            'description' => 'Successfully delivered',
				            'color' => 'success',
				            'icon' => 'check-circle',
				        ],
				    ];
				} elseif ($isDeliveryman) {
				    $metricsList = [
				        [
				            'label' => 'Assigned Jobs',
				            'value' => $metrics['assigned_jobs'] ?? 0,
				            'description' => 'Pending deliveries',
				            'color' => 'primary',
				            'icon' => 'box-seam',
				        ],
				        [
				            'label' => 'Delivered Today',
				            'value' => $metrics['delivered_today'] ?? 0,
				            'description' => 'Successfully completed',
				            'color' => 'success',
				            'icon' => 'patch-check',
				        ],
				    ];
				}
			@endphp

			@foreach ($metricsList as $metric)
				<div class="col-sm-6 {{ count($metricsList) === 4 ? 'col-xl-3' : 'col-xl-4' }}">
					<div class="card border-0 shadow-sm rounded-3 p-3 h-100">
						<div class="d-flex justify-content-between align-items-center">
							<div>
								<span class="text-muted small fw-medium">
									{{ $metric['label'] }}
								</span>

								<h3 class="fw-bold my-2 text-{{ $metric['color'] }}">
									{{ $metric['value'] }}
								</h3>

								<small class="text-muted">
									{{ $metric['description'] }}
								</small>
							</div>

							<div class="bg-{{ $metric['color'] }} bg-opacity-10 text-{{ $metric['color'] }} rounded-3 p-3">
								<i class="bi bi-{{ $metric['icon'] }} fs-4"></i>
							</div>
						</div>
					</div>
				</div>
			@endforeach
		</div>

		<div class="card border-0 shadow-sm rounded-3">
			<div class="card-body p-4">
				<div class="d-flex align-items-center gap-3">
					<div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
						<i class="bi bi-speedometer2 fs-3"></i>
					</div>

					<div>
						<h5 class="fw-bold mb-1">System Overview</h5>
						<p class="text-muted mb-0">
							Your dashboard is ready. Use the sidebar to navigate the system.
						</p>
					</div>
				</div>
			</div>
		</div>

	</div>
@endsection
