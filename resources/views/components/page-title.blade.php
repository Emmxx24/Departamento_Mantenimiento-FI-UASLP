@props(['titulo', 'subtitulo' => ''])

<div class="mb-4 mt-2">
    <h2 class="fw-bold" style="color: #004A98;">{{ $titulo }}</h2>
    @if($subtitulo)
        <p class="fw-bold fs-5 text-secondary">{{ $subtitulo }}</p>
    @endif
</div>
