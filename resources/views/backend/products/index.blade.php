@extends('backend.layouts.app')

@section('content')
	<div class="container-fluid">
		<div class="d-flex justify-content-between align-items-center mb-4">
			<div>
				<h4 class="mb-1">Products</h4>
				<p class="text-muted mb-0">Manage products, prices and current stock.</p>
			</div>
			<a href="{{ route('backend.products.create') }}" class="btn btn-primary">
				<i class="bi bi-plus-lg me-1"></i> Add Product
			</a>
		</div>

		@include('backend.partials.alerts')

		<div class="card border-0 shadow-sm">
			<div class="table-responsive">
				<table class="table table-hover align-middle mb-0">
					<thead class="table-light">
						<tr>
							<th>#</th>
							<th>Product</th>
							<th>Unit</th>
							<th>Price</th>
							<th>Current Stock</th>
							<th class="text-end">Actions</th>
						</tr>
					</thead>
					<tbody>
						@forelse ($products as $product)
							<tr>
								<td>{{ $products->firstItem() + $loop->index }}</td>
								<td>
									<div class="fw-semibold">{{ $product->name }}</div>
									<small class="text-muted">
										{{ \Illuminate\Support\Str::limit($product->description, 50) }}
									</small>
								</td>
								<td>
									{{ $product->unit?->name ?? '—' }}
									@if ($product->unit?->abbreviation)
										({{ $product->unit->abbreviation }})
									@endif
								</td>
								<td>{{ number_format((float) $product->price, 2) }}</td>
								<td>
									<span class="badge {{ (float) $product->stock_quantity > 0 ? 'text-bg-success' : 'text-bg-danger' }}">
										{{ $product->stock_quantity }}
										{{ $product->unit?->abbreviation }}
									</span>
								</td>
								<td class="text-end">
									<a href="{{ route('backend.products.edit', $product) }}" class="btn btn-sm btn-outline-primary">
										<i class="bi bi-pencil"></i>
									</a>
									<form action="{{ route('backend.products.destroy', $product) }}" method="POST" class="d-inline"
										onsubmit="return confirm('Delete this product?')">
										@csrf
										@method('DELETE')
										<button type="submit" class="btn btn-sm btn-outline-danger">
											<i class="bi bi-trash"></i>
										</button>
									</form>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="6" class="text-center py-4 text-muted">
									No products found.
								</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>

			<div class="card-body">
				{{ $products->links() }}
			</div>
		</div>
	</div>
@endsection
