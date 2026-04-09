@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Novo Orçamento" icon="task" />

    @if (session('success'))
        <x-ui.success-notification title="Success Notification" message="{{ session('success') }}" />
    @endif

    @if (session('error'))
        <x-ui.alert variant="error" title="Atenção" message="{{ session('error') }}" />
    @endif

    <div class="space-y-6">
        @include('pages.orcamentos._form', [
            'formAction' => route('orcamentos.store'),
            'formMethod' => 'POST',
            'clienteInicial' => $clienteInicial,
            'catalog' => $catalog,
            'defaultLines' => $defaultLines,
            'orcamento' => null,
        ])
    </div>
@endsection
