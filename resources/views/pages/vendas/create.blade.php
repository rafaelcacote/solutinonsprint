@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Nova Venda" icon="ecommerce" />

    @if (session('success'))
        <x-ui.success-notification title="Success Notification" message="{{ session('success') }}" />
    @endif

    @if (session('error'))
        <x-ui.alert variant="error" title="Atenção" message="{{ session('error') }}" />
    @endif

    <div class="space-y-6">
        @include('pages.vendas._form', [
            'formAction' => route('vendas.store'),
            'formMethod' => 'POST',
            'clienteInicial' => $clienteInicial,
            'catalog' => $catalog,
            'categorias' => $categorias,
            'formasPagamento' => $formasPagamento,
            'orcamentos' => $orcamentos,
            'defaultLines' => $defaultLines,
            'defaultRecebimentos' => $defaultRecebimentos,
            'venda' => null,
        ])
    </div>
@endsection
