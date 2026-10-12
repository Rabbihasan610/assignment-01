<!DOCTYPE html>
<html lang="en">

	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>{{ $title ?? 'Panel' }}</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
		<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />

		<link href="{{ asset('assets/backend/css/styles.css') }}" rel="stylesheet" />
	</head>

	<body>

		<aside class="layout-sidebar bg-white border-end">
			<x-sidebar />
		</aside>

		<div class="layout-content">
			<x-topbar />

			<main class="p-4 flex-grow-1">
				@yield('content')
			</main>

			<x-footer />
		</div>

		@stack('scripts')

		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	</body>

</html>
