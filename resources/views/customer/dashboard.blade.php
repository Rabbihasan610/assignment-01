@extends('backend.layouts.app')

@section('content')
	<div class="container-fluid py-4">
		<div class="mb-4">
			<h3 class="fw-bold">Customer Dashboard</h3>
			<p class="text-muted mb-0">
				Welcome, {{ auth()->user()->name }}!
			</p>
		</div>

		<div class="row g-3 mb-4">
			<div class="col-6 col-lg-4">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body">
						<div class="text-muted small">Total Orders</div>
						<h3 class="fw-bold mt-2">{{ $totalOrders }}</h3>
					</div>
				</div>
			</div>

			<div class="col-6 col-lg-4">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body">
						<div class="text-muted small">Pending Orders</div>
						<h3 class="fw-bold text-warning mt-2">{{ $pendingOrders }}</h3>
					</div>
				</div>
			</div>

			<div class="col-6 col-lg-4">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body">
						<div class="text-muted small">Active Orders</div>
						<h3 class="fw-bold text-primary mt-2">{{ $activeOrders }}</h3>
					</div>
				</div>
			</div>

			<div class="col-6 col-lg-4">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body">
						<div class="text-muted small">Delivered</div>
						<h3 class="fw-bold text-success mt-2">{{ $deliveredOrders }}</h3>
					</div>
				</div>
			</div>

			<div class="col-6 col-lg-4">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body">
						<div class="text-muted small">Cancelled</div>
						<h3 class="fw-bold text-danger mt-2">{{ $cancelledOrders }}</h3>
					</div>
				</div>
			</div>

			<div class="col-6 col-lg-4">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body">
						<div class="text-muted small">Total Spent</div>
						<h3 class="fw-bold mt-2">
							৳{{ number_format((float) $totalSpent, 2) }}
						</h3>
					</div>
				</div>
			</div>
		</div>

		<div class="d-flex justify-content-between align-items-center mb-3">
			<h5 class="fw-bold mb-0">Recent Orders</h5>

			<a href="{{ route('customer.orders.index') }}" class="btn btn-sm btn-outline-primary">
				View All
			</a>
		</div>

		<div class="card border-0 shadow-sm">
			<div class="table-responsive">
				<table class="table table-hover align-middle mb-0">
					<thead class="table-light">
						<tr>
							<th>Order ID</th>
							<th>Items</th>
							<th>Amount</th>
							<th>Status</th>
							<th>Date</th>
							<th></th>
						</tr>
					</thead>

					<tbody>
						@forelse ($recentOrders as $order)
							<tr>
								<td>#{{ $order->id }}</td>
								<td>{{ $order->items->count() }} products</td>
								<td>
									৳{{ number_format((float) $order->total_amount, 2) }}
								</td>
								<td>
									@php
										$statusClass = match ($order->status) {
										    'DELIVERED' => 'success',
										    'CANCELLED' => 'danger',
										    'PENDING' => 'warning',
										    default => 'primary',
										};
									@endphp

									<span class="badge bg-{{ $statusClass }}">
										{{ str_replace('_', ' ', $order->status) }}
									</span>
								</td>
								<td>{{ $order->created_at->format('d M Y') }}</td>
								<td>
									<a href="{{ route('customer.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
										Details
									</a>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="6" class="text-center py-4 text-muted">
									No orders found.
									<a href="{{ route('customer.products.index') }}">
										Place your first order
									</a>
								</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>
		</div>

		<div class="mt-4">
			<a href="{{ route('customer.products.index') }}" class="btn btn-primary">
				<i class="bi bi-cart-plus me-1"></i>
				Place New Order
			</a>
		</div>
	</div>
@endsection
