@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Resumo do sistema" icon="info" />

    <div
        class="rounded-2xl border border-gray-200 bg-white px-5 py-7 dark:border-gray-800 dark:bg-white/[0.03] xl:px-10 xl:py-12"
    >
        <div class="mx-auto max-w-3xl space-y-10 text-gray-700 dark:text-gray-300">
            <header class="space-y-2 border-b border-gray-200 pb-8 dark:border-gray-800">
                <p class="text-sm font-medium uppercase tracking-wide text-brand-600 dark:text-brand-400">
                    Página temporária
                </p>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white sm:text-3xl">
                    📊 Resumo do Sistema da Loja
                </h1>
                <p class="text-base leading-relaxed">
                    Fala mano! 👊<br />
                    Vou te explicar como o sistema está funcionando hoje e o que já foi implementado.
                </p>
            </header>

            <section class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">🧠 Visão geral</h2>
                <p class="leading-relaxed">
                    O sistema foi desenvolvido para organizar completamente a gestão da loja, substituindo controle manual (ou só banco/maquininha) por um sistema completo que controla:
                </p>
                <ul class="list-inside list-disc space-y-1 pl-1 text-sm sm:text-base">
                    <li>vendas</li>
                    <li>orçamentos</li>
                    <li>produtos e serviços</li>
                    <li>clientes</li>
                    <li>despesas</li>
                    <li>caixa</li>
                    <li>relatórios básicos</li>
                </ul>
            </section>

            <section class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">📦 Cadastro da loja (Base do sistema)</h2>
                <p class="leading-relaxed">Antes de vender, o sistema permite cadastrar tudo que a loja oferece:</p>

                <div class="space-y-3 rounded-xl bg-gray-50 p-4 dark:bg-white/[0.04]">
                    <h3 class="font-semibold text-gray-900 dark:text-white">✅ Produtos e serviços</h3>
                    <p class="text-sm leading-relaxed">Tu pode cadastrar tudo que tu vende, por exemplo:</p>
                    <ul class="list-inside list-disc space-y-1 text-sm">
                        <li>impressão PB</li>
                        <li>impressão colorida</li>
                        <li>impressão couchê</li>
                        <li>encadernação</li>
                        <li>plastificação</li>
                        <li>scanner</li>
                        <li>caneca personalizada</li>
                        <li>adesivo vinil</li>
                        <li>lona por metro</li>
                    </ul>
                    <p class="text-sm leading-relaxed">Cada item pode ter:</p>
                    <ul class="list-inside list-disc space-y-1 text-sm">
                        <li>preço</li>
                        <li>tipo (produto ou serviço)</li>
                        <li>forma de cobrança (fixo, por quantidade, por metro, etc.)</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <h3 class="font-semibold text-gray-900 dark:text-white">✅ Categorias</h3>
                    <p class="text-sm leading-relaxed">Organiza os itens, tipo:</p>
                    <ul class="list-inside list-disc space-y-1 text-sm">
                        <li>impressão</li>
                        <li>personalizados</li>
                        <li>papelaria</li>
                        <li>etc.</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <h3 class="font-semibold text-gray-900 dark:text-white">✅ Regras de preço</h3>
                    <p class="text-sm leading-relaxed">Tu consegue configurar preço por quantidade, tipo:</p>
                    <p class="rounded-lg border border-gray-200 bg-white p-3 font-mono text-xs dark:border-gray-700 dark:bg-gray-900">
                        impressão PB:<br />
                        1 a 9 → R$1,00<br />
                        10 a 49 → R$0,75<br />
                        50+ → R$0,55
                    </p>
                    <p class="text-sm leading-relaxed">Isso já deixa o sistema preparado para orçamento inteligente.</p>
                </div>

                <div class="space-y-2">
                    <h3 class="font-semibold text-gray-900 dark:text-white">✅ Clientes</h3>
                    <p class="text-sm leading-relaxed">Cadastro de clientes com:</p>
                    <ul class="list-inside list-disc space-y-1 text-sm">
                        <li>nome</li>
                        <li>telefone</li>
                        <li>email</li>
                        <li>CPF/CNPJ</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <h3 class="font-semibold text-gray-900 dark:text-white">✅ Fornecedores</h3>
                    <p class="text-sm leading-relaxed">Cadastro de quem tu compra:</p>
                    <ul class="list-inside list-disc space-y-1 text-sm">
                        <li>tinta</li>
                        <li>papel</li>
                        <li>materiais</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <h3 class="font-semibold text-gray-900 dark:text-white">✅ Formas de pagamento</h3>
                    <p class="text-sm leading-relaxed">Configurado:</p>
                    <ul class="list-inside list-disc space-y-1 text-sm">
                        <li>dinheiro</li>
                        <li>pix</li>
                        <li>cartão</li>
                        <li>transferência</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <h3 class="font-semibold text-gray-900 dark:text-white">✅ Categorias de despesa</h3>
                    <p class="text-sm leading-relaxed">Organiza teus gastos:</p>
                    <ul class="list-inside list-disc space-y-1 text-sm">
                        <li>aluguel</li>
                        <li>luz</li>
                        <li>internet</li>
                        <li>insumos</li>
                        <li>etc.</li>
                    </ul>
                </div>
            </section>

            <section class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">🧾 Orçamentos</h2>
                <p class="leading-relaxed">Agora tu consegue fazer orçamento direto no sistema:</p>
                <ul class="list-inside list-disc space-y-1 text-sm sm:text-base">
                    <li>cria orçamento na hora</li>
                    <li>adiciona vários itens</li>
                    <li>define quantidade e preço</li>
                    <li>sistema calcula tudo automaticamente</li>
                    <li>pode salvar sem cliente ou com cliente</li>
                </ul>
                <p class="font-medium text-gray-900 dark:text-white">Isso resolve:</p>
                <ul class="list-inside list-disc space-y-1 text-sm sm:text-base">
                    <li>👉 não precisar mais perguntar preço pra ninguém</li>
                    <li>👉 responder cliente na hora</li>
                    <li>👉 padronizar valores</li>
                </ul>
            </section>

            <section class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">💰 Vendas</h2>
                <p class="leading-relaxed">Agora vem a parte mais importante:</p>

                <div class="space-y-2">
                    <h3 class="font-semibold text-gray-900 dark:text-white">✅ Cadastro de vendas</h3>
                    <p class="text-sm leading-relaxed">Tu consegue registrar uma venda completa:</p>
                    <ul class="list-inside list-disc space-y-1 text-sm">
                        <li>cliente (opcional)</li>
                        <li>itens vendidos</li>
                        <li>quantidades</li>
                        <li>valores</li>
                        <li>desconto</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <h3 class="font-semibold text-gray-900 dark:text-white">✅ Recebimentos</h3>
                    <p class="text-sm leading-relaxed">Tu pode registrar como foi pago:</p>
                    <ul class="list-inside list-disc space-y-1 text-sm">
                        <li>pix</li>
                        <li>dinheiro</li>
                        <li>cartão</li>
                        <li>misto (ex: metade pix, metade dinheiro)</li>
                    </ul>
                    <p class="text-sm leading-relaxed">O sistema controla:</p>
                    <ul class="list-inside list-disc space-y-1 text-sm">
                        <li>total da venda</li>
                        <li>quanto foi recebido</li>
                        <li>quanto falta (se faltar)</li>
                    </ul>
                </div>
            </section>

            <section class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">💸 Despesas</h2>
                <p class="leading-relaxed">Tu consegue controlar tudo que sai da loja:</p>
                <ul class="list-inside list-disc space-y-1 text-sm sm:text-base">
                    <li>aluguel</li>
                    <li>luz</li>
                    <li>internet</li>
                    <li>compra de material</li>
                    <li>qualquer gasto</li>
                </ul>
                <p class="text-sm leading-relaxed">Cada despesa tem:</p>
                <ul class="list-inside list-disc space-y-1 text-sm">
                    <li>categoria</li>
                    <li>valor</li>
                    <li>status (pago, pendente, vencido)</li>
                    <li>forma de pagamento</li>
                </ul>
            </section>

            <section class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">🏦 Caixa</h2>
                <p class="leading-relaxed">O sistema tem controle de caixa manual:</p>
                <p class="text-sm leading-relaxed">Tu consegue registrar:</p>
                <ul class="list-inside list-disc space-y-1 text-sm">
                    <li>entradas (ex: dinheiro que entrou)</li>
                    <li>saídas (ex: pagamento de contas)</li>
                    <li>ajustes</li>
                    <li>retiradas</li>
                    <li>aportes</li>
                </ul>
                <p class="text-sm leading-relaxed">E pode vincular:</p>
                <ul class="list-inside list-disc space-y-1 text-sm">
                    <li>a uma venda</li>
                    <li>a uma despesa</li>
                </ul>
            </section>

            <section class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">📊 Dashboard (Painel principal)</h2>
                <p class="leading-relaxed">Logo que entra no sistema, tu vê:</p>

                <div class="space-y-3 rounded-xl bg-gray-50 p-4 dark:bg-white/[0.04]">
                    <h3 class="font-semibold text-gray-900 dark:text-white">📈 Resumo do mês</h3>
                    <ul class="list-inside list-disc space-y-1 text-sm">
                        <li>total vendido</li>
                        <li>total gasto</li>
                        <li>entradas</li>
                        <li>saídas</li>
                        <li>saldo</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <h3 class="font-semibold text-gray-900 dark:text-white">📦 Operacional</h3>
                    <ul class="list-inside list-disc space-y-1 text-sm">
                        <li>quantidade de vendas</li>
                        <li>quantidade de orçamentos</li>
                        <li>total de clientes</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <h3 class="font-semibold text-gray-900 dark:text-white">🧠 Inteligência do negócio</h3>
                    <ul class="list-inside list-disc space-y-1 text-sm">
                        <li>itens mais vendidos</li>
                        <li>vendas recentes</li>
                        <li>despesas recentes</li>
                        <li>movimentações recentes</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <h3 class="font-semibold text-gray-900 dark:text-white">💳 Financeiro</h3>
                    <p class="text-sm leading-relaxed">Quanto entrou por:</p>
                    <ul class="list-inside list-disc space-y-1 text-sm">
                        <li>pix</li>
                        <li>dinheiro</li>
                        <li>cartão</li>
                        <li>despesas por categoria</li>
                    </ul>
                </div>
            </section>

            <section class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">🔥 O que o sistema resolve hoje</h2>
                <p class="leading-relaxed">Hoje o sistema já resolve:</p>
                <ul class="list-inside list-disc space-y-1 text-sm sm:text-base">
                    <li>✅ saber quanto tu vendeu</li>
                    <li>✅ saber quanto tu gastou</li>
                    <li>✅ saber se o mês foi bom ou ruim</li>
                    <li>✅ saber o que mais vende</li>
                    <li>✅ fazer orçamento rápido</li>
                    <li>✅ registrar vendas corretamente</li>
                    <li>✅ controlar pagamentos</li>
                    <li>✅ organizar despesas</li>
                    <li>✅ visualizar tudo em um painel</li>
                </ul>
            </section>

            <section class="space-y-3 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/40 dark:bg-amber-950/20">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">⚠️ Ponto importante (atual)</h2>
                <p class="text-sm leading-relaxed">Hoje o sistema:</p>
                <p class="text-sm font-medium">👉 ainda não lança automaticamente no caixa quando faz:</p>
                <ul class="list-inside list-disc space-y-1 text-sm">
                    <li>uma venda</li>
                    <li>uma despesa</li>
                </ul>
                <p class="text-sm leading-relaxed">Mas:</p>
                <ul class="list-inside list-disc space-y-1 text-sm">
                    <li>✔ já está preparado para isso</li>
                    <li>✔ isso será a próxima evolução</li>
                </ul>
            </section>

            <section class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">🚀 Próximos passos (evolução)</h2>
                <p class="leading-relaxed">O sistema ainda pode evoluir com:</p>
                <ul class="list-inside list-disc space-y-1 text-sm sm:text-base">
                    <li>integração automática com caixa</li>
                    <li>impressão de orçamento (PDF)</li>
                    <li>impressão de venda</li>
                    <li>relatórios mais avançados</li>
                    <li>controle de estoque</li>
                    <li>controle de lucro por item</li>
                </ul>
            </section>

            <footer class="border-t border-gray-200 pt-8 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">🧠 Resumo simples</h2>
                <p class="mt-3 leading-relaxed">Hoje tu já tem um sistema completo que:</p>
                <ul class="mt-2 list-inside list-disc space-y-1 text-sm sm:text-base">
                    <li>👉 organiza tua loja</li>
                    <li>👉 te dá controle financeiro</li>
                    <li>👉 te ajuda a vender melhor</li>
                    <li>👉 te mostra como está o teu negócio</li>
                </ul>
            </footer>
        </div>
    </div>
@endsection
