<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PontoController extends Controller
{
    public function index()
    {
        $pontos = DB::table('ponto')
            ->leftJoin('users', 'users.id', '=', 'ponto.id_apoio')
            ->select(
                'ponto.id',
                'ponto.nome',
                'ponto.id_apoio',
                'ponto.status',
                'ponto.passwd',
                'ponto.modo',
                'ponto.cadastro',
                'ponto.porcent_ponto',
                'users.name as cliente_nome'
            )
            ->orderBy('ponto.nome')
            ->paginate(20);

        return view('pontos.index', compact('pontos'));
    }
    public function create()
    {
        $clientes = User::where('nivel', User::NIVEL_CLIENTE)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        return view('pontos.create', compact('clientes'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'id_apoio' => 'required|integer|exists:users,id',
            'nome' => 'required|string|max:100',
            'passwd' => 'required|string|max:10',
            'porcent_ponto' => 'required|integer|min:0',
        ], [
            'id_apoio.required' => 'Selecione um cliente.',
            'nome.required' => 'Informe o nome do ponto.',
            'passwd.required' => 'Informe a senha para acesso ao Kiosk pela web.',
            'passwd.max' => 'A senha do Kiosk deve ter no máximo 10 caracteres.',
            'porcent_ponto.required' => 'Informe a porcentagem do ponto.',
        ]);

        DB::table('ponto')->insert([
            'nome' => $request->nome,
            'porcent_ponto' => $request->porcent_ponto,
            'cadastro' => now(),
            'id_apoio' => $request->id_apoio,
            'status' => true,
            'passwd' => $request->passwd,
            'modo' => false,
        ]);

        return redirect()
            ->route('pontos.index')
            ->with('success', 'Ponto cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $ponto = DB::table('ponto')->where('id', $id)->first();

        if (!$ponto) {
            abort(404);
        }

        $clientes = User::where('nivel', User::NIVEL_CLIENTE)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        return view('pontos.edit', compact('ponto', 'clientes'));
    }

    public function update(Request $request, $id)
    {
        $ponto = DB::table('ponto')->where('id', $id)->first();

        if (!$ponto) {
            abort(404);
        }

        $request->validate([
            'id_apoio' => 'required|integer|exists:users,id',
            'nome' => 'required|string|max:100',
            'passwd' => 'nullable|string|max:10',
            'porcent_ponto' => 'required|integer|min:0',
        ], [
            'id_apoio.required' => 'Selecione um cliente.',
            'nome.required' => 'Informe o nome do ponto.',
            'passwd.max' => 'A senha do Kiosk deve ter no máximo 10 caracteres.',
            'porcent_ponto.required' => 'Informe a porcentagem do ponto.',
        ]);

        $dados = [
            'nome' => $request->nome,
            'porcent_ponto' => $request->porcent_ponto,
            'id_apoio' => $request->id_apoio,
        ];

        if ($request->filled('passwd')) {
            $dados['passwd'] = $request->passwd;
        }

        DB::table('ponto')
            ->where('id', $id)
            ->update($dados);

        return redirect()
            ->route('pontos.index')
            ->with('success', 'Ponto atualizado com sucesso.');
    }

    public function toggleStatus($id)
    {
        $ponto = DB::table('ponto')->where('id', $id)->first();

        if (!$ponto) {
            abort(404);
        }

        $novoStatus = (int) $ponto->status === 1 ? 0 : 1;

        DB::table('ponto')
            ->where('id', $id)
            ->update([
                'status' => $novoStatus,
            ]);

        $mensagem = $novoStatus === 1
            ? 'Ponto ativado com sucesso.'
            : 'Ponto bloqueado com sucesso.';

        return redirect()
            ->route('pontos.index')
            ->with('success', $mensagem);
    }
}