@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Editar Orçamento" icon="task" />

    @if (session('success'))
        <x-ui.success-notification title="Success Notification" message="{{ session('success') }}" />
    @endif

    @if (session('error'))
        <x-ui.alert variant="error" title="Atenção" message="{{ session('error') }}" />
    @endif

    <div class="space-y-6">
        @include('pages.orcamentos._form', [
            'formAction' => route('orcamentos.update', $orcamento),
            'formMethod' => 'PUT',
            'clienteInicial' => $clienteInicial,
            'catalog' => $catalog,
            'defaultLines' => $defaultLines,
            'orcamento' => $orcamento,
        ])
    </div>
@endsection
