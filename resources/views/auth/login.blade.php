<!DOCTYPE html>
<html lang="en">

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Login - Assignment</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
	</head>

	<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">

		<div class="card shadow-sm border-0 p-4" style="max-width: 420px; width: 100%;">
			<div class="text-center mb-4">
				<i class="bi bi-box-seam text-primary fs-1"></i>
				<h4 class="fw-bold mt-2">Sign In</h4>
				<p class="text-muted small">Select your role credentials to continue</p>
			</div>

			@if ($errors->any())
				<div class="alert alert-danger py-2 small">
					{{ $errors->first() }}
				</div>
			@endif

			<form method="POST" action="{{ route('login.post') }}">
				@csrf
				<div class="mb-3">
					<label class="form-label small fw-semibold">Email Address</label>
					<input type="email" name="email" class="form-control" placeholder="admin@supply.com" required autofocus>
				</div>

				<div class="mb-4">
					<label class="form-label small fw-semibold">Password</label>
					<input type="password" name="password" class="form-control" placeholder="••••••••" required>
				</div>

				<x-button variant="primary" type="submit" class="w-100">
					Submit <i class="bi bi-box-arrow-in-right ms-1"></i>
				</x-button>
			</form>

			<div class="border-top mt-4 pt-3 text-center">
				<small class="text-muted">Demo Credentials:</small>
				<div class="d-flex justify-content-between small text-secondary mt-1">
					<span class="text-start me-2 text-truncate">
						admin: admin@test.com </br>
						password: password
					</span>
					<span class="text-start me-2 text-truncate">
						driver: deliveryman@test.com </br>
						password: password
					</span>
					<span class="text-start me-2 text-truncate">
						user: customer1@test.com </br>
						password: password
					</span>
				</div>
			</div>
		</div>
	</body>

</html>
