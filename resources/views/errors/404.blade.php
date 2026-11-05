@extends('layouts.app')

@section('content')
	<main class="container d-flex align-items-center justify-content-center py-5" style="min-height: 70vh;">
		<div class="text-center">
			<div class="mb-4">
				<span class="display-1 fw-bold text-primary">404</span>
			</div>

			<h1 class="h2 fw-bold mb-3">Página no encontrada</h1>
			<p class="text-muted mb-4">
				Lo sentimos, no pudimos encontrar la página que estás buscando.
			</p>

			<a href="{{ route('home') }}" class="btn btn-primary px-4">
				Volver al inicio
			</a>
		</div>
	</main>
@endsection
