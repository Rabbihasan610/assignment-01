@extends('backend.layouts.app')

@section('content')
	<div class="container-fluid py-4">

		<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
			<div>
				<h2 class="fw-bold mb-1">Order Management</h2>
				<p class="text-muted mb-0">Manage customer orders and delivery assignments.</p>
			</div>

			<span class="badge bg-primary fs-6">
				Total: {{ $orders->total() }} Orders
			</span>
		</div>

		@include('backend.partials.alerts')

		<div class="row g-3 mb-4">
			@foreach ([
									'PENDING' => ['warning', 'Pending', 'hourglass-split'],
									'ACCEPTED' => ['info', 'Accepted', 'check-circle'],
									'ASSIGNED' => ['primary', 'Assigned', 'truck'],
									'OUT_FOR_DELIVERY' => ['info', 'Out for Delivery', 'bicycle'],
									'DELIVERED' => ['success', 'Delivered', 'check-circle-fill'],
									'CANCELLED' => ['danger', 'Cancelled', 'x-circle'],
					] as $status => $info)
				<div class="col-6 col-md-4 col-xl-2">
					<div class="card border-0 shadow-sm h-100">
						<div class="card-body">
							<div class="small text-muted mb-2">
								{{ $info[1] }}
							</div>

							<div class="d-flex justify-content-between align-items-center">
								<span class="fw-bold fs-4">
									{{ \App\Models\Order::where('status', $status)->count() }}
								</span>

								<i class="bi bi-{{ $info[2] }} fs-4 text-{{ $info[0] }}"></i>
							</div>
						</div>
					</div>
				</div>
			@endforeach
		</div>

		{{-- Orders Table --}}
		<div class="card border-0 shadow-sm">
			<div class="card-header bg-white py-3">
				<h5 class="fw-bold mb-0">All Orders</h5>
			</div>

			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-hover align-middle mb-0">
						<thead class="table-light">
							<tr>
								<th class="ps-3">Order</th>
								<th>Customer</th>
								<th>Items</th>
								<th>Total</th>
								<th>Status</th>
								<th>Deliveryman</th>
								<th>Date</th>
								<th class="text-center pe-3">Actions</th>
							</tr>
						</thead>

						<tbody>
							@forelse($orders as $order)
								@php
									$statusClasses = [
									    'PENDING' => 'bg-warning text-dark',
									    'ACCEPTED' => 'bg-info text-dark',
									    'ASSIGNED' => 'bg-primary',
									    'PICKED_UP' => 'bg-primary',
									    'OUT_FOR_DELIVERY' => 'bg-info text-dark',
									    'DELIVERED' => 'bg-success',
									    'CANCELLED' => 'bg-danger',
									];

									$statusClass = $statusClasses[$order->status] ?? 'bg-secondary';
								@endphp

								<tr>
									{{-- Order ID --}}
									<td class="ps-3">
										<span class="fw-bold">#{{ $order->id }}</span>
									</td>

									{{-- Customer --}}
									<td>
										<div class="fw-semibold">
											{{ $order->customer?->name ?? 'Unknown Customer' }}
										</div>

										<small class="text-muted">
											User ID: {{ $order->user_id }}
										</small>
									</td>

									{{-- Items --}}
									<td>
										<span class="badge bg-light text-dark border">
											{{ $order->items->count() }}
											{{ $order->items->count() === 1 ? 'item' : 'items' }}
										</span>
									</td>

									{{-- Total --}}
									<td class="fw-semibold text-nowrap">
										৳{{ number_format((float) $order->total_amount, 2) }}
									</td>

									{{-- Status --}}
									<td>
										<span class="badge {{ $statusClass }}">
											{{ str_replace('_', ' ', $order->status) }}
										</span>
									</td>

									{{-- Deliveryman --}}
									<td>
										@if ($order->deliveryman)
											<div class="fw-semibold">
												<i class="bi bi-truck me-1 text-primary"></i>
												{{ $order->deliveryman->name }}
											</div>
										@else
											<span class="small text-muted">
												Not assigned
											</span>
										@endif
									</td>

									{{-- Date --}}
									<td class="text-nowrap">
										<div>
											{{ $order->created_at?->format('d M Y') }}
										</div>
										<small class="text-muted">
											{{ $order->created_at?->format('h:i A') }}
										</small>
									</td>

									{{-- Actions --}}
									<td class="text-center pe-3">
										<div class="d-flex justify-content-center gap-1">

											@if ($order->delivery_latitude !== null && $order->delivery_longitude !== null)
												<a href="https://www.google.com/maps?q={{ $order->delivery_latitude }},{{ $order->delivery_longitude }}"
													target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary"
													title="View Delivery Location" aria-label="View Delivery Location">
													<i class="bi bi-geo-alt"></i>
												</a>
											@endif

											<a href="{{ route('backend.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary"
												title="View Order Details" aria-label="View Order Details">
												<i class="bi bi-eye"></i>
											</a>

										</div>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="8" class="text-center py-5">
										<i class="bi bi-inbox display-5 text-muted"></i>
										<h6 class="mt-3 fw-bold">No orders found</h6>
										<p class="text-muted mb-0">
											Customer orders will appear here.
										</p>
									</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>

			@if ($orders->hasPages())
				<div class="card-footer bg-white">
					{{ $orders->links() }}
				</div>
			@endif
		</div>

	</div>
@endsection
