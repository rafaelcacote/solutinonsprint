@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('orcamentoForm', (config) => ({
                catalog: config.catalog || [],
                lines: [],
                desconto: config.desconto,
                activeTab: config.initialTab || 'dados',
                buscaClienteUrl: config.buscaClienteUrl || '',
                clienteId: '',
                clienteQuery: '',
                _clienteNomeSnapshot: '',
                clientesSugeridos: [],
                clientesAberto: false,
                clientesCarregando: false,
                _buscaClienteTimer: null,
                formItem: {
                    item_id: '',
                    descricao_item: '',
                    quantidade: 1,
                    valor_unitario: '0',
                    largura: '',
                    altura: '',
                    metragem: '',
                    observacoes: '',
                },
                init() {
                    if (config.clienteInicial && config.clienteInicial.id) {
                        this.clienteId = String(config.clienteInicial.id);
                        this.clienteQuery = config.clienteInicial.nome || '';
                        this._clienteNomeSnapshot = this.clienteQuery;
                    }
                    const raw = Array.isArray(config.initialLines) && config.initialLines.length ?
                        config.initialLines : [{
                            item_id: '',
                            descricao_item: '',
                            quantidade: '1',
                            valor_unitario: '0',
                            largura: '',
                            altura: '',
                            metragem: '',
                            observacoes: '',
                        }];
                    this.lines = raw.map((line) => ({
                        _id: line._id || crypto.randomUUID(),
                        item_id: line.item_id != null && line.item_id !== '' ? String(line.item_id) : '',
                        descricao_item: line.descricao_item ?? '',
                        quantidade: String(line.quantidade ?? '1'),
                        valor_unitario: String(line.valor_unitario ?? '0'),
                        largura: line.largura != null && line.largura !== '' ? String(line.largura) : '',
                        altura: line.altura != null && line.altura !== '' ? String(line.altura) : '',
                        metragem: line.metragem != null && line.metragem !== '' ? String(line.metragem) : '',
                        observacoes: line.observacoes ?? '',
                    }));
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
                    if (q === '') {
                        this.clientesSugeridos = [];
                        return;
                    }
                    if (!this.buscaClienteUrl) {
                        return;
                    }
                    this.clientesCarregando = true;
                    try {
                        const url = `${this.buscaClienteUrl}?q=${encodeURIComponent(q)}`;
                        const r = await fetch(url, {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });
                        const data = await r.json();
                        this.clientesSugeridos = Array.isArray(data) ? data : [];
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
                resetFormItem() {
                    this.formItem = {
                        item_id: '',
                        descricao_item: '',
                        quantidade: 1,
                        valor_unitario: '0',
                        largura: '',
                        altura: '',
                        metragem: '',
                        observacoes: '',
                    };
                },
                onFormCatalogChange() {
                    const it = this.findCatalogItem(this.formItem.item_id);
                    if (it) {
                        this.formItem.descricao_item = it.nome;
                        this.formItem.valor_unitario = String(it.preco_venda);
                    }
                },
                addLineFromForm(e) {
                    if (e) {
                        e.preventDefault();
                    }
                    const d = (this.formItem.descricao_item || '').trim();
                    if (!d) {
                        return;
                    }
                    const q = Math.max(0.01, this.parseNum(this.formItem.quantidade) || 1);
                    this.lines.push({
                        _id: crypto.randomUUID(),
                        item_id: this.formItem.item_id ? String(this.formItem.item_id) : '',
                        descricao_item: d,
                        quantidade: String(q),
                        valor_unitario: String(this.formItem.valor_unitario ?? '0'),
                        largura: this.formItem.largura != null && this.formItem.largura !== '' ? String(this.formItem.largura) : '',
                        altura: this.formItem.altura != null && this.formItem.altura !== '' ? String(this.formItem.altura) : '',
                        metragem: this.formItem.metragem != null && this.formItem.metragem !== '' ? String(this.formItem.metragem) : '',
                        observacoes: this.formItem.observacoes ?? '',
                    });
                    this.resetFormItem();
                },
                addLine() {
                    this.lines.push({
                        _id: crypto.randomUUID(),
                        item_id: '',
                        descricao_item: '',
                        quantidade: '1',
                        valor_unitario: '0',
                        largura: '',
                        altura: '',
                        metragem: '',
                        observacoes: '',
                    });
                },
                removeLine(index) {
                    if (this.lines.length <= 1) {
                        return;
                    }
                    this.lines.splice(index, 1);
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
                        line.valor_unitario = String(it.preco_venda);
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
                subtotalOrcamento() {
                    return Math.round(this.lines.reduce((sum, line) => sum + this.lineSubtotal(line), 0) * 100) / 100;
                },
                totalOrcamento() {
                    const t = this.subtotalOrcamento() - this.parseNum(this.desconto);
                    return Math.round(Math.max(0, t) * 100) / 100;
                },
                formatCurrency(n) {
                    return new Intl.NumberFormat('pt-BR', {
                        style: 'currency',
                        currency: 'BRL'
                    }).format(n);
                },
            }));
        });
    </script>
@endpush

@php
    $descontoDefault = old('desconto', $orcamento !== null ? (string) $orcamento->desconto : '0');
    $dataOrcamentoDefault = old(
        'data_orcamento',
        $orcamento !== null
            ? $orcamento->data_orcamento->format('Y-m-d\TH:i')
            : now()->format('Y-m-d\TH:i'),
    );
    $statusDefault = old('status', $orcamento !== null ? $orcamento->status : \App\Models\Orcamento::STATUS_ABERTO);
    $validadeDefault = old('validade_dias', $orcamento !== null ? $orcamento->validade_dias : 7);
    $obsDefault = old('observacoes', $orcamento !== null ? $orcamento->observacoes : '');
    $orcamentoTabInicial = collect($errors->keys())->contains(fn ($k) => $k === 'itens' || str_starts_with($k, 'itens.'))
        ? 'itens'
        : 'dados';
@endphp

<div
    class="space-y-6"
    x-data="orcamentoForm({
        catalog: {{ \Illuminate\Support\Js::from($catalog) }},
        initialLines: {{ \Illuminate\Support\Js::from($defaultLines) }},
        desconto: {{ \Illuminate\Support\Js::from($descontoDefault) }},
        initialTab: {{ \Illuminate\Support\Js::from($orcamentoTabInicial) }},
        clienteInicial: {{ \Illuminate\Support\Js::from($clienteInicial ?? null) }},
        buscaClienteUrl: {{ \Illuminate\Support\Js::from(route('clientes.busca')) }},
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
                            Dados do orçamento
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium transition-colors duration-200 ease-in-out"
                            x-bind:class="activeTab === 'itens' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-white/[0.03] dark:text-white' : 'bg-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                            x-on:click="activeTab = 'itens'"
                        >
                            Itens do orçamento
                        </button>
                    </nav>
                </div>
                <div class="rounded-b-xl border border-t-0 border-gray-200 p-6 pt-4 dark:border-gray-800">
                    <div x-show="activeTab === 'dados'">
                        <h3 class="mb-1 text-xl font-medium text-gray-800 dark:text-white/90">Dados do orçamento</h3>
                        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                            Preencha cliente, datas e condições. Use a aba <strong>Itens do orçamento</strong> para montar a lista e o resumo.
                        </p>

                    @if ($orcamento)
                        <div class="mb-5 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-white/[0.03]">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Número</p>
                            <p class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $orcamento->numero }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div class="relative md:col-span-2" @click.away="clientesAberto = false">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Cliente
                            </label>
                            <input type="hidden" name="cliente_id" x-bind:value="clienteId" />
                            <div class="flex gap-2">
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
                            </div>
                            @error('cliente_id')
                                <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Data do orçamento<span class="text-error-500">*</span>
                            </label>
                            <input
                                type="datetime-local"
                                name="data_orcamento"
                                value="{{ $dataOrcamentoDefault }}"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('data_orcamento') border-red-500 dark:border-red-500 @enderror"
                            />
                            @error('data_orcamento')
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
                                    @foreach (\App\Models\Orcamento::STATUS_LABELS as $valor => $rotulo)
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

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Desconto (R$)
                            </label>
                            <input
                                type="text"
                                name="desconto"
                                x-model="desconto"
                                inputmode="decimal"
                                placeholder="0,00"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('desconto') border-red-500 dark:border-red-500 @enderror"
                            />
                            @error('desconto')
                                <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Validade (dias)<span class="text-error-500">*</span>
                            </label>
                            <input
                                type="number"
                                name="validade_dias"
                                min="1"
                                max="3650"
                                value="{{ $validadeDefault }}"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('validade_dias') border-red-500 dark:border-red-500 @enderror"
                            />
                            @error('validade_dias')
                                <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Observações
                        </label>
                        <textarea
                            name="observacoes"
                            rows="3"
                            placeholder="Observações gerais do orçamento"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('observacoes') border-red-500 dark:border-red-500 @enderror"
                        >{{ $obsDefault }}</textarea>
                        @error('observacoes')
                            <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    </div>

                    <div x-show="activeTab === 'itens'" x-cloak>
                        <h3 class="mb-1 text-xl font-medium text-gray-800 dark:text-white/90">Itens do orçamento</h3>
                    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                        Primeiro use o bloco <strong>Adicionar item à lista</strong> para incluir linhas (mínimo uma). Depois confira e edite na tabela de itens. Item cadastrado preenche descrição e preço automaticamente; você pode usar <strong>Enter</strong> na descrição ou em <strong>Adicionar</strong>.
                    </p>

                    @error('itens')
                        <p class="mb-4 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    <div class="border-b border-gray-200 pb-6 dark:border-gray-800 sm:-mx-2 sm:px-2">
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                            <p class="mb-4 text-sm font-medium text-gray-800 dark:text-white/90">Adicionar item à lista</p>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-12">
                                    <div class="w-full lg:col-span-3">
                                        <label class="mb-1 inline-block text-sm font-semibold text-gray-700 dark:text-gray-400">Item cadastrado</label>
                                        <div class="relative z-10 bg-transparent">
                                            <select
                                                x-model="formItem.item_id"
                                                @change="onFormCatalogChange()"
                                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-white px-4 py-2.5 pr-11 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                            >
                                                <option value="">Sem vínculo (manual)</option>
                                                @foreach ($catalog as $opt)
                                                    <option value="{{ $opt['id'] }}">{{ $opt['nome'] }}</option>
                                                @endforeach
                                            </select>
                                            <span class="pointer-events-none absolute top-1/2 right-4 z-20 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M4.79199 7.396L10.0003 12.6043L15.2087 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="w-full lg:col-span-4">
                                        <label class="mb-1 inline-block text-sm font-semibold text-gray-700 dark:text-gray-400">Descrição no orçamento<span class="text-error-500">*</span></label>
                                        <input
                                            type="text"
                                            x-model="formItem.descricao_item"
                                            placeholder="Ex.: Banner lona fosca 2x1m"
                                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                            @keydown.enter.prevent="addLineFromForm()"
                                        />
                                    </div>
                                    <div class="w-full lg:col-span-2">
                                        <label class="mb-1 inline-block text-sm font-semibold text-gray-700 dark:text-gray-400">Valor unit. (R$)</label>
                                        <input
                                            type="text"
                                            x-model="formItem.valor_unitario"
                                            inputmode="decimal"
                                            placeholder="0"
                                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                            @keydown.enter.prevent
                                        />
                                    </div>
                                    <div class="w-full lg:col-span-2">
                                        <label class="mb-1 inline-block text-sm font-semibold text-gray-700 dark:text-gray-400">Quantidade</label>
                                        <div class="flex h-11 divide-x divide-gray-300 overflow-hidden rounded-lg border border-gray-300 dark:divide-gray-800 dark:border-gray-700">
                                            <button
                                                type="button"
                                                @click="formItem.quantidade = Math.max(1, (Number(formItem.quantidade) || 1) - 1)"
                                                class="inline-flex w-1/3 items-center justify-center bg-white text-gray-700 hover:bg-gray-100 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                                            >
                                                <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M6.66699 12H18.6677" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </button>
                                            <div class="w-1/3">
                                                <input
                                                    type="number"
                                                    min="1"
                                                    step="0.01"
                                                    x-model.number="formItem.quantidade"
                                                    class="h-full w-full border-0 bg-white text-center text-sm text-gray-700 outline-none focus:ring-0 dark:bg-gray-900 dark:text-gray-400"
                                                    @keydown.enter.prevent
                                                />
                                            </div>
                                            <button
                                                type="button"
                                                @click="formItem.quantidade = (Number(formItem.quantidade) || 1) + 1"
                                                class="inline-flex w-1/3 items-center justify-center bg-white text-gray-700 hover:bg-gray-100 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                                            >
                                                <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M6.66699 12.0002H18.6677M12.6672 6V18.0007" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="flex w-full items-end lg:col-span-1">
                                        <button
                                            type="button"
                                            class="h-11 w-full rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600"
                                            @click="addLineFromForm()"
                                        >
                                            Adicionar
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-12">
                                    <div class="lg:col-span-2">
                                        <label class="mb-1 inline-block text-xs font-medium text-gray-600 dark:text-gray-400">Largura</label>
                                        <input
                                            type="text"
                                            x-model="formItem.largura"
                                            inputmode="decimal"
                                            class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                            @keydown.enter.prevent
                                        />
                                    </div>
                                    <div class="lg:col-span-2">
                                        <label class="mb-1 inline-block text-xs font-medium text-gray-600 dark:text-gray-400">Altura</label>
                                        <input
                                            type="text"
                                            x-model="formItem.altura"
                                            inputmode="decimal"
                                            class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                            @keydown.enter.prevent
                                        />
                                    </div>
                                    <div class="lg:col-span-2">
                                        <label class="mb-1 inline-block text-xs font-medium text-gray-600 dark:text-gray-400">Metragem</label>
                                        <input
                                            type="text"
                                            x-model="formItem.metragem"
                                            inputmode="decimal"
                                            class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                            @keydown.enter.prevent
                                        />
                                    </div>
                                    <div class="lg:col-span-6">
                                        <label class="mb-1 inline-block text-xs font-medium text-gray-600 dark:text-gray-400">Observações do item</label>
                                        <input
                                            type="text"
                                            x-model="formItem.observacoes"
                                            class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                            @keydown.enter.prevent="addLineFromForm()"
                                        />
                                    </div>
                                </div>
                            <div class="mt-5 flex max-w-2xl items-start gap-2">
                                <svg class="mt-0.5 shrink-0 text-gray-500 dark:text-gray-400" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10 7.22485H10.0007" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M10.0004 9.34575V12.8661M17.7087 10.0001C17.7087 14.2573 14.2575 17.7084 10.0003 17.7084C5.74313 17.7084 2.29199 14.2573 2.29199 10.0001C2.29199 5.74289 5.74313 2.29175 10.0003 2.29175C14.2575 2.29175 17.7087 5.74289 17.7087 10.0001Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Depois de preencher, pressione <strong>Enter</strong> na descrição ou clique em <strong>Adicionar</strong>. Os itens aparecem na tabela abaixo; você pode editá-los ali antes de salvar o orçamento.
                                </p>
                            </div>
                        </div>

                        <div class="mt-8">
                            <h4 class="mb-3 text-base font-medium text-gray-800 dark:text-white/90">Itens no orçamento</h4>
                            <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
                            <div class="custom-scrollbar overflow-x-auto">
                                <table class="min-w-[920px] text-left text-sm text-gray-700 dark:text-gray-300">
                                    <thead class="bg-gray-50 dark:bg-gray-900">
                                        <tr class="whitespace-nowrap border-b border-gray-100 dark:border-gray-800">
                                            <th class="px-4 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400 sm:px-5">Nº</th>
                                            <th class="min-w-[200px] px-4 py-4 text-sm font-medium whitespace-nowrap text-gray-500 dark:text-gray-400 sm:px-5">Item / descrição</th>
                                            <th class="px-4 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400 sm:px-5">Qtd</th>
                                            <th class="px-4 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400 sm:px-5">Vlr. unit.</th>
                                            <th class="px-4 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400 sm:px-5">Subtotal</th>
                                            <th class="min-w-[140px] px-4 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400 sm:px-5">L × A × m²</th>
                                            <th class="min-w-[120px] px-4 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400 sm:px-5">Obs.</th>
                                            <th class="relative px-4 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400 sm:px-5">
                                                <span class="sr-only">Ações</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
                                        <template x-for="(line, index) in lines" :key="line._id">
                                            <tr>
                                                <td class="px-4 py-3 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400 sm:px-5" x-text="index + 1"></td>
                                                <td class="px-4 py-3 sm:px-5">
                                                    <div class="relative z-10 mb-2 bg-transparent">
                                                        <select
                                                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 h-9 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-3 py-1.5 pr-9 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                                            x-model="line.item_id"
                                                            @change="onItemChange(index)"
                                                            x-bind:name="'itens[' + index + '][item_id]'"
                                                        >
                                                            <option value="">Sem cadastro</option>
                                                            @foreach ($catalog as $opt)
                                                                <option value="{{ $opt['id'] }}">{{ $opt['nome'] }}</option>
                                                            @endforeach
                                                        </select>
                                                        <span class="pointer-events-none absolute top-1/2 right-2 z-20 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                                            <svg class="stroke-current" width="14" height="14" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M4.79199 7.396L10.0003 12.6043L15.2087 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <input
                                                        type="text"
                                                        required
                                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 h-9 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-1.5 text-sm font-medium text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                                        x-model="line.descricao_item"
                                                        x-bind:name="'itens[' + index + '][descricao_item]'"
                                                        placeholder="Descrição no orçamento"
                                                    />
                                                    <p class="mt-1 text-xs text-amber-600 dark:text-amber-500/90" x-show="modoPreco(index) === 'metro'" x-cloak>Preço por metro — use metragem.</p>
                                                </td>
                                                <td class="px-4 py-3 sm:px-5">
                                                    <input
                                                        type="text"
                                                        inputmode="decimal"
                                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 h-9 w-20 rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                                        x-model="line.quantidade"
                                                        x-bind:name="'itens[' + index + '][quantidade]'"
                                                    />
                                                </td>
                                                <td class="px-4 py-3 sm:px-5">
                                                    <input
                                                        type="text"
                                                        inputmode="decimal"
                                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 h-9 w-24 rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                                        x-model="line.valor_unitario"
                                                        x-bind:name="'itens[' + index + '][valor_unitario]'"
                                                    />
                                                </td>
                                                <td class="px-4 py-3 text-sm whitespace-nowrap text-gray-800 dark:text-gray-300 sm:px-5" x-text="formatCurrency(lineSubtotal(line))"></td>
                                                <td class="px-4 py-3 sm:px-5">
                                                    <div class="flex flex-wrap gap-1">
                                                        <input
                                                            type="text"
                                                            inputmode="decimal"
                                                            title="Largura"
                                                            placeholder="L"
                                                            class="dark:bg-dark-900 h-8 w-14 rounded border border-gray-300 bg-transparent px-1.5 text-xs dark:border-gray-700 dark:text-white/90"
                                                            x-model="line.largura"
                                                            x-bind:name="'itens[' + index + '][largura]'"
                                                        />
                                                        <input
                                                            type="text"
                                                            inputmode="decimal"
                                                            title="Altura"
                                                            placeholder="A"
                                                            class="dark:bg-dark-900 h-8 w-14 rounded border border-gray-300 bg-transparent px-1.5 text-xs dark:border-gray-700 dark:text-white/90"
                                                            x-model="line.altura"
                                                            x-bind:name="'itens[' + index + '][altura]'"
                                                        />
                                                        <input
                                                            type="text"
                                                            inputmode="decimal"
                                                            title="Metragem"
                                                            placeholder="m²"
                                                            class="dark:bg-dark-900 h-8 w-14 rounded border border-gray-300 bg-transparent px-1.5 text-xs dark:border-gray-700 dark:text-white/90"
                                                            x-model="line.metragem"
                                                            x-bind:name="'itens[' + index + '][metragem]'"
                                                        />
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 sm:px-5">
                                                    <input
                                                        type="text"
                                                        class="dark:bg-dark-900 h-9 w-full min-w-[100px] rounded-lg border border-gray-300 bg-transparent px-2 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:text-white/90"
                                                        x-model="line.observacoes"
                                                        x-bind:name="'itens[' + index + '][observacoes]'"
                                                        placeholder="—"
                                                    />
                                                </td>
                                                <td class="px-4 py-3 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400 sm:px-5">
                                                    <div class="flex items-center justify-center">
                                                        <button
                                                            type="button"
                                                            class="rounded p-1 text-gray-500 hover:text-red-500 disabled:cursor-not-allowed disabled:opacity-40 dark:text-gray-400 dark:hover:text-red-400"
                                                            @click="removeLine(index)"
                                                            x-bind:disabled="lines.length <= 1"
                                                            aria-label="Remover linha"
                                                        >
                                                            <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.54142 3.7915C6.54142 2.54886 7.54878 1.5415 8.79142 1.5415H11.2081C12.4507 1.5415 13.4581 2.54886 13.4581 3.7915V4.0415H15.6252H16.666C17.0802 4.0415 17.416 4.37729 17.416 4.7915C17.416 5.20572 17.0802 5.5415 16.666 5.5415H16.3752V8.24638V13.2464V16.2082C16.3752 17.4508 15.3678 18.4582 14.1252 18.4582H5.87516C4.63252 18.4582 3.62516 17.4508 3.62516 16.2082V13.2464V8.24638V5.5415H3.3335C2.91928 5.5415 2.5835 5.20572 2.5835 4.7915C2.5835 4.37729 2.91928 4.0415 3.3335 4.0415H4.37516H6.54142V3.7915ZM14.8752 13.2464V8.24638V5.5415H13.4581H12.7081H7.29142H6.54142H5.12516V8.24638V13.2464V16.2082C5.12516 16.6224 5.46095 16.9582 5.87516 16.9582H14.1252C14.5394 16.9582 14.8752 16.6224 14.8752 16.2082V13.2464ZM8.04142 4.0415H11.9581V3.7915C11.9581 3.37729 11.6223 3.0415 11.2081 3.0415H8.79142C8.37721 3.0415 8.04142 3.37729 8.04142 3.7915V4.0415ZM8.3335 7.99984C8.74771 7.99984 9.0835 8.33562 9.0835 8.74984V13.7498C9.0835 14.1641 8.74771 14.4998 8.3335 14.4998C7.91928 14.4998 7.5835 14.1641 7.5835 13.7498V8.74984C7.5835 8.33562 7.91928 7.99984 8.3335 7.99984ZM12.4168 8.74984C12.4168 8.33562 12.081 7.99984 11.6668 7.99984C11.2526 7.99984 10.9168 8.33562 10.9168 8.74984V13.7498C10.9168 14.1641 11.2526 14.4998 11.6668 14.4998C12.081 14.4998 12.4168 14.1641 12.4168 13.7498V8.74984Z" fill="" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                                <template x-if="lines.length === 0">
                                    <div class="px-5 py-6 text-center text-sm text-gray-400 dark:text-gray-500">
                                        Nenhum item na lista. Use o bloco <strong>Adicionar item à lista</strong> acima.
                                    </div>
                                </template>
                            </div>
                        </div>
                        </div>

                        <div class="mt-6 flex flex-wrap justify-end border-t border-gray-100 pt-6 dark:border-gray-800">
                            <div class="w-full space-y-2 text-right sm:w-[260px]">
                                <p class="mb-2 text-left text-sm font-medium text-gray-800 dark:text-white/90">Resumo (itens)</p>
                                <ul class="space-y-2">
                                    <li class="flex justify-between gap-5">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Subtotal itens</span>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="formatCurrency(subtotalOrcamento())"></span>
                                    </li>
                                    <li class="flex justify-between gap-5">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Desconto (cabeçalho)</span>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="formatCurrency(parseNum(desconto))"></span>
                                    </li>
                                    <li class="flex items-center justify-between border-t border-gray-200 pt-2 dark:border-gray-700">
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Total</span>
                                        <span class="text-lg font-semibold text-gray-800 dark:text-white/90" x-text="formatCurrency(totalOrcamento())"></span>
                                    </li>
                                </ul>
                                <p class="mt-3 text-left text-xs text-gray-500 dark:text-gray-400">
                                    Os valores finais são recalculados no servidor ao salvar.
                                </p>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
            <a
                href="{{ route('orcamentos.index') }}"
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
