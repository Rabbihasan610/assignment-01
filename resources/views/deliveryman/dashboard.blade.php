@extends('backend.layouts.app')

@section('title', 'Delivery Dashboard')

@section('content')
	<div class="container-fluid py-3">

		{{-- Header --}}
		<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
			<div>
				<h4 class="fw-bold mb-1">Delivery Dashboard</h4>
				<p class="text-muted mb-0">
					Welcome, {{ auth()->user()->name }}!
					Here is your delivery overview.
				</p>
			</div>

			<a href="{{ route('deliveryman.route-planner') }}" class="btn btn-primary">
				<i class="bi bi-geo-alt me-1"></i>
				Plan My Route
			</a>
		</div>

		{{-- Summary Cards --}}
		<div class="row g-3 mb-4">

			<div class="col-12 col-sm-6 col-xl-3">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body d-flex justify-content-between">
						<div>
							<p class="text-muted mb-2">Active Deliveries</p>
							<h3 class="fw-bold mb-0">
								{{ $stats['active_deliveries'] }}
							</h3>
						</div>
						<div class="rounded-3 bg-primary-subtle text-primary p-3 align-self-start">
							<i class="bi bi-truck fs-4"></i>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-6 col-xl-3">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body d-flex justify-content-between">
						<div>
							<p class="text-muted mb-2">Pending Deliveries</p>
							<h3 class="fw-bold mb-0">
								{{ $stats['pending_deliveries'] }}
							</h3>
						</div>
						<div class="rounded-3 bg-warning-subtle text-warning p-3 align-self-start">
							<i class="bi bi-clock-history fs-4"></i>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-6 col-xl-3">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body d-flex justify-content-between">
						<div>
							<p class="text-muted mb-2">Completed Today</p>
							<h3 class="fw-bold mb-0">
								{{ $stats['today_completed'] }}
							</h3>
						</div>
						<div class="rounded-3 bg-success-subtle text-success p-3 align-self-start">
							<i class="bi bi-check-circle fs-4"></i>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-6 col-xl-3">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body d-flex justify-content-between">
						<div>
							<p class="text-muted mb-2">All Completed</p>
							<h3 class="fw-bold mb-0">
								{{ $stats['completed_deliveries'] }}
							</h3>
						</div>
						<div class="rounded-3 bg-info-subtle text-info p-3 align-self-start">
							<i class="bi bi-box-seam fs-4"></i>
						</div>
					</div>
				</div>
			</div>

		</div>

		{{-- Quick Actions --}}
		<div class="card border-0 shadow-sm mb-4">
			<div class="card-body">
				<h6 class="fw-bold mb-3">Quick Actions</h6>

				<div class="row g-3">
					<div class="col-12 col-md-4">
						<a href="#" class="btn btn-outline-primary w-100 py-3">
							<i class="bi bi-list-check me-2"></i>
							My Assigned Deliveries
						</a>
					</div>

					<div class="col-12 col-md-4">
						<a href="{{ route('deliveryman.route-planner') }}" class="btn btn-outline-success w-100 py-3">
							<i class="bi bi-signpost-split me-2"></i>
							Optimize Delivery Route
						</a>
					</div>

					<div class="col-12 col-md-4">
						<a href="#" class="btn btn-outline-secondary w-100 py-3">
							<i class="bi bi-clock-history me-2"></i>
							Delivery History
						</a>
					</div>
				</div>
			</div>
		</div>

		<div class="card border-0 shadow-sm mb-4">
			<div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
				<h6 class="fw-bold mb-0">My Active Deliveries</h6>
			</div>

			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-hover align-middle mb-0">
						<thead class="table-light">
							<tr>
								<th class="ps-3">Order</th>
								<th>Customer</th>
								<th>Phone</th>
								<th>Status</th>
								<th class="text-end pe-3">Action</th>
							</tr>
						</thead>

						<tbody>
							@forelse ($recentOrders as $order)
								@php
									$status = strtoupper($order->status ?? 'PENDING');

									$statusClasses = [
									    'PENDING' => 'bg-warning-subtle text-warning-emphasis',
									    'ASSIGNED' => 'bg-primary-subtle text-primary-emphasis',
									    'ACCEPTED' => 'bg-info-subtle text-info-emphasis',
									    'PICKED_UP' => 'bg-info-subtle text-info-emphasis',
									    'OUT_FOR_DELIVERY' => 'bg-primary-subtle text-primary-emphasis',
									];

									$badgeClass = $statusClasses[$status] ?? 'bg-secondary-subtle text-secondary-emphasis';
								@endphp

								<tr>
									<td class="ps-3 fw-semibold">
										#{{ $order->id }}
									</td>

									<td>
										{{ $order->customer?->name ?? 'Unknown Customer' }}
									</td>

									<td>
										{{ $order->customer?->phone ?? '—' }}
									</td>

									<td>
										<span class="badge {{ $badgeClass }}">
											{{ str_replace('_', ' ', $status) }}
										</span>
									</td>

									<td class="text-end pe-3">
										<a href="#" class="btn btn-sm btn-outline-primary">
											View
										</a>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="5" class="text-center py-5">
										<i class="bi bi-inbox fs-1 text-muted"></i>
										<p class="text-muted mt-2 mb-0">
											No active deliveries assigned.
										</p>
									</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>
		</div>

		{{-- Today's Completed Deliveries --}}
		<div class="card border-0 shadow-sm">
			<div class="card-header bg-white py-3">
				<h6 class="fw-bold mb-0">Recently Completed Today</h6>
			</div>

			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-hover align-middle mb-0">
						<thead class="table-light">
							<tr>
								<th class="ps-3">Order</th>
								<th>Customer</th>
								<th>Phone</th>
								<th>Updated At</th>
							</tr>
						</thead>

						<tbody>
							@forelse ($todayCompletedOrders as $order)
								<tr>
									<td class="ps-3 fw-semibold">#{{ $order->id }}</td>
									<td>{{ $order->customer?->name ?? 'Unknown Customer' }}</td>
									<td>{{ $order->customer?->phone ?? '—' }}</td>
									<td>
										{{ $order->updated_at?->format('h:i A') ?? '—' }}
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="4" class="text-center text-muted py-4">
										No deliveries completed today yet.
									</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>
		</div>

	</div>
@endsection
