@extends('backend.layouts.app')

@section('title', 'Deliverymen')

@section('content')
	<div class="container-fluid py-4">

		<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
			<div>
				<h4 class="mb-1">Deliverymen</h4>
				<p class="text-muted mb-0">
					Manage deliveryman accounts and their status.
				</p>
			</div>

			<a href="{{ route('backend.deliverymen.create') }}" class="btn btn-primary">
				<i class="bi bi-plus-lg me-1"></i> Add Deliveryman
			</a>
		</div>

		@include('backend.partials.alerts')

		<div class="card shadow-sm mb-4">
			<div class="card-body">
				<form action="{{ route('backend.deliverymen.index') }}" method="GET" class="row g-2">
					<div class="col-md-6 col-lg-4">
						<input type="text" name="search" class="form-control" placeholder="Search by name or email..."
							value="{{ request('search') }}">
					</div>

					<div class="col-auto">
						<button type="submit" class="btn btn-primary">
							<i class="bi bi-search me-1"></i> Search
						</button>

						@if (request('search'))
							<a href="{{ route('backend.deliverymen.index') }}" class="btn btn-outline-secondary">
								<i class="bi bi-arrow-counterclockwise me-1"></i> Reset
							</a>
						@endif
					</div>
				</form>
			</div>
		</div>

		<div class="card shadow-sm">
			<div class="card-header bg-white d-flex justify-content-between align-items-center">
				<h5 class="mb-0">
					<i class="bi bi-people me-1"></i> Deliveryman List
				</h5>

				<span class="badge bg-secondary">
					Total: {{ $deliverymen->total() }}
				</span>
			</div>

			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-bordered table-hover align-middle mb-0">
						<thead class="table-light">
							<tr>
								<th style="width: 70px;">#</th>
								<th>Name</th>
								<th>Email</th>
								<th class="text-center">Total Orders</th>
								<th class="text-center">Status</th>
								<th class="text-center" style="min-width: 250px;">Actions</th>
							</tr>
						</thead>

						<tbody>
							@forelse($deliverymen as $deliveryman)
								<tr>
									<td>{{ $deliverymen->firstItem() + $loop->index }}</td>

									<td>
										<div class="fw-semibold">{{ $deliveryman->name }}</div>
										<small class="text-muted">
											ID: {{ $deliveryman->id }}
										</small>
									</td>

									<td>{{ $deliveryman->email }}</td>

									<td class="text-center">
										<span class="badge bg-info text-dark">
											<i class="bi bi-box-seam me-1"></i>
											{{ $deliveryman->delivery_orders_count ?? 0 }}
										</span>
									</td>

									<td class="text-center">
										@if ($deliveryman->is_active)
											<span class="badge bg-success">
												<i class="bi bi-check-circle me-1"></i> Active
											</span>
										@else
											<span class="badge bg-danger">
												<i class="bi bi-x-circle me-1"></i> Inactive
											</span>
										@endif
									</td>

									<td class="text-center">
										<div class="d-flex justify-content-center flex-wrap gap-1">

											<a href="{{ route('backend.deliverymen.edit', $deliveryman) }}" class="btn btn-sm btn-outline-primary"
												title="Edit" aria-label="Edit deliveryman">
												<i class="bi bi-pencil-square"></i>
											</a>

											<form action="{{ route('backend.deliverymen.toggleStatus', $deliveryman) }}" method="POST"
												onsubmit="return confirm('Are you sure you want to change this deliveryman status?')">
												@csrf
												@method('PATCH')

												<button type="submit"
													class="btn btn-sm {{ $deliveryman->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
													title="{{ $deliveryman->is_active ? 'Deactivate' : 'Activate' }}"
													aria-label="{{ $deliveryman->is_active ? 'Deactivate' : 'Activate' }} deliveryman">
													<i class="bi {{ $deliveryman->is_active ? 'bi-person-dash' : 'bi-person-check' }}"></i>
												</button>
											</form>

											<form action="{{ route('backend.deliverymen.destroy', $deliveryman) }}" method="POST"
												onsubmit="return confirm('Are you sure you want to delete this deliveryman?')">
												@csrf
												@method('DELETE')

												<button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" aria-label="Delete deliveryman">
													<i class="bi bi-trash3"></i>
												</button>
											</form>

										</div>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="6" class="text-center py-5">
										<i class="bi bi-people display-5 text-muted"></i>

										<p class="mt-3 mb-1 fw-semibold">
											No deliverymen found.
										</p>

										<p class="text-muted mb-3">
											Create a new deliveryman to get started.
										</p>

										<a href="{{ route('backend.deliverymen.create') }}" class="btn btn-sm btn-primary">
											<i class="bi bi-plus-lg me-1"></i>
											Add Deliveryman
										</a>
									</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>

			{{-- Pagination --}}
			@if ($deliverymen->hasPages())
				<div class="card-footer bg-white">
					<div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
						<small class="text-muted">
							Showing {{ $deliverymen->firstItem() ?? 0 }}
							to {{ $deliverymen->lastItem() ?? 0 }}
							of {{ $deliverymen->total() }} deliverymen
						</small>

						{{ $deliverymen->links() }}
					</div>
				</div>
			@endif
		</div>

	</div>
@endsection
