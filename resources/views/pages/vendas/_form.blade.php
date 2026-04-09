@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('vendaForm', (config) => ({
                catalog: config.catalog || [],
                categorias: config.categorias || [],
                formasPagamento: config.formasPagamento || [],
                lines: [],
                recebimentos: [],
                desconto: config.desconto,
                activeTab: config.initialTab || 'dados',
                buscaClienteUrl: config.buscaClienteUrl || '',
                orcamentoImportUrl: config.orcamentoImportUrl || '',
                clienteId: '',
                clienteQuery: '',
                _clienteNomeSnapshot: '',
                orcamentoId: config.orcamentoIdInicial || '',
                clientesSugeridos: [],
                clientesAberto: false,
                clientesCarregando: false,
                _buscaClienteTimer: null,
                defaultRecebimentoDatetime: config.defaultRecebimentoDatetime || '',
                init() {
                    this.orcamentoId = config.orcamentoIdInicial || '';
                    if (config.clienteInicial && config.clienteInicial.id) {
                        this.clienteId = String(config.clienteInicial.id);
                        this.clienteQuery = config.clienteInicial.nome || '';
                        this._clienteNomeSnapshot = this.clienteQuery;
                    }
                    const raw = Array.isArray(config.initialLines) && config.initialLines.length
                        ? config.initialLines
                        : [this.emptyLine()];
                    this.lines = raw.map((line) => ({
                        _id: line._id || crypto.randomUUID(),
                        item_id: line.item_id != null && line.item_id !== '' ? String(line.item_id) : '',
                        categoria_id: line.categoria_id != null && line.categoria_id !== '' ? String(line.categoria_id) : '',
                        descricao_item: line.descricao_item ?? '',
                        quantidade: String(line.quantidade ?? '1'),
                        unidade_medida: line.unidade_medida ?? 'un',
                        valor_unitario: String(line.valor_unitario ?? '0'),
                        custo_unitario: line.custo_unitario != null && line.custo_unitario !== '' ? String(line.custo_unitario) : '',
                        largura: line.largura != null && line.largura !== '' ? String(line.largura) : '',
                        altura: line.altura != null && line.altura !== '' ? String(line.altura) : '',
                        metragem: line.metragem != null && line.metragem !== '' ? String(line.metragem) : '',
                        observacoes: line.observacoes ?? '',
                    }));
                    const rawRec = Array.isArray(config.initialRecebimentos) ? config.initialRecebimentos : [];
                    this.recebimentos = rawRec.map((r) => ({
                        _id: crypto.randomUUID(),
                        forma_pagamento_id: r.forma_pagamento_id != null && r.forma_pagamento_id !== '' ? String(r.forma_pagamento_id) : '',
                        valor: String(r.valor ?? '0'),
                        data_recebimento: r.data_recebimento || this.defaultRecebimentoDatetime,
                        observacoes: r.observacoes ?? '',
                    }));
                },
                emptyLine() {
                    return {
                        _id: crypto.randomUUID(),
                        item_id: '',
                        categoria_id: '',
                        descricao_item: '',
                        quantidade: '1',
                        unidade_medida: 'un',
                        valor_unitario: '0',
                        custo_unitario: '',
                        largura: '',
                        altura: '',
                        metragem: '',
                        observacoes: '',
                    };
                },
                onClienteInput() {
                    if (this._clienteNomeSnapshot !== '' && this.clienteQuery !== this._clienteNomeSnapshot) {
                        this.clienteId = '';
                        this._clienteNomeSnapshot = '';
                    }
                    if (this._buscaClienteTimer) {
                        clearTimeout(this._buscaClienteTimer);
                    }
                    this.clientesAberto = true;
                    this._buscaClienteTimer = setTimeout(() => this.fetchClientes(), 220);
                },
                onClienteFocus() {
                    this.clientesAberto = true;
                    this.fetchClientes();
                },
                async fetchClientes() {
                    const q = (this.clienteQuery || '').trim();
                    if (q === '' || !this.buscaClienteUrl) {
                        this.clientesSugeridos = [];
                        return;
                    }
                    this.clientesCarregando = true;
                    try {
                        const url = `${this.buscaClienteUrl}?q=${encodeURIComponent(q)}`;
                        const r = await fetch(url, {
                            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        this.clientesSugeridos = await r.json();
                        if (!Array.isArray(this.clientesSugeridos)) {
                            this.clientesSugeridos = [];
                        }
                    } catch (e) {
                        this.clientesSugeridos = [];
                    } finally {
                        this.clientesCarregando = false;
                    }
                },
                selectCliente(c) {
                    this.clienteId = String(c.id);
                    this.clienteQuery = c.nome;
                    this._clienteNomeSnapshot = c.nome;
                    this.clientesAberto = false;
                    this.clientesSugeridos = [];
                },
                clearCliente() {
                    this.clienteId = '';
                    this.clienteQuery = '';
                    this._clienteNomeSnapshot = '';
                    this.clientesSugeridos = [];
                    this.clientesAberto = false;
                },
                async onOrcamentoChange() {
                    const id = (this.orcamentoId || '').trim();
                    if (id === '' || !this.orcamentoImportUrl) {
                        return;
                    }
                    const url = this.orcamentoImportUrl.replace('{orcamento}', id);
                    try {
                        const r = await fetch(url, {
                            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        const d = await r.json();
                        if (!r.ok) {
                            window.alert(d.message || 'Não foi possível carregar o orçamento.');
                            return;
                        }
                        if (d.cliente_id) {
                            this.clienteId = String(d.cliente_id);
                            this.clienteQuery = d.cliente_nome || '';
                            this._clienteNomeSnapshot = this.clienteQuery;
                        }
                        if (d.desconto !== undefined) {
                            this.desconto = String(d.desconto);
                        }
                        if (Array.isArray(d.itens) && d.itens.length) {
                            this.lines = d.itens.map((line) => ({
                                _id: crypto.randomUUID(),
                                item_id: line.item_id != null && line.item_id !== '' ? String(line.item_id) : '',
                                categoria_id: line.categoria_id != null && line.categoria_id !== '' ? String(line.categoria_id) : '',
                                descricao_item: line.descricao_item ?? '',
                                quantidade: String(line.quantidade ?? '1'),
                                unidade_medida: line.unidade_medida ?? 'un',
                                valor_unitario: String(line.valor_unitario ?? '0'),
                                custo_unitario: line.custo_unitario != null && line.custo_unitario !== '' ? String(line.custo_unitario) : '',
                                largura: line.largura != null && line.largura !== '' ? String(line.largura) : '',
                                altura: line.altura != null && line.altura !== '' ? String(line.altura) : '',
                                metragem: line.metragem != null && line.metragem !== '' ? String(line.metragem) : '',
                                observacoes: line.observacoes ?? '',
                            }));
                        }
                    } catch (e) {
                        window.alert('Erro ao carregar dados do orçamento.');
                    }
                },
                addLine() {
                    this.lines.push({ ...this.emptyLine(), _id: crypto.randomUUID() });
                },
                removeLine(index) {
                    if (this.lines.length <= 1) {
                        return;
                    }
                    this.lines.splice(index, 1);
                },
                addRecebimento() {
                    this.recebimentos.push({
                        _id: crypto.randomUUID(),
                        forma_pagamento_id: '',
                        valor: '0',
                        data_recebimento: this.defaultRecebimentoDatetime,
                        observacoes: '',
                    });
                },
                removeRecebimento(index) {
                    this.recebimentos.splice(index, 1);
                },
                findCatalogItem(itemId) {
                    if (!itemId) {
                        return null;
                    }
                    return this.catalog.find((c) => String(c.id) === String(itemId)) || null;
                },
                onItemChange(index) {
                    const line = this.lines[index];
                    const it = this.findCatalogItem(line.item_id);
                    if (it) {
                        line.descricao_item = it.nome;
                        line.unidade_medida = it.unidade_medida || 'un';
                        line.valor_unitario = String(it.preco_venda);
                        if (it.categoria_id != null) {
                            line.categoria_id = String(it.categoria_id);
                        }
                        if (it.custo_estimado != null && it.custo_estimado !== '') {
                            line.custo_unitario = String(it.custo_estimado);
                        }
                    }
                },
                modoPreco(index) {
                    const line = this.lines[index];
                    const it = this.findCatalogItem(line.item_id);
                    return it ? it.modo_preco : null;
                },
                parseNum(v) {
                    if (v === null || v === undefined || v === '') {
                        return 0;
                    }
                    const s = String(v).replace(',', '.');
                    const n = parseFloat(s);
                    return Number.isFinite(n) ? n : 0;
                },
                lineSubtotal(line) {
                    return Math.round(this.parseNum(line.quantidade) * this.parseNum(line.valor_unitario) * 100) / 100;
                },
                lineCustoTotal(line) {
                    const cu = this.parseNum(line.custo_unitario);
                    if (cu <= 0) {
                        return 0;
                    }
                    return Math.round(this.parseNum(line.quantidade) * cu * 100) / 100;
                },
                subtotalVenda() {
                    return Math.round(this.lines.reduce((sum, line) => sum + this.lineSubtotal(line), 0) * 100) / 100;
                },
                totalVenda() {
                    const t = this.subtotalVenda() - this.parseNum(this.desconto);
                    return Math.round(Math.max(0, t) * 100) / 100;
                },
                totalRecebido() {
                    return Math.round(
                        this.recebimentos.reduce((sum, r) => sum + this.parseNum(r.valor), 0) * 100,
                    ) / 100;
                },
                saldoRestante() {
                    return Math.round(Math.max(0, this.totalVenda() - this.totalRecebido()) * 100) / 100;
                },
                formatCurrency(n) {
                    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(n);
                },
            }));
        });
    </script>
@endpush

@php
    $descontoDefault = old('desconto', $venda !== null ? (string) $venda->desconto : '0');
    $dataVendaDefault = old(
        'data_venda',
        $venda !== null
            ? $venda->data_venda->format('Y-m-d\TH:i')
            : now()->format('Y-m-d\TH:i'),
    );
    $statusDefault = old('status', $venda !== null ? $venda->status : \App\Models\Venda::STATUS_ABERTA);
    $obsDefault = old('observacoes', $venda !== null ? $venda->observacoes : '');
    $orcamentoIdOld = old('orcamento_id', $venda !== null ? $venda->orcamento_id : '');
    $orcamentoIdInicial = $orcamentoIdOld !== null && $orcamentoIdOld !== '' ? (string) $orcamentoIdOld : '';
    $defaultRecDt = now()->format('Y-m-d\TH:i');
    $vendaTabInicial = collect($errors->keys())->contains(fn ($k) => $k === 'itens' || str_starts_with($k, 'itens.'))
        ? 'itens'
        : (collect($errors->keys())->contains(fn ($k) => $k === 'recebimentos' || str_starts_with($k, 'recebimentos.'))
            ? 'recebimentos'
            : 'dados');
    $orcImportUrl = url('/vendas/orcamentos/{orcamento}/dados-importacao');
    $categoriasJson = $categorias->map(fn ($c) => ['id' => $c->id, 'nome' => $c->nome])->values();
    $formasJson = $formasPagamento->map(fn ($f) => ['id' => $f->id, 'nome' => $f->nome, 'tipo' => $f->tipo])->values();
@endphp

<div
    class="space-y-6"
    x-data="vendaForm({
        catalog: {{ \Illuminate\Support\Js::from($catalog) }},
        categorias: {{ \Illuminate\Support\Js::from($categoriasJson) }},
        formasPagamento: {{ \Illuminate\Support\Js::from($formasJson) }},
        initialLines: {{ \Illuminate\Support\Js::from($defaultLines) }},
        initialRecebimentos: {{ \Illuminate\Support\Js::from($defaultRecebimentos) }},
        desconto: {{ \Illuminate\Support\Js::from($descontoDefault) }},
        initialTab: {{ \Illuminate\Support\Js::from($vendaTabInicial) }},
        clienteInicial: {{ \Illuminate\Support\Js::from($clienteInicial ?? null) }},
        buscaClienteUrl: {{ \Illuminate\Support\Js::from(route('clientes.busca')) }},
        orcamentoImportUrl: {{ \Illuminate\Support\Js::from($orcImportUrl) }},
        orcamentoIdInicial: {{ \Illuminate\Support\Js::from($orcamentoIdInicial) }},
        defaultRecebimentoDatetime: {{ \Illuminate\Support\Js::from($defaultRecDt) }},
    })"
>
    <form method="POST" action="{{ $formAction }}" class="space-y-6">
        @csrf
        @if (strtoupper($formMethod) !== 'POST')
            @method($formMethod)
        @endif

        <div class="space-y-6">
            <div>
                <div class="rounded-t-xl border border-gray-200 p-3 dark:border-gray-800">
                    <nav class="flex overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-900 [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-gray-200 dark:[&::-webkit-scrollbar-thumb]:bg-gray-600 [&::-webkit-scrollbar-track]:bg-white dark:[&::-webkit-scrollbar-track]:bg-transparent">
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium transition-colors duration-200 ease-in-out"
                            x-bind:class="activeTab === 'dados' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-white/[0.03] dark:text-white' : 'bg-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                            x-on:click="activeTab = 'dados'"
                        >
                            Dados da venda
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium transition-colors duration-200 ease-in-out"
                            x-bind:class="activeTab === 'itens' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-white/[0.03] dark:text-white' : 'bg-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                            x-on:click="activeTab = 'itens'"
                        >
                            Itens
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium transition-colors duration-200 ease-in-out"
                            x-bind:class="activeTab === 'recebimentos' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-white/[0.03] dark:text-white' : 'bg-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                            x-on:click="activeTab = 'recebimentos'"
                        >
                            Recebimentos
                        </button>
                    </nav>
                </div>
                <div class="rounded-b-xl border border-t-0 border-gray-200 p-6 pt-4 dark:border-gray-800">
                    <div x-show="activeTab === 'dados'">
                        <h3 class="mb-1 text-xl font-medium text-gray-800 dark:text-white/90">Dados da venda</h3>
                        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                            Informe cliente, orçamento (opcional), data e status. Monte os itens na aba <strong>Itens</strong> e os pagamentos na aba <strong>Recebimentos</strong>.
                        </p>

                        @if ($venda)
                            <div class="mb-5 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-white/[0.03]">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Número</p>
                                <p class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $venda->numero }}</p>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="relative md:col-span-2" @click.away="clientesAberto = false">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Cliente</label>
                                <input type="hidden" name="cliente_id" x-bind:value="clienteId" />
                                <div class="relative flex-1">
                                    <input
                                        type="text"
                                        autocomplete="off"
                                        placeholder="Digite para buscar cliente cadastrado…"
                                        x-model="clienteQuery"
                                        @input="onClienteInput()"
                                        @focus="onClienteFocus()"
                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-10 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('cliente_id') border-red-500 dark:border-red-500 @enderror"
                                    />
                                    <button
                                        type="button"
                                        class="absolute top-1/2 right-2 z-10 -translate-y-1/2 rounded p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                        x-show="clienteId || clienteQuery"
                                        x-on:click="clearCliente()"
                                        x-cloak
                                        aria-label="Limpar cliente"
                                    >
                                        <svg class="stroke-current" width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M5.21967 5.21967C5.51256 4.92678 5.98744 4.92678 6.28033 5.21967L10 8.93934L13.7197 5.21967C14.0126 4.92678 14.4874 4.92678 14.7803 5.21967C15.0732 5.51256 15.0732 5.98744 14.7803 6.28033L11.0607 10L14.7803 13.7197C15.0732 14.0126 15.0732 14.4874 14.7803 14.7803C14.4874 15.0732 14.0126 15.0732 13.7197 14.7803L10 11.0607L6.28033 14.7803C5.98744 15.0732 5.51256 15.0732 5.21967 14.7803C4.92678 14.4874 4.92678 14.0126 5.21967 13.7197L8.93934 10L5.21967 6.28033C4.92678 5.98744 4.92678 5.51256 5.21967 5.21967Z" stroke-width="0.5" />
                                        </svg>
                                    </button>
                                    <div
                                        class="absolute z-50 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-700 dark:bg-gray-900"
                                        x-show="clientesAberto && clienteQuery.trim() !== ''"
                                        x-cloak
                                    >
                                        <div class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400" x-show="clientesCarregando">Buscando…</div>
                                        <div class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400" x-show="!clientesCarregando && clientesSugeridos.length === 0">Nenhum cliente encontrado.</div>
                                        <template x-for="c in clientesSugeridos" :key="c.id">
                                            <button
                                                type="button"
                                                class="flex w-full flex-col items-start gap-0.5 px-4 py-2.5 text-left text-sm hover:bg-gray-50 dark:hover:bg-white/[0.05]"
                                                x-on:click="selectCliente(c)"
                                            >
                                                <span class="font-medium text-gray-800 dark:text-white/90" x-text="c.nome"></span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400" x-show="c.email || c.telefone" x-text="(c.email || '') + (c.email && c.telefone ? ' · ' : '') + (c.telefone || '')"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                @error('cliente_id')
                                    <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Orçamento (importar itens)</label>
                                <select
                                    name="orcamento_id"
                                    x-model="orcamentoId"
                                    @change="onOrcamentoChange()"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-11 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('orcamento_id') border-red-500 dark:border-red-500 @enderror"
                                >
                                    <option value="">Nenhum</option>
                                    @foreach ($orcamentos as $orc)
                                        <option value="{{ $orc->id }}" @selected((string) $orcamentoIdInicial === (string) $orc->id)>
                                            {{ $orc->numero }} — {{ $orc->cliente?->nome ?? 'Sem cliente' }} ({{ \App\Models\Orcamento::STATUS_LABELS[$orc->status] ?? $orc->status }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Ao escolher um orçamento, cliente, desconto e itens são preenchidos automaticamente (você pode ajustar antes de salvar).</p>
                                @error('orcamento_id')
                                    <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Data da venda<span class="text-error-500">*</span>
                                </label>
                                <input
                                    type="datetime-local"
                                    name="data_venda"
                                    value="{{ $dataVendaDefault }}"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('data_venda') border-red-500 dark:border-red-500 @enderror"
                                />
                                @error('data_venda')
                                    <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Status<span class="text-error-500">*</span>
                                </label>
                                <div class="relative z-20 bg-transparent">
                                    <select
                                        name="status"
                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('status') border-red-500 dark:border-red-500 @enderror"
                                    >
                                        @foreach (\App\Models\Venda::STATUS_LABELS as $valor => $rotulo)
                                            <option value="{{ $valor }}" @selected($statusDefault === $valor)>{{ $rotulo }}</option>
                                        @endforeach
                                    </select>
                                    <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4.79199 7.396L10.0003 12.6043L15.2087 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                </div>
                                @error('status')
                                    <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Desconto (R$)</label>
                                <input
                                    type="text"
                                    name="desconto"
                                    x-model="desconto"
                                    inputmode="decimal"
                                    placeholder="0,00"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full max-w-xs rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('desconto') border-red-500 dark:border-red-500 @enderror"
                                />
                                @error('desconto')
                                    <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-5">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Observações</label>
                            <textarea
                                name="observacoes"
                                rows="3"
                                placeholder="Observações gerais da venda"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('observacoes') border-red-500 dark:border-red-500 @enderror"
                            >{{ $obsDefault }}</textarea>
                            @error('observacoes')
                                <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div x-show="activeTab === 'itens'" x-cloak>
                        <h3 class="mb-1 text-xl font-medium text-gray-800 dark:text-white/90">Itens da venda</h3>
                        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                            Inclua ao menos um item. Ao vincular um item cadastrado, descrição, unidade, valores e categoria podem ser preenchidos automaticamente.
                        </p>
                        @error('itens')
                            <p class="mb-4 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                        @enderror

                        <div class="mb-4 flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                                @click="addLine()"
                            >
                                <svg class="fill-current" width="18" height="18" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3.25a.75.75 0 01.75.75v5.25H16a.75.75 0 010 1.5h-5.25V16a.75.75 0 01-1.5 0v-5.25H4a.75.75 0 010-1.5h5.25V4a.75.75 0 01.75-.75z"/></svg>
                                Adicionar item
                            </button>
                        </div>

                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                            <div class="max-w-full overflow-x-auto custom-scrollbar">
                                <table class="min-w-[1100px] text-left text-sm text-gray-700 dark:text-gray-300">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr class="whitespace-nowrap border-b border-gray-100 dark:border-gray-800">
                                            <th class="px-3 py-3 text-xs font-medium text-gray-600 dark:text-gray-400">Item cad.</th>
                                            <th class="px-3 py-3 text-xs font-medium text-gray-600 dark:text-gray-400">Categoria</th>
                                            <th class="min-w-[180px] px-3 py-3 text-xs font-medium text-gray-600 dark:text-gray-400">Descrição<span class="text-error-500">*</span></th>
                                            <th class="px-3 py-3 text-xs font-medium text-gray-600 dark:text-gray-400">Un.<span class="text-error-500">*</span></th>
                                            <th class="px-3 py-3 text-xs font-medium text-gray-600 dark:text-gray-400">Qtd<span class="text-error-500">*</span></th>
                                            <th class="px-3 py-3 text-xs font-medium text-gray-600 dark:text-gray-400">Vlr. unit.<span class="text-error-500">*</span></th>
                                            <th class="px-3 py-3 text-xs font-medium text-gray-600 dark:text-gray-400">Subtotal</th>
                                            <th class="px-3 py-3 text-xs font-medium text-gray-600 dark:text-gray-400">Custo un.</th>
                                            <th class="px-3 py-3 text-xs font-medium text-gray-600 dark:text-gray-400">Custo tot.</th>
                                            <th class="px-3 py-3 text-xs font-medium text-gray-600 dark:text-gray-400">L/A/m²</th>
                                            <th class="px-3 py-3 text-xs font-medium text-gray-600 dark:text-gray-400">Obs.</th>
                                            <th class="px-3 py-3"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
                                        <template x-for="(line, index) in lines" :key="line._id">
                                            <tr>
                                                <td class="px-3 py-2 align-top">
                                                    <select
                                                        class="dark:bg-dark-900 h-9 w-36 rounded-lg border border-gray-300 bg-transparent text-xs dark:border-gray-700 dark:text-white/90"
                                                        x-model="line.item_id"
                                                        @change="onItemChange(index)"
                                                        x-bind:name="'itens[' + index + '][item_id]'"
                                                    >
                                                        <option value="">Manual</option>
                                                        @foreach ($catalog as $opt)
                                                            <option value="{{ $opt['id'] }}">{{ $opt['nome'] }}</option>
                                                        @endforeach
                                                    </select>
                                                    <p class="mt-1 text-[10px] text-amber-600 dark:text-amber-500/90" x-show="modoPreco(index) === 'metro'" x-cloak>Preço por metro</p>
                                                </td>
                                                <td class="px-3 py-2 align-top">
                                                    <select
                                                        class="dark:bg-dark-900 h-9 w-32 rounded-lg border border-gray-300 bg-transparent text-xs dark:border-gray-700 dark:text-white/90"
                                                        x-model="line.categoria_id"
                                                        x-bind:name="'itens[' + index + '][categoria_id]'"
                                                    >
                                                        <option value="">—</option>
                                                        @foreach ($categorias as $cat)
                                                            <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="px-3 py-2 align-top">
                                                    <input
                                                        type="text"
                                                        required
                                                        class="dark:bg-dark-900 h-9 w-full min-w-[160px] rounded-lg border border-gray-300 bg-transparent px-2 text-xs dark:border-gray-700 dark:text-white/90"
                                                        x-model="line.descricao_item"
                                                        x-bind:name="'itens[' + index + '][descricao_item]'"
                                                    />
                                                </td>
                                                <td class="px-3 py-2 align-top">
                                                    <input
                                                        type="text"
                                                        class="dark:bg-dark-900 h-9 w-16 rounded-lg border border-gray-300 bg-transparent px-1 text-xs dark:border-gray-700 dark:text-white/90"
                                                        x-model="line.unidade_medida"
                                                        x-bind:name="'itens[' + index + '][unidade_medida]'"
                                                    />
                                                </td>
                                                <td class="px-3 py-2 align-top">
                                                    <input
                                                        type="text"
                                                        inputmode="decimal"
                                                        class="dark:bg-dark-900 h-9 w-20 rounded-lg border border-gray-300 bg-transparent px-1 text-xs dark:border-gray-700 dark:text-white/90"
                                                        x-model="line.quantidade"
                                                        x-bind:name="'itens[' + index + '][quantidade]'"
                                                    />
                                                </td>
                                                <td class="px-3 py-2 align-top">
                                                    <input
                                                        type="text"
                                                        inputmode="decimal"
                                                        class="dark:bg-dark-900 h-9 w-24 rounded-lg border border-gray-300 bg-transparent px-1 text-xs dark:border-gray-700 dark:text-white/90"
                                                        x-model="line.valor_unitario"
                                                        x-bind:name="'itens[' + index + '][valor_unitario]'"
                                                    />
                                                </td>
                                                <td class="px-3 py-2 align-top text-xs whitespace-nowrap" x-text="formatCurrency(lineSubtotal(line))"></td>
                                                <td class="px-3 py-2 align-top">
                                                    <input
                                                        type="text"
                                                        inputmode="decimal"
                                                        class="dark:bg-dark-900 h-9 w-24 rounded-lg border border-gray-300 bg-transparent px-1 text-xs dark:border-gray-700 dark:text-white/90"
                                                        x-model="line.custo_unitario"
                                                        x-bind:name="'itens[' + index + '][custo_unitario]'"
                                                    />
                                                </td>
                                                <td class="px-3 py-2 align-top text-xs whitespace-nowrap" x-text="lineCustoTotal(line) > 0 ? formatCurrency(lineCustoTotal(line)) : '—'"></td>
                                                <td class="px-3 py-2 align-top">
                                                    <div class="flex flex-wrap gap-1">
                                                        <input type="text" inputmode="decimal" placeholder="L" class="dark:bg-dark-900 h-8 w-12 rounded border border-gray-300 bg-transparent px-1 text-xs dark:border-gray-700" x-model="line.largura" x-bind:name="'itens[' + index + '][largura]'" />
                                                        <input type="text" inputmode="decimal" placeholder="A" class="dark:bg-dark-900 h-8 w-12 rounded border border-gray-300 bg-transparent px-1 text-xs dark:border-gray-700" x-model="line.altura" x-bind:name="'itens[' + index + '][altura]'" />
                                                        <input type="text" inputmode="decimal" placeholder="m²" class="dark:bg-dark-900 h-8 w-12 rounded border border-gray-300 bg-transparent px-1 text-xs dark:border-gray-700" x-model="line.metragem" x-bind:name="'itens[' + index + '][metragem]'" />
                                                    </div>
                                                </td>
                                                <td class="px-3 py-2 align-top">
                                                    <input type="text" class="dark:bg-dark-900 h-9 w-24 rounded-lg border border-gray-300 bg-transparent px-1 text-xs dark:border-gray-700 dark:text-white/90" x-model="line.observacoes" x-bind:name="'itens[' + index + '][observacoes]'" />
                                                </td>
                                                <td class="px-3 py-2 align-top">
                                                    <button type="button" class="text-gray-500 hover:text-red-500 disabled:opacity-40 dark:text-gray-400" @click="removeLine(index)" x-bind:disabled="lines.length <= 1" aria-label="Remover">
                                                        <svg class="fill-current" width="18" height="18" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.54 3.79a1.25 1.25 0 011.25-1.25h4.42a1.25 1.25 0 011.25 1.25v.25h2.17a.75.75 0 010 1.5h-.29v11.5a1.75 1.75 0 01-1.75 1.75H6.21a1.75 1.75 0 01-1.75-1.75V5.54h-.29a.75.75 0 010-1.5h2.17v-.25zm2.5.25h2.92v-.25a.25.25 0 00-.25-.25H9.29a.25.25 0 00-.25.25v.25zM6.46 5.54v11.5c0 .14.11.25.25.25h7.58c.14 0 .25-.11.25-.25V5.54H6.46z"/></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-6 rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-white/[0.03] sm:ml-auto sm:max-w-sm">
                            <p class="mb-2 text-sm font-medium text-gray-800 dark:text-white/90">Resumo (itens)</p>
                            <ul class="space-y-2 text-sm">
                                <li class="flex justify-between gap-4">
                                    <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                                    <span class="font-medium text-gray-800 dark:text-gray-200" x-text="formatCurrency(subtotalVenda())"></span>
                                </li>
                                <li class="flex justify-between gap-4">
                                    <span class="text-gray-500 dark:text-gray-400">Desconto</span>
                                    <span class="font-medium text-gray-800 dark:text-gray-200" x-text="formatCurrency(parseNum(desconto))"></span>
                                </li>
                                <li class="flex justify-between border-t border-gray-200 pt-2 dark:border-gray-700">
                                    <span class="font-semibold text-gray-800 dark:text-white/90">Total</span>
                                    <span class="font-semibold text-brand-600 dark:text-brand-400" x-text="formatCurrency(totalVenda())"></span>
                                </li>
                            </ul>
                            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">Valores recalculados no servidor ao salvar.</p>
                        </div>
                    </div>

                    <div x-show="activeTab === 'recebimentos'" x-cloak>
                        <h3 class="mb-1 text-xl font-medium text-gray-800 dark:text-white/90">Recebimentos</h3>
                        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                            Opcional: registre um ou mais pagamentos. Cada linha com valor exige forma e data.
                        </p>
                        @if ($errors->any() && ($errors->has('recebimentos') || collect($errors->keys())->contains(fn ($k) => str_starts_with($k, 'recebimentos.'))))
                            <p class="mb-4 text-sm text-red-500 dark:text-red-400">Verifique os recebimentos preenchidos.</p>
                        @endif

                        <div class="mb-4">
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                                @click="addRecebimento()"
                            >
                                <svg class="fill-current" width="18" height="18" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3.25a.75.75 0 01.75.75v5.25H16a.75.75 0 010 1.5h-5.25V16a.75.75 0 01-1.5 0v-5.25H4a.75.75 0 010-1.5h5.25V4a.75.75 0 01.75-.75z"/></svg>
                                Adicionar recebimento
                            </button>
                        </div>

                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                            <div class="max-w-full overflow-x-auto custom-scrollbar">
                                <table class="w-full min-w-[720px] text-left text-sm">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr class="border-b border-gray-100 dark:border-gray-800">
                                            <th class="px-4 py-3 text-xs font-medium text-gray-600 dark:text-gray-400">Forma<span class="text-error-500">*</span></th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-600 dark:text-gray-400">Valor<span class="text-error-500">*</span></th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-600 dark:text-gray-400">Data<span class="text-error-500">*</span></th>
                                            <th class="px-4 py-3 text-xs font-medium text-gray-600 dark:text-gray-400">Obs.</th>
                                            <th class="px-4 py-3"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <template x-for="(rec, rIndex) in recebimentos" :key="rec._id">
                                            <tr>
                                                <td class="px-4 py-2">
                                                    <select
                                                        class="dark:bg-dark-900 h-10 w-full min-w-[160px] rounded-lg border border-gray-300 bg-transparent text-sm dark:border-gray-700 dark:text-white/90"
                                                        x-model="rec.forma_pagamento_id"
                                                        x-bind:name="'recebimentos[' + rIndex + '][forma_pagamento_id]'"
                                                    >
                                                        <option value="">Selecione</option>
                                                        @foreach ($formasPagamento as $fp)
                                                            <option value="{{ $fp->id }}">{{ $fp->nome }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <input
                                                        type="text"
                                                        inputmode="decimal"
                                                        class="dark:bg-dark-900 h-10 w-28 rounded-lg border border-gray-300 bg-transparent px-2 text-sm dark:border-gray-700 dark:text-white/90"
                                                        x-model="rec.valor"
                                                        x-bind:name="'recebimentos[' + rIndex + '][valor]'"
                                                    />
                                                </td>
                                                <td class="px-4 py-2">
                                                    <input
                                                        type="datetime-local"
                                                        class="dark:bg-dark-900 h-10 w-full min-w-[180px] rounded-lg border border-gray-300 bg-transparent px-2 text-sm dark:border-gray-700 dark:text-white/90"
                                                        x-model="rec.data_recebimento"
                                                        x-bind:name="'recebimentos[' + rIndex + '][data_recebimento]'"
                                                    />
                                                </td>
                                                <td class="px-4 py-2">
                                                    <input
                                                        type="text"
                                                        class="dark:bg-dark-900 h-10 w-full min-w-[120px] rounded-lg border border-gray-300 bg-transparent px-2 text-sm dark:border-gray-700 dark:text-white/90"
                                                        x-model="rec.observacoes"
                                                        x-bind:name="'recebimentos[' + rIndex + '][observacoes]'"
                                                    />
                                                </td>
                                                <td class="px-4 py-2">
                                                    <button type="button" class="text-gray-500 hover:text-red-500 dark:text-gray-400" @click="removeRecebimento(rIndex)" aria-label="Remover recebimento">
                                                        <svg class="fill-current" width="18" height="18" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.54 3.79a1.25 1.25 0 011.25-1.25h4.42a1.25 1.25 0 011.25 1.25v.25h2.17a.75.75 0 010 1.5h-.29v11.5a1.75 1.75 0 01-1.75 1.75H6.21a1.75 1.75 0 01-1.75-1.75V5.54h-.29a.75.75 0 010-1.5h2.17v-.25zm2.5.25h2.92v-.25a.25.25 0 00-.25-.25H9.29a.25.25 0 00-.25.25v.25zM6.46 5.54v11.5c0 .14.11.25.25.25h7.58c.14 0 .25-.11.25-.25V5.54H6.46z"/></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <template x-if="recebimentos.length === 0">
                            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Nenhum recebimento. Você pode salvar apenas com os itens.</p>
                        </template>

                        <div class="mt-6 rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-white/[0.03] sm:ml-auto sm:max-w-sm">
                            <p class="mb-2 text-sm font-medium text-gray-800 dark:text-white/90">Pagamentos</p>
                            <ul class="space-y-2 text-sm">
                                <li class="flex justify-between gap-4">
                                    <span class="text-gray-500 dark:text-gray-400">Total recebido</span>
                                    <span class="font-medium text-gray-800 dark:text-gray-200" x-text="formatCurrency(totalRecebido())"></span>
                                </li>
                                <li class="flex justify-between border-t border-gray-200 pt-2 dark:border-gray-700">
                                    <span class="font-semibold text-gray-800 dark:text-white/90">Saldo restante</span>
                                    <span class="font-semibold text-gray-800 dark:text-white/90" x-text="formatCurrency(saldoRestante())"></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Resumo geral</p>
            <div class="mt-3 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-4">
                <div class="flex justify-between gap-2 rounded-lg bg-gray-50 px-3 py-2 dark:bg-white/[0.05]">
                    <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                    <span class="font-medium text-gray-800 dark:text-gray-200" x-text="formatCurrency(subtotalVenda())"></span>
                </div>
                <div class="flex justify-between gap-2 rounded-lg bg-gray-50 px-3 py-2 dark:bg-white/[0.05]">
                    <span class="text-gray-500 dark:text-gray-400">Total venda</span>
                    <span class="font-medium text-brand-600 dark:text-brand-400" x-text="formatCurrency(totalVenda())"></span>
                </div>
                <div class="flex justify-between gap-2 rounded-lg bg-gray-50 px-3 py-2 dark:bg-white/[0.05]">
                    <span class="text-gray-500 dark:text-gray-400">Recebido</span>
                    <span class="font-medium text-gray-800 dark:text-gray-200" x-text="formatCurrency(totalRecebido())"></span>
                </div>
                <div class="flex justify-between gap-2 rounded-lg bg-gray-50 px-3 py-2 dark:bg-white/[0.05]">
                    <span class="text-gray-500 dark:text-gray-400">Saldo</span>
                    <span class="font-medium text-gray-800 dark:text-gray-200" x-text="formatCurrency(saldoRestante())"></span>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
            <a
                href="{{ route('vendas.index') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]"
            >
                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M5.21967 5.21967C5.51256 4.92678 5.98744 4.92678 6.28033 5.21967L10 8.93934L13.7197 5.21967C14.0126 4.92678 14.4874 4.92678 14.7803 5.21967C15.0732 5.51256 15.0732 5.98744 14.7803 6.28033L11.0607 10L14.7803 13.7197C15.0732 14.0126 15.0732 14.4874 14.7803 14.7803C14.4874 15.0732 14.0126 15.0732 13.7197 14.7803L10 11.0607L6.28033 14.7803C5.98744 15.0732 5.51256 15.0732 5.21967 14.7803C4.92678 14.4874 4.92678 14.0126 5.21967 13.7197L8.93934 10L5.21967 6.28033C4.92678 5.98744 4.92678 5.51256 5.21967 5.21967Z" fill="" />
                </svg>
                Cancelar
            </a>
            <button
                type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
            >
                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10.0002 2.91699C10.4144 2.91699 10.7502 3.25278 10.7502 3.66699V10.2331L12.9698 8.01344C13.2627 7.72055 13.7376 7.72055 14.0305 8.01344C14.3234 8.30634 14.3234 8.78121 14.0305 9.0741L10.5305 12.5741C10.2376 12.867 9.76273 12.867 9.46983 12.5741L5.96983 9.0741C5.67694 8.78121 5.67694 8.30634 5.96983 8.01344C6.26273 7.72055 6.7376 7.72055 7.03049 8.01344L9.25016 10.2331V3.66699C9.25016 3.25278 9.58595 2.91699 10.0002 2.91699ZM4.16683 14.5837C4.58104 14.5837 4.91683 14.9194 4.91683 15.3337C4.91683 15.5178 5.06699 15.667 5.25016 15.667H14.7502C14.9333 15.667 15.0835 15.5178 15.0835 15.3337C15.0835 14.9194 15.4193 14.5837 15.8335 14.5837C16.2477 14.5837 16.5835 14.9194 16.5835 15.3337C16.5835 16.3462 15.7616 17.167 14.7502 17.167H5.25016C4.23864 17.167 3.41683 16.3462 3.41683 15.3337C3.41683 14.9194 3.75262 14.5837 4.16683 14.5837Z" fill="" />
                </svg>
                Salvar
            </button>
        </div>
    </form>
</div>
