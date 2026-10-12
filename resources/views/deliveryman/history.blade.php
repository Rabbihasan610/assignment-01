@extends('backend.layouts.app')

@section('title', 'Delivery History')

@section('content')
	<div class="container-fluid py-3">

		{{-- Header --}}
		<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
			<div>
				<h4 class="fw-bold mb-1">Delivery History</h4>
				<p class="text-muted mb-0">
					View your completed and cancelled deliveries.
				</p>
			</div>

			<a href="{{ route('deliveryman.assigned-drops.index') }}" class="btn btn-outline-primary">
				<i class="fas fa-truck me-1"></i>
				Active Deliveries
			</a>
		</div>

		{{-- Flash Messages --}}
		@if (session('success'))
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				{{ session('success') }}
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		@endif

		@if (session('error'))
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				{{ session('error') }}
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		@endif

		{{-- Summary --}}
		@php
			$historyOrders = $orders->getCollection();

			$deliveredCount = $historyOrders->where('status', 'DELIVERED')->count();

			$cancelledCount = $historyOrders->where('status', 'CANCELLED')->count();
		@endphp

		<div class="row g-3 mb-4">
			<div class="col-12 col-sm-6">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body d-flex justify-content-between align-items-center">
						<div>
							<p class="text-muted mb-1">Delivered Orders</p>
							<h3 class="fw-bold text-success mb-0">
								{{ $deliveredCount }}
							</h3>
						</div>

						<div class="rounded-circle bg-success-subtle text-success p-3">
							<i class="fas fa-check-circle fa-lg"></i>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-6">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body d-flex justify-content-between align-items-center">
						<div>
							<p class="text-muted mb-1">Cancelled Orders</p>
							<h3 class="fw-bold text-danger mb-0">
								{{ $cancelledCount }}
							</h3>
						</div>

						<div class="rounded-circle bg-danger-subtle text-danger p-3">
							<i class="fas fa-times-circle fa-lg"></i>
						</div>
					</div>
				</div>
			</div>
		</div>

		{{-- History Table --}}
		<div class="card border-0 shadow-sm">

			<div class="card-header bg-white border-0 py-3">
				<div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
					<h5 class="fw-bold mb-0">Order Records</h5>

					<span class="text-muted small">
						{{ $orders->total() }} record(s) found
					</span>
				</div>
			</div>

			<div class="table-responsive">
				<table class="table table-hover align-middle mb-0">
					<thead class="table-light">
						<tr>
							<th class="ps-3">Order ID</th>
							<th>Customer</th>
							<th>Phone</th>
							<th>Status</th>
							<th>Order Date</th>
							<th>Last Updated</th>
							<th class="text-end pe-3">Action</th>
						</tr>
					</thead>

					<tbody>
						@forelse($orders as $order)
							@php
								$status = strtoupper($order->status ?? '');

								$badge = match ($status) {
								    'DELIVERED' => 'success',
								    'CANCELLED' => 'danger',
								    default => 'secondary',
								};
							@endphp

							<tr>
								<td class="ps-3">
									<span class="fw-bold">
										#{{ $order->id }}
									</span>
								</td>

								<td>
									<div class="fw-semibold">
										{{ $order->customer?->name ?? 'Unknown Customer' }}
									</div>
								</td>

								<td>
									@if ($order->customer?->phone)
										<a href="tel:{{ $order->customer->phone }}" class="text-decoration-none">
											<i class="fas fa-phone me-1 text-muted"></i>
											{{ $order->customer->phone }}
										</a>
									@else
										<span class="text-muted">Not available</span>
									@endif
								</td>

								<td>
									<span class="badge text-bg-{{ $badge }}">
										<i class="fas {{ $status === 'DELIVERED' ? 'fa-check' : 'fa-times' }} me-1"></i>
										{{ str_replace('_', ' ', $status) }}
									</span>
								</td>

								<td>
									<div>
										{{ $order->created_at?->format('d M Y') ?? '—' }}
									</div>
									<small class="text-muted">
										{{ $order->created_at?->format('h:i A') ?? '' }}
									</small>
								</td>

								<td>
									<div>
										{{ $order->updated_at?->format('d M Y') ?? '—' }}
									</div>
									<small class="text-muted">
										{{ $order->updated_at?->format('h:i A') ?? '' }}
									</small>
								</td>

								<td class="text-end pe-3">
									<a href="{{ route('deliveryman.assigned-drops.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
										<i class="fas fa-eye me-1"></i>
										View Details
									</a>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="7" class="text-center py-5">
									<div class="text-muted mb-3">
										<i class="fas fa-history fa-3x"></i>
									</div>

									<h6 class="fw-bold">No Delivery History</h6>

									<p class="text-muted mb-0">
										Completed or cancelled orders will appear here.
									</p>

									<a href="{{ route('deliveryman.assigned-drops.index') }}" class="btn btn-primary btn-sm mt-3">
										View Active Deliveries
									</a>
								</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>

			@if ($orders->hasPages())
				<div class="card-footer bg-white border-0 py-3">
					{{ $orders->links() }}
				</div>
			@endif

		</div>
	</div>
@endsection
