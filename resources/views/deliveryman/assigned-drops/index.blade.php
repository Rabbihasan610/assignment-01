@extends('backend.layouts.app')

@section('title', 'My Assigned Deliveries')

@section('content')
	<div class="container-fluid py-3">

		{{-- Page Header --}}
		<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
			<div>
				<h4 class="fw-bold mb-1">My Assigned Deliveries</h4>
				<p class="text-muted mb-0">
					Manage customer orders and track delivery progress.
				</p>
			</div>

			<div class="d-flex gap-2">
				<a href="{{ route('deliveryman.history') }}" class="btn btn-outline-secondary">
					<i class="fas fa-history me-1"></i>
					Delivery History
				</a>

				<a href="{{ route('deliveryman.route-planner') }}" class="btn btn-primary">
					<i class="fas fa-route me-1"></i>
					Plan Route
				</a>
			</div>
		</div>

		{{-- Flash Messages --}}
		@if (session('success'))
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				<i class="fas fa-check-circle me-2"></i>
				{{ session('success') }}

				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		@endif

		@if (session('error'))
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<i class="fas fa-exclamation-circle me-2"></i>
				{{ session('error') }}

				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		@endif

		@if ($errors->any())
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<strong>Please check the following:</strong>
				<ul class="mb-0 mt-2">
					@foreach ($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>

				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		@endif

		{{-- Summary Cards --}}
		<div class="row g-3 mb-4">
			@php
				$activeOrders = $orders->getCollection();

				$assignedCount = $activeOrders->where('status', 'ASSIGNED')->count();
				$acceptedCount = $activeOrders->where('status', 'ACCEPTED')->count();
				$inProgressCount = $activeOrders->whereIn('status', ['PICKED_UP', 'OUT_FOR_DELIVERY'])->count();
			@endphp

			<div class="col-12 col-sm-6 col-xl-4">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body d-flex align-items-center justify-content-between">
						<div>
							<p class="text-muted mb-1">Assigned</p>
							<h3 class="fw-bold mb-0">{{ $assignedCount }}</h3>
						</div>
						<div class="rounded-circle bg-primary-subtle text-primary p-3">
							<i class="fas fa-clipboard-list fa-lg"></i>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-6 col-xl-4">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body d-flex align-items-center justify-content-between">
						<div>
							<p class="text-muted mb-1">Accepted</p>
							<h3 class="fw-bold mb-0">{{ $acceptedCount }}</h3>
						</div>
						<div class="rounded-circle bg-info-subtle text-info p-3">
							<i class="fas fa-check fa-lg"></i>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12 col-sm-6 col-xl-4">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body d-flex align-items-center justify-content-between">
						<div>
							<p class="text-muted mb-1">In Progress</p>
							<h3 class="fw-bold mb-0">{{ $inProgressCount }}</h3>
						</div>
						<div class="rounded-circle bg-success-subtle text-success p-3">
							<i class="fas fa-truck fa-lg"></i>
						</div>
					</div>
				</div>
			</div>
		</div>

		{{-- Assigned Orders Table --}}
		<div class="card border-0 shadow-sm">
			<div class="card-header bg-white border-0 py-3">
				<div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
					<h5 class="fw-bold mb-0">Assigned Orders</h5>

					<span class="text-muted small">
						{{ $orders->total() }} order(s) found
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
							<th>Assigned Date</th>
							<th class="text-end pe-3">Action</th>
						</tr>
					</thead>

					<tbody>
						@forelse ($orders as $order)
							@php
								$status = strtoupper($order->status ?? '');

								$badge = match ($status) {
								    'ASSIGNED' => 'primary',
								    'ACCEPTED' => 'info',
								    'PICKED_UP' => 'warning',
								    'OUT_FOR_DELIVERY' => 'success',
								    default => 'secondary',
								};

								$statusLabel = str_replace('_', ' ', $status);
							@endphp

							<tr>
								<td class="ps-3">
									<span class="fw-bold">#{{ $order->id }}</span>
								</td>

								<td>
									<div class="fw-semibold">
										{{ $order->customer?->name ?? 'Unknown Customer' }}
									</div>
									<small class="text-muted">
										Order #{{ $order->id }}
									</small>
								</td>

								<td>
									@if ($order->customer?->phone)
										<a href="tel:{{ $order->customer->phone }}" class="text-decoration-none">
											<i class="fas fa-phone-alt me-1 text-muted"></i>
											{{ $order->customer->phone }}
										</a>
									@else
										<span class="text-muted">Not available</span>
									@endif
								</td>

								<td>
									<span class="badge text-bg-{{ $badge }}">
										{{ $statusLabel }}
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

								<td class="text-end pe-3">
									<a href="{{ route('deliveryman.assigned-drops.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
										<i class="fas fa-eye me-1"></i>
										View Details
									</a>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="6" class="text-center py-5">
									<div class="mb-3 text-muted">
										<i class="fas fa-truck fa-3x"></i>
									</div>

									<h6 class="fw-bold">No Active Deliveries</h6>

									<p class="text-muted mb-0">
										You currently have no active orders assigned to you.
									</p>
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
