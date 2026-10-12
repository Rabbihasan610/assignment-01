@extends('backend.layouts.app')

@section('content')
	<div class="container py-4 py-lg-5">

		<nav aria-label="breadcrumb" class="mb-4">
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="{{ route('customer.products.index') }}">Products</a>
				</li>
				<li class="breadcrumb-item">
					<a href="{{ route('customer.cart.index') }}">Cart</a>
				</li>
				<li class="breadcrumb-item active">Checkout</li>
			</ol>
		</nav>

		@include('backend.partials.alerts')

		<h2 class="fw-bold mb-4">Checkout</h2>

		<form action="{{ route('customer.checkout.store') }}" method="POST" id="checkout-form">
			@csrf

			<div class="row g-4">

				<div class="col-lg-7">
					<div class="card border-0 shadow-sm mb-4">
						<div class="card-body p-4">

							<h5 class="fw-bold mb-3">
								<i class="bi bi-geo-alt me-2"></i>
								Delivery Location
							</h5>

							<p class="text-muted small">
								Use GPS or click on the map to select your delivery location.
								You can drag the marker to adjust it.
							</p>

							<button type="button" id="use-current-location" class="btn btn-outline-primary mb-3">
								<i class="bi bi-crosshair me-2"></i>
								Use My Current Location
							</button>

							<div id="location-status" class="small text-muted mb-3" role="status" aria-live="polite">
								Select your delivery location on the map.
							</div>

							<div id="checkout-map" class="rounded border" style="height:350px;width:100%;background:#f1f3f5;">
							</div>

							<div class="row g-3 mt-2">
								<div class="col-md-6">
									<label for="delivery_latitude" class="form-label fw-semibold">
										Latitude
									</label>

									<input type="text" name="delivery_latitude" id="delivery_latitude"
										class="form-control @error('delivery_latitude') is-invalid @enderror" value="{{ old('delivery_latitude') }}"
										readonly required>

									@error('delivery_latitude')
										<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>

								<div class="col-md-6">
									<label for="delivery_longitude" class="form-label fw-semibold">
										Longitude
									</label>

									<input type="text" name="delivery_longitude" id="delivery_longitude"
										class="form-control @error('delivery_longitude') is-invalid @enderror" value="{{ old('delivery_longitude') }}"
										readonly required>

									@error('delivery_longitude')
										<div class="invalid-feedback">{{ $message }}</div>
									@enderror
								</div>
							</div>

						</div>
					</div>

					{{-- Address --}}
					<div class="card border-0 shadow-sm">
						<div class="card-body p-4">

							<h5 class="fw-bold mb-4">
								<i class="bi bi-house-door me-2"></i>
								Delivery Address
							</h5>

							<div class="mb-3">
								<label for="delivery_address" class="form-label fw-semibold">
									Complete Address
									<span class="text-danger">*</span>
								</label>

								<textarea name="delivery_address" id="delivery_address" rows="4"
								 class="form-control @error('delivery_address') is-invalid @enderror"
								 placeholder="House, road, area, district and nearby landmark" maxlength="5000" required>{{ old('delivery_address') }}</textarea>

								@error('delivery_address')
									<div class="invalid-feedback">{{ $message }}</div>
								@enderror

								<small class="text-muted">
									Check the address after selecting your location.
									You can edit it manually.
								</small>
							</div>

							<div class="mb-3">
								<label for="delivery_instructions" class="form-label fw-semibold">
									Delivery Instructions
								</label>

								<textarea name="delivery_instructions" id="delivery_instructions" rows="3"
								 class="form-control @error('delivery_instructions') is-invalid @enderror" maxlength="5000"
								 placeholder="Optional delivery instructions">{{ old('delivery_instructions') }}</textarea>

								@error('delivery_instructions')
									<div class="invalid-feedback">{{ $message }}</div>
								@enderror
							</div>

						</div>
					</div>
				</div>

				{{-- Order Summary --}}
				<div class="col-lg-5">
					<div class="card border-0 shadow-sm">
						<div class="card-body p-4">

							<h5 class="fw-bold mb-4">Your Order</h5>

							@foreach ($items as $item)
								@php
									$unitName = $item->productUnit?->unit?->abbreviation ?? ($item->productUnit?->unit?->name ?? 'Unit');

									$quantity = (float) $item->quantity;
									$unitPrice = (float) $item->unit_price;
									$lineTotal = $quantity * $unitPrice;
								@endphp

								<div class="d-flex justify-content-between gap-3 mb-3">
									<div>
										<div class="fw-semibold">
											{{ $item->product?->name ?? 'Product' }}
										</div>

										<div class="small text-muted">
											{{ number_format($quantity, 4) }}
											{{ $unitName }}
											× ৳{{ number_format($unitPrice, 2) }}
										</div>
									</div>

									<div class="fw-semibold text-nowrap">
										৳{{ number_format($lineTotal, 2) }}
									</div>
								</div>
							@endforeach

							<hr>

							<div class="d-flex justify-content-between mb-3">
								<span class="text-muted">Subtotal</span>
								<span>৳{{ number_format($subtotal, 2) }}</span>
							</div>

							<div class="d-flex justify-content-between mb-3">
								<span class="text-muted">Delivery Fee</span>
								<span>৳{{ number_format($deliveryFee, 2) }}</span>
							</div>

							<hr>

							<div class="d-flex justify-content-between mb-4">
								<span class="fw-bold">Total</span>
								<span class="fs-4 fw-bold text-primary">
									৳{{ number_format($total, 2) }}
								</span>
							</div>

							<div class="alert alert-info small">
								<i class="bi bi-info-circle me-1"></i>
								Your order will be created with Pending status.
							</div>

							<button type="submit" id="place-order-button" class="btn btn-primary btn-lg w-100">
								<i class="bi bi-check-circle me-2"></i>
								Place Order
							</button>

							<a href="{{ route('customer.cart.index') }}" class="btn btn-outline-secondary w-100 mt-2">
								<i class="bi bi-arrow-left me-1"></i>
								Back to Cart
							</a>

							<p class="small text-muted mt-3 mb-0">
								Stock, prices and GPS coordinates will be validated by the server.
							</p>

						</div>
					</div>
				</div>

			</div>
		</form>
	</div>

	<script>
		let checkoutMap;
		let checkoutMarker;
		let checkoutGeocoder;

		window.initCheckoutMap = function() {
			const latitudeInput = document.getElementById('delivery_latitude');
			const longitudeInput = document.getElementById('delivery_longitude');
			const addressInput = document.getElementById('delivery_address');
			const statusElement = document.getElementById('location-status');

			const defaultPosition = {
				lat: 23.8103,
				lng: 90.4125
			};

			const savedLat = Number(latitudeInput.value);
			const savedLng = Number(longitudeInput.value);

			const hasSavedLocation =
				latitudeInput.value.trim() !== '' &&
				longitudeInput.value.trim() !== '' &&
				Number.isFinite(savedLat) &&
				Number.isFinite(savedLng) &&
				Math.abs(savedLat) <= 90 &&
				Math.abs(savedLng) <= 180;

			const initialPosition = hasSavedLocation ? {
					lat: savedLat,
					lng: savedLng
				} :
				defaultPosition;

			checkoutMap = new google.maps.Map(
				document.getElementById('checkout-map'), {
					center: initialPosition,
					zoom: hasSavedLocation ? 16 : 12,
					mapTypeControl: true,
					streetViewControl: false,
					fullscreenControl: true
				}
			);

			checkoutGeocoder = new google.maps.Geocoder();

			checkoutMarker = new google.maps.Marker({
				position: initialPosition,
				map: checkoutMap,
				draggable: true,
				title: 'Delivery location'
			});

			function setStatus(message, isError = false) {
				statusElement.textContent = message;
				statusElement.classList.toggle('text-danger', isError);
				statusElement.classList.toggle('text-muted', !isError);
			}

			function updateCoordinates(position, reverseGeocode = true) {
				const lat = Number(position.lat);
				const lng = Number(position.lng);

				if (
					!Number.isFinite(lat) ||
					!Number.isFinite(lng) ||
					Math.abs(lat) > 90 ||
					Math.abs(lng) > 180
				) {
					setStatus('Invalid map location. Please select another point.', true);
					return;
				}

				latitudeInput.value = lat.toFixed(7);
				longitudeInput.value = lng.toFixed(7);

				checkoutMarker.setPosition({
					lat,
					lng
				});

				setStatus('Location selected. Please verify the address.');

				if (reverseGeocode) {
					checkoutGeocoder.geocode({
							location: {
								lat,
								lng
							}
						},
						function(results, status) {
							if (status === 'OK' && results && results[0]) {
								if (!addressInput.value.trim()) {
									addressInput.value = results[0].formatted_address;
								}

								setStatus('Location selected. Verify or edit the delivery address.');
							} else {
								setStatus(
									'GPS coordinates saved in the form. Please enter the address manually.'
								);
							}
						}
					);
				}
			}

			checkoutMap.addListener('click', function(event) {
				if (!event.latLng) return;

				updateCoordinates({
					lat: event.latLng.lat(),
					lng: event.latLng.lng()
				});
			});

			checkoutMarker.addListener('dragend', function(event) {
				if (!event.latLng) return;

				updateCoordinates({
					lat: event.latLng.lat(),
					lng: event.latLng.lng()
				});
			});

			document.getElementById('use-current-location')
				.addEventListener('click', function() {
					if (!navigator.geolocation) {
						setStatus('Your browser does not support GPS location.', true);
						return;
					}

					setStatus('Getting your current location...');

					navigator.geolocation.getCurrentPosition(
						function(position) {
							const currentPosition = {
								lat: position.coords.latitude,
								lng: position.coords.longitude
							};

							checkoutMap.setCenter(currentPosition);
							checkoutMap.setZoom(17);

							updateCoordinates(currentPosition);
						},
						function(error) {
							const messages = {
								1: 'Location permission denied. Allow location access in your browser.',
								2: 'Your location is currently unavailable. Try again or select the map manually.',
								3: 'Location request timed out. Please try again.'
							};

							setStatus(
								messages[error.code] ||
								'Could not retrieve your current location.',
								true
							);
						}, {
							enableHighAccuracy: true,
							timeout: 15000,
							maximumAge: 0
						}
					);
				});

			if (hasSavedLocation) {
				setStatus('Previously selected location loaded. Verify it before placing your order.');
			} else {
				setStatus('Click the map or use GPS to select your delivery location.');
			}
		};

		document.addEventListener('DOMContentLoaded', function() {
			const form = document.getElementById('checkout-form');

			form.addEventListener('submit', function(event) {
				const lat = document.getElementById('delivery_latitude').value.trim();
				const lng = document.getElementById('delivery_longitude').value.trim();

				if (lat === '' || lng === '') {
					event.preventDefault();

					document.getElementById('location-status').textContent =
						'Please select your delivery location on the map before placing the order.';

					document.getElementById('location-status').classList.add('text-danger');
					document.getElementById('checkout-map').scrollIntoView({
						behavior: 'smooth',
						block: 'center'
					});
				}
			});
		});
	</script>

	<script async defer
		src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&callback=initCheckoutMap&loading=async&v=weekly">
	</script>
@endsection
