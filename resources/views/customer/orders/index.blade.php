@extends('backend.layouts.app')

@section('content')
	<div class="container py-4">

		<h2 class="fw-bold mb-4">My Orders</h2>

		@include('backend.partials.alerts')

		@if ($orders->isEmpty())
			<div class="card border-0 shadow-sm">
				<div class="card-body text-center py-5">
					<i class="bi bi-bag-x display-4 text-muted"></i>
					<h5 class="mt-3">No orders found</h5>
					<p class="text-muted">You haven't placed any orders yet.</p>

					<a href="{{ route('customer.products.index') }}" class="btn btn-primary">
						Browse Products
					</a>
				</div>
			</div>
		@else
			<div class="card border-0 shadow-sm">
				<div class="table-responsive">
					<table class="table table-hover align-middle mb-0">
						<thead class="table-light">
							<tr>
								<th>Order ID</th>
								<th>Date</th>
								<th>Total</th>
								<th>Status</th>
								<th>Delivery Location</th>
							</tr>
						</thead>

						<tbody>
							@foreach ($orders as $order)
								<tr>
									<td>#{{ $order->id }}</td>

									<td>
										{{ $order->created_at?->format('d M Y, h:i A') }}
									</td>

									<td>
										৳{{ number_format($order->total_amount, 2) }}
									</td>

									<td>
										<span class="badge bg-secondary">
											{{ $order->status }}
										</span>
									</td>

									<td>
										<div class="d-flex flex-wrap gap-1">

											@if ($order->delivery_latitude !== null && $order->delivery_longitude !== null)
												<a href="https://www.google.com/maps?q={{ $order->delivery_latitude }},{{ $order->delivery_longitude }}"
													target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary" title="View Map"
													aria-label="View Map">
													<i class="bi bi-geo-alt"></i>
												</a>
											@endif

											<a href="{{ route('customer.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary"
												title="View Details" aria-label="View Details">
												<i class="bi bi-eye"></i>
											</a>

										</div>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>

				@if (method_exists($orders, 'links'))
					<div class="card-footer bg-white">
						{{ $orders->links() }}
					</div>
				@endif
			</div>
		@endif

	</div>
@endsection
