<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Item;
use App\Models\Orcamento;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrcamentosController extends Controller
{
    public function index(Request $request)
    {
        $query = Orcamento::query()
            ->with(['cliente'])
            ->orderByDesc('data_orcamento')
            ->orderByDesc('id');

        $search = $request->string('search')->trim();
        $statusFiltro = $request->string('status')->trim()->toString();
        $dataInicio = $request->string('data_inicio')->trim()->toString();
        $dataFim = $request->string('data_fim')->trim()->toString();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                    ->orWhereHas('cliente', function ($cq) use ($search) {
                        $cq->where('nome', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $statusFiltro);
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_orcamento', '>=', $dataInicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('data_orcamento', '<=', $dataFim);
        }

        $orcamentos = $query
            ->paginate(10)
            ->withQueryString();

        return view('pages.orcamentos.index', [
            'title' => 'Orçamentos',
            'orcamentos' => $orcamentos,
            'search' => $search->toString(),
            'statusFiltro' => $statusFiltro,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
        ]);
    }

    public function create()
    {
        $catalog = $this->catalogoItensParaSelect();

        return view('pages.orcamentos.create', [
            'title' => 'Novo Orçamento',
            'clienteInicial' => $this->clienteInicialParaForm(),
            'catalog' => $catalog,
            'defaultLines' => old('itens', [
                [
                    'item_id' => '',
                    'descricao_item' => '',
                    'quantidade' => '1',
                    'valor_unitario' => '0',
                    'largura' => '',
                    'altura' => '',
                    'metragem' => '',
                    'observacoes' => '',
                ],
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $this->normalizeItensRequest($request);
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $numero = $this->gerarProximoNumero();
            $linhas = $this->montarLinhasPersistencia($data['itens']);
            $subtotal = round(array_sum(array_column($linhas, 'subtotal')), 2);
            $desconto = round((float) ($data['desconto'] ?? 0), 2);
            $total = max(0, round($subtotal - $desconto, 2));

            $orcamento = Orcamento::create([
                'cliente_id' => $data['cliente_id'] ?? null,
                'numero' => $numero,
                'data_orcamento' => $data['data_orcamento'],
                'status' => $data['status'],
                'subtotal' => $subtotal,
                'desconto' => $desconto,
                'total' => $total,
                'validade_dias' => (int) $data['validade_dias'],
                'observacoes' => $data['observacoes'] ?? null,
            ]);

            foreach ($linhas as $linha) {
                $orcamento->itens()->create($linha);
            }
        });

        return redirect()
            ->route('orcamentos.index')
            ->with('success', 'Orçamento criado com sucesso.');
    }

    public function show(Orcamento $orcamento)
    {
        $orcamento->load(['cliente', 'itens.item']);

        return view('pages.orcamentos.show', [
            'title' => 'Orçamento '.$orcamento->numero,
            'orcamento' => $orcamento,
        ]);
    }

    public function pdf(Orcamento $orcamento): Response
    {
        $orcamento->load(['cliente', 'itens.item']);

        $logoPath = public_path((string) config('empresa.logo_pdf'));
        $logo = $this->logoAssetForPdf($logoPath);

        $cliente = $orcamento->cliente;
        $clienteContato = implode("\n", array_filter([
            $cliente?->telefone,
            $cliente?->email,
            $cliente?->cpf_cnpj,
        ]));

        $dataValidade = $orcamento->data_orcamento
            ->copy()
            ->addDays(max(1, (int) $orcamento->validade_dias))
            ->format('d/m/Y');

        $safeFile = 'orcamento-'.preg_replace('/[^A-Za-z0-9._-]+/', '-', $orcamento->numero).'.pdf';

        return Pdf::loadView('pdf.orcamento', [
            'orcamento' => $orcamento,
            'logoSvgInline' => $logo !== null ? ($logo['inline_svg'] ?? null) : null,
            'logoDataUri' => $logo !== null ? $logo['data_uri'] : null,
            'logoImgWidth' => $logo !== null ? $logo['width_px'] : null,
            'logoImgHeight' => $logo !== null ? $logo['height_px'] : null,
            'empresaNome' => (string) config('empresa.nome'),
            'empresaEndereco' => trim((string) config('empresa.endereco')),
            'empresaTelefone' => trim((string) config('empresa.telefone')),
            'empresaEmail' => trim((string) config('empresa.email')),
            'clienteContato' => $clienteContato,
            'dataEmissao' => $orcamento->data_orcamento->format('d/m/Y H:i'),
            'dataValidade' => $dataValidade,
            'geradoEm' => now()->format('d/m/Y H:i'),
        ])
            ->setPaper('a4')
            ->stream($safeFile);
    }

    /**
     * SVG é embutido no HTML: o DomPDF tende a rasterizar SVG em img pequeno dentro da
     * caixa grande. PNG/JPEG seguem em data URI no img.
     *
     * @return array{inline_svg: string|null, data_uri: string|null, width_px: int, height_px: int|null}|null
     */
    private function logoAssetForPdf(string $path): ?array
    {
        if ($path === '' || ! is_readable($path)) {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        // DomPDF incorpora JPEG sem GD; PNG (e transparência) exige imagecreatefrompng().
        if ($ext === 'png' && ! function_exists('imagecreatefrompng')) {
            foreach (['.jpg', '.jpeg', '.JPG', '.JPEG'] as $suffix) {
                $alt = (string) preg_replace('/\.png$/i', $suffix, $path);
                if ($alt !== $path && is_readable($alt)) {
                    $path = $alt;
                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    break;
                }
            }
        }

        if ($ext === 'png' && ! function_exists('imagecreatefrompng')) {
            throw new \RuntimeException(
                'O PDF com logo em PNG precisa da extensão PHP GD. No Arch Linux: sudo pacman -S php-gd (reinicie o PHP-FPM / servidor). '.
                'Sem instalar pacotes: converta a marca para JPEG e use EMPRESA_LOGO_PDF=images/logo/seu_arquivo.jpg, '.
                'ou coloque um .jpg ao lado do .png com o mesmo nome (ex.: logo_print.jpg junto de logo_print.png).'
            );
        }

        $mime = match ($ext) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => null,
        };

        if ($mime === null) {
            return null;
        }

        $binary = @file_get_contents($path);
        if ($binary === false) {
            return null;
        }

        $targetW = max(48, min(720, (int) config('empresa.logo_pdf_width_px', 300)));
        $targetH = null;

        if ($ext === 'svg') {
            $ratio = $this->svgIntrinsicAspectRatio($binary);
            $targetH = $ratio !== null
                ? max(1, (int) round($targetW * $ratio))
                : max(1, (int) round($targetW * 0.25));
            $binary = $this->svgWithRasterDisplaySize($binary, $targetW, $targetH);
            $binary = $this->svgStripXmlProlog($binary);

            return [
                'inline_svg' => $binary,
                'data_uri' => null,
                'width_px' => $targetW,
                'height_px' => $targetH,
            ];
        }

        if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true)) {
            $dims = @getimagesizefromstring($binary);
            if (is_array($dims) && $dims[0] > 0) {
                $targetH = (int) max(1, round($targetW * ($dims[1] / $dims[0])));
            }
        }

        return [
            'inline_svg' => null,
            'data_uri' => 'data:'.$mime.';base64,'.base64_encode($binary),
            'width_px' => $targetW,
            'height_px' => $targetH,
        ];
    }

    private function svgStripXmlProlog(string $svg): string
    {
        $svg = preg_replace('/^\xEF\xBB\xBF/', '', $svg) ?? $svg;
        $svg = preg_replace('/<\?xml[^?]*\?>\s*/i', '', $svg) ?? $svg;

        return trim($svg);
    }

    private function svgIntrinsicAspectRatio(string $svg): ?float
    {
        if (preg_match('/viewBox="\s*([\d.\-+eE\s]+)\s*"/i', $svg, $m)) {
            $parts = preg_split('/\s+/', trim($m[1]));
            if (count($parts) === 4) {
                $w = (float) $parts[2];
                $h = (float) $parts[3];
                if ($w > 0) {
                    return $h / $w;
                }
            }
        }

        if (preg_match('/<svg\b[^>]{0,1200}>/is', $svg, $tag)) {
            $t = $tag[0];
            if (preg_match('/\bwidth="([\d.]+)"/i', $t, $w) && preg_match('/\bheight="([\d.]+)"/i', $t, $h)) {
                $iw = (float) $w[1];
                $ih = (float) $h[1];
                if ($iw > 0) {
                    return $ih / $iw;
                }
            }
        }

        return null;
    }

    private function svgWithRasterDisplaySize(string $svg, int $width, int $height): string
    {
        $out = preg_replace_callback(
            '/<svg\b([^>]*)>/i',
            function (array $m) use ($width, $height): string {
                $inner = preg_replace('/\s(width|height)="[^"]*"/i', '', $m[1]);

                return '<svg '.ltrim($inner).' width="'.$width.'" height="'.$height.'">';
            },
            $svg,
            1
        );

        return is_string($out) ? $out : $svg;
    }

    public function edit(Orcamento $orcamento)
    {
        $orcamento->load(['itens']);
        $catalog = $this->catalogoItensParaSelect($orcamento);

        $persistedLines = $orcamento->itens->map(function ($li) {
            return [
                'item_id' => $li->item_id,
                'descricao_item' => $li->descricao_item,
                'quantidade' => (string) $li->quantidade,
                'valor_unitario' => (string) $li->valor_unitario,
                'largura' => $li->largura !== null ? (string) $li->largura : '',
                'altura' => $li->altura !== null ? (string) $li->altura : '',
                'metragem' => $li->metragem !== null ? (string) $li->metragem : '',
                'observacoes' => $li->observacoes ?? '',
            ];
        })->values()->all();

        $fallbackLinha = [
            'item_id' => '',
            'descricao_item' => '',
            'quantidade' => '1',
            'valor_unitario' => '0',
            'largura' => '',
            'altura' => '',
            'metragem' => '',
            'observacoes' => '',
        ];

        return view('pages.orcamentos.edit', [
            'title' => 'Editar Orçamento',
            'orcamento' => $orcamento,
            'clienteInicial' => $this->clienteInicialParaForm($orcamento),
            'catalog' => $catalog,
            'defaultLines' => old('itens', $persistedLines !== [] ? $persistedLines : [$fallbackLinha]),
        ]);
    }

    public function update(Request $request, Orcamento $orcamento)
    {
        $this->normalizeItensRequest($request);
        $data = $this->validated($request);

        DB::transaction(function () use ($data, $orcamento) {
            $linhas = $this->montarLinhasPersistencia($data['itens']);
            $subtotal = round(array_sum(array_column($linhas, 'subtotal')), 2);
            $desconto = round((float) ($data['desconto'] ?? 0), 2);
            $total = max(0, round($subtotal - $desconto, 2));

            $orcamento->cliente_id = $data['cliente_id'] ?? null;
            $orcamento->data_orcamento = $data['data_orcamento'];
            $orcamento->status = $data['status'];
            $orcamento->subtotal = $subtotal;
            $orcamento->desconto = $desconto;
            $orcamento->total = $total;
            $orcamento->validade_dias = (int) $data['validade_dias'];
            $orcamento->observacoes = $data['observacoes'] ?? null;
            $orcamento->save();

            $orcamento->itens()->delete();

            foreach ($linhas as $linha) {
                $orcamento->itens()->create($linha);
            }
        });

        return redirect()
            ->route('orcamentos.index')
            ->with('success', 'Orçamento atualizado com sucesso.');
    }

    public function destroy(Orcamento $orcamento)
    {
        $orcamento->delete();

        return redirect()
            ->route('orcamentos.index')
            ->with('success', 'Orçamento removido com sucesso.');
    }

    /**
     * @return array{id: int, nome: string}|null
     */
    private function clienteInicialParaForm(?Orcamento $orcamento = null): ?array
    {
        $cid = old('cliente_id', $orcamento?->cliente_id);
        if ($cid === null || $cid === '') {
            return null;
        }

        $cliente = Cliente::query()->find((int) $cid);
        if ($cliente === null) {
            return null;
        }

        return [
            'id' => $cliente->id,
            'nome' => $cliente->nome,
        ];
    }

    /**
     * Itens ativos + itens já vinculados ao orçamento (edição).
     *
     * @return Collection<int, array{id: int, nome: string, modo_preco: string, preco_venda: string}>
     */
    private function catalogoItensParaSelect(?Orcamento $orcamento = null)
    {
        $ativos = Item::query()
            ->where('ativo', true)
            ->orderBy('nome')
            ->get(['id', 'nome', 'modo_preco', 'preco_venda']);

        if ($orcamento === null) {
            return $ativos->map(fn (Item $i) => [
                'id' => $i->id,
                'nome' => $i->nome,
                'modo_preco' => $i->modo_preco,
                'preco_venda' => (string) $i->preco_venda,
            ])->values();
        }

        $idsExtras = $orcamento->itens->pluck('item_id')->filter()->unique()->values();
        $extras = $idsExtras->isEmpty()
            ? collect()
            : Item::query()->whereIn('id', $idsExtras)->get(['id', 'nome', 'modo_preco', 'preco_venda']);

        return $ativos
            ->concat($extras)
            ->unique('id')
            ->sortBy('nome', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->map(fn (Item $i) => [
                'id' => $i->id,
                'nome' => $i->nome,
                'modo_preco' => $i->modo_preco,
                'preco_venda' => (string) $i->preco_venda,
            ])
            ->values();
    }

    /**
     * @param  array<int, array<string, mixed>>  $itens
     * @return array<int, array<string, mixed>>
     */
    private function montarLinhasPersistencia(array $itens): array
    {
        $out = [];
        foreach ($itens as $row) {
            $q = round((float) $row['quantidade'], 2);
            $vu = round((float) $row['valor_unitario'], 2);
            $sub = round($q * $vu, 2);

            $out[] = [
                'item_id' => ! empty($row['item_id']) ? (int) $row['item_id'] : null,
                'descricao_item' => $row['descricao_item'],
                'quantidade' => $q,
                'valor_unitario' => $vu,
                'subtotal' => $sub,
                'largura' => $this->optionalDecimal($row['largura'] ?? null),
                'altura' => $this->optionalDecimal($row['altura'] ?? null),
                'metragem' => $this->optionalDecimal($row['metragem'] ?? null),
                'observacoes' => isset($row['observacoes']) && $row['observacoes'] !== '' ? $row['observacoes'] : null,
            ];
        }

        return $out;
    }

    private function optionalDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return round((float) $value, 2);
    }

    private function gerarProximoNumero(): string
    {
        $year = (int) now()->year;
        $prefix = sprintf('ORC-%d-', $year);
        $last = Orcamento::query()
            ->where('numero', 'like', $prefix.'%')
            ->orderByDesc('numero')
            ->lockForUpdate()
            ->first();

        if ($last === null) {
            return $prefix.str_pad('1', 5, '0', STR_PAD_LEFT);
        }

        $suffix = substr($last->numero, strlen($prefix));
        $next = (int) $suffix + 1;

        return $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    private function normalizeItensRequest(Request $request): void
    {
        if ($request->input('cliente_id') === '' || $request->input('cliente_id') === null) {
            $request->merge(['cliente_id' => null]);
        }

        if ($request->input('desconto') === '' || $request->input('desconto') === null) {
            $request->merge(['desconto' => '0']);
        } elseif (is_string($request->input('desconto'))) {
            $request->merge(['desconto' => str_replace(',', '.', $request->input('desconto'))]);
        }

        $itens = $request->input('itens', []);
        if (! is_array($itens)) {
            return;
        }

        $normalized = [];
        foreach ($itens as $row) {
            if (! is_array($row)) {
                continue;
            }
            $itemId = $row['item_id'] ?? null;
            if ($itemId === '' || $itemId === null) {
                $row['item_id'] = null;
            } else {
                $row['item_id'] = (int) $itemId;
            }

            foreach (['quantidade', 'valor_unitario', 'largura', 'altura', 'metragem'] as $campo) {
                if (isset($row[$campo]) && is_string($row[$campo])) {
                    $row[$campo] = str_replace(',', '.', $row[$campo]);
                }
            }

            $normalized[] = $row;
        }

        $request->merge(['itens' => $normalized]);
    }

    private function validated(Request $request): array
    {
        return $request->validate(
            [
                'cliente_id' => ['nullable', 'integer', 'exists:clientes,id'],
                'data_orcamento' => ['required', 'date'],
                'status' => ['required', 'in:aberto,aprovado,recusado,vencido,convertido'],
                'desconto' => ['nullable', 'numeric', 'min:0'],
                'validade_dias' => ['required', 'integer', 'min:1', 'max:3650'],
                'observacoes' => ['nullable', 'string'],
                'itens' => ['required', 'array', 'min:1'],
                'itens.*.item_id' => ['nullable', 'integer', 'exists:itens,id'],
                'itens.*.descricao_item' => ['required', 'string', 'max:255'],
                'itens.*.quantidade' => ['required', 'numeric', 'min:0.01'],
                'itens.*.valor_unitario' => ['required', 'numeric', 'min:0'],
                'itens.*.largura' => ['nullable', 'numeric', 'min:0'],
                'itens.*.altura' => ['nullable', 'numeric', 'min:0'],
                'itens.*.metragem' => ['nullable', 'numeric', 'min:0'],
                'itens.*.observacoes' => ['nullable', 'string'],
            ],
            $this->validationMessages(),
            $this->validationAttributes()
        );
    }

    private function validationMessages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'string' => 'O campo :attribute deve ser um texto válido.',
            'numeric' => 'O campo :attribute deve ser um número válido.',
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            'min' => 'O campo :attribute deve ser no mínimo :min.',
            'max' => 'O campo :attribute não pode ser maior que :max.',
            'date' => 'Informe uma data válida.',
            'exists' => 'O :attribute selecionado é inválido.',
            'in' => 'O valor selecionado para :attribute é inválido.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'cliente_id' => 'cliente',
            'data_orcamento' => 'data do orçamento',
            'status' => 'status',
            'desconto' => 'desconto',
            'validade_dias' => 'validade (dias)',
            'observacoes' => 'observações',
            'itens' => 'itens',
            'itens.*.item_id' => 'item cadastrado',
            'itens.*.descricao_item' => 'descrição do item',
            'itens.*.quantidade' => 'quantidade',
            'itens.*.valor_unitario' => 'valor unitário',
            'itens.*.largura' => 'largura',
            'itens.*.altura' => 'altura',
            'itens.*.metragem' => 'metragem',
            'itens.*.observacoes' => 'observações do item',
        ];
    }
}
