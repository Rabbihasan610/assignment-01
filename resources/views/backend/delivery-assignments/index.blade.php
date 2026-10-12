@extends('backend.layouts.app')

@section('title', 'Delivery Assignments')

@section('content')
	<div class="container-fluid py-4">

		<div class="mb-4">
			<h4>Delivery Assignments</h4>
			<p class="text-muted mb-0">
				Assign accepted orders to available deliverymen.
			</p>
		</div>

		@include('backend.partials.alerts')

		<div class="card shadow-sm mb-4">
			<div class="card-body">
				<form method="GET" action="{{ route('backend.delivery-assignments.index') }}" class="row g-2">
					<div class="col-md-5">
						<input type="text" name="search" class="form-control" placeholder="Search order, customer or status"
							value="{{ request('search') }}">
					</div>
					<div class="col-auto">
						<button class="btn btn-primary" type="submit">
							<i class="bi bi-search me-1"></i> Search
						</button>
						<a href="{{ route('backend.delivery-assignments.index') }}" class="btn btn-outline-secondary">
							Reset
						</a>
					</div>
				</form>
			</div>
		</div>

		<div class="card shadow-sm">
			<div class="table-responsive">
				<table class="table table-bordered table-hover align-middle mb-0">
					<thead class="table-light">
						<tr>
							<th>Order</th>
							<th>Customer</th>
							<th>Delivery Address</th>
							<th>Status</th>
							<th>Assigned Deliveryman</th>
							<th style="min-width: 280px;">Assign / Reassign</th>
						</tr>
					</thead>
					<tbody>
						@forelse($orders as $order)
							<tr>
								<td>
									<strong>#{{ $order->id }}</strong>
									<div class="small text-muted">
										{{ number_format($order->total_amount, 2) }}
									</div>
								</td>

								<td>
									{{ $order->customer->name ?? 'N/A' }}
								</td>

								<td>
									{{ $order->delivery_address ?? 'N/A' }}
								</td>

								<td>
									<span class="badge bg-secondary">
										{{ $order->status }}
									</span>
								</td>

								<td>
									{{ $order->deliveryman->name ?? 'Not assigned' }}
								</td>

								<td>
									@if (in_array($order->status, ['ACCEPTED', 'ASSIGNED'], true))
										<form action="{{ route('backend.delivery-assignments.assign', $order) }}" method="POST" class="d-flex gap-2">
											@csrf
											@method('PATCH')

											<select name="delivery_man_id" class="form-select form-select-sm" required>
												<option value="">Select deliveryman</option>

												@foreach ($deliverymen as $deliveryman)
													<option value="{{ $deliveryman->id }}" @selected((int) $order->delivery_man_id === (int) $deliveryman->id)>
														{{ $deliveryman->name }}
													</option>
												@endforeach
											</select>

											<button type="submit" class="btn btn-sm btn-primary">
												<i class="bi bi-truck me-1"></i>
												Assign
											</button>
										</form>
									@else
										<span class="text-muted small">
											Assignment unavailable
										</span>
									@endif
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="6" class="text-center py-4">
									No orders found.
								</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>

			<div class="card-footer bg-white">
				{{ $orders->links() }}
			</div>
		</div>

	</div>
@endsection
