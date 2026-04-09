<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->orderByDesc('id');

        $search = $request->string('search')->trim();
        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query
            ->paginate(10)
            ->withQueryString();

        return view('pages.users.index', [
            'title' => 'Usuários',
            'users' => $users,
            'search' => $search->toString(),
        ]);
    }

    public function create()
    {
        return view('pages.users.create', [
            'title' => 'Novo Usuário',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'password_confirmation' => ['required', 'string', 'min:8'],
            ],
            $this->validationMessages(),
            $this->validationAttributes()
        );

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário criado com sucesso.');
    }

    public function edit(User $usuario)
    {
        return view('pages.users.edit', [
            'title' => 'Editar Usuário',
            'user' => $usuario,
        ]);
    }

    public function update(Request $request, User $usuario)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$usuario->id],
        ];

        // Só valida senha se o usuário preencher.
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
            $rules['password_confirmation'] = ['required', 'string', 'min:8'];
        }

        $data = $request->validate(
            $rules,
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $usuario->name = $data['name'];
        $usuario->email = $data['email'];

        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->string('password')->toString());
        }

        $usuario->save();

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy(Request $request, User $usuario)
    {
        // Evita excluir o usuário atualmente autenticado.
        if ($request->user()->id === $usuario->id) {
            return redirect()
                ->route('usuarios.index')
                ->with('error', 'Não é possível excluir o usuário autenticado.');
        }

        $usuario->delete();

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário excluído com sucesso.');
    }

    public function updatePassword(Request $request, User $usuario)
    {
        $data = $request->validate(
            [
                'new_password' => ['required', 'string', 'min:8', 'confirmed'],
                'new_password_confirmation' => ['required', 'string', 'min:8'],
            ],
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $usuario->password = Hash::make($data['new_password']);
        $usuario->save();

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Senha atualizada com sucesso.');
    }

    private function validationMessages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'string' => 'O campo :attribute deve ser um texto válido.',
            'email' => 'Informe um e-mail válido.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
            'min' => 'O campo :attribute deve ter no mínimo :min caracteres.',
            'unique' => 'Este :attribute já está em uso.',
            'confirmed' => 'A confirmação do campo :attribute não confere.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'e-mail',
            'password' => 'senha',
            'password_confirmation' => 'confirmação de senha',
            'new_password' => 'nova senha',
            'new_password_confirmation' => 'confirmação da nova senha',
        ];
    }
}
