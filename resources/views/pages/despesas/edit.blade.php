@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Editar Despesa" icon="tables" />

    @if (session('success'))
        <x-ui.success-notification title="Success Notification" message="{{ session('success') }}" />
    @endif

    @if (session('error'))
        <x-ui.alert variant="error" title="Atenção" message="{{ session('error') }}" />
    @endif

    <div class="space-y-6">
        @include('pages.despesas._form', [
            'formAction' => route('despesas.update', $despesa),
            'formMethod' => 'PUT',
            'despesa' => $despesa,
            'categorias' => $categorias,
            'fornecedores' => $fornecedores,
            'formasPagamento' => $formasPagamento,
        ])
    </div>
@endsection
