<!DOCTYPE html>
<html lang="en">

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<title>Welcome | Laravel</title>

		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	</head>

	<body class="bg-light">

		<div class="container min-vh-100 d-flex justify-content-center align-items-center">
			<div class="card shadow border-0 rounded-4 p-5 text-center" style="max-width: 550px; width: 100%;">

				<h1 class="fw-bold mb-3">Welcome to Assistant</h1>

				<p class="text-secondary mb-4">
					Get started by logging in or creating a new customer account.
				</p>

				<div class="d-flex justify-content-center gap-3">
					<a href="{{ route('login') }}" class="btn btn-primary px-4">
						Login
					</a>

					<a href="{{ route('register') }}" class="btn btn-outline-primary px-4">
						Register
					</a>
				</div>

			</div>
		</div>

	</body>

</html>
