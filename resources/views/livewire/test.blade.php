<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-semibold text-gray-900">Prueba de ResourceVerifier</h1>

    <!-- Incluir el verificador de recursos con el contexto de evaluaciones -->
    @livewire('components.resource-verifier', ['context' => $resourceContext])

    <div class="mt-4">
        <p>Esta es una página de prueba para verificar que el ResourceVerifier funciona correctamente.</p>
        <p>El contexto actual es: <strong>{{ $resourceContext }}</strong></p>
    </div>
</div>
