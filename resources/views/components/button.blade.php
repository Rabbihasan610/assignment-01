@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
])

@php
	$variants = [
	    'primary' => 'btn btn-primary text-white shadow-sm',
	    'secondary' => 'btn btn-secondary',
	    'success' => 'btn btn-success text-white shadow-sm',
	    'danger' => 'btn btn-danger text-white',
	    'outline' => 'btn btn-outline-secondary',
	];

	$sizes = [
	    'sm' => 'btn-sm',
	    'md' => '',
	    'lg' => 'btn-lg',
	];

	$classes = ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? '');

@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
	{{ $slot }}
</button>
