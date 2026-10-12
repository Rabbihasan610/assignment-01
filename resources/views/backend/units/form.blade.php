```blade
@extends('backend.layouts.app')

@section('content')
	<div class="container-fluid">
		@php($editing = $unit->exists)

		<div class="d-flex justify-content-between align-items-center mb-4">
			<div>
				<h4>{{ $editing ? 'Edit Unit' : 'Create Unit' }}</h4>
				<p class="text-muted mb-0">
					{{ $editing ? 'Update unit information below.' : 'Enter unit information below.' }}
				</p>
			</div>

			<a href="{{ route('backend.units.index') }}" class="btn btn-light border">
				<i class="fas fa-arrow-left me-1"></i> Back
			</a>
		</div>

		@include('backend.partials.alerts')

		<div class="card border-0 shadow-sm">
			<div class="card-body p-4">
				<form method="POST" action="{{ $editing ? route('backend.units.update', $unit) : route('backend.units.store') }}">
					@csrf

					@if ($editing)
						@method('PUT')
					@endif

					<div class="mb-3">
						<label for="name" class="form-label">
							Unit Name <span class="text-danger">*</span>
						</label>

						<input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
							value="{{ old('name', $unit->name) }}" placeholder="e.g. Kilogram" maxlength="255" required>

						@error('name')
							<div class="invalid-feedback">{{ $message }}</div>
						@enderror
					</div>

					<div class="mb-3">
						<label for="abbreviation" class="form-label">
							Abbreviation
						</label>

						<input type="text" name="abbreviation" id="abbreviation"
							class="form-control @error('abbreviation') is-invalid @enderror"
							value="{{ old('abbreviation', $unit->abbreviation) }}" placeholder="e.g. kg" maxlength="50">

						@error('abbreviation')
							<div class="invalid-feedback">{{ $message }}</div>
						@enderror

						<small class="text-muted">
							Short form of the unit, such as kg, pcs, g or L.
						</small>
					</div>

					<div class="mb-4">
						<label for="description" class="form-label">
							Description
						</label>

						<textarea name="description" id="description" rows="3" maxlength="255"
						 class="form-control @error('description') is-invalid @enderror" placeholder="Optional description">{{ old('description', $unit->description) }}</textarea>

						@error('description')
							<div class="invalid-feedback">{{ $message }}</div>
						@enderror
					</div>

					<div class="d-flex gap-2">
						<button type="submit" class="btn btn-primary">
							<i class="fas fa-save me-1"></i>
							{{ $editing ? 'Update Unit' : 'Save Unit' }}
						</button>

						<a href="{{ route('backend.units.index') }}" class="btn btn-light border">
							Cancel
						</a>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
```
