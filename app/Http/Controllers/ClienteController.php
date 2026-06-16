<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = User::where('nivel', User::NIVEL_CLIENTE)
            ->orderBy('name')
            ->paginate(20);

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        $paises = [
            1 => 'Brasil',
            2 => 'Paraguai',
            3 => 'Argentina',
            4 => 'Uruguai',
        ];

        return view('clientes.create', compact('paises'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email',
            'porcentagem' => 'required|numeric',
            'id_pais' => 'required|integer',
            'username' => 'required|string|max:100|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required' => 'Informe o nome.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'porcentagem.required' => 'Informe a porcentagem de locação.',
            'id_pais.required' => 'Selecione o país.',
            'username.required' => 'Informe o login.',
            'username.unique' => 'Este login já está em uso.',
            'password.required' => 'Informe a senha.',
            'password.min' => 'A senha precisa ter pelo menos 6 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'porcentagem' => $request->porcentagem,
            'id_pais' => $request->id_pais,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'legacy_passwd' => null,

            'nivel' => User::NIVEL_CLIENTE,
            'id_apoio' => auth()->id(),
            'status' => 1,

            'fechar_faturas_ponto' => 0,
        ]);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $cliente = User::where('nivel', User::NIVEL_CLIENTE)
            ->findOrFail($id);

        $paises = [
            1 => 'Brasil',
            2 => 'Paraguai',
            3 => 'Argentina',
            4 => 'Uruguai',
        ];

        return view('clientes.edit', compact('cliente', 'paises'));
    }

    public function update(Request $request, $id)
    {
        $cliente = User::where('nivel', User::NIVEL_CLIENTE)
            ->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $cliente->id,
            'porcentagem' => 'required|numeric',
            'id_pais' => 'required|integer',
            'username' => 'required|string|max:100|unique:users,username,' . $cliente->id,
            'password' => 'nullable|string|min:6|confirmed',
        ], [
            'name.required' => 'Informe o nome.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'porcentagem.required' => 'Informe a porcentagem de locação.',
            'id_pais.required' => 'Selecione o país.',
            'username.required' => 'Informe o login.',
            'username.unique' => 'Este login já está em uso.',
            'password.min' => 'A senha precisa ter pelo menos 6 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
        ]);

        $dados = [
            'name' => $request->name,
            'email' => $request->email,
            'porcentagem' => $request->porcentagem,
            'id_pais' => $request->id_pais,
            'username' => $request->username,

            // Segurança: continua cliente.
            'nivel' => User::NIVEL_CLIENTE,
        ];

        if ($request->filled('password')) {
            $dados['password'] = Hash::make($request->password);
            $dados['legacy_passwd'] = null;
        }

        $cliente->update($dados);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente atualizado com sucesso.');
    }

    public function toggleStatus($id)
    {
        $cliente = User::where('nivel', User::NIVEL_CLIENTE)
            ->findOrFail($id);

        if (auth()->id() == $cliente->id) {
            return redirect()
                ->route('clientes.index')
                ->with('error', 'Você não pode bloquear o próprio usuário logado.');
        }

        $cliente->status = (int) $cliente->status === 1 ? 0 : 1;
        $cliente->save();

        $mensagem = (int) $cliente->status === 1
            ? 'Cliente ativado com sucesso.'
            : 'Cliente bloqueado com sucesso.';

        return redirect()
            ->route('clientes.index')
            ->with('success', $mensagem);
    }
}