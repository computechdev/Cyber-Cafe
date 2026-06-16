<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $nivel = $request->get('nivel');

        $usuarios = User::query()
            ->when($nivel, function ($query) use ($nivel) {
                $query->where('nivel', $nivel);
            })
            ->orderBy('name')
            ->paginate(20);

        return view('users.index', compact('usuarios', 'nivel'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:users,username',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'nivel' => 'required|integer|in:1,2,3,4,5,6',
            'status' => 'required|integer|in:0,1',
            'porcentagem' => 'nullable|numeric',
            'id_apoio' => 'nullable|integer',
            'id_pais' => 'nullable|integer',
            'validade' => 'nullable|date',
            'afiliado' => 'nullable|integer',
            'fechar_faturas_ponto' => 'nullable|integer|in:0,1',
        ], [
            'name.required' => 'Informe o nome.',
            'username.required' => 'Informe o usuário.',
            'username.unique' => 'Este usuário já está em uso.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'password.required' => 'Informe a senha.',
            'password.min' => 'A senha precisa ter pelo menos 6 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'nivel.required' => 'Informe o nível do usuário.',
            'status.required' => 'Informe o status.',
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'legacy_passwd' => '',
            'nivel' => $request->nivel,
            'status' => $request->status,
            'porcentagem' => $request->porcentagem,
            'id_apoio' => 0,
            'id_pais' => 0,
            'validade' => 0,
            'afiliado' => $request->afiliado,
            'fechar_faturas_ponto' => $request->fechar_faturas_ponto ?? 0,
        ]);



        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $usuario = User::findOrFail($id);

        return view('users.edit', compact('usuario'));
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:users,username,' . $usuario->id,
            'email' => 'nullable|email|max:255|unique:users,email,' . $usuario->id,
            'password' => 'nullable|string|min:6|confirmed',
            'nivel' => 'required|integer|in:1,2,3,4,5,6',
            'status' => 'required|integer|in:0,1',
            'porcentagem' => 'nullable|numeric',
            'id_apoio' => 'nullable|integer',
            'id_pais' => 'nullable|integer',
            'validade' => 'nullable|date',
            'afiliado' => 'nullable|integer',
            'fechar_faturas_ponto' => 'nullable|integer|in:0,1',
        ], [
            'name.required' => 'Informe o nome.',
            'username.required' => 'Informe o usuário.',
            'username.unique' => 'Este usuário já está em uso.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'password.min' => 'A senha precisa ter pelo menos 6 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'nivel.required' => 'Informe o nível do usuário.',
            'status.required' => 'Informe o status.',
        ]);

        $dados = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'nivel' => $request->nivel,
            'status' => $request->status,
            'porcentagem' => $request->porcentagem,
            'id_apoio' => 0,
            'id_pais' => 0,
            'validade' => 0,
            'afiliado' => $request->afiliado,
            'fechar_faturas_ponto' => $request->fechar_faturas_ponto ?? 0,
        ];

        if ($request->filled('password')) {
            $dados['password'] = Hash::make($request->password);
            $dados['legacy_passwd'] = null;
        }

        $usuario->update($dados);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    public function toggleStatus($id)
    {
        $usuario = User::findOrFail($id);

        if (auth()->id() == $usuario->id) {
            return redirect()
                ->route('usuarios.index')
                ->with('error', 'Você não pode bloquear o próprio usuário logado.');
        }

        $usuario->status = (int) $usuario->status === 1 ? 0 : 1;
        $usuario->save();

        $mensagem = (int) $usuario->status === 1
            ? 'Usuário ativado com sucesso.'
            : 'Usuário bloqueado com sucesso.';

        return redirect()
            ->route('usuarios.index')
            ->with('success', $mensagem);
    }
}