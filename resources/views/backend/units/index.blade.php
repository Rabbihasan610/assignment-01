@extends('backend.layouts.app')

@section('content')
	<div class="container-fluid">
		<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
			<div>
				<h4 class="mb-1">Units</h4>
				<p class="text-muted mb-0">
					Manage measurement units used by your products.
				</p>
			</div>

			<a href="{{ route('backend.units.create') }}" class="btn btn-primary">
				<i class="bi bi-plus-lg me-1"></i>
				Add Unit
			</a>
		</div>

		@include('backend.partials.alerts')

		<div class="row g-3 mb-4">
			<div class="col-sm-6 col-xl-4">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body d-flex align-items-center gap-3">
						<div class="rounded bg-primary-subtle text-primary p-3">
							<i class="bi bi-rulers fs-4"></i>
						</div>
						<div>
							<div class="text-muted small">Total Units</div>
							<h4 class="mb-0">{{ $units->total() }}</h4>
						</div>
					</div>
				</div>
			</div>
		</div>

		{{-- Units Table --}}
		<div class="card border-0 shadow-sm">
			<div class="card-header bg-white border-0 py-3">
				<h6 class="mb-0">All Measurement Units</h6>
			</div>

			<div class="table-responsive">
				<table class="table table-hover align-middle mb-0">
					<thead class="table-light">
						<tr>
							<th class="ps-3" style="width: 70px;">#</th>
							<th>Name</th>
							<th>Abbreviation</th>
							<th>Description</th>
							<th class="text-center">Base Products</th>
							<th class="text-center">Selling Units</th>
							<th class="text-end pe-3">Actions</th>
						</tr>
					</thead>

					<tbody>
						@forelse ($units as $unit)
							@php
								$baseProductCount = $unit->products_count ?? 0;
								$sellingUnitCount = $unit->product_units_count ?? 0;

								$isUsed = $baseProductCount > 0 || $sellingUnitCount > 0;
							@endphp

							<tr>
								<td class="ps-3 text-muted">
									{{ $units->firstItem() + $loop->index }}
								</td>

								<td>
									<div class="fw-semibold">{{ $unit->name }}</div>
									@if ($isUsed)
										<small class="text-muted">Currently in use</small>
									@endif
								</td>

								<td>
									@if ($unit->abbreviation)
										<span class="badge text-bg-light border">
											{{ $unit->abbreviation }}
										</span>
									@else
										<span class="text-muted">—</span>
									@endif
								</td>

								<td>
									<span class="text-muted">
										{{ $unit->description ?: '—' }}
									</span>
								</td>

								<td class="text-center">
									<span class="badge {{ $baseProductCount > 0 ? 'text-bg-primary' : 'text-bg-light border text-secondary' }}">
										{{ $baseProductCount }}
									</span>
								</td>

								<td class="text-center">
									<span class="badge {{ $sellingUnitCount > 0 ? 'text-bg-info' : 'text-bg-light border text-secondary' }}">
										{{ $sellingUnitCount }}
									</span>
								</td>

								<td class="text-end pe-3">
									<div class="d-inline-flex gap-1">
										<a href="{{ route('backend.units.edit', $unit) }}" class="btn btn-sm btn-outline-primary" title="Edit unit"
											aria-label="Edit {{ $unit->name }}">
											<i class="bi bi-pencil"></i>
										</a>

										<form action="{{ route('backend.units.destroy', $unit) }}" method="POST" class="d-inline"
											onsubmit="return confirm('Are you sure you want to delete this unit?')">
											@csrf
											@method('DELETE')

											<button type="submit" class="btn btn-sm btn-outline-danger"
												title="{{ $isUsed ? 'Cannot delete a unit currently in use' : 'Delete unit' }}"
												aria-label="Delete {{ $unit->name }}" @disabled($isUsed)>
												<i class="bi bi-trash"></i>
											</button>
										</form>
									</div>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="7" class="text-center py-5">
									<i class="bi bi-rulers fs-1 text-muted d-block mb-2"></i>
									<h6 class="mb-1">No units found</h6>
									<p class="text-muted mb-3">
										Add your first measurement unit to get started.
									</p>
									<a href="{{ route('backend.units.create') }}" class="btn btn-primary btn-sm">
										<i class="bi bi-plus-lg me-1"></i>
										Add Unit
									</a>
								</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>

			@if ($units->hasPages())
				<div class="card-footer bg-white border-0 py-3">
					<div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
						<small class="text-muted">
							Showing {{ $units->firstItem() }}–{{ $units->lastItem() }}
							of {{ $units->total() }} units
						</small>

						{{ $units->links() }}
					</div>
				</div>
			@endif
		</div>
	</div>
@endsection
