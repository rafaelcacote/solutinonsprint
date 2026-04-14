<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Orçamento {{ $orcamento->numero }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #374151;
            margin: 0;
            padding: 24px 28px 32px;
            line-height: 1.45;
        }
        .box {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }
        .box-head {
            width: 100%;
            border-bottom: 1px solid #e5e7eb;
            padding: 14px 22px 14px 20px;
        }
        .box-head-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }
        .box-head-table td {
            vertical-align: middle;
            padding: 0;
        }
        .box-head-logo {
            width: 1%;
            white-space: nowrap;
            text-align: left;
            padding-right: 10px;
        }
        .box-head-title {
            text-align: center;
            padding: 0 8px;
        }
        .box-head-id {
            width: 1%;
            white-space: nowrap;
            text-align: right;
            padding-left: 12px;
            padding-right: 4px;
        }
        .logo-row {
            margin: 0 0 8px 0;
            line-height: 0;
        }
        .box-head .logo-row {
            margin: 0;
            line-height: 1;
        }
        .logo-row img {
            display: block;
            max-width: 720px;
        }
        .box-head .logo-row > img {
            display: inline-block !important;
            vertical-align: middle;
            max-height: 44px;
            max-width: 160px;
            width: auto !important;
            height: auto !important;
        }
        .logo-row--inline svg {
            display: block;
            margin: 0;
            padding: 0;
        }
        .box-head .logo-row--inline svg {
            display: inline-block;
            vertical-align: middle;
            max-height: 44px;
            width: auto;
        }
        .title-main {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
            padding: 0;
            text-align: center;
            line-height: 1.2;
            vertical-align: middle;
        }
        .id-line {
            font-size: 12px;
            font-weight: 600;
            color: #4b5563;
            margin: 0;
            padding: 0;
            line-height: 1.2;
            vertical-align: middle;
            white-space: nowrap;
            word-break: keep-all;
        }
        .inner { padding: 22px 22px 26px; }
        .two-cols {
            width: 100%;
            margin-bottom: 22px;
        }
        .two-cols td { vertical-align: top; }
        .col-from { width: 50%; padding-right: 18px; }
        .col-to {
            width: 50%;
            padding-left: 18px;
            text-align: right;
            border-left: 1px solid #e5e7eb;
        }
        .lbl {
            display: block;
            font-size: 10px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .name {
            font-size: 12px;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 8px 0;
        }
        .muted {
            font-size: 10px;
            color: #6b7280;
            margin: 0 0 12px 0;
        }
        .table-wrap {
            border: 1px solid #f3f4f6;
            border-radius: 10px;
            overflow: hidden;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
        }
        table.items thead { background: #f9fafb; }
        table.items th {
            padding: 10px 12px;
            font-size: 10px;
            font-weight: 600;
            color: #4b5563;
            border-bottom: 1px solid #f3f4f6;
            text-align: left;
        }
        table.items th.center { text-align: center; }
        table.items th.right { text-align: right; }
        table.items td {
            padding: 10px 12px;
            font-size: 10px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }
        table.items td.center { text-align: center; }
        table.items td.right { text-align: right; }
        table.items td.prod { font-weight: 600; color: #1f2937; }
        table.items tbody tr:last-child td { border-bottom: none; }
        .summary-wrap {
            margin-top: 18px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f3f4f6;
            text-align: right;
        }
        .summary {
            display: inline-block;
            width: 220px;
            text-align: left;
        }
        .summary-title {
            font-size: 11px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 10px 0;
        }
        .summary ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .summary li {
            display: table;
            width: 100%;
            margin-bottom: 6px;
            font-size: 10px;
        }
        .summary li span:first-child {
            display: table-cell;
            color: #6b7280;
        }
        .summary li span:last-child {
            display: table-cell;
            text-align: right;
            font-weight: 600;
            color: #4b5563;
        }
        .summary li.total span:first-child { font-weight: 600; color: #4b5563; }
        .summary li.total span:last-child {
            font-size: 13px;
            font-weight: 700;
            color: #1f2937;
        }
        .foot-note {
            margin-top: 16px;
            font-size: 9px;
            color: #9ca3af;
        }
        .status-pill {
            display: inline-block;
            margin-top: 8px;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 9px;
            font-weight: 600;
            background: #eef2ff;
            color: #3641f5;
        }
    </style>
</head>
<body>
    @php
        $fmt = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
    @endphp
    <div class="box">
        <div class="box-head">
            <table class="box-head-table" cellspacing="0" cellpadding="0">
                <tr>
                    <td class="box-head-logo" valign="middle">
                        @if (!empty($logoSvgInline))
                            <div class="logo-row logo-row--inline">
                                {!! $logoSvgInline !!}
                            </div>
                        @elseif (!empty($logoDataUri))
                            <div class="logo-row">
                                <img
                                    src="{{ $logoDataUri }}"
                                    alt="{{ config('empresa.nome') }}"
                                >
                            </div>
                        @endif
                    </td>
                    <td class="box-head-title" valign="middle" align="center">
                        <p class="title-main">Orçamento</p>
                    </td>
                    <td class="box-head-id" valign="middle" align="right">
                        
                    </td>
                </tr>
            </table>
        </div>

        <div class="inner">
            <table class="two-cols" cellspacing="0" cellpadding="0">
                <tr>
                    <td class="col-from">
                    <p class="id-line">{{ $orcamento->numero }}</p>
                        <span class="lbl">De</span>
                        <p class="name">{{ $empresaNome }}</p>
                        @if ($empresaEndereco !== '')
                            <p class="muted">{!! nl2br(e($empresaEndereco)) !!}</p>
                        @endif
                        @if ($empresaTelefone !== '' || $empresaEmail !== '')
                            <p class="muted">
                                @if ($empresaTelefone !== ''){{ $empresaTelefone }}@endif
                                @if ($empresaTelefone !== '' && $empresaEmail !== '')<br>@endif
                                @if ($empresaEmail !== ''){{ $empresaEmail }}@endif
                            </p>
                        @endif
                        <span class="lbl">Emitido em</span>
                        <span class="muted" style="margin:0;">{{ $dataEmissao }}</span>
                    </td>
                    <td class="col-to">
                        <span class="lbl">Para</span>
                        <p class="name">{{ $orcamento->cliente?->nome ?? '—' }}</p>
                        @if ($clienteContato !== '')
                            <p class="muted" style="text-align:right;">{!! nl2br(e($clienteContato)) !!}</p>
                        @endif
                        <span class="lbl">Válido até</span>
                        <span class="muted" style="margin:0;">{{ $dataValidade }}</span>
                    </td>
                </tr>
            </table>

            <div class="table-wrap">
                <table class="items">
                    <thead>
                        <tr>
                            <th style="width:36px;">N.º</th>
                            <th>Produtos / serviços</th>
                            <th class="center" style="width:56px;">Qtd</th>
                            <th class="center" style="width:72px;">Vlr. unit.</th>
                            <th class="center" style="width:52px;">Desc.</th>
                            <th class="right" style="width:78px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orcamento->itens as $idx => $li)
                            @php
                                $dims = collect([$li->largura, $li->altura, $li->metragem])
                                    ->filter(fn ($v) => $v !== null && $v !== '')
                                    ->map(fn ($v) => number_format((float) $v, 2, ',', '.'))
                                    ->implode(' × ');
                            @endphp
                            <tr>
                                <td class="muted">{{ $idx + 1 }}</td>
                                <td class="prod">
                                    {{ $li->descricao_item }}
                                    @if ($li->item)
                                        <br><span style="font-weight:400;color:#6b7280;font-size:9px;">Ref.: {{ $li->item->nome }}</span>
                                    @endif
                                    @if ($dims !== '')
                                        <br><span style="font-weight:400;color:#6b7280;font-size:9px;">Med.: {{ $dims }}</span>
                                    @endif
                                    @if ($li->observacoes)
                                        <br><span style="font-weight:400;color:#6b7280;font-size:9px;">{{ $li->observacoes }}</span>
                                    @endif
                                </td>
                                <td class="center muted">{{ number_format((float) $li->quantidade, 2, ',', '.') }}</td>
                                <td class="center muted">{{ $fmt($li->valor_unitario) }}</td>
                                <td class="center muted">—</td>
                                <td class="right muted">{{ $fmt($li->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="summary-wrap">
                <div class="summary">
                    <p class="summary-title">Resumo do pedido</p>
                    <ul>
                        <li>
                            <span>Subtotal</span>
                            <span>{{ $fmt($orcamento->subtotal) }}</span>
                        </li>
                        <li>
                            <span>Desconto</span>
                            <span>{{ $fmt($orcamento->desconto) }}</span>
                        </li>
                        <li class="total">
                            <span>Total</span>
                            <span>{{ $fmt($orcamento->total) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            @if ($orcamento->observacoes)
                <p class="lbl" style="margin-top:12px;">Observações</p>
                <p class="muted" style="white-space:pre-wrap;margin:0 0 8px;">{{ $orcamento->observacoes }}</p>
            @endif

            <span class="status-pill">Status: {{ \App\Models\Orcamento::STATUS_LABELS[$orcamento->status] ?? $orcamento->status }}</span>

            <p class="foot-note">
                Documento gerado em {{ $geradoEm }} — {{ $empresaNome }}.
            </p>
        </div>
    </div>
</body>
</html>
