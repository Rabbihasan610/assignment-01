```blade
@extends('backend.layouts.app')

@section('content')
	<div class="container py-4 py-lg-5">
		<nav aria-label="breadcrumb" class="mb-4">
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="{{ route('customer.products.index') }}">Products</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">
					Shopping Cart
				</li>
			</ol>
		</nav>

		@include('backend.partials.alerts')

		<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
			<div>
				<h2 class="fw-bold mb-1">Shopping Cart</h2>
				<p class="text-muted mb-0">Review and update your selected products.</p>
			</div>

			<a href="{{ route('customer.products.index') }}" class="btn btn-outline-primary">
				<i class="bi bi-arrow-left me-1"></i>
				Continue Shopping
			</a>
		</div>

		@if ($items->isEmpty())
			<div class="card border-0 shadow-sm">
				<div class="card-body text-center py-5">
					<i class="bi bi-cart3 display-1 text-muted"></i>

					<h4 class="fw-bold mt-3">Your cart is empty</h4>

					<p class="text-muted">
						You have not added any products to your cart yet.
					</p>

					<a href="{{ route('customer.products.index') }}" class="btn btn-primary mt-2">
						<i class="bi bi-bag-plus me-2"></i>
						Browse Products
					</a>
				</div>
			</div>
		@else
			@php
				$cartSubtotal = 0;
			@endphp

			<div class="row g-4">

				<div class="col-lg-8">
					<div class="card border-0 shadow-sm">
						<div class="card-header bg-white py-3">
							<div class="d-flex justify-content-between align-items-center">
								<h5 class="fw-bold mb-0">
									Cart Items
								</h5>

								<span class="badge bg-primary rounded-pill">
									{{ $items->count() }} {{ $items->count() === 1 ? 'item' : 'items' }}
								</span>
							</div>
						</div>

						<div class="card-body p-0">
							@foreach ($items as $item)
								@php
									$product = $item->product;
									$productUnit = $item->productUnit;
									$unit = $productUnit?->unit;

									$unitName = $unit?->abbreviation ?? ($unit?->name ?? ($item->unit_name ?? 'Unit'));

									$quantity = (float) $item->quantity;
									$unitPrice = (float) $item->unit_price;
									$lineTotal = $quantity * $unitPrice;

									$cartSubtotal += $lineTotal;

									$conversionFactor =
									    (float) ($item->base_quantity > 0 && $quantity > 0
									        ? $item->base_quantity / $quantity
									        : $productUnit?->conversion_factor ?? 1);

									$baseStock = (float) ($product?->stock_quantity ?? 0);
								@endphp

								<div class="p-3 p-md-4 {{ !$loop->last ? 'border-bottom' : '' }}">
									<div class="row g-3 align-items-center">

										{{-- Product Info --}}
										<div class="col-12 col-md-5">
											<div class="d-flex align-items-center gap-3">
												@if (!empty($product?->image))
													<img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="rounded border"
														style="width:75px;height:75px;object-fit:cover;">
												@else
													<div class="rounded border bg-light d-flex align-items-center justify-content-center"
														style="width:75px;height:75px;flex-shrink:0;">
														<i class="bi bi-box-seam fs-2 text-muted"></i>
													</div>
												@endif

												<div>
													<h6 class="fw-bold mb-1">
														{{ $product?->name ?? 'Product unavailable' }}
													</h6>

													<div class="small text-muted">
														Unit: {{ $unitName }}
													</div>

													<div class="small text-muted">
														Price: ৳{{ number_format($unitPrice, 2) }}
														/ {{ $unitName }}
													</div>
												</div>
											</div>
										</div>

										{{-- Quantity Update --}}
										<div class="col-7 col-md-4">
											<label for="quantity-{{ $item->id }}" class="form-label small fw-semibold mb-1">
												Quantity
											</label>

											<form action="{{ route('customer.cart.update', $item->id) }}" method="POST"
												class="d-flex align-items-center gap-2">
												@csrf
												@method('PATCH')

												<div class="input-group input-group-sm" style="max-width:160px;">

													<button type="button" class="btn btn-outline-secondary quantity-minus" aria-label="Decrease quantity">
														<i class="bi bi-dash"></i>
													</button>

													<input type="number" id="quantity-{{ $item->id }}" name="quantity"
														class="form-control text-center quantity-input"
														value="{{ rtrim(rtrim(number_format($quantity, 2, '.', ''), '0'), '.') }}" min="0.01" max="999999"
														step="0.01" required>

													<button type="button" class="btn btn-outline-secondary quantity-plus" aria-label="Increase quantity">
														<i class="bi bi-plus"></i>
													</button>
												</div>

												<button type="submit" class="btn btn-sm btn-outline-primary" title="Update quantity">
													<i class="bi bi-arrow-repeat"></i>
													<span class="d-none d-xl-inline">Update</span>
												</button>
											</form>

											<div class="small text-muted mt-1">
												Edit quantity and press Update.
											</div>
										</div>

										{{-- Subtotal and Delete --}}
										<div class="col-5 col-md-3 text-end">
											<div class="small text-muted mb-1">
												Subtotal
											</div>

											<div class="fw-bold fs-5 text-primary">
												৳{{ number_format($lineTotal, 2) }}
											</div>

											<form action="{{ route('customer.cart.remove', $item->id) }}" method="POST" class="mt-2"
												onsubmit="return confirm('Are you sure you want to remove this product from your cart?');">
												@csrf
												@method('DELETE')

												<button type="submit" class="btn btn-sm btn-outline-danger">
													<i class="bi bi-trash me-1"></i>
													Remove
												</button>
											</form>
										</div>

									</div>
								</div>
							@endforeach
						</div>

						<div class="card-footer bg-white p-3">
							<a href="{{ route('customer.products.index') }}" class="btn btn-outline-secondary">
								<i class="bi bi-plus-circle me-1"></i>
								Add More Products
							</a>
						</div>
					</div>
				</div>

				{{-- Order Summary --}}
				<div class="col-lg-4">
					<div class="card border-0 shadow-sm position-sticky" style="top:20px;">
						<div class="card-body p-4">

							<h5 class="fw-bold mb-4">
								<i class="bi bi-receipt me-2"></i>
								Order Summary
							</h5>

							<div class="d-flex justify-content-between mb-3">
								<span class="text-muted">Products</span>
								<span>{{ $items->count() }}</span>
							</div>

							<div class="d-flex justify-content-between mb-3">
								<span class="text-muted">Subtotal</span>
								<span class="fw-semibold">
									৳{{ number_format($cartSubtotal, 2) }}
								</span>
							</div>

							<div class="d-flex justify-content-between mb-3">
								<span class="text-muted">Delivery Fee</span>
								<span class="text-muted">Calculated at checkout</span>
							</div>

							<hr>

							<div class="d-flex justify-content-between align-items-center mb-4">
								<span class="fw-bold">Estimated Total</span>

								<span class="fs-4 fw-bold text-primary">
									৳{{ number_format($cartSubtotal, 2) }}
								</span>
							</div>

							<a href="{{ route('customer.checkout.index') }}" class="btn btn-primary btn-lg w-100">
								<i class="bi bi-credit-card me-2"></i>
								Proceed to Checkout
							</a>

							<div class="small text-muted mt-3">
								<i class="bi bi-shield-check me-1"></i>
								Stock and prices will be checked again before your order is placed.
							</div>

						</div>
					</div>
				</div>

			</div>
		@endif
	</div>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			document.querySelectorAll('.quantity-minus').forEach(function(button) {
				button.addEventListener('click', function() {
					const input = this.closest('form').querySelector('.quantity-input');
					const step = parseFloat(input.step) || 0.01;
					const min = parseFloat(input.min) || 0.01;
					const value = parseFloat(input.value) || min;

					input.value = Math.max(min, value - step).toFixed(2);
				});
			});

			document.querySelectorAll('.quantity-plus').forEach(function(button) {
				button.addEventListener('click', function() {
					const input = this.closest('form').querySelector('.quantity-input');
					const step = parseFloat(input.step) || 0.01;
					const max = parseFloat(input.max) || 999999;
					const value = parseFloat(input.value) || 0;

					input.value = Math.min(max, value + step).toFixed(2);
				});
			});
		});
	</script>
@endsection
