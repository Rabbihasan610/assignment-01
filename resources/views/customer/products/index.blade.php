@extends('backend.layouts.app')

@section('content')
	<div class="container py-4 py-lg-5">

		<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
			<div>
				<h3 class="fw-bold mb-1">Our Products</h3>
				<p class="text-muted mb-0">
					Browse products and choose your preferred unit.
				</p>
			</div>

			<div class="text-muted">
				<i class="bi bi-box-seam me-1"></i>
				{{ $products->total() }} products
			</div>
		</div>

		@include('backend.partials.alerts')

		<form action="{{ route('customer.products.index') }}" method="GET" class="mb-4">
			<div class="row g-2">
				<div class="col-md-9">
					<div class="input-group">
						<span class="input-group-text bg-white">
							<i class="bi bi-search"></i>
						</span>
						<input type="search" name="search" class="form-control" placeholder="Search products..."
							value="{{ request('search') }}">
					</div>
				</div>

				<div class="col-md-3 d-grid">
					<button type="submit" class="btn btn-primary">
						Search Products
					</button>
				</div>
			</div>
		</form>

		<div class="row g-3 g-lg-4">
			@forelse ($products as $product)
				<div class="col-6 col-md-4 col-lg-3">
					<div class="card h-100 border-0 shadow-sm product-card">

						<a href="{{ route('customer.products.show', $product) }}" class="text-decoration-none">
							<div class="product-image bg-light d-flex align-items-center justify-content-center">
								@if ($product->image)
									<img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="img-fluid" loading="lazy">
								@else
									<i class="bi bi-image text-secondary display-4"></i>
								@endif
							</div>
						</a>

						<div class="card-body d-flex flex-column">
							<h6 class="fw-semibold mb-1">
								<a href="{{ route('customer.products.show', $product) }}" class="text-dark text-decoration-none">
									{{ $product->name }}
								</a>
							</h6>

							<small class="text-muted mb-2">
								Base unit:
								{{ $product->unit?->abbreviation ?? ($product->unit?->name ?? '—') }}
							</small>

							<div class="mb-2">
								<span class="fs-5 fw-bold text-primary">
									৳{{ number_format((float) $product->price, 2) }}
								</span>
								<small class="text-muted">
									/ {{ $product->unit?->abbreviation ?? $product->unit?->name }}
								</small>
							</div>

							@if ($product->productUnits->isNotEmpty())
								<div class="mb-3">
									<label class="form-label small text-muted mb-1">
										Available units
									</label>

									<select class="form-select form-select-sm product-unit-select">
										@foreach ($product->productUnits as $productUnit)
											<option value="{{ $productUnit->id }}" data-price="{{ $productUnit->price }}" @selected($productUnit->is_default)>
												{{ $productUnit->unit?->name }}
												— ৳{{ number_format((float) $productUnit->price, 2) }}
											</option>
										@endforeach
									</select>
								</div>
							@endif

							<div class="mb-3">
								@if ((float) $product->stock_quantity > 0)
									<span class="badge text-bg-success">
										<i class="bi bi-check-circle me-1"></i>
										In stock
									</span>
								@else
									<span class="badge text-bg-secondary">
										Out of stock
									</span>
								@endif
							</div>

							<div class="mt-auto">
								<a href="{{ route('customer.products.show', $product) }}" class="btn btn-outline-primary w-100">
									<i class="bi bi-eye me-1"></i>
									View Product
								</a>
							</div>
						</div>
					</div>
				</div>
			@empty
				<div class="col-12">
					<div class="card border-0 shadow-sm">
						<div class="card-body text-center py-5">
							<i class="bi bi-search display-4 text-muted"></i>
							<h5 class="mt-3">No products found</h5>
							<p class="text-muted mb-3">
								Try searching with a different product name.
							</p>
							<a href="{{ route('customer.products.index') }}" class="btn btn-primary">
								View All Products
							</a>
						</div>
					</div>
				</div>
			@endforelse
		</div>

		{{-- Pagination --}}
		@if ($products->hasPages())
			<div class="mt-4">
				{{ $products->withQueryString()->links() }}
			</div>
		@endif
	</div>

	<style>
		.product-card {
			border-radius: 12px;
			overflow: hidden;
			transition: transform .2s ease, box-shadow .2s ease;
		}

		.product-card:hover {
			transform: translateY(-3px);
			box-shadow: 0 .75rem 1.5rem rgba(0, 0, 0, .09) !important;
		}

		.product-image {
			height: 180px;
			overflow: hidden;
		}

		.product-image img {
			width: 100%;
			height: 100%;
			object-fit: cover;
		}

		@media (max-width: 575.98px) {
			.product-image {
				height: 130px;
			}
		}
	</style>
@endsection
