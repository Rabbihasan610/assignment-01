@extends('backend.layouts.app')

@section('title', 'Order Details')

@section('content')
	<div class="container-fluid py-4">

		<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
			<div>
				<h4 class="mb-1">Order #{{ $order->id }}</h4>
				<p class="text-muted mb-0">
					Created {{ $order->created_at?->format('d M Y, h:i A') }}
				</p>
			</div>

			<div class="d-flex gap-2">
				<a href="{{ route('backend.orders.index') }}" class="btn btn-outline-secondary">
					<i class="fas fa-arrow-left me-1"></i> Back to Orders
				</a>

				@if ($order->delivery_latitude !== null && $order->delivery_longitude !== null)
					<a href="https://www.google.com/maps?q={{ $order->delivery_latitude }},{{ $order->delivery_longitude }}"
						target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary">
						<i class="fas fa-map-marker-alt me-1"></i> View Location
					</a>
				@endif
			</div>
		</div>

		@include('backend.partials.alerts')

		@php
			$statusColors = [
			    'PENDING' => 'warning',
			    'ACCEPTED' => 'info',
			    'ASSIGNED' => 'primary',
			    'PICKED_UP' => 'secondary',
			    'OUT_FOR_DELIVERY' => 'primary',
			    'DELIVERED' => 'success',
			    'CANCELLED' => 'danger',
			];

			$statusColor = $statusColors[$order->status] ?? 'secondary';
		@endphp

		<div class="card shadow-sm mb-4">
			<div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
				<div>
					<small class="text-muted d-block mb-1">Current Status</small>
					<span class="badge bg-{{ $statusColor }} fs-6">
						{{ str_replace('_', ' ', $order->status) }}
					</span>
				</div>

				<div class="text-md-end">
					<small class="text-muted d-block">Order Total</small>
					<h4 class="text-primary mb-0">
						৳{{ number_format((float) $order->total_amount, 2) }}
					</h4>
				</div>
			</div>
		</div>

		<div class="row g-4">

			<div class="col-lg-8">

				<div class="card shadow-sm mb-4">
					<div class="card-header bg-white">
						<h5 class="mb-0">Order Items</h5>
					</div>

					<div class="card-body p-0">
						<div class="table-responsive">
							<table class="table table-hover align-middle mb-0">
								<thead class="table-light">
									<tr>
										<th>Product</th>
										<th>Unit</th>
										<th class="text-end">Price</th>
										<th class="text-end">Quantity</th>
										<th class="text-end">Subtotal</th>
									</tr>
								</thead>

								<tbody>
									@forelse($order->items as $item)
										<tr>
											<td>
												<div class="fw-semibold">
													{{ $item->product?->name ?? 'Deleted Product' }}
												</div>

												@if ($item->returned_quantity > 0)
													<small class="text-danger">
														Returned: {{ $item->returned_quantity }}
													</small>
												@endif
											</td>

											<td>
												{{ $item->unit_name ?? ($item->productUnit?->unit?->name ?? ($item->product?->unit?->name ?? 'Unit')) }}
											</td>

											<td class="text-end">
												৳{{ number_format((float) $item->price, 2) }}
											</td>

											<td class="text-end">
												{{ rtrim(rtrim(number_format((float) $item->quantity, 3, '.', ''), '0'), '.') }}
											</td>

											<td class="text-end fw-semibold">
												৳{{ number_format((float) $item->sub_total, 2) }}
											</td>
										</tr>
									@empty
										<tr>
											<td colspan="5" class="text-center py-4 text-muted">
												No items found for this order.
											</td>
										</tr>
									@endforelse
								</tbody>
							</table>
						</div>
					</div>

					<div class="card-body border-top">
						<div class="row justify-content-end">
							<div class="col-md-6 col-lg-5">
								<div class="d-flex justify-content-between mb-2">
									<span>Items Subtotal</span>
									<span>
										৳{{ number_format((float) $order->items->sum('sub_total'), 2) }}
									</span>
								</div>

								<div class="d-flex justify-content-between mb-2">
									<span>Delivery Fee</span>
									<span>
										৳{{ number_format((float) $order->delivery_fee, 2) }}
									</span>
								</div>

								<hr>

								<div class="d-flex justify-content-between fw-bold fs-5">
									<span>Total</span>
									<span class="text-primary">
										৳{{ number_format((float) $order->total_amount, 2) }}
									</span>
								</div>

								@if ((float) $order->refund_amount > 0)
									<div class="d-flex justify-content-between text-danger mt-2">
										<span>Refund Amount</span>
										<span>
											৳{{ number_format((float) $order->refund_amount, 2) }}
										</span>
									</div>
								@endif
							</div>
						</div>
					</div>
				</div>

				<div class="card shadow-sm mb-4">
					<div class="card-header bg-white">
						<h5 class="mb-0">Delivery Information</h5>
					</div>

					<div class="card-body">
						<h6>Delivery Address</h6>
						<p class="text-muted">
							{{ $order->delivery_address ?: 'No delivery address provided.' }}
						</p>

						<h6>Delivery Instructions</h6>
						<p class="text-muted mb-3">
							{{ $order->delivery_instructions ?: 'No special instructions.' }}
						</p>

						@if ($order->delivery_latitude !== null && $order->delivery_longitude !== null)
							<div class="row g-3">
								<div class="col-md-6">
									<small class="text-muted d-block">Latitude</small>
									<code>{{ $order->delivery_latitude }}</code>
								</div>

								<div class="col-md-6">
									<small class="text-muted d-block">Longitude</small>
									<code>{{ $order->delivery_longitude }}</code>
								</div>
							</div>

							<a href="https://www.google.com/maps?q={{ $order->delivery_latitude }},{{ $order->delivery_longitude }}"
								target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary mt-3">
								<i class="fas fa-map me-1"></i> Open in Google Maps
							</a>
						@else
							<div class="alert alert-warning mb-0">
								GPS location is not available for this order.
							</div>
						@endif
					</div>
				</div>

				@if ($order->status === 'CANCELLED')
					<div class="card border-danger shadow-sm mb-4">
						<div class="card-header text-danger bg-white">
							<h5 class="mb-0">Cancellation Details</h5>
						</div>

						<div class="card-body">
							{{ $order->cancellation_reason ?: 'No cancellation reason provided.' }}
						</div>
					</div>
				@endif

			</div>

			<div class="col-lg-4">

				<div class="card shadow-sm mb-4">
					<div class="card-header bg-white">
						<h5 class="mb-0">Customer Information</h5>
					</div>

					<div class="card-body">
						<div class="mb-3">
							<small class="text-muted d-block">Name</small>
							<strong>{{ $order->customer?->name ?? 'N/A' }}</strong>
						</div>

						<div class="mb-3">
							<small class="text-muted d-block">Email</small>
							{{ $order->customer?->email ?? 'N/A' }}
						</div>

						<div class="mb-0">
							<small class="text-muted d-block">Customer ID</small>
							#{{ $order->user_id }}
						</div>
					</div>
				</div>

				<div class="card shadow-sm mb-4">
					<div class="card-header bg-white">
						<h5 class="mb-0">Deliveryman</h5>
					</div>

					<div class="card-body">
						@if ($order->deliveryman)
							<div class="mb-3">
								<small class="text-muted d-block">Currently Assigned</small>
								<strong>{{ $order->deliveryman->name }}</strong>
							</div>
						@else
							<div class="alert alert-warning">
								No deliveryman assigned yet.
							</div>
						@endif

						@if (in_array($order->status, ['ACCEPTED', 'ASSIGNED'], true))
							<form action="{{ route('backend.delivery-assignments.assign', $order->id) }}" method="POST">
								@csrf
								@method('PATCH')

								<label for="delivery_man_id" class="form-label">
									Assign / Change Deliveryman
								</label>

								<select name="delivery_man_id" id="delivery_man_id"
									class="form-select @error('delivery_man_id') is-invalid @enderror" required>
									<option value="">Select deliveryman</option>

									@foreach ($deliverymen as $deliveryman)
										<option value="{{ $deliveryman->id }}" @selected((string) old('delivery_man_id', $order->delivery_man_id) === (string) $deliveryman->id)>
											{{ $deliveryman->name }}
										</option>
									@endforeach
								</select>

								@error('delivery_man_id')
									<div class="invalid-feedback">{{ $message }}</div>
								@enderror

								<button type="submit" class="btn btn-primary w-100 mt-3">
									<i class="fas fa-truck me-1"></i>
									Save Assignment
								</button>
							</form>
						@elseif($order->status === 'PENDING')
							<div class="text-muted small">
								Accept the order before assigning a deliveryman.
							</div>
						@else
							<div class="text-muted small">
								Deliveryman assignment is unavailable at this order status.
							</div>
						@endif
					</div>
				</div>

				@if ($order->status === 'PENDING')
					<div class="card shadow-sm mb-4">
						<div class="card-header bg-white">
							<h5 class="mb-0">Review Order</h5>
						</div>

						<div class="card-body">
							<form action="{{ route('backend.orders.updateStatus', $order->id) }}" method="POST" id="orderStatusForm">
								@csrf
								@method('PATCH')

								<div class="mb-3">
									<label for="status" class="form-label">
										Decision
									</label>

									<select name="status" id="status" class="form-select" required>
										<option value="ACCEPTED" @selected(old('status') === 'ACCEPTED')>
											Accept Order
										</option>
										<option value="CANCELLED" @selected(old('status') === 'CANCELLED')>
											Cancel Order
										</option>
									</select>
								</div>

								<div class="mb-3" id="cancellationReasonWrapper">
									<label for="cancellation_reason" class="form-label">
										Cancellation Reason
									</label>

									<textarea name="cancellation_reason" id="cancellation_reason" rows="3" maxlength="1000" class="form-control"
									 placeholder="Enter reason if cancelling">{{ old('cancellation_reason') }}</textarea>

									@error('cancellation_reason')
										<div class="text-danger small mt-1">{{ $message }}</div>
									@enderror
								</div>

								@error('status')
									<div class="text-danger small mb-2">{{ $message }}</div>
								@enderror

								<button type="submit" class="btn btn-success w-100"
									onclick="return confirm('Are you sure you want to submit this decision?')">
									Submit Decision
								</button>
							</form>
						</div>
					</div>
				@endif

				@if ($order->accepted_at)
					<div class="card shadow-sm mb-4">
						<div class="card-header bg-white">
							<h6 class="mb-0">Acceptance Details</h6>
						</div>

						<div class="card-body">
							<small class="text-muted d-block">Accepted At</small>
							<span>{{ $order->accepted_at->format('d M Y, h:i A') }}</span>

							@if ($order->accepted_by)
								<small class="text-muted d-block mt-3">Accepted By User ID</small>
								<span>#{{ $order->accepted_by }}</span>
							@endif
						</div>
					</div>
				@endif

			</div>
		</div>
	</div>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const status = document.getElementById('status');
			const reasonWrapper = document.getElementById('cancellationReasonWrapper');
			const reason = document.getElementById('cancellation_reason');

			if (!status || !reasonWrapper || !reason) {
				return;
			}

			function toggleCancellationReason() {
				const isCancelled = status.value === 'CANCELLED';

				reasonWrapper.style.display = isCancelled ? 'block' : 'none';
				reason.required = isCancelled;
			}

			status.addEventListener('change', toggleCancellationReason);
			toggleCancellationReason();
		});
	</script>
@endsection
