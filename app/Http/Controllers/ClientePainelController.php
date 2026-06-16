<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Hash;
class ClientePainelController extends Controller
{
    public function painelTeste(Request $request)
    {
        $usuario = auth()->user();

        $aba = $request->get('aba', 'contabilidade');
        $subAbaMovimentos = $request->get('subaba', 'resumo');

        /*
        |--------------------------------------------------------------------------
        | Datas do filtro
        |--------------------------------------------------------------------------
        | Essas datas continuam sendo usadas nas tabelas.
        | A Conta corrente NÃO depende delas.
        */
        $hoje = \Carbon\Carbon::now('America/Sao_Paulo');

        $dataInicial = $request->get('data_inicial') ?: $hoje->copy()->format('Y-m-d');
        $dataFinal = $request->get('data_final') ?: $hoje->copy()->format('Y-m-d');

        /*
        |--------------------------------------------------------------------------
        | Tablets do cliente logado
        |--------------------------------------------------------------------------
        */
        $idprodsCliente = DB::table('tablet')
            ->where('id_apoio', $usuario->id)
            ->pluck('idprod');

        /*
        |--------------------------------------------------------------------------
        | Resumo filtrado por data
        |--------------------------------------------------------------------------
        | Esse resumo é o da tabela principal da aba Contabilidade.
        */
        $resumoFiltrado = DB::table('transacoes')
            ->whereIn('idprod', $idprodsCliente)
            ->whereDate('data_hora', '>=', $dataInicial)
            ->whereDate('data_hora', '<=', $dataFinal)
            ->selectRaw("
            COALESCE(SUM(CASE WHEN tipo = 1 THEN valor ELSE 0 END), 0) as entradas,
            COALESCE(SUM(CASE WHEN tipo = 2 THEN valor ELSE 0 END), 0) as saidas
        ")
            ->first();

        $entradas = (float) ($resumoFiltrado->entradas ?? 0);
        $saidas = (float) ($resumoFiltrado->saidas ?? 0);
        $diferenca = $entradas - $saidas;

        /*
        |--------------------------------------------------------------------------
        | Conta corrente em aberto
        |--------------------------------------------------------------------------
        | Aqui está a correção principal:
        | NÃO USA data_inicial nem data_final.
        |
        | Mostra o saldo total de tudo que ainda não foi fechado.
        */
        $contaCorrente = DB::table('metricas')
            ->join('tablet', 'tablet.idprod', '=', 'metricas.idprod')
            ->where('tablet.id_apoio', $usuario->id)
            ->where('metricas.ativo', true)
            ->where('metricas.status_leitura', true)
            ->selectRaw('
        COALESCE(SUM(metricas.entrada), 0) as entrada_total,
        COALESCE(SUM(metricas.saida), 0) as saida_total,
        COALESCE(SUM(metricas.saldo_total), 0) as saldo_total
    ')
            ->first();
        $resumo = [
            'usuario' => $usuario->username ?? $usuario->name,

            /*
            |--------------------------------------------------------------------------
            | Esses respeitam o filtro de data
            |--------------------------------------------------------------------------
            */
            'entradas' => $entradas,
            'saidas' => $saidas,
            'diferenca' => $diferenca,

            /*
            |--------------------------------------------------------------------------
            | Conta corrente em aberto
            |--------------------------------------------------------------------------
            | Não depende do filtro de data.
            */
            'conta_entrada' => (float) ($contaCorrente->entrada_total ?? 0),
            'conta_saida' => (float) ($contaCorrente->saida_total ?? 0),
            'conta_saldo' => (float) ($contaCorrente->saldo_total ?? 0),
        ];

        /*
        |--------------------------------------------------------------------------
        | Movimentos - Resumo
        |--------------------------------------------------------------------------
        */
        $movimentos = DB::table('transacoes')
            ->whereIn('idprod', $idprodsCliente)
            ->whereDate('data_hora', '>=', $dataInicial)
            ->whereDate('data_hora', '<=', $dataFinal)
            ->selectRaw("
            DATE(data_hora) as data_movimento,
            COALESCE(SUM(CASE WHEN tipo = 1 THEN valor ELSE 0 END), 0) as entradas,
            COALESCE(SUM(CASE WHEN tipo = 2 THEN valor ELSE 0 END), 0) as saidas,
            COALESCE(SUM(CASE WHEN tipo = 1 THEN valor ELSE 0 END), 0)
            -
            COALESCE(SUM(CASE WHEN tipo = 2 THEN valor ELSE 0 END), 0) as diferenca
        ")
            ->groupByRaw('DATE(data_hora)')
            ->orderByRaw('DATE(data_hora)')
            ->get()
            ->map(function ($movimento) {
                $entradas = (float) $movimento->entradas;
                $saidas = (float) $movimento->saidas;

                $movimento->porcentagem = $entradas > 0
                    ? ($saidas / $entradas) * 100
                    : 0;

                return $movimento;
            });

        /*
        |--------------------------------------------------------------------------
        | Movimentos - Detalhe
        |--------------------------------------------------------------------------
        */
        $movimentosDetalhe = DB::table('transacoes')
            ->whereIn('idprod', $idprodsCliente)
            ->whereDate('data_hora', '>=', $dataInicial)
            ->whereDate('data_hora', '<=', $dataFinal)
            ->select(
                'id',
                'idprod',
                'data_hora',
                'tipo',
                'valor'
            )
            ->orderBy('data_hora')
            ->paginate(10, ['*'], 'detalhe_page');

        $movimentosDetalhe->getCollection()->transform(function ($detalhe) {
            $valor = (float) ($detalhe->valor ?? 0);

            $detalhe->entrada = 0;
            $detalhe->saida = 0;

            if ((int) $detalhe->tipo === 1) {
                $detalhe->entrada = $valor;
                $detalhe->tipo_nome = 'Bilhete';
            } elseif ((int) $detalhe->tipo === 2) {
                $detalhe->saida = $valor;
                $detalhe->tipo_nome = 'Cheque de brinde';
            } else {
                $detalhe->tipo_nome = 'Outros';
            }

            return $detalhe;
        });

        /*
        |--------------------------------------------------------------------------
        | Itens
        |--------------------------------------------------------------------------
        | Por enquanto mostra os tablets do cliente.
        | Depois ligamos com a imagem/jogada da Unity.
        */
        $ultimaLeituraSub = DB::table('leitura')
            ->select('idprod', DB::raw('MAX(id) as ultimo_id'))
            ->groupBy('idprod');

        $itens = DB::table('tablet')
            ->leftJoinSub($ultimaLeituraSub, 'ultima_leitura', function ($join) {
                $join->on('ultima_leitura.idprod', '=', 'tablet.idprod');
            })
            ->leftJoin('leitura', 'leitura.id', '=', 'ultima_leitura.ultimo_id')
            ->select(
                'tablet.id',
                'tablet.idprod',
                'tablet.idprod as jogo_id',
                'tablet.ultimo_contato',
                'leitura.creditos'
            )
            ->where('tablet.id_apoio', $usuario->id)
            ->where('tablet.ativo', true)
            ->orderBy('tablet.idprod')
            ->paginate(10, ['*'], 'itens_page');

        $itens->getCollection()->transform(function ($item) use ($hoje) {
            $creditos = $this->normalizarCreditoPainelCliente($item->creditos ?? 0);

            $item->credito_texto = 'Créditos: ' . number_format($creditos, 2, ',', '.');
            $item->fecha = $hoje->copy()->format('d/m/Y') . ' 23:59';

            $item->total_1 = '0,00';
            $item->total_2 = '0,00';
            $item->total_3 = '0,00';

            return $item;
        });

        $faturasPendentes = DB::table('cobranca_agregado')
            ->where('id_cliente', $usuario->id)
            ->where('ativo', 1)
            ->where('cobranca_fechada', 1)
            ->where('pago', 0)
            ->orderByDesc('id_cobranca')
            ->get();

        return view('clientes.painel-teste', compact(
            'usuario',
            'aba',
            'subAbaMovimentos',
            'dataInicial',
            'dataFinal',
            'resumo',
            'movimentos',
            'movimentosDetalhe',
            'itens',
            'faturasPendentes'
        ));
    }

    private function buscarResumoContabilidade($idCliente, $dataInicial, $dataFinal)
    {
        $entrada = DB::table('transacoes')
            ->join('tablet', 'tablet.idprod', '=', 'transacoes.idprod')
            ->where('tablet.id_apoio', $idCliente)
            ->where('transacoes.tipo', 1)
            ->whereDate('transacoes.data_hora', '>=', $dataInicial)
            ->whereDate('transacoes.data_hora', '<=', $dataFinal)
            ->sum('transacoes.valor');

        $saida = DB::table('transacoes')
            ->join('tablet', 'tablet.idprod', '=', 'transacoes.idprod')
            ->where('tablet.id_apoio', $idCliente)
            ->where('transacoes.tipo', 2)
            ->whereDate('transacoes.data_hora', '>=', $dataInicial)
            ->whereDate('transacoes.data_hora', '<=', $dataFinal)
            ->sum('transacoes.valor');

        $entrada = (float) $entrada;
        $saida = (float) $saida;
        $saldo = $entrada - $saida;

        return [
            'usuario' => auth()->user()->username ?? auth()->user()->name,
            'entradas' => $entrada,
            'saidas' => $saida,
            'diferenca' => $saldo,
            'conta_entrada' => $entrada,
            'conta_saida' => $saida,
            'conta_saldo' => $saldo,
        ];
    }

    private function buscarMovimentosPorTablet($idCliente, $dataInicial, $dataFinal)
    {
        return DB::table('transacoes')
            ->join('tablet', 'tablet.idprod', '=', 'transacoes.idprod')
            ->leftJoin('ponto', 'ponto.id', '=', 'tablet.id_ponto')
            ->select(
                'tablet.idprod',
                'tablet.cliente',
                'ponto.nome as ponto_nome',
                DB::raw('SUM(CASE WHEN transacoes.tipo = 1 THEN transacoes.valor ELSE 0 END) as entradas'),
                DB::raw('SUM(CASE WHEN transacoes.tipo = 2 THEN transacoes.valor ELSE 0 END) as saidas'),
                DB::raw('DATE(transacoes.data_hora) as data_movimento')
            )
            ->where('tablet.id_apoio', $idCliente)
            ->whereDate('transacoes.data_hora', '>=', $dataInicial)
            ->whereDate('transacoes.data_hora', '<=', $dataFinal)
            ->groupBy(
                'tablet.idprod',
                'tablet.cliente',
                'ponto.nome',
                DB::raw('DATE(transacoes.data_hora)')
            )
            ->orderByDesc('data_movimento')
            ->orderBy('tablet.idprod')
            ->get()
            ->map(function ($movimento) {
                $movimento->entradas = (float) $movimento->entradas;
                $movimento->saidas = (float) $movimento->saidas;
                $movimento->diferenca = $movimento->entradas - $movimento->saidas;

                $movimento->porcentagem = $movimento->entradas > 0
                    ? ($movimento->saidas / $movimento->entradas) * 100
                    : 0;

                return $movimento;
            });
    }

    private function buscarDetalheMovimentos($idCliente, $dataInicial, $dataFinal)
    {
        $paginado = DB::table('transacoes')
            ->join('tablet', 'tablet.idprod', '=', 'transacoes.idprod')
            ->select(
                'transacoes.id',
                'transacoes.tipo',
                'transacoes.valor',
                'transacoes.idprod',
                'transacoes.data_hora',
                'tablet.cliente'
            )
            ->where('tablet.id_apoio', $idCliente)
            ->whereDate('transacoes.data_hora', '>=', $dataInicial)
            ->whereDate('transacoes.data_hora', '<=', $dataFinal)
            ->orderByDesc('transacoes.data_hora')
            ->paginate(10, ['*'], 'detalhe_page');

        $paginado->getCollection()->transform(function ($transacao) {
            $transacao->entrada = (int) $transacao->tipo === 1
                ? (float) $transacao->valor
                : 0;

            $transacao->saida = (int) $transacao->tipo === 2
                ? (float) $transacao->valor
                : 0;

            if ((int) $transacao->tipo === 1) {
                $transacao->tipo_nome = 'Bilhete';
            } elseif ((int) $transacao->tipo === 2) {
                $transacao->tipo_nome = 'Cheque de brinde';
            } else {
                $transacao->tipo_nome = '-';
            }

            return $transacao;
        });

        return $paginado->appends(request()->query());
    }

    private function buscarItensDosTablets($idCliente, $dataInicial, $dataFinal)
    {
        $paginado = DB::table('tablet')
            ->leftJoin('ponto', 'ponto.id', '=', 'tablet.id_ponto')
            ->select(
                'tablet.id',
                'tablet.idprod',
                'tablet.cliente',
                'tablet.ultimo_contato',
                'tablet.status',
                'tablet.ligado',
                'tablet.ativo',
                'ponto.nome as ponto_nome'
            )
            ->where('tablet.id_apoio', $idCliente)
            ->orderBy('tablet.idprod')
            ->paginate(10, ['*'], 'itens_page');

        $paginado->getCollection()->transform(function ($tablet) use ($dataFinal) {
            $tablet->jogo_id = $tablet->idprod;

            $tablet->fecha = \Carbon\Carbon::parse($dataFinal . ' 23:59:00')
                ->format('d/m/y H:i:s');

            $tablet->credito_texto = 'Cr 0 -> 0';

            $tablet->total_1 = 0;
            $tablet->total_2 = 0;
            $tablet->total_3 = 0;

            return $tablet;
        });

        return $paginado->appends(request()->query());
    }

    public function creditoJogadorRealtime(Request $request)
    {
        $usuario = auth()->user();

        $idprod = strtoupper(trim($request->get('idprod', '')));

        if (empty($idprod)) {
            return response()->json([
                'success' => false,
                'message' => 'Tablet não informado.',
            ]);
        }

        $ultimaLeituraSub = DB::table('leitura')
            ->select('idprod', DB::raw('MAX(id) as ultimo_id'))
            ->groupBy('idprod');

        $tablet = DB::table('tablet')
            ->leftJoinSub($ultimaLeituraSub, 'ultima_leitura', function ($join) {
                $join->on('ultima_leitura.idprod', '=', 'tablet.idprod');
            })
            ->leftJoin('leitura', 'leitura.id', '=', 'ultima_leitura.ultimo_id')
            ->leftJoin('jogador', 'jogador.idprod', '=', 'tablet.idprod')
            ->select(
                'tablet.idprod',
                'tablet.id_apoio',
                'leitura.creditos as creditos_leitura',
                'jogador.creditos as creditos_jogador'
            )
            ->where('tablet.idprod', $idprod)
            ->where('tablet.id_apoio', $usuario->id)
            ->first();

        if (!$tablet) {
            return response()->json([
                'success' => false,
                'message' => 'Tablet não encontrado para este cliente.',
            ]);
        }

        $creditosJogador = $this->normalizarCreditoPainelCliente($tablet->creditos_jogador);
        $creditosLeitura = $this->normalizarCreditoPainelCliente($tablet->creditos_leitura);

        /*
        |--------------------------------------------------------------------------
        | Prioridade
        |--------------------------------------------------------------------------
        | Se existir crédito na tabela jogador, usa ele.
        | Se não existir, usa leitura.creditos.
        */
        $creditos = $creditosJogador > 0
            ? $creditosJogador
            : $creditosLeitura;

        return response()
            ->json([
                'success' => true,
                'idprod' => $tablet->idprod,
                'creditos' => $creditos,
                'creditos_texto' => 'Créditos: ' . number_format($creditos, 2, ',', '.'),
                'debug' => [
                    'creditos_jogador_original' => $tablet->creditos_jogador,
                    'creditos_leitura_original' => $tablet->creditos_leitura,
                    'creditos_jogador_normalizado' => $creditosJogador,
                    'creditos_leitura_normalizado' => $creditosLeitura,
                ],
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }


    private function normalizarCreditoPainelCliente($valor)
    {
        if ($valor === null || $valor === '') {
            return 0;
        }

        $valor = trim((string) $valor);

        $valor = str_replace(['R$', '$', '€', 'Créditos:', 'Creditos:'], '', $valor);
        $valor = trim($valor);

        $valor = preg_replace('/[^0-9,\.\-]/', '', $valor);

        if ($valor === '' || $valor === '-' || $valor === null) {
            return 0;
        }

        if (strpos($valor, ',') !== false && strpos($valor, '.') !== false) {
            $ultimaVirgula = strrpos($valor, ',');
            $ultimoPonto = strrpos($valor, '.');

            if ($ultimaVirgula > $ultimoPonto) {
                $valor = str_replace('.', '', $valor);
                $valor = str_replace(',', '.', $valor);
            } else {
                $valor = str_replace(',', '', $valor);
            }
        } elseif (strpos($valor, ',') !== false) {
            $valor = str_replace(',', '.', $valor);
        }

        return (float) $valor;
    }

    public function alterarSenha(Request $request)
    {
        $usuarioLogado = auth()->user();

        $request->validate([
            'senha_atual' => 'required|string',
            'nova_senha' => 'required|string|min:4',
            'nova_senha_confirmacao' => 'required|string|same:nova_senha',
        ], [
            'senha_atual.required' => 'Informe sua senha atual.',
            'nova_senha.required' => 'Informe a nova senha.',
            'nova_senha.min' => 'A nova senha precisa ter pelo menos 4 caracteres.',
            'nova_senha_confirmacao.required' => 'Confirme a nova senha.',
            'nova_senha_confirmacao.same' => 'A confirmação da nova senha não confere.',
        ]);

        $usuario = DB::table('users')
            ->where('id', $usuarioLogado->id)
            ->first();

        if (!$usuario) {
            return redirect()
                ->back()
                ->withInput()
                ->with('swal_error', 'Usuário não encontrado.');
        }

        $senhaAtual = $request->input('senha_atual');
        $novaSenha = $request->input('nova_senha');

        $senhaValida = false;

        /*
        |--------------------------------------------------------------------------
        | Senha atual Laravel
        |--------------------------------------------------------------------------
        */
        if (!empty($usuario->password) && Hash::check($senhaAtual, $usuario->password)) {
            $senhaValida = true;
        }

        /*
        |--------------------------------------------------------------------------
        | Senha legado: legacy_passwd
        |--------------------------------------------------------------------------
        */
        if (!$senhaValida && property_exists($usuario, 'legacy_passwd')) {
            if (!empty($usuario->legacy_passwd) && md5($senhaAtual) === $usuario->legacy_passwd) {
                $senhaValida = true;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Senha legado: passwd
        |--------------------------------------------------------------------------
        */
        if (!$senhaValida && property_exists($usuario, 'passwd')) {
            if (!empty($usuario->passwd) && md5($senhaAtual) === $usuario->passwd) {
                $senhaValida = true;
            }

            if (!empty($usuario->passwd) && $senhaAtual === $usuario->passwd) {
                $senhaValida = true;
            }
        }

        if (!$senhaValida) {
            return redirect()
                ->back()
                ->withInput()
                ->with('swal_error', 'Senha atual incorreta.');
        }

        $dadosUpdate = [
            'password' => Hash::make($novaSenha),
            'updated_at' => now(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Limpa senha legado se a coluna existir
        |--------------------------------------------------------------------------
        */
        if (property_exists($usuario, 'legacy_passwd')) {
            $dadosUpdate['legacy_passwd'] = null;
        }

        if (property_exists($usuario, 'passwd')) {
            $dadosUpdate['passwd'] = md5($novaSenha);
        }

        DB::table('users')
            ->where('id', $usuario->id)
            ->update($dadosUpdate);

        return redirect()
            ->route('cliente.painel', [
                'aba' => 'configuracao',
                'data_inicial' => $request->input('data_inicial'),
                'data_final' => $request->input('data_final'),
            ])
            ->with('swal_success', 'Senha alterada com sucesso.');
    }
}