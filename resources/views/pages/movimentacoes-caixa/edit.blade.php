@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Editar movimentação" icon="charts" />

    @if (session('success'))
        <x-ui.success-notification title="Success Notification" message="{{ session('success') }}" />
    @endif

    @if (session('error'))
        <x-ui.alert variant="error" title="Atenção" message="{{ session('error') }}" />
    @endif

    <div class="space-y-6">
        @include('pages.movimentacoes-caixa._form', [
            'formAction' => route('movimentacoes-caixa.update', $movimentacao),
            'formMethod' => 'PUT',
            'movimentacao' => $movimentacao,
            'formasPagamento' => $formasPagamento,
            'vendas' => $vendas,
            'despesas' => $despesas,
        ])
    </div>
@endsection
