@extends('backend.layouts.app')

@section('content')
	<div class="container-fluid">
		<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
			<div>
				<h4 class="mb-1">Stock Adjustments</h4>
				<p class="text-muted mb-0">
					Inventory movement history.
				</p>
			</div>

			<a href="{{ route('backend.stock-adjustments.create') }}" class="btn btn-primary">
				<i class="bi bi-plus-lg me-1"></i>
				New Adjustment
			</a>
		</div>

		@include('backend.partials.alerts')

		<div class="card border-0 shadow-sm">
			<div class="card-body border-bottom">
				<div class="row g-3">
					<div class="col-md-3">
						<div class="text-muted small">Total Records</div>
						<div class="fw-semibold">
							{{ $adjustments->total() }}
						</div>
					</div>

					<div class="col-md-3">
						<div class="text-muted small">Current Page</div>
						<div class="fw-semibold">
							{{ $adjustments->count() }} records
						</div>
					</div>
				</div>
			</div>

			<div class="table-responsive">
				<table class="table table-hover align-middle mb-0">
					<thead class="table-light">
						<tr>
							<th>#</th>
							<th>Date</th>
							<th>Product</th>
							<th>Type</th>
							<th>Quantity</th>
							<th>Base Quantity</th>
							<th>Conversion</th>
							<th>Adjusted By</th>
							<th>Reason</th>
						</tr>
					</thead>

					<tbody>
						@forelse ($adjustments as $adjustment)
							@php
								$badge = match ($adjustment->adjustment_type) {
								    'IN' => 'success',
								    'RETURN' => 'info',
								    'OUT' => 'warning',
								    'DAMAGE' => 'danger',
								    'ADJUSTMENT' => 'secondary',
								    default => 'secondary',
								};

								$product = $adjustment->product;
								$productUnit = $adjustment->productUnit;
								$baseUnit = $product?->unit;

								$selectedUnitName =
								    $productUnit?->unit?->abbreviation ??
								    ($productUnit?->unit?->name ?? ($baseUnit?->abbreviation ?? ($baseUnit?->name ?? '')));

								$baseUnitName = $baseUnit?->abbreviation ?? ($baseUnit?->name ?? '');
							@endphp

							<tr>
								<td>
									{{ $adjustments->firstItem() + $loop->index }}
								</td>

								<td class="text-nowrap">
									<div>
										{{ $adjustment->created_at?->format('d/m/Y') ?? '—' }}
									</div>
									<small class="text-muted">
										{{ $adjustment->created_at?->format('h:i A') ?? '' }}
									</small>
								</td>

								<td>
									<div class="fw-semibold">
										{{ $product?->name ?? 'Deleted product' }}
									</div>

									<small class="text-muted">
										Base unit: {{ $baseUnitName ?: '—' }}
									</small>
								</td>

								<td>
									<span class="badge text-bg-{{ $badge }}">
										{{ $adjustment->adjustment_type }}
									</span>
								</td>

								<td class="text-nowrap">
									<div class="fw-semibold">
										{{ number_format((float) $adjustment->quantity, 4, '.', '') }}
										{{ $selectedUnitName }}
									</div>
								</td>

								<td class="text-nowrap">
									{{ number_format((float) $adjustment->base_quantity, 4, '.', '') }}
									{{ $baseUnitName }}
								</td>

								<td class="text-nowrap">
									1 {{ $selectedUnitName }}
									=
									{{ number_format((float) $adjustment->conversion_factor, 4, '.', '') }}
									{{ $baseUnitName }}
								</td>

								<td>
									{{ $adjustment->user?->name ?? 'System / Unknown' }}
								</td>

								<td style="min-width: 180px;">
									{{ $adjustment->reason ?: '—' }}
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="9" class="text-center py-5">
									<i class="bi bi-box-seam fs-2 text-muted"></i>
									<p class="text-muted mt-2 mb-0">
										No stock adjustments found.
									</p>

									<a href="{{ route('backend.stock-adjustments.create') }}" class="btn btn-sm btn-outline-primary mt-3">
										Create First Adjustment
									</a>
								</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>

			@if ($adjustments->hasPages())
				<div class="card-body">
					{{ $adjustments->links() }}
				</div>
			@endif
		</div>
	</div>
@endsection
