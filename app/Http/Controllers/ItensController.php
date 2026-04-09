<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Item;
use Illuminate\Http\Request;

class ItensController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::query()
            ->with('categoria')
            ->orderByDesc('id');

        $search = $request->string('search')->trim();
        $categoriaId = $request->string('categoria_id')->trim()->toString();
        $tipo = $request->string('tipo')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        if ($request->filled('search')) {
            $query->where('nome', 'like', "%{$search}%");
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $categoriaId);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $tipo);
        }

        if ($request->filled('status')) {
            $query->where('ativo', $status === 'ativo');
        }

        $itens = $query
            ->paginate(10)
            ->withQueryString();
        $categorias = Categoria::query()
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return view('pages.itens.index', [
            'title' => 'Itens',
            'itens' => $itens,
            'categorias' => $categorias,
            'search' => $search->toString(),
            'categoriaId' => $categoriaId,
            'tipo' => $tipo,
            'status' => $status,
        ]);
    }

    public function create()
    {
        $categorias = Categoria::query()
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return view('pages.itens.create', [
            'title' => 'Novo Item',
            'categorias' => $categorias,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
                'tipo' => ['required', 'in:produto,servico'],
                'nome' => ['required', 'string', 'max:255'],
                'descricao' => ['nullable', 'string'],
                'unidade_medida' => ['required', 'string', 'max:50'],
                'modo_preco' => ['required', 'in:fixo,quantidade,metro,personalizado'],
                'preco_venda' => ['required', 'numeric', 'min:0'],
                'custo_estimado' => ['nullable', 'numeric', 'min:0'],
                'ativo' => ['nullable', 'boolean'],
            ],
            $this->validationMessages(),
            $this->validationAttributes()
        );

        Item::create([
            'categoria_id' => (int) $data['categoria_id'],
            'tipo' => $data['tipo'],
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'unidade_medida' => $data['unidade_medida'],
            'modo_preco' => $data['modo_preco'],
            'preco_venda' => $data['preco_venda'],
            'custo_estimado' => $data['custo_estimado'] ?? null,
            'ativo' => $request->boolean('ativo', true),
        ]);

        return redirect()
            ->route('itens.index')
            ->with('success', 'Item criado com sucesso.');
    }

    public function edit(Item $iten)
    {
        $categorias = Categoria::query()
            ->where('ativo', true)
            ->orWhere('id', $iten->categoria_id)
            ->orderBy('nome')
            ->get();

        return view('pages.itens.edit', [
            'title' => 'Editar Item',
            'item' => $iten,
            'categorias' => $categorias,
        ]);
    }

    public function update(Request $request, Item $iten)
    {
        $data = $request->validate(
            [
                'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
                'tipo' => ['required', 'in:produto,servico'],
                'nome' => ['required', 'string', 'max:255'],
                'descricao' => ['nullable', 'string'],
                'unidade_medida' => ['required', 'string', 'max:50'],
                'modo_preco' => ['required', 'in:fixo,quantidade,metro,personalizado'],
                'preco_venda' => ['required', 'numeric', 'min:0'],
                'custo_estimado' => ['nullable', 'numeric', 'min:0'],
                'ativo' => ['nullable', 'boolean'],
            ],
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $iten->categoria_id = (int) $data['categoria_id'];
        $iten->tipo = $data['tipo'];
        $iten->nome = $data['nome'];
        $iten->descricao = $data['descricao'] ?? null;
        $iten->unidade_medida = $data['unidade_medida'];
        $iten->modo_preco = $data['modo_preco'];
        $iten->preco_venda = $data['preco_venda'];
        $iten->custo_estimado = $data['custo_estimado'] ?? null;
        $iten->ativo = $request->boolean('ativo');
        $iten->save();

        return redirect()
            ->route('itens.index')
            ->with('success', 'Item atualizado com sucesso.');
    }

    public function destroy(Item $iten)
    {
        $iten->delete();

        return redirect()
            ->route('itens.index')
            ->with('success', 'Item removido com sucesso.');
    }

    public function toggleStatus(Request $request, Item $iten)
    {
        $data = $request->validate(
            [
                'ativo' => ['required', 'boolean'],
            ],
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $iten->ativo = (bool) $data['ativo'];
        $iten->save();

        $mensagem = $iten->ativo
            ? 'Item ativado com sucesso.'
            : 'Item desativado com sucesso.';

        return redirect()
            ->route('itens.index', $request->only(['search', 'categoria_id', 'tipo', 'status']))
            ->with('success', $mensagem);
    }

    private function validationMessages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'string' => 'O campo :attribute deve ser um texto válido.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
            'numeric' => 'O campo :attribute deve ser numérico.',
            'min' => 'O campo :attribute deve ser no mínimo :min.',
            'exists' => 'O :attribute selecionado é inválido.',
            'in' => 'O valor selecionado para :attribute é inválido.',
            'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'categoria_id' => 'categoria',
            'tipo' => 'tipo',
            'nome' => 'nome',
            'descricao' => 'descrição',
            'unidade_medida' => 'unidade de medida',
            'modo_preco' => 'modo de preço',
            'preco_venda' => 'preço de venda',
            'custo_estimado' => 'custo estimado',
            'ativo' => 'status',
        ];
    }
}
