@extends('backend.layouts.app')

@section('content')
	<div class="container-fluid">
		@php
			$editing = $product->exists;

			$savedUnits = old('units');

			if (is_array($savedUnits)) {
			    $unitRows = collect($savedUnits);
			} elseif ($editing) {
			    $unitRows = $product->productUnits->map(function ($item) {
			        return [
			            'unit_id' => $item->unit_id,
			            'conversion_factor' => $item->conversion_factor,
			            'price' => $item->price,
			            'is_default' => $item->is_default,
			        ];
			    });
			} else {
			    $unitRows = collect();
			}

			if ($unitRows->isEmpty()) {
			    $unitRows = collect([
			        [
			            'unit_id' => old('unit_id', $product->unit_id ?? ''),
			            'conversion_factor' => 1,
			            'price' => old('price', $product->price ?? ''),
			            'is_default' => true,
			        ],
			    ]);
			}
		@endphp

		<div class="mb-4">
			<h4>{{ $editing ? 'Edit Product' : 'Create Product' }}</h4>
			<p class="text-muted mb-0">
				Product details, selling units, conversion and pricing.
			</p>
		</div>

		@include('backend.partials.alerts')

		<div class="card border-0 shadow-sm">
			<div class="card-body p-4">
				<form method="POST" enctype="multipart/form-data"
					action="{{ $editing ? route('backend.products.update', $product) : route('backend.products.store') }}">
					@csrf

					@if ($editing)
						@method('PUT')
					@endif

					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">
								Product Name <span class="text-danger">*</span>
							</label>

							<input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
								value="{{ old('name', $product->name) }}" required>

							@error('name')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>

						<div class="col-md-6 mb-3">
							<label for="unit_id" class="form-label">
								Base Unit <span class="text-danger">*</span>
							</label>

							<select name="unit_id" id="unit_id" class="form-select @error('unit_id') is-invalid @enderror" required>
								<option value="">Select base unit</option>

								@foreach ($units as $unit)
									<option value="{{ $unit->id }}" @selected(old('unit_id', $product->unit_id) == $unit->id)>
										{{ $unit->name }}
										@if ($unit->abbreviation)
											({{ $unit->abbreviation }})
										@endif
									</option>
								@endforeach
							</select>

							@error('unit_id')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror

							<small class="text-muted">
								Stock is always managed using this unit.
							</small>
						</div>

						<div class="col-md-6 mb-3">
							<label class="form-label">Product Image</label>

							<input type="file" name="image" accept="image/*" class="form-control @error('image') is-invalid @enderror">

							@error('image')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror

							@if ($product->image)
								<img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="mt-2 rounded border"
									style="width:80px;height:80px;object-fit:cover">
							@endif
						</div>

						<div class="col-md-6 mb-3">
							<label class="form-label">Description</label>

							<textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>

							@error('description')
								<div class="invalid-feedback">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<hr class="my-4">

					<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
						<div>
							<h5 class="mb-1">Selling Units & Conversion</h5>
							<p class="text-muted small mb-0">
								Add all units customers can use to purchase this product.
							</p>
						</div>

						<button type="button" class="btn btn-outline-primary" id="add-unit">
							<i class="fas fa-plus me-1"></i> Add Selling Unit
						</button>
					</div>

					@error('units')
						<div class="alert alert-danger">{{ $message }}</div>
					@enderror

					@foreach ($errors->getMessages() as $key => $messages)
						@if (str_starts_with($key, 'units.'))
							<div class="alert alert-danger py-2">
								{{ implode(' ', $messages) }}
							</div>
						@endif
					@endforeach

					<div id="product-units">
						@foreach ($unitRows as $index => $row)
							<div class="unit-row border rounded p-3 mb-3">
								<div class="row align-items-end">
									<div class="col-lg-3 col-md-6 mb-3">
										<label class="form-label">Selling Unit *</label>

										<select name="units[{{ $index }}][unit_id]" class="form-select selling-unit" required>
											<option value="">Select unit</option>

											@foreach ($units as $unit)
												<option value="{{ $unit->id }}" @selected((string) ($row['unit_id'] ?? '') === (string) $unit->id)>
													{{ $unit->name }}
													@if ($unit->abbreviation)
														({{ $unit->abbreviation }})
													@endif
												</option>
											@endforeach
										</select>
									</div>

									<div class="col-lg-3 col-md-6 mb-3">
										<label class="form-label">Conversion Factor *</label>

										<input type="number" name="units[{{ $index }}][conversion_factor]"
											class="form-control conversion-factor" value="{{ $row['conversion_factor'] ?? 1 }}" min="0.0001"
											step="0.0001" required>

										<small class="text-muted conversion-help">
											1 selling unit = base units
										</small>
									</div>

									<div class="col-lg-3 col-md-6 mb-3">
										<label class="form-label">Selling Price (৳) *</label>

										<input type="number" name="units[{{ $index }}][price]" class="form-control selling-price"
											value="{{ $row['price'] ?? '' }}" min="0" step="0.01" required>
									</div>

									<div class="col-lg-2 col-md-4 mb-3">
										<div class="form-check mb-2">
											<input type="checkbox" name="units[{{ $index }}][is_default]" value="1"
												class="form-check-input default-unit" @checked(filter_var($row['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN))>

											<label class="form-check-label">
												Default
											</label>
										</div>
									</div>

									<div class="col-lg-1 col-md-2 mb-3">
										<button type="button" class="btn btn-outline-danger remove-unit" title="Remove unit">
											<i class="bi bi-trash"></i>
										</button>
									</div>
								</div>
							</div>
						@endforeach
					</div>

					<div class="alert alert-info">
						<strong>Example:</strong>
						Base unit = kg. If one sack contains 25 kg, set the sack conversion factor to
						<strong>25</strong>. The sack price can be different from the kg price.
					</div>

					@if ($editing)
						<div class="alert alert-secondary mb-4">
							Current stock:
							<strong>
								{{ $product->stock_quantity }}
								{{ $product->unit?->abbreviation ?? $product->unit?->name }}
							</strong>.

							Use Stock Adjustments to change inventory quantity.
						</div>
					@else
						<div class="alert alert-secondary mb-4">
							New products start with zero stock. Use Stock Adjustments to add stock.
						</div>
					@endif

					<button class="btn btn-primary" type="submit">
						<i class="fas fa-save me-1"></i>
						{{ $editing ? 'Update Product' : 'Save Product' }}
					</button>

					<a href="{{ route('backend.products.index') }}" class="btn btn-light border">
						Cancel
					</a>
				</form>
			</div>
		</div>
	</div>

	<template id="unit-row-template">
		<div class="unit-row border rounded p-3 mb-3">
			<div class="row align-items-end">
				<div class="col-lg-3 col-md-6 mb-3">
					<label class="form-label">Selling Unit *</label>

					<select class="form-select selling-unit" data-field="unit_id" required>
						<option value="">Select unit</option>

						@foreach ($units as $unit)
							<option value="{{ $unit->id }}">
								{{ $unit->name }}
								@if ($unit->abbreviation)
									({{ $unit->abbreviation }})
								@endif
							</option>
						@endforeach
					</select>
				</div>

				<div class="col-lg-3 col-md-6 mb-3">
					<label class="form-label">Conversion Factor *</label>

					<input type="number" class="form-control conversion-factor" data-field="conversion_factor" value="1"
						min="0.0001" step="0.0001" required>

					<small class="text-muted conversion-help">
						1 selling unit = base units
					</small>
				</div>

				<div class="col-lg-3 col-md-6 mb-3">
					<label class="form-label">Selling Price (৳) *</label>

					<input type="number" class="form-control selling-price" data-field="price" min="0" step="0.01"
						required>
				</div>

				<div class="col-lg-2 col-md-4 mb-3">
					<div class="form-check mb-2">
						<input type="checkbox" class="form-check-input default-unit" data-field="is_default" value="1">

						<label class="form-check-label">Default</label>
					</div>
				</div>

				<div class="col-lg-1 col-md-2 mb-3">
					<button type="button" class="btn btn-outline-danger remove-unit" title="Remove unit">
						<i class="bi bi-trash"></i>
					</button>
				</div>
			</div>
		</div>
	</template>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const container = document.getElementById('product-units');
			const template = document.getElementById('unit-row-template');
			const addButton = document.getElementById('add-unit');
			const baseUnitSelect = document.getElementById('unit_id');

			let nextIndex = {{ $unitRows->count() }};

			function updateRow(row) {
				const selectedUnit = row.querySelector('.selling-unit');
				const factorInput = row.querySelector('.conversion-factor');
				const help = row.querySelector('.conversion-help');
				const isBaseUnit = selectedUnit.value === baseUnitSelect.value;

				if (isBaseUnit) {
					factorInput.value = '1';
					factorInput.readOnly = true;
					help.textContent = 'Base unit conversion factor is always 1.';
				} else {
					factorInput.readOnly = false;
					help.textContent = 'How many base units are in one selling unit.';
				}
			}

			function updateDefaultCheckboxes(changedCheckbox = null) {
				const checkboxes = container.querySelectorAll('.default-unit');

				if (changedCheckbox && changedCheckbox.checked) {
					checkboxes.forEach(function(checkbox) {
						if (checkbox !== changedCheckbox) {
							checkbox.checked = false;
						}
					});
				}

				if (![...checkboxes].some(checkbox => checkbox.checked)) {
					const first = checkboxes[0];

					if (first) {
						first.checked = true;
					}
				}
			}

			function addRow() {
				const fragment = template.content.cloneNode(true);
				const row = fragment.querySelector('.unit-row');

				row.querySelectorAll('[data-field]').forEach(function(field) {
					field.name = `units[${nextIndex}][${field.dataset.field}]`;
				});

				container.appendChild(fragment);

				const addedRow = container.lastElementChild;
				nextIndex++;

				const firstCheckbox = addedRow.querySelector('.default-unit');

				if (container.querySelectorAll('.unit-row').length === 1) {
					firstCheckbox.checked = true;
				}

				updateRow(addedRow);
				updateDefaultCheckboxes();
			}

			addButton.addEventListener('click', addRow);

			baseUnitSelect.addEventListener('change', function() {
				container.querySelectorAll('.unit-row').forEach(updateRow);
			});

			container.addEventListener('change', function(event) {
				const row = event.target.closest('.unit-row');

				if (!row) {
					return;
				}

				if (event.target.classList.contains('selling-unit')) {
					updateRow(row);
				}

				if (event.target.classList.contains('default-unit')) {
					updateDefaultCheckboxes(event.target);
				}
			});

			container.addEventListener('click', function(event) {
				const removeButton = event.target.closest('.remove-unit');

				if (!removeButton) {
					return;
				}

				const rows = container.querySelectorAll('.unit-row');

				if (rows.length === 1) {
					alert('At least one selling unit is required.');
					return;
				}

				const row = removeButton.closest('.unit-row');
				const wasDefault = row.querySelector('.default-unit').checked;

				row.remove();

				if (wasDefault) {
					const firstCheckbox = container.querySelector('.default-unit');

					if (firstCheckbox) {
						firstCheckbox.checked = true;
					}
				}

				updateDefaultCheckboxes();
			});

			container.querySelectorAll('.unit-row').forEach(updateRow);
			updateDefaultCheckboxes();
		});
	</script>
@endsection
