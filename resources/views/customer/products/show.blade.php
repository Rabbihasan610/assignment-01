@extends('backend.layouts.app')

@section('content')
	<div class="container py-4 py-lg-5">

		{{-- Breadcrumb --}}
		<nav aria-label="breadcrumb" class="mb-4">
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="{{ route('customer.products.index') }}">Products</a>
				</li>
				<li class="breadcrumb-item active" aria-current="page">
					{{ $product->name }}
				</li>
			</ol>
		</nav>

		@include('backend.partials.alerts')

		@php
			$baseUnitName = $product->unit?->abbreviation ?? ($product->unit?->name ?? 'Unit');

			$productUnits = $product->productUnits ?? collect();

			$hasProductUnits = $productUnits->isNotEmpty();

			$defaultProductUnit = $productUnits->firstWhere('is_default', true) ?? $productUnits->first();

			$initialPrice = $hasProductUnits ? (float) $defaultProductUnit->price : (float) $product->price;

			$initialUnitName = $hasProductUnits
			    ? $defaultProductUnit->unit?->abbreviation ?? ($defaultProductUnit->unit?->name ?? 'Unit')
			    : $baseUnitName;

			$initialFactor = $hasProductUnits ? (float) $defaultProductUnit->conversion_factor : 1;

			$availableStock = (float) $product->stock_quantity;

			$initialMaxQuantity = $initialFactor > 0 ? floor(($availableStock / $initialFactor) * 10000) / 10000 : 0;
		@endphp

		<div class="card border-0 shadow-sm overflow-hidden">
			<div class="row g-0">

				<div class="col-md-5 bg-light">
					<div class="product-image d-flex align-items-center justify-content-center h-100">

						@if ($product->image)
							<img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="img-fluid">
						@else
							<i class="bi bi-image display-1 text-secondary"></i>
						@endif

					</div>
				</div>

				<div class="col-md-7">
					<div class="card-body p-4 p-lg-5">

						<span class="badge text-bg-light border mb-3">
							{{ $product->unit?->name ?? 'Product' }}
						</span>

						<h2 class="fw-bold mb-3">
							{{ $product->name }}
						</h2>

						@if ($product->description)
							<p class="text-muted mb-4">
								{{ $product->description }}
							</p>
						@endif

						<div class="mb-4">
							<div class="text-muted small mb-1">
								Price
							</div>

							<div class="d-flex align-items-baseline gap-2">
								<h3 class="text-primary fw-bold mb-0" id="product-price">
									৳{{ number_format($initialPrice, 2) }}
								</h3>

								<span class="text-muted" id="price-unit">
									/ {{ $initialUnitName }}
								</span>
							</div>
						</div>

						<div class="mb-4">
							<span class="text-muted me-2">
								Availability:
							</span>

							@if ($availableStock > 0)
								<span class="badge text-bg-success">
									In Stock
								</span>

								<div class="small text-muted mt-2">
									Available stock:
									<strong id="available-stock">
										{{ number_format($availableStock, 4) }}
									</strong>

									{{ $baseUnitName }}
								</div>
							@else
								<span class="badge text-bg-secondary">
									Out of Stock
								</span>
							@endif
						</div>

						@if ($availableStock > 0)

							<form action="{{ route('customer.cart.add') }}" method="POST" id="add-to-cart-form">
								@csrf

								<input type="hidden" name="product_id" value="{{ $product->id }}">

								<div class="mb-3">
									<label for="product_unit_id" class="form-label fw-semibold">
										Select Unit
									</label>

									<select name="product_unit_id" id="product_unit_id" class="form-select" required>
										@if ($hasProductUnits)
											@foreach ($productUnits as $productUnit)
												@php
													$unitName = $productUnit->unit?->abbreviation ?? ($productUnit->unit?->name ?? 'Unit');

													$factor = (float) $productUnit->conversion_factor;

													$maxQuantity = $factor > 0 ? floor(($availableStock / $factor) * 10000) / 10000 : 0;
												@endphp

												<option value="{{ $productUnit->id }}" data-price="{{ $productUnit->price }}"
													data-factor="{{ $factor }}" data-unit="{{ $unitName }}" data-max-quantity="{{ $maxQuantity }}"
													@selected($defaultProductUnit && $defaultProductUnit->id === $productUnit->id) @disabled($factor <= 0 || $maxQuantity <= 0)>
													{{ $productUnit->unit?->name ?? $unitName }}
													— ৳{{ number_format((float) $productUnit->price, 2) }}
													(×{{ $factor }})
												</option>
											@endforeach
										@else
											<option value="" data-price="{{ $product->price }}" data-factor="1" data-unit="{{ $baseUnitName }}"
												data-max-quantity="{{ floor($availableStock * 10000) / 10000 }}" selected>
												{{ $product->unit?->name ?? 'Unit' }}
												— ৳{{ number_format((float) $product->price, 2) }}
											</option>
										@endif
									</select>

									<small class="text-muted d-block mt-2">
										Select your preferred selling unit.
										Conversion factor is relative to the base unit.
									</small>
								</div>

								<div class="mb-4">
									<label for="quantity" class="form-label fw-semibold">
										Quantity
									</label>

									<div class="input-group" style="max-width: 260px;">
										<button type="button" class="btn btn-outline-secondary" id="quantity-minus" aria-label="Decrease quantity">
											<i class="bi bi-dash-lg"></i>
										</button>

										<input type="number" name="quantity" id="quantity" class="form-control text-center" value="1"
											min="0.0001" max="{{ $initialMaxQuantity }}" step="0.0001" inputmode="decimal" required>

										<button type="button" class="btn btn-outline-secondary" id="quantity-plus" aria-label="Increase quantity">
											<i class="bi bi-plus-lg"></i>
										</button>
									</div>

									<div class="small text-muted mt-2" id="conversion-info">
										1 {{ $initialUnitName }}
										= {{ number_format($initialFactor, 4) }}
										{{ $baseUnitName }}
									</div>

									<div class="small text-danger mt-1 d-none" id="quantity-error">
										Quantity exceeds available stock.
									</div>
								</div>

								<div class="bg-light rounded p-3 mb-4">
									<div class="d-flex justify-content-between align-items-center gap-3">
										<span class="text-muted">
											Estimated Total
										</span>

										<h4 class="fw-bold text-primary mb-0" id="estimated-total">
											৳{{ number_format($initialPrice, 2) }}
										</h4>
									</div>

									<hr>

									<div class="d-flex justify-content-between small">
										<span class="text-muted">
											Total base quantity
										</span>

										<strong id="base-quantity">
											{{ number_format($initialFactor, 4) }}
											{{ $baseUnitName }}
										</strong>
									</div>

									<small class="text-muted d-block mt-2">
										Final price and stock will be validated by the server.
									</small>
								</div>

								<div class="d-flex flex-wrap gap-2">
									<button type="submit" class="btn btn-primary btn-lg flex-grow-1" id="add-to-cart-button">
										<i class="bi bi-cart-plus me-2"></i>
										Add to Cart
									</button>

									<a href="{{ route('customer.products.index') }}" class="btn btn-outline-secondary btn-lg">
										<i class="bi bi-arrow-left me-1"></i>
										Back
									</a>
								</div>

							</form>
						@else
							<div class="alert alert-warning">
								<i class="bi bi-exclamation-circle me-2"></i>
								This product is currently out of stock.
							</div>

							<a href="{{ route('customer.products.index') }}" class="btn btn-outline-secondary">
								<i class="bi bi-arrow-left me-1"></i>
								Back to Products
							</a>

						@endif

					</div>
				</div>
			</div>
		</div>
	</div>

	<style>
		.product-image {
			min-height: 360px;
			padding: 24px;
		}

		.product-image img {
			max-height: 420px;
			width: 100%;
			object-fit: contain;
		}

		#quantity {
			min-width: 80px;
		}

		#add-to-cart-button:disabled {
			cursor: not-allowed;
			opacity: 0.65;
		}

		@media (max-width: 767.98px) {
			.product-image {
				min-height: 240px;
				max-height: 300px;
			}

			.product-image img {
				max-height: 260px;
			}
		}
	</style>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const form = document.getElementById('add-to-cart-form');
			const unitSelect = document.getElementById('product_unit_id');
			const quantityInput = document.getElementById('quantity');

			const priceElement = document.getElementById('product-price');
			const priceUnitElement = document.getElementById('price-unit');
			const totalElement = document.getElementById('estimated-total');
			const baseQuantityElement = document.getElementById('base-quantity');
			const conversionInfoElement = document.getElementById('conversion-info');

			const minusButton = document.getElementById('quantity-minus');
			const plusButton = document.getElementById('quantity-plus');
			const submitButton = document.getElementById('add-to-cart-button');
			const quantityError = document.getElementById('quantity-error');

			if (!form || !unitSelect || !quantityInput) {
				return;
			}

			const baseUnitName = @json($baseUnitName);

			const currency = function(amount) {
				return '৳' + Number(amount).toLocaleString('en-BD', {
					minimumFractionDigits: 2,
					maximumFractionDigits: 2
				});
			};

			function selectedOption() {
				return unitSelect.options[unitSelect.selectedIndex];
			}

			function updateTotal() {
				const option = selectedOption();

				if (!option) {
					return;
				}

				const price = Number(option.dataset.price || 0);
				const factor = Number(option.dataset.factor || 0);
				const unitName = option.dataset.unit || 'Unit';
				const maxQuantity = Number(option.dataset.maxQuantity || 0);

				const quantity = Number(quantityInput.value) || 0;
				const validFactor = Number.isFinite(factor) && factor > 0;
				const validPrice = Number.isFinite(price) && price >= 0;

				quantityInput.max = maxQuantity;

				priceElement.textContent = currency(price);
				priceUnitElement.textContent = '/ ' + unitName;

				totalElement.textContent = currency(
					validPrice ? price * quantity : 0
				);

				baseQuantityElement.textContent =
					(validFactor ? quantity * factor : 0).toLocaleString('en-BD', {
						minimumFractionDigits: 0,
						maximumFractionDigits: 4
					}) + ' ' + baseUnitName;

				conversionInfoElement.textContent =
					'1 ' + unitName + ' = ' +
					(validFactor ? factor : 0).toLocaleString('en-BD', {
						minimumFractionDigits: 0,
						maximumFractionDigits: 4
					}) + ' ' + baseUnitName;

				const validQuantity =
					quantity > 0 &&
					quantity >= Number(quantityInput.min) &&
					quantity <= maxQuantity;

				const validUnit = validFactor && validPrice;

				quantityError.classList.toggle(
					'd-none',
					validQuantity || quantity === 0
				);

				submitButton.disabled = !validQuantity || !validUnit;
			}

			unitSelect.addEventListener('change', function() {
				const option = selectedOption();
				const maxQuantity = Number(option?.dataset.maxQuantity || 0);

				// Reset quantity when selling unit changes.
				quantityInput.value = maxQuantity >= 1 ? '1' : (
					maxQuantity > 0 ? String(maxQuantity) : ''
				);

				updateTotal();
			});

			quantityInput.addEventListener('input', updateTotal);

			minusButton.addEventListener('click', function() {
				const step = Number(quantityInput.step) || 1;
				const min = Number(quantityInput.min) || step;
				const current = Number(quantityInput.value) || min;

				quantityInput.value = Math.max(min, current - step)
					.toFixed(4)
					.replace(/\.?0+$/, '');

				updateTotal();
			});

			plusButton.addEventListener('click', function() {
				const step = Number(quantityInput.step) || 1;
				const max = Number(quantityInput.max) || 0;
				const current = Number(quantityInput.value) || 0;
				const next = Math.min(max, current + step);

				quantityInput.value = next > 0 ?
					next.toFixed(4).replace(/\.?0+$/, '') :
					'';

				updateTotal();
			});

			form.addEventListener('submit', function(event) {
				const option = selectedOption();
				const quantity = Number(quantityInput.value) || 0;
				const maxQuantity = Number(option?.dataset.maxQuantity || 0);
				const factor = Number(option?.dataset.factor || 0);

				if (
					!option ||
					quantity <= 0 ||
					quantity > maxQuantity ||
					factor <= 0
				) {
					event.preventDefault();
					updateTotal();
					quantityInput.focus();
				}
			});

			updateTotal();
		});
	</script>
@endsection
