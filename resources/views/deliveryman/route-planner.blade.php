@extends('backend.layouts.app')

@section('title', 'Delivery Route Planner')

@section('content')
	<div class="container-fluid py-3">
		<div class="d-flex justify-content-between align-items-center mb-3">
			<div>
				<h4 class="mb-1">Delivery Route Planner</h4>
				<p class="text-muted mb-0">
					Optimize your assigned deliveries and track your route.
				</p>
			</div>
			<span class="badge bg-primary">
				{{ $orders->count() }} Active Deliveries
			</span>
		</div>

		@if ($orders->isEmpty())
			<div class="alert alert-info">
				<i class="bi bi-info-circle me-2"></i>
				No active deliveries assigned to you at the moment.
			</div>
		@else
			<div class="card shadow-sm mb-3">
				<div class="card-body">
					<form id="routeForm">
						@csrf

						<div class="row g-3 align-items-end">
							<div class="col-md-4">
								<label for="latitude" class="form-label">
									Your Current Latitude
								</label>
								<input type="number" step="any" id="latitude" name="latitude" class="form-control"
									placeholder="e.g. 23.8103" required>
							</div>

							<div class="col-md-4">
								<label for="longitude" class="form-label">
									Your Current Longitude
								</label>
								<input type="number" step="any" id="longitude" name="longitude" class="form-control"
									placeholder="e.g. 90.4125" required>
							</div>

							<div class="col-md-4 d-flex gap-2">
								<button type="button" id="locationBtn" class="btn btn-outline-primary">
									<i class="bi bi-geo-alt"></i>
									Get My Location
								</button>

								<button type="submit" id="calculateBtn" class="btn btn-primary">
									Calculate Route
								</button>
							</div>
						</div>
					</form>

					<div class="small text-muted mt-2">
						Note: The optimized route is calculated based on your current location and the assigned deliveries. Ensure your
						device has location access enabled.
					</div>

				</div>
			</div>

			<div id="fallbackNotice" class="alert alert-warning d-none">
				Google Routes API unavailable.
				Route order is estimated using straight-line distance.
				Actual road distance and traffic-based ETA may differ.
			</div>

			<div id="errorMessage" class="alert alert-danger d-none"></div>

			<div id="summarySection" class="row g-3 mb-3 d-none">
				<div class="col-md-4">
					<div class="card shadow-sm h-100">
						<div class="card-body">
							<div class="text-muted small">Total Road Distance</div>
							<h4 id="totalDistance" class="mb-0">—</h4>

						</div>
					</div>
				</div>

				<div class="col-md-4">
					<div class="card shadow-sm h-100">
						<div class="card-body">
							<div class="text-muted small">Estimated Driving Time</div>
							<h4 id="totalDuration" class="mb-0">—</h4>
							<small class="text-muted">
								Note: This is an estimated driving time and may vary based on traffic conditions.
							</small>
						</div>
					</div>
				</div>

				<div class="col-md-4">
					<div class="card shadow-sm h-100">
						<div class="card-body">
							<div class="text-muted small">Delivery Stops</div>
							<h4 id="totalStops" class="mb-0">—</h4>
						</div>
					</div>
				</div>
			</div>

			<div class="row g-3">
				<div class="col-lg-8">
					<div class="card shadow-sm">
						<div class="card-header bg-white">
							<strong>Optimized Route Map</strong>
						</div>
						<div class="card-body p-0">
							<div id="map" style="height: 520px; width: 100%;"></div>
						</div>
					</div>
				</div>

				<div class="col-lg-4">
					<div class="card shadow-sm mb-3">
						<div class="card-header bg-white">
							<strong>Optimized Delivery Sequence</strong>
						</div>
						<div class="card-body">
							<ol id="deliveryList" class="list-group list-group-numbered">
								<li class="list-group-item text-muted">
									Calculate route to see delivery sequence.
								</li>
							</ol>

							<a href="#" id="googleMapsLink" target="_blank" rel="noopener noreferrer"
								class="btn btn-success w-100 mt-3 d-none">
								Open in Google Maps
							</a>
						</div>
					</div>

					<div class="card shadow-sm">
						<div class="card-header bg-white">
							<strong>Alternative Routes</strong>
						</div>
						<div class="card-body">
							<div id="alternativeRoutes">
								<p class="text-muted mb-0">
									Calculate the route to check alternatives.
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		@endif
	</div>
@endsection

@push('scripts')
	@if ($orders->isNotEmpty())
		<script>
			let map;
			let directionsService;
			let directionsRenderer;
			let currentRouteData = null;
			let currentOrigin = null;
			let mapMarkers = [];

			function initMap() {
				map = new google.maps.Map(document.getElementById('map'), {
					center: {
						lat: 23.8103,
						lng: 90.4125
					},
					zoom: 12,
					mapTypeControl: true,
					streetViewControl: false,
				});

				directionsService = new google.maps.DirectionsService();

				directionsRenderer = new google.maps.DirectionsRenderer({
					map: map,
					suppressMarkers: false,
					preserveViewport: false,
				});
			}

			document.addEventListener('DOMContentLoaded', function() {
				const form = document.getElementById('routeForm');
				const locationBtn = document.getElementById('locationBtn');
				const calculateBtn = document.getElementById('calculateBtn');

				locationBtn.addEventListener('click', function() {
					const errorBox = document.getElementById('errorMessage');
					errorBox.classList.add('d-none');

					if (!navigator.geolocation) {
						showError(
							'Your browser does not support geolocation. Please enter your coordinates manually.'
						);
						return;
					}

					locationBtn.disabled = true;
					locationBtn.textContent = 'Getting location...';

					navigator.geolocation.getCurrentPosition(
						function(position) {
							document.getElementById('latitude').value =
								position.coords.latitude;

							document.getElementById('longitude').value =
								position.coords.longitude;

							locationBtn.disabled = false;
							locationBtn.textContent = 'Get My Location';
						},
						function(error) {
							locationBtn.disabled = false;
							locationBtn.textContent = 'Get My Location';

							showError(
								'Unable to retrieve your location. Please ensure location access is enabled and try again.'
							);
						}, {
							enableHighAccuracy: true,
							timeout: 15000,
							maximumAge: 10000
						}
					);
				});

				form.addEventListener('submit', async function(event) {
					event.preventDefault();

					hideError();
					calculateBtn.disabled = true;
					calculateBtn.textContent = 'Calculating...';

					try {
						const formData = new FormData(form);

						const response = await fetch(
							@json(route('deliveryman.route-planner.calculate')), {
								method: 'POST',
								headers: {
									'Accept': 'application/json',
									'X-Requested-With': 'XMLHttpRequest',
								},
								body: formData,
							}
						);

						const result = await response.json();

						if (!response.ok || !result.success) {
							throw new Error(
								result.message || 'Failed to calculate route.'
							);
						}

						currentRouteData = result.data;

						currentOrigin = {
							lat: Number(formData.get('latitude')),
							lng: Number(formData.get('longitude')),
						};

						renderOptimizedRoute(currentRouteData);
					} catch (error) {
						showError(error.message || 'Unexpected error occurred.');
					} finally {
						calculateBtn.disabled = false;
						calculateBtn.textContent = 'Calculate Route';
					}
				});
			});

			function renderOptimizedRoute(route) {
				const fallbackNotice = document.getElementById('fallbackNotice');

				if (fallbackNotice) {
					fallbackNotice.classList.toggle(
						'd-none',
						route.route_source !== 'local_fallback'
					);
				}

				document.getElementById('summarySection').classList.remove('d-none');

				document.getElementById('totalDistance').textContent =
					(Number(route.distance_meters || 0) / 1000).toFixed(2) + ' km';

				document.getElementById('totalDuration').textContent =
					formatDuration(Number(route.duration_seconds || 0));

				document.getElementById('totalStops').textContent =
					(route.stops || []).length;

				renderDeliveryList(route.stops || []);
				renderGoogleMapsLink(route.stops || []);
				renderMap(route);
				renderAlternatives(route.alternatives || []);
			}

			function renderDeliveryList(stops) {
				const list = document.getElementById('deliveryList');
				list.replaceChildren();

				if (!stops.length) {
					list.innerHTML = '<li class="list-group-item">No stops found.</li>';
					return;
				}

				stops.forEach(function(stop, index) {
					const item = document.createElement('li');
					item.className = 'list-group-item';

					const name = document.createElement('strong');
					name.textContent = stop.name || 'Customer';

					const order = document.createElement('div');
					order.className = 'small text-muted';
					order.textContent = 'Order #' + stop.order_id;

					const phone = document.createElement('div');
					phone.className = 'small';
					phone.textContent = stop.phone || 'No phone number';

					const distance = document.createElement('div');
					distance.className = 'small text-muted';

					const leg = (currentRouteData.legs || [])[index];

					if (leg) {
						distance.textContent =
							'Drive: ' + formatDuration(Number(leg.duration_seconds || 0)) +
							' · ' +
							(Number(leg.distance_meters || 0) / 1000).toFixed(2) + ' km';
					}

					item.append(name, order, phone, distance);
					list.appendChild(item);
				});
			}


			function renderMap(route) {
				clearMapMarkers();

				directionsRenderer.setDirections({
					routes: []
				});

				const stops = route.stops || [];

				if (!stops.length || !currentOrigin) {
					return;
				}

				// Build ordered points: starting location + delivery stops.
				const points = [
					currentOrigin,
					...stops.map(function(stop) {
						return {
							lat: Number(stop.latitude),
							lng: Number(stop.longitude),
						};
					}),
				];

				// Add starting location marker.
				const originMarker = new google.maps.Marker({
					position: currentOrigin,
					map: map,
					title: 'Your Starting Location',
					label: 'S',
				});

				mapMarkers.push(originMarker);

				// Add numbered delivery markers.
				stops.forEach(function(stop, index) {
					const marker = new google.maps.Marker({
						position: {
							lat: Number(stop.latitude),
							lng: Number(stop.longitude),
						},
						map: map,
						title: 'Order #' + stop.order_id + ' - ' +
							(stop.name || 'Customer'),
						label: String(index + 1),
					});

					mapMarkers.push(marker);
				});

				// Fit map to all route points.
				const bounds = new google.maps.LatLngBounds();

				points.forEach(function(point) {
					bounds.extend(point);
				});

				map.fitBounds(bounds);

				// Fallback mode: show estimated straight-line connections.
				if (route.route_source === 'local_fallback') {
					const fallbackLine = new google.maps.Polyline({
						path: points,
						geodesic: true,
						strokeColor: '#f59e0b',
						strokeOpacity: 0.9,
						strokeWeight: 4,
						map: map,
					});

					mapMarkers.push(fallbackLine);

					showError(
						'Estimated route shown. Straight lines do not represent actual roads or traffic.'
					);

					return;
				}

				// Google Directions route for non-fallback responses.
				const waypoints = stops.slice(0, -1).map(function(stop) {
					return {
						location: {
							lat: Number(stop.latitude),
							lng: Number(stop.longitude),
						},
						stopover: true,
					};
				});

				const destinationStop = stops[stops.length - 1];

				directionsService.route({
					origin: currentOrigin,
					destination: {
						lat: Number(destinationStop.latitude),
						lng: Number(destinationStop.longitude),
					},
					waypoints: waypoints,
					optimizeWaypoints: false,
					travelMode: google.maps.TravelMode.DRIVING,
				}, function(result, status) {
					if (status === 'OK') {
						directionsRenderer.setDirections(result);
					} else {
						// Keep markers visible and draw a fallback line.
						const fallbackLine = new google.maps.Polyline({
							path: points,
							geodesic: true,
							strokeColor: '#f59e0b',
							strokeOpacity: 0.9,
							strokeWeight: 4,
							map: map,
						});

						mapMarkers.push(fallbackLine);

						showError(
							'Road route unavailable (' + status +
							'). Showing an estimated straight-line route instead.'
						);
					}
				});
			}


			function renderGoogleMapsLink(stops) {
				const link = document.getElementById('googleMapsLink');

				if (!stops.length || !currentOrigin) {
					link.classList.add('d-none');
					return;
				}

				const destination = stops[stops.length - 1];

				const params = new URLSearchParams({
					api: '1',
					origin: currentOrigin.lat + ',' + currentOrigin.lng,
					destination: destination.latitude + ',' + destination.longitude,
					travelmode: 'driving',
				});

				if (stops.length > 1) {
					params.set(
						'waypoints',
						stops.slice(0, -1).map(function(stop) {
							return stop.latitude + ',' + stop.longitude;
						}).join('|')
					);
				}

				link.href = 'https://www.google.com/maps/dir/?' + params.toString();
				link.classList.remove('d-none');
			}

			function renderAlternatives(alternatives) {
				const container = document.getElementById('alternativeRoutes');
				container.replaceChildren();

				if (!alternatives.length) {
					const message = document.createElement('p');
					message.className = 'text-muted mb-0';
					message.textContent =
						'No alternative routes available.';
					container.appendChild(message);
					return;
				}

				alternatives.forEach(function(alternative, index) {
					const wrapper = document.createElement('div');
					wrapper.className = 'border rounded p-2 mb-2';

					const heading = document.createElement('strong');
					heading.textContent = 'Alternative ' + (index + 1);

					const details = document.createElement('div');
					details.className = 'small text-muted mt-1';
					details.textContent =
						formatDuration(Number(alternative.duration_seconds || 0)) +
						' · ' +
						(Number(alternative.distance_meters || 0) / 1000).toFixed(2) +
						' km';

					const button = document.createElement('button');
					button.type = 'button';
					button.className = 'btn btn-sm btn-outline-primary mt-2';
					button.textContent = 'Show on Map';

					button.addEventListener('click', function() {
						showAlternative(index);
					});

					wrapper.append(heading, details, button);
					container.appendChild(wrapper);
				});
			}

			function showAlternative(index) {
				const alternative = (currentRouteData?.alternatives || [])[index];

				if (!alternative || !alternative.polyline) {
					showError('No polyline data available for this alternative route.');
					return;
				}

				const path = google.maps.geometry.encoding.decodePath(
					alternative.polyline
				);

				clearMapMarkers();
				directionsRenderer.setDirections({
					routes: []
				});

				const alternativeLine = new google.maps.Polyline({
					path: path,
					geodesic: true,
					strokeOpacity: 0.85,
					strokeWeight: 5,
					map: map,
				});

				mapMarkers.push(alternativeLine);

				const bounds = new google.maps.LatLngBounds();

				path.forEach(function(point) {
					bounds.extend(point);
				});

				map.fitBounds(bounds);
			}

			function clearMapMarkers() {
				mapMarkers.forEach(function(marker) {
					marker.setMap(null);
				});

				mapMarkers = [];
			}

			function formatDuration(seconds) {
				const minutes = Math.round(seconds / 60);
				const hours = Math.floor(minutes / 60);
				const remainingMinutes = minutes % 60;

				if (hours > 0) {
					return hours + ' hr ' + remainingMinutes + ' min';
				}

				return minutes + ' min';
			}

			function showError(message) {
				const box = document.getElementById('errorMessage');
				box.textContent = message;
				box.classList.remove('d-none');
			}

			function hideError() {
				document.getElementById('errorMessage').classList.add('d-none');
			}
		</script>

		<script async defer
			src="https://maps.googleapis.com/maps/api/js?key={{ urlencode($mapsKey) }}&libraries=geometry&callback=initMap">
		</script>
	@endif
@endpush
