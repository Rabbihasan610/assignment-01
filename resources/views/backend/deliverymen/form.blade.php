@extends('backend.layouts.app')

@section('title', isset($deliveryman) ? 'Edit Deliveryman' : 'Create Deliveryman')

@section('content')
	<div class="container-fluid py-4">

		<div class="d-flex justify-content-between align-items-center mb-4">
			<div>
				<h4 class="mb-1">
					{{ isset($deliveryman) ? 'Edit Deliveryman' : 'Create Deliveryman' }}
				</h4>
				<p class="text-muted mb-0">
					{{ isset($deliveryman) ? 'Update deliveryman information.' : 'Create a new deliveryman account.' }}
				</p>
			</div>

			<a href="{{ route('backend.deliverymen.index') }}" class="btn btn-outline-secondary">
				<i class="fas fa-arrow-left me-1"></i> Back to List
			</a>
		</div>

		@include('backend.partials.alerts')

		<div class="card shadow-sm">
			<div class="card-header bg-white">
				<h5 class="mb-0">Deliveryman Information</h5>
			</div>

			<div class="card-body">
				<form
					action="{{ isset($deliveryman) ? route('backend.deliverymen.update', $deliveryman) : route('backend.deliverymen.store') }}"
					method="POST">

					@csrf

					@if (isset($deliveryman))
						@method('PUT')
					@endif

					<div class="table-responsive">
						<table class="table table-bordered align-middle mb-0">
							<tbody>
								<tr>
									<th style="width: 25%;">
										<label for="name" class="mb-0">
											Full Name <span class="text-danger">*</span>
										</label>
									</th>
									<td>
										<input type="text" name="name" id="name" value="{{ old('name', $deliveryman->name ?? '') }}"
											class="form-control @error('name') is-invalid @enderror" placeholder="Enter full name" required>

										@error('name')
											<div class="invalid-feedback">{{ $message }}</div>
										@enderror
									</td>
								</tr>

								<tr>
									<th>
										<label for="email" class="mb-0">
											Email Address <span class="text-danger">*</span>
										</label>
									</th>
									<td>
										<input type="email" name="email" id="email" value="{{ old('email', $deliveryman->email ?? '') }}"
											class="form-control @error('email') is-invalid @enderror" placeholder="deliveryman@example.com" required>

										@error('email')
											<div class="invalid-feedback">{{ $message }}</div>
										@enderror
									</td>
								</tr>

								<tr>
									<th>
										<label for="password" class="mb-0">
											Password
											@unless (isset($deliveryman))
												<span class="text-danger">*</span>
											@else
												<span class="text-muted fw-normal">(Optional)</span>
											@endunless
										</label>
									</th>
									<td>
										<input type="password" name="password" id="password"
											class="form-control @error('password') is-invalid @enderror" minlength="8" autocomplete="new-password"
											{{ isset($deliveryman) ? '' : 'required' }}>

										@if (isset($deliveryman))
											<small class="text-muted">
												Leave blank to keep the current password.
											</small>
										@endif

										@error('password')
											<div class="invalid-feedback">{{ $message }}</div>
										@enderror
									</td>
								</tr>

								<tr>
									<th>
										<label for="password_confirmation" class="mb-0">
											Confirm Password
											@unless (isset($deliveryman))
												<span class="text-danger">*</span>
											@endunless
										</label>
									</th>
									<td>
										<input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
											minlength="8" autocomplete="new-password" {{ isset($deliveryman) ? '' : 'required' }}>
									</td>
								</tr>

								<tr>
									<td colspan="2">
										<button type="submit" class="btn btn-primary">
											<i class="fas fa-save me-1"></i>
											{{ isset($deliveryman) ? 'Update Deliveryman' : 'Create Deliveryman' }}
										</button>

										<a href="{{ route('backend.deliverymen.index') }}" class="btn btn-outline-secondary ms-1">
											Cancel
										</a>
									</td>
								</tr>
							</tbody>
						</table>
					</div>

				</form>
			</div>
		</div>

	</div>
@endsection
