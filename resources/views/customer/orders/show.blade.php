@extends('backend.layouts.app')

@section('content')
	<div class="container py-4 py-lg-5">

		{{-- Breadcrumb --}}
		<nav aria-label="breadcrumb" class="mb-4">
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="{{ route('customer.products.index') }}">Products</a>
				</li>
				<li class="breadcrumb-item">
					<a href="{{ route('customer.orders.index') }}">My Orders</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">
					Order #{{ $order->id }}
				</li>
			</ol>
		</nav>

		@include('backend.partials.alerts')

		{{-- Order Header --}}
		<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
			<div>
				<h2 class="fw-bold mb-1">Order #{{ $order->id }}</h2>
				<div class="text-muted small">
					Placed on {{ $order->created_at?->format('d M Y, h:i A') }}
				</div>
			</div>

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

			<span class="badge {{ $statusClass }} fs-6 px-3 py-2">
				{{ str_replace('_', ' ', $order->status) }}
			</span>
		</div>

		<div class="row g-4">

			{{-- Order Items --}}
			<div class="col-lg-8">
				<div class="card border-0 shadow-sm mb-4">
					<div class="card-header bg-white py-3">
						<h5 class="fw-bold mb-0">
							<i class="bi bi-bag-check me-2"></i>
							Order Items
						</h5>
					</div>

					<div class="card-body p-0">
						<div class="table-responsive">
							<table class="table align-middle mb-0">
								<thead class="table-light">
									<tr>
										<th class="ps-3">Product</th>
										<th>Unit Price</th>
										<th>Quantity</th>
										<th class="text-end pe-3">Subtotal</th>
									</tr>
								</thead>

								<tbody>
									@foreach ($order->items as $item)
										@php
											$unitName =
											    $item->unit_name ??
											    ($item->productUnit?->unit?->abbreviation ?? ($item->productUnit?->unit?->name ?? 'Unit'));

											$quantity = (float) $item->quantity;
											$price = (float) $item->price;
											$subtotal = (float) $item->sub_total;
										@endphp

										<tr>
											<td class="ps-3">
												<div class="fw-semibold">
													{{ $item->product?->name ?? 'Product unavailable' }}
												</div>
											</td>

											<td class="text-nowrap">
												৳{{ number_format($price, 2) }}
											</td>

											<td>
												{{ rtrim(rtrim(number_format($quantity, 2, '.', ''), '0'), '.') }}
												{{ $unitName }}
											</td>

											<td class="text-end pe-3 fw-semibold text-nowrap">
												৳{{ number_format($subtotal, 2) }}
											</td>
										</tr>
									@endforeach

									@if ($order->items->isEmpty())
										<tr>
											<td colspan="4" class="text-center text-muted py-4">
												No order items found.
											</td>
										</tr>
									@endif
								</tbody>
							</table>
						</div>
					</div>
				</div>

				{{-- Delivery Information --}}
				<div class="card border-0 shadow-sm">
					<div class="card-header bg-white py-3">
						<h5 class="fw-bold mb-0">
							<i class="bi bi-geo-alt me-2"></i>
							Delivery Information
						</h5>
					</div>

					<div class="card-body p-4">
						<div class="mb-3">
							<div class="text-muted small mb-1">Delivery Address</div>
							<div class="fw-semibold">
								{{ $order->delivery_address ?: 'No address provided' }}
							</div>
						</div>

						@if ($order->delivery_instructions)
							<div class="mb-3">
								<div class="text-muted small mb-1">Delivery Instructions</div>
								<div>{{ $order->delivery_instructions }}</div>
							</div>
						@endif

						@if ($order->delivery_latitude !== null && $order->delivery_longitude !== null)
							<hr>

							<div class="mb-3">
								<div class="text-muted small mb-1">GPS Coordinates</div>
								<div>
									<strong>Latitude:</strong>
									{{ $order->delivery_latitude }}
								</div>
								<div>
									<strong>Longitude:</strong>
									{{ $order->delivery_longitude }}
								</div>
							</div>

							<a href="https://www.google.com/maps?q={{ $order->delivery_latitude }},{{ $order->delivery_longitude }}"
								target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary">
								<i class="bi bi-map me-2"></i>
								Open Delivery Location in Google Maps
							</a>
						@else
							<div class="alert alert-secondary mb-0">
								<i class="bi bi-info-circle me-2"></i>
								GPS delivery location is not available for this order.
							</div>
						@endif
					</div>
				</div>
			</div>

			{{-- Order Summary --}}
			<div class="col-lg-4">
				<div class="card border-0 shadow-sm mb-4">
					<div class="card-body p-4">
						<h5 class="fw-bold mb-4">
							<i class="bi bi-receipt me-2"></i>
							Order Summary
						</h5>

						@php
							$itemsSubtotal = $order->items->sum('sub_total');
						@endphp

						<div class="d-flex justify-content-between mb-3 gap-3">
							<span class="text-muted">Items Subtotal</span>
							<span class="text-nowrap">
								৳{{ number_format($itemsSubtotal, 2) }}
							</span>
						</div>

						<div class="d-flex justify-content-between mb-3 gap-3">
							<span class="text-muted">Delivery Fee</span>
							<span class="text-nowrap">
								৳{{ number_format((float) $order->delivery_fee, 2) }}
							</span>
						</div>

						<hr>

						<div class="d-flex justify-content-between align-items-center gap-3">
							<span class="fw-bold">Total Amount</span>
							<span class="fs-4 fw-bold text-primary text-nowrap">
								৳{{ number_format((float) $order->total_amount, 2) }}
							</span>
						</div>

						@if ((float) $order->refund_amount > 0)
							<div class="d-flex justify-content-between mt-3 text-success gap-3">
								<span>Refund Amount</span>
								<span class="text-nowrap">
									৳{{ number_format((float) $order->refund_amount, 2) }}
								</span>
							</div>
						@endif
					</div>
				</div>

				{{-- Status Information --}}
				<div class="card border-0 shadow-sm mb-4">
					<div class="card-body p-4">
						<h6 class="fw-bold mb-3">Order Status</h6>

						<div class="d-flex align-items-center gap-2 mb-2">
							<i class="bi bi-circle-fill text-primary"></i>
							<span>Current status:</span>
						</div>

						<div class="fw-bold">
							{{ str_replace('_', ' ', $order->status) }}
						</div>

						@if ($order->accepted_at)
							<div class="small text-muted mt-3">
								Accepted on
								{{ $order->accepted_at->format('d M Y, h:i A') }}
							</div>
						@endif

						@if ($order->delivered_at)
							<div class="small text-success mt-2">
								Delivered on
								{{ $order->delivered_at->format('d M Y, h:i A') }}
							</div>
						@endif

						@if ($order->cancellation_reason)
							<div class="alert alert-danger mt-3 mb-0">
								<strong>Cancellation Reason</strong>
								<p class="mb-0 mt-1">
									{{ $order->cancellation_reason }}
								</p>
							</div>
						@endif
					</div>
				</div>

				<a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary w-100">
					<i class="bi bi-arrow-left me-2"></i>
					Back to My Orders
				</a>
			</div>

		</div>
	</div>
@endsection
