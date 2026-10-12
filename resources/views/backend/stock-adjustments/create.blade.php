@extends('backend.layouts.app')

@section('content')
	<div class="container-fluid">
		<div class="mb-4">
			<h4>Stock Adjustment</h4>
			<p class="text-muted mb-0">
				Add stock, issue stock, record damage or returns.
			</p>
		</div>

		@include('backend.partials.alerts')

		<div class="card border-0 shadow-sm">
			<div class="card-body p-4">
				<form method="POST" action="{{ route('backend.stock-adjustments.store') }}">
					@csrf

					{{-- Product --}}
					<div class="mb-3">
						<label for="product_id" class="form-label">Product *</label>

						<select name="product_id" id="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
							<option value="">Select product</option>

							@foreach ($products as $product)
								<option value="{{ $product->id }}" data-stock="{{ $product->stock_quantity }}"
									data-base-unit="{{ $product->unit?->abbreviation ?? ($product->unit?->name ?? '') }}"
									data-units="{{ json_encode(
									    $product->productUnits->map(
									            fn($pu) => [
									                'id' => $pu->id,
									                'unit_id' => $pu->unit_id,
									                'name' => $pu->unit?->name ?? '',
									                'abbreviation' => $pu->unit?->abbreviation ?? '',
									                'conversion_factor' => (float) $pu->conversion_factor,
									                'is_default' => (bool) $pu->is_default,
									            ],
									        )->values(),
									) }}"
									@selected(old('product_id') == $product->id)>
									{{ $product->name }}
									— Stock: {{ $product->stock_quantity }}
									{{ $product->unit?->abbreviation ?? $product->unit?->name }}
								</option>
							@endforeach
						</select>

						@error('product_id')
							<div class="invalid-feedback">{{ $message }}</div>
						@enderror

						<div id="current-stock" class="form-text"></div>
					</div>

					{{-- Selling unit --}}
					<div class="mb-3">
						<label for="product_unit_id" class="form-label">
							Selling Unit *
						</label>

						<select name="product_unit_id" id="product_unit_id"
							class="form-select @error('product_unit_id') is-invalid @enderror" required>
							<option value="">Select product first</option>
						</select>

						@error('product_unit_id')
							<div class="invalid-feedback">{{ $message }}</div>
						@enderror

						<div id="conversion-info" class="form-text">
							Select a product to see its available units.
						</div>
					</div>

					{{-- Adjustment type --}}
					<div class="mb-3">
						<label for="adjustment_type" class="form-label">
							Adjustment Type *
						</label>

						<select name="adjustment_type" id="adjustment_type"
							class="form-select @error('adjustment_type') is-invalid @enderror" required>
							<option value="">Select type</option>

							@foreach (['IN', 'OUT', 'DAMAGE', 'RETURN', 'ADJUSTMENT'] as $type)
								<option value="{{ $type }}" @selected(old('adjustment_type') === $type)>
									{{ $type }}
								</option>
							@endforeach
						</select>

						@error('adjustment_type')
							<div class="invalid-feedback">{{ $message }}</div>
						@enderror

						<div id="adjustment-help" class="form-text">
							Select an adjustment type.
						</div>
					</div>

					{{-- Quantity --}}
					<div class="mb-3">
						<label for="quantity" class="form-label">Quantity *</label>

						<input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror"
							step="0.0001" min="0.0001" max="999999999" value="{{ old('quantity', 1) }}" required>

						@error('quantity')
							<div class="invalid-feedback">{{ $message }}</div>
						@enderror
					</div>

					{{-- Conversion preview --}}
					<div class="alert alert-light border mb-3">
						<div class="small text-muted mb-1">Stock conversion preview</div>
						<div id="conversion-preview">
							Select a product, unit and quantity.
						</div>
					</div>

					{{-- Reason --}}
					<div class="mb-4">
						<label for="reason" class="form-label">Reason</label>

						<textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" rows="3"
						 maxlength="2000" placeholder="Enter reason for this adjustment">{{ old('reason') }}</textarea>

						@error('reason')
							<div class="invalid-feedback">{{ $message }}</div>
						@enderror
					</div>

					<button type="submit" class="btn btn-primary">
						<i class="bi bi-check-lg me-1"></i>
						Save Adjustment
					</button>

					<a href="{{ route('backend.stock-adjustments.index') }}" class="btn btn-light border">
						Cancel
					</a>
				</form>
			</div>
		</div>
	</div>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const productSelect = document.getElementById('product_id');
			const unitSelect = document.getElementById('product_unit_id');
			const typeSelect = document.getElementById('adjustment_type');
			const quantityInput = document.getElementById('quantity');

			const stockInfo = document.getElementById('current-stock');
			const conversionInfo = document.getElementById('conversion-info');
			const adjustmentHelp = document.getElementById('adjustment-help');
			const preview = document.getElementById('conversion-preview');

			const oldUnitId = @json(old('product_unit_id', ''));

			let availableUnits = [];
			let currentStock = 0;
			let baseUnit = '';

			function selectedUnit() {
				return availableUnits.find(
					unit => String(unit.id) === String(unitSelect.value)
				);
			}

			function formatNumber(value) {
				return Number(value).toLocaleString(undefined, {
					maximumFractionDigits: 4
				});
			}

			function loadProduct() {
				const option = productSelect.options[productSelect.selectedIndex];

				unitSelect.innerHTML = '';
				availableUnits = [];

				if (!option || !option.value) {
					currentStock = 0;
					baseUnit = '';

					unitSelect.add(new Option('Select product first', ''));
					stockInfo.textContent = '';
					conversionInfo.textContent =
						'Select a product to see its available units.';

					updatePreview();
					return;
				}

				currentStock = Number(option.dataset.stock || 0);
				baseUnit = option.dataset.baseUnit || '';

				try {
					availableUnits = JSON.parse(option.dataset.units || '[]');
				} catch (error) {
					availableUnits = [];
				}

				stockInfo.textContent =
					`Current stock: ${formatNumber(currentStock)} ${baseUnit}`;

				unitSelect.add(new Option('Select selling unit', ''));

				availableUnits.forEach(unit => {
					const label = unit.abbreviation ?
						`${unit.name} (${unit.abbreviation})` :
						unit.name;

					const unitOption = new Option(label, unit.id);

					unitOption.dataset.factor = unit.conversion_factor;

					unitSelect.add(unitOption);
				});

				const defaultUnit = availableUnits.find(unit => unit.is_default);

				const preferredUnit = availableUnits.find(
					unit => String(unit.id) === String(oldUnitId)
				);

				if (preferredUnit) {
					unitSelect.value = String(preferredUnit.id);
				} else if (defaultUnit) {
					unitSelect.value = String(defaultUnit.id);
				}

				updateConversionInfo();
				updatePreview();
			}

			function updateConversionInfo() {
				const unit = selectedUnit();

				if (!unit) {
					conversionInfo.textContent =
						'Select a selling unit to see its conversion factor.';
					return;
				}

				conversionInfo.textContent =
					`1 ${unit.abbreviation || unit.name} = ` +
					`${formatNumber(unit.conversion_factor)} ${baseUnit}`;
			}

			function updateAdjustmentHelp() {
				const type = typeSelect.value;

				const messages = {
					IN: 'Stock received or purchased. This increases stock.',
					RETURN: 'Returned goods accepted back into stock.',
					OUT: 'Stock issued or removed. This decreases stock.',
					DAMAGE: 'Damaged goods will be deducted from stock.',
					ADJUSTMENT: 'Positive quantity increases stock in this form.'
				};

				adjustmentHelp.textContent =
					messages[type] || 'Select an adjustment type.';
			}

			function updatePreview() {
				const unit = selectedUnit();
				const quantity = Number(quantityInput.value);
				const type = typeSelect.value;

				if (!unit || !Number.isFinite(quantity) || quantity <= 0) {
					preview.textContent =
						'Select a product, unit and valid quantity.';
					return;
				}

				const baseQuantity = quantity * Number(unit.conversion_factor);

				let newStock = currentStock;

				if (['IN', 'RETURN', 'ADJUSTMENT'].includes(type)) {
					newStock += baseQuantity;
				} else if (['OUT', 'DAMAGE'].includes(type)) {
					newStock -= baseQuantity;
				} else {
					preview.textContent =
						`This quantity equals ${formatNumber(baseQuantity)} ${baseUnit}.`;
					return;
				}

				preview.textContent =
					`${formatNumber(quantity)} ${unit.abbreviation || unit.name} = ` +
					`${formatNumber(baseQuantity)} ${baseUnit}. ` +
					`Estimated stock after adjustment: ${formatNumber(newStock)} ${baseUnit}.`;
			}

			productSelect.addEventListener('change', loadProduct);

			unitSelect.addEventListener('change', function() {
				updateConversionInfo();
				updatePreview();
			});

			typeSelect.addEventListener('change', function() {
				updateAdjustmentHelp();
				updatePreview();
			});

			quantityInput.addEventListener('input', updatePreview);

			loadProduct();
			updateAdjustmentHelp();
		});
	</script>
@endsection
