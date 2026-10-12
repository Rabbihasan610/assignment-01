@extends('backend.layouts.app')

@section('title', 'Customers')

@section('content')
	<div class="container-fluid py-4">

		{{-- Header --}}
		<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
			<div>
				<h4 class="mb-1">Customers</h4>
				<p class="text-muted mb-0">
					View and manage customer records.
				</p>
			</div>

			<span class="badge bg-secondary fs-6">
				Total: {{ $customers->total() }}
			</span>
		</div>

		@include('backend.partials.alerts')

		{{-- Search --}}
		<div class="card shadow-sm mb-4">
			<div class="card-body">
				<form action="{{ route('backend.customers.index') }}" method="GET" class="row g-2">

					<div class="col-md-6 col-lg-4">
						<input type="text" name="search" class="form-control" placeholder="Search name, email or phone..."
							value="{{ request('search') }}">
					</div>

					<div class="col-auto">
						<button type="submit" class="btn btn-primary">
							<i class="bi bi-search me-1"></i>
							Search
						</button>

						@if (request('search'))
							<a href="{{ route('backend.customers.index') }}" class="btn btn-outline-secondary">
								<i class="bi bi-arrow-counterclockwise me-1"></i>
								Reset
							</a>
						@endif
					</div>
				</form>
			</div>
		</div>

		{{-- Customer Table --}}
		<div class="card shadow-sm">
			<div class="card-header bg-white">
				<h5 class="mb-0">
					<i class="bi bi-people me-1"></i>
					Customer List
				</h5>
			</div>

			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-bordered table-hover align-middle mb-0">
						<thead class="table-light">
							<tr>
								<th style="width: 70px;">#</th>
								<th>Customer</th>
								<th>Email</th>
								<th>Phone</th>
								<th class="text-center">Total Orders</th>
								<th>Registered At</th>
							</tr>
						</thead>

						<tbody>
							@forelse($customers as $customer)
								<tr>
									<td>
										{{ $customers->firstItem() + $loop->index }}
									</td>

									<td>
										<div class="d-flex align-items-center gap-2">
											<div class="rounded-circle bg-light border d-flex align-items-center justify-content-center"
												style="width: 38px; height: 38px;">
												<i class="bi bi-person text-secondary"></i>
											</div>

											<div>
												<div class="fw-semibold">
													{{ $customer->name }}
												</div>
												<small class="text-muted">
													ID: {{ $customer->id }}
												</small>
											</div>
										</div>
									</td>

									<td>{{ $customer->email }}</td>

									<td>{{ $customer->phone ?? '—' }}</td>

									<td class="text-center">
										<span class="badge bg-info text-dark">
											<i class="bi bi-box-seam me-1"></i>
											{{ $customer->orders_count ?? 0 }}
										</span>
									</td>

									<td>
										{{ $customer->created_at?->format('d M Y') ?? '—' }}
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="6" class="text-center py-5">
										<i class="bi bi-people display-5 text-muted"></i>

										<p class="fw-semibold mt-3 mb-1">
											No customers found.
										</p>

										<p class="text-muted mb-0">
											Try another search term.
										</p>
									</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>

			{{-- Pagination --}}
			@if ($customers->hasPages())
				<div class="card-footer bg-white">
					<div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
						<small class="text-muted">
							Showing {{ $customers->firstItem() ?? 0 }}
							to {{ $customers->lastItem() ?? 0 }}
							of {{ $customers->total() }} customers
						</small>

						{{ $customers->links() }}
					</div>
				</div>
			@endif
		</div>

	</div>
@endsection
