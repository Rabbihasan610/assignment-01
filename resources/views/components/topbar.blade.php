<header class="bg-white border-bottom px-4 py-2 d-flex justify-content-between align-items-center sticky-top">
	<div class="d-flex align-items-center gap-2">
		<span class="text-muted small">Assignment:</span>
		<span class="badge bg-success-subtle text-success">Testing</span>
	</div>

	<div class="d-flex align-items-center gap-3">
		<span class="small text-muted">Signed in as: <strong>{{ auth()?->user()?->email }}</strong></span>
		<span class="badge bg-primary text-uppercase">{{ auth()?->user()?->role?->name }}</span>
	</div>
</header>
