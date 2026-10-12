@extends('backend.layouts.app')

@section('title', 'Delivery Details')

@section('content')
	<div class="container-fluid py-3">

		{{-- Header --}}
		<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
			<div>
				<h4 class="fw-bold mb-1">Delivery Details #{{ $order->id }}</h4>
				<p class="text-muted mb-0">Review products and record delivered quantities.</p>
			</div>

			<a href="{{ route('deliveryman.assigned-drops.index') }}" class="btn btn-outline-secondary">
				<i class="fas fa-arrow-left me-1"></i> Back to Deliveries
			</a>
		</div>

		{{-- Alerts --}}
		@if (session('success'))
			<div class="alert alert-success alert-dismissible fade show">
				{{ session('success') }}
				<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
			</div>
		@endif

		@if (session('error'))
			<div class="alert alert-danger alert-dismissible fade show">
				{{ session('error') }}
				<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
			</div>
		@endif

		@if ($errors->any())
			<div class="alert alert-danger">
				<strong>Please fix the following errors:</strong>
				<ul class="mb-0 mt-2">
					@foreach ($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif

		@php
			$status = strtoupper($order->status ?? '');

			$badge = match ($status) {
			    'ASSIGNED' => 'primary',
			    'ACCEPTED' => 'info',
			    'PICKED_UP' => 'warning',
			    'OUT_FOR_DELIVERY' => 'success',
			    'DELIVERED' => 'success',
			    'CANCELLED' => 'danger',
			    default => 'secondary',
			};

			$items = $order->items ?? collect();

			$totalItems = $items->count();

			$completedItems = $items
			    ->filter(function ($item) {
			        return (float) $item->delivered_quantity >= (float) $item->quantity;
			    })
			    ->count();

			$isClosed = in_array($status, ['DELIVERED', 'CANCELLED'], true);

			$totalQuantity = $items->sum(fn($item) => (float) $item->quantity);
			$deliveredQuantity = $items->sum(fn($item) => (float) $item->delivered_quantity);

			$progress = $totalQuantity > 0 ? min(100, ($deliveredQuantity / $totalQuantity) * 100) : 0;

			$customer = $order->customer;
			$hasCustomerLocation = $customer && is_numeric($customer->latitude) && is_numeric($customer->longitude);
		@endphp

		<div class="row g-4">

			{{-- Customer and Order Information --}}
			<div class="col-12 col-xl-4">

				<div class="card border-0 shadow-sm mb-4">
					<div class="card-header bg-white py-3">
						<h5 class="fw-bold mb-0">Order Information</h5>
					</div>

					<div class="card-body">
						<div class="d-flex justify-content-between align-items-center mb-3">
							<span class="text-muted">Order ID</span>
							<span class="fw-bold">#{{ $order->id }}</span>
						</div>

						<div class="d-flex justify-content-between align-items-center mb-3">
							<span class="text-muted">Status</span>
							<span class="badge text-bg-{{ $badge }}">
								{{ str_replace('_', ' ', $status) }}
							</span>
						</div>

						<div class="d-flex justify-content-between align-items-center mb-3">
							<span class="text-muted">Order Date</span>
							<span>{{ $order->created_at?->format('d M Y, h:i A') ?? '—' }}</span>
						</div>

						<div class="d-flex justify-content-between align-items-center">
							<span class="text-muted">Items Completed</span>
							<span class="fw-semibold">{{ $completedItems }} / {{ $totalItems }}</span>
						</div>

						<div class="progress mt-3" style="height: 8px;">
							<div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%"
								aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
						</div>

						<small class="text-muted d-block mt-2">
							{{ number_format($progress, 1) }}% of total quantity delivered
						</small>
					</div>
				</div>

				<div class="card border-0 shadow-sm">
					<div class="card-header bg-white py-3">
						<h5 class="fw-bold mb-0">Customer Information</h5>
					</div>

					<div class="card-body">
						<h6 class="fw-bold mb-3">
							{{ $customer?->name ?? 'Unknown Customer' }}
						</h6>

						<div class="mb-3">
							<div class="text-muted small mb-1">Phone</div>

							@if ($customer?->phone)
								<a href="tel:{{ $customer->phone }}" class="text-decoration-none">
									<i class="fas fa-phone me-2"></i>{{ $customer->phone }}
								</a>
							@else
								<span class="text-muted">Not available</span>
							@endif
						</div>

						@if ($hasCustomerLocation)
							<div class="mb-3">
								<div class="text-muted small mb-1">Customer Coordinates</div>
								<div>
									{{ $customer->latitude }},
									{{ $customer->longitude }}
								</div>
							</div>

							<a
								href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($customer->latitude . ',' . $customer->longitude) }}"
								target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary w-100">
								<i class="fas fa-map-marker-alt me-1"></i>
								Open Directions in Google Maps
							</a>
						@else
							<div class="alert alert-warning mb-0">
								Customer coordinates are not available.
								Delivery cannot be recorded until a valid location is provided.
							</div>
						@endif
					</div>
				</div>
			</div>

			{{-- Product Delivery --}}
			<div class="col-12 col-xl-8">
				<div class="card border-0 shadow-sm">

					<div class="card-header bg-white py-3">
						<div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
							<h5 class="fw-bold mb-0">Products & Delivery</h5>

							<span class="text-muted small">
								{{ $totalItems }} product line(s)
							</span>
						</div>
					</div>

					<div class="card-body">

						@if ($isClosed)
							<div class="alert alert-{{ $status === 'CANCELLED' ? 'danger' : 'success' }}">
								@if ($status === 'CANCELLED')
									This order has been cancelled. No further delivery is allowed.
								@else
									This order has been delivered successfully.
								@endif
							</div>
						@else
							<div class="alert alert-info">
								Enter only the quantity delivered during this trip.
								Stock will be deducted for the delivered quantity only.
							</div>
						@endif

						<form method="POST" action="{{ route('deliveryman.assigned-drops.partial-delivery', $order->id) }}"
							id="delivery-form">
							@csrf

							<input type="hidden" name="request_key"
								value="{{ old('request_key', $requestKey ?? (string) \Illuminate\Support\Str::uuid()) }}">

							<input type="hidden" name="latitude" id="delivery-latitude" value="{{ old('latitude') }}">

							<input type="hidden" name="longitude" id="delivery-longitude" value="{{ old('longitude') }}">

							<div class="table-responsive">
								<table class="table table-bordered align-middle">
									<thead class="table-light">
										<tr>
											<th>Product</th>
											<th>Ordered</th>
											<th>Delivered</th>
											<th>Remaining</th>
											<th style="min-width: 140px;">Deliver Now</th>
										</tr>
									</thead>

									<tbody>
										@forelse($items as $item)
											@php
												$ordered = (float) $item->quantity;
												$alreadyDelivered = (float) $item->delivered_quantity;
												$remaining = max(0, round($ordered - $alreadyDelivered, 2));

												$unitName = $item->unit_name ?? ($item->productUnit?->name ?? 'unit');

												$inputName = 'items.' . $loop->index . '.quantity';
											@endphp

											<tr>
												<td>
													<div class="fw-semibold">
														{{ $item->product?->name ?? 'Product #' . $item->product_id }}
													</div>

													<small class="text-muted">
														{{ $unitName }}
													</small>

													<input type="hidden" name="items[{{ $loop->index }}][item_id]" value="{{ $item->id }}">
												</td>

												<td>
													{{ number_format($ordered, 2) }}
													<small class="text-muted">{{ $unitName }}</small>
												</td>

												<td class="text-success fw-semibold">
													{{ number_format($alreadyDelivered, 2) }}
													<small>{{ $unitName }}</small>
												</td>

												<td>
													@if ($remaining > 0)
														<span class="badge text-bg-warning">
															{{ number_format($remaining, 2) }}
															{{ $unitName }}
														</span>
													@else
														<span class="badge text-bg-success">Completed</span>
													@endif
												</td>

												<td>
													<input type="number" name="items[{{ $loop->index }}][quantity]" class="form-control delivery-quantity"
														value="{{ old($inputName, 0) }}" min="0" max="{{ $remaining }}" step="0.01"
														data-remaining="{{ $remaining }}"
														aria-label="Quantity to deliver for {{ $item->product?->name ?? 'product' }}"
														@disabled($isClosed || $remaining <= 0)>

													@error($inputName)
														<small class="text-danger">{{ $message }}</small>
													@enderror
												</td>
											</tr>
										@empty
											<tr>
												<td colspan="5" class="text-center text-muted py-4">
													No products found for this order.
												</td>
											</tr>
										@endforelse
									</tbody>
								</table>
							</div>

							@if (
								!$isClosed &&
									$items->isNotEmpty() &&
									$items->contains(fn($item) => (float) $item->delivered_quantity < (float) $item->quantity))
								<div class="border rounded p-3 mt-3">
									<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
										<div>
											<h6 class="fw-bold mb-1">Confirm Delivery</h6>
											<small class="text-muted">
												Your current GPS location is required.
											</small>
										</div>

										<button type="button" id="get-location" class="btn btn-outline-primary btn-sm">
											<i class="fas fa-location-arrow me-1"></i>
											Get My Location
										</button>
									</div>

									<div id="location-status" class="small text-muted mb-3" aria-live="polite">
										Location not captured yet.
									</div>

									<button type="submit" id="submit-delivery" class="btn btn-success w-100">
										<i class="fas fa-truck me-1"></i>
										Save Delivered Quantities
									</button>

									<small class="text-muted d-block mt-2">
										Only positive quantities are processed. Remaining products can be delivered later.
									</small>
								</div>
							@endif
						</form>

					</div>
				</div>
			</div>
		</div>
	</div>

	@if (!$isClosed && $items->contains(fn($item) => (float) $item->delivered_quantity < (float) $item->quantity))
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				const form = document.getElementById('delivery-form');
				const latitudeInput = document.getElementById('delivery-latitude');
				const longitudeInput = document.getElementById('delivery-longitude');
				const locationStatus = document.getElementById('location-status');
				const locationButton = document.getElementById('get-location');
				const submitButton = document.getElementById('submit-delivery');

				function captureLocation() {
					if (!navigator.geolocation) {
						locationStatus.textContent = 'Geolocation is not supported by this browser.';
						return;
					}

					locationButton.disabled = true;
					locationStatus.textContent = 'Getting your current location...';

					navigator.geolocation.getCurrentPosition(
						function(position) {
							latitudeInput.value = position.coords.latitude;
							longitudeInput.value = position.coords.longitude;

							locationStatus.textContent =
								'Location captured successfully. Accuracy: approximately ' +
								Math.round(position.coords.accuracy) + ' metres.';

							locationButton.disabled = false;
						},
						function(error) {
							latitudeInput.value = '';
							longitudeInput.value = '';

							const messages = {
								1: 'Location permission denied. Please allow location access.',
								2: 'Your location is currently unavailable.',
								3: 'Location request timed out. Please try again.'
							};

							locationStatus.textContent =
								messages[error.code] || 'Unable to get your location.';

							locationButton.disabled = false;
						}, {
							enableHighAccuracy: true,
							timeout: 15000,
							maximumAge: 0
						}
					);
				}

				locationButton.addEventListener('click', captureLocation);

				form.addEventListener('submit', function(event) {
					let hasPositiveQuantity = false;
					let invalidQuantity = false;

					document.querySelectorAll('.delivery-quantity').forEach(function(input) {
						const quantity = Number(input.value);
						const remaining = Number(input.dataset.remaining);

						if (!Number.isFinite(quantity) || quantity < 0 || quantity > remaining) {
							invalidQuantity = true;
							input.classList.add('is-invalid');
						} else {
							input.classList.remove('is-invalid');
						}

						if (quantity > 0) {
							hasPositiveQuantity = true;
						}
					});

					if (!latitudeInput.value || !longitudeInput.value) {
						event.preventDefault();
						locationStatus.textContent = 'Please capture your current GPS location before submitting.';
						return;
					}

					if (!hasPositiveQuantity || invalidQuantity) {
						event.preventDefault();
						alert(
							'Enter at least one valid delivery quantity. Each quantity must be within the remaining amount.'
						);
						return;
					}

					submitButton.disabled = true;
					submitButton.innerHTML = 'Saving delivery...';
				});
			});
		</script>
	@endif
@endsection
