<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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

        $hoje = Carbon::now('America/Sao_Paulo');

        $dataInicial = $request->get('data_inicial') ?: $hoje->copy()->format('Y-m-d');
        $dataFinal = $request->get('data_final') ?: $hoje->copy()->format('Y-m-d');

        $porcentagemCliente = (float) ($usuario->porcentagem ?? 0);

        /*
        |--------------------------------------------------------------------------
        | Resumo principal por métricas abertas
        |--------------------------------------------------------------------------
        */
        $resumoMetricas = $this->buscarResumoMetricasAbertas($usuario->id, $porcentagemCliente);

        $resumo = [
            'usuario' => $usuario->username ?? $usuario->name,

            'entradas' => $resumoMetricas['entrada_acerto'],
            'saidas' => $resumoMetricas['saida_acerto'],
            'diferenca' => $resumoMetricas['saldo_acerto'],
            'comissao_cliente' => $resumoMetricas['comissao_cliente'],
            'porcentagem_cliente' => $porcentagemCliente,

            /*
            |--------------------------------------------------------------------------
            | Conta corrente
            |--------------------------------------------------------------------------
            | Continua mostrando o TOTAL do acerto aberto.
            */
            'conta_entrada' => $resumoMetricas['entrada_acerto'],
            'conta_saida' => $resumoMetricas['saida_acerto'],
            'conta_saldo' => $resumoMetricas['saldo_acerto'],
        ];

        /*
        |--------------------------------------------------------------------------
        | Movimentos - Resumo
        |--------------------------------------------------------------------------
        | Continua por métricas abertas.
        */
        $movimentos = $this->buscarMovimentosResumoPaginado(
            $usuario->id,
            $dataInicial,
            $dataFinal,
            $porcentagemCliente,
            $request
        );

        /*
        |--------------------------------------------------------------------------
        | Movimentos - Detalhe
        |--------------------------------------------------------------------------
        | Corrigido:
        | Mostra transações avulsas, mas somente do acerto aberto.
        | Não traz transações de faturas já fechadas.
        */
        $movimentosDetalhe = $this->buscarMovimentosDetalheAvulsosAbertos(
            $usuario->id,
            $dataInicial,
            $dataFinal,
            $request
        );

        /*
        |--------------------------------------------------------------------------
        | Itens
        |--------------------------------------------------------------------------
        */
        $itens = $this->buscarItensPainelCliente($usuario->id, $hoje, $request);

        /*
        |--------------------------------------------------------------------------
        | Faturas pendentes
        |--------------------------------------------------------------------------
        */
        $faturasPendentes = $this->buscarFaturasPendentes($usuario->id, $porcentagemCliente);

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

    private function buscarResumoMetricasAbertas($idCliente, $porcentagemCliente)
    {
        $resumo = DB::table('metricas')
            ->join('tablet', 'tablet.idprod', '=', 'metricas.idprod')
            ->where('tablet.id_apoio', $idCliente)
            ->where('metricas.ativo', 1)
            ->where('metricas.status', 1)
            ->where('metricas.status_leitura', 1)
            ->selectRaw('
                COALESCE(SUM(metricas.entrada - metricas.entrada_anterior), 0) as entrada_acerto,
                COALESCE(SUM(metricas.saida - metricas.saida_anterior), 0) as saida_acerto,
                COALESCE(SUM(metricas.saldo_total), 0) as saldo_acerto
            ')
            ->first();

        $entradaAcerto = (float) ($resumo->entrada_acerto ?? 0);
        $saidaAcerto = (float) ($resumo->saida_acerto ?? 0);
        $saldoAcerto = (float) ($resumo->saldo_acerto ?? 0);

        return [
            'entrada_acerto' => $entradaAcerto,
            'saida_acerto' => $saidaAcerto,
            'saldo_acerto' => $saldoAcerto,
            'comissao_cliente' => $this->calcularComissaoCliente($saldoAcerto, $porcentagemCliente),
        ];
    }

    private function buscarMovimentosResumoPorMetricas($idCliente, $dataInicial, $dataFinal, $porcentagemCliente)
    {
        return DB::table('metricas')
            ->join('tablet', 'tablet.idprod', '=', 'metricas.idprod')
            ->where('tablet.id_apoio', $idCliente)
            ->where('metricas.ativo', 1)
            ->where('metricas.status', 1)
            ->where('metricas.status_leitura', 1)
            ->whereDate('metricas.dataorder', '>=', $dataInicial)
            ->whereDate('metricas.dataorder', '<=', $dataFinal)
            ->selectRaw('
                DATE(metricas.dataorder) as data_movimento,
                COALESCE(SUM(metricas.entrada - metricas.entrada_anterior), 0) as entradas,
                COALESCE(SUM(metricas.saida - metricas.saida_anterior), 0) as saidas,
                COALESCE(SUM(metricas.saldo_total), 0) as diferenca
            ')
            ->groupByRaw('DATE(metricas.dataorder)')
            ->orderByRaw('DATE(metricas.dataorder)')
            ->get()
            ->map(function ($movimento) use ($porcentagemCliente) {
                $entradas = (float) ($movimento->entradas ?? 0);
                $saidas = (float) ($movimento->saidas ?? 0);
                $diferenca = (float) ($movimento->diferenca ?? 0);

                $movimento->entradas = $entradas;
                $movimento->saidas = $saidas;
                $movimento->diferenca = $diferenca;

                $movimento->porcentagem = $entradas > 0
                    ? ($saidas / $entradas) * 100
                    : 0;

                $movimento->porcentagem_cliente = $porcentagemCliente;
                $movimento->comissao_cliente = $this->calcularComissaoCliente($diferenca, $porcentagemCliente);

                return $movimento;
            });
    }

    private function buscarMovimentosDetalheAvulsosAbertos($idCliente, $dataInicial, $dataFinal, Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Page size
        |--------------------------------------------------------------------------
        | Igual ao legado:
        | 10, 25, 50 ou 100 linhas por página.
        */
        $pageSize = (int) $request->get('detalhe_page_size', 10);

        if (!in_array($pageSize, [10, 25, 50, 100])) {
            $pageSize = 10;
        }

        /*
        |--------------------------------------------------------------------------
        | Métricas abertas do cliente
        |--------------------------------------------------------------------------
        | Se não existe métrica aberta, não deve aparecer transação no detalhe.
        */
        $metricasAbertas = DB::table('metricas')
            ->join('tablet', 'tablet.idprod', '=', 'metricas.idprod')
            ->select(
                'metricas.id',
                'metricas.idprod',
                'metricas.dataorder'
            )
            ->where('tablet.id_apoio', $idCliente)
            ->where('metricas.ativo', 1)
            ->where('metricas.status', 1)
            ->where('metricas.status_leitura', 1)
            ->get();

        if ($metricasAbertas->isEmpty()) {
            return $this->paginadorVazio('detalhe_page', $pageSize);
        }

        $idprodsAbertos = $metricasAbertas
            ->pluck('idprod')
            ->unique()
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Último fechamento por tablet
        |--------------------------------------------------------------------------
        | Tudo que foi antes ou no fechamento não pode aparecer no detalhe.
        */
        $ultimosFechamentos = DB::table('metricas')
            ->select(
                'idprod',
                DB::raw('MAX(data_fechamento_leitura) as ultimo_fechamento')
            )
            ->whereIn('idprod', $idprodsAbertos)
            ->whereNotNull('data_fechamento_leitura')
            ->where('status', 0)
            ->whereIn('status_leitura', [2])
            ->groupBy('idprod')
            ->get()
            ->keyBy('idprod');

        /*
        |--------------------------------------------------------------------------
        | Transações avulsas abertas
        |--------------------------------------------------------------------------
        | Mostra linha por linha:
        | - Entrada 10
        | - Entrada 5
        | - Saída 3
        |
        | Mas somente do acerto aberto.
        */
        $query = DB::table('transacoes')
            ->join('tablet', 'tablet.idprod', '=', 'transacoes.idprod')
            ->select(
                'transacoes.id',
                'transacoes.idprod',
                'transacoes.data_hora',
                'transacoes.tipo',
                'transacoes.valor',
                'tablet.cliente'
            )
            ->where('tablet.id_apoio', $idCliente)
            ->whereIn('transacoes.idprod', $idprodsAbertos)
            ->whereDate('transacoes.data_hora', '>=', $dataInicial)
            ->whereDate('transacoes.data_hora', '<=', $dataFinal);

        $query->where(function ($q) use ($idprodsAbertos, $ultimosFechamentos) {
            foreach ($idprodsAbertos as $idprod) {
                $ultimoFechamento = null;

                if (isset($ultimosFechamentos[$idprod])) {
                    $ultimoFechamento = $ultimosFechamentos[$idprod]->ultimo_fechamento;
                }

                $q->orWhere(function ($sub) use ($idprod, $ultimoFechamento) {
                    $sub->where('transacoes.idprod', $idprod);

                    if (!empty($ultimoFechamento)) {
                        $sub->where('transacoes.data_hora', '>', $ultimoFechamento);
                    }
                });
            }
        });

        $paginado = $query
            ->orderByDesc('transacoes.data_hora')
            ->orderByDesc('transacoes.id')
            ->paginate($pageSize, ['*'], 'detalhe_page');

        $paginado->getCollection()->transform(function ($transacao) {
            $valor = (float) ($transacao->valor ?? 0);

            $transacao->entrada = 0;
            $transacao->saida = 0;

            if ((int) $transacao->tipo === 1) {
                $transacao->entrada = $valor;
                $transacao->tipo_nome = 'Bilhete';
            } elseif ((int) $transacao->tipo === 2) {
                $transacao->saida = $valor;
                $transacao->tipo_nome = 'Cheque de brinde';
            } else {
                $transacao->tipo_nome = 'Outros';
            }

            return $transacao;
        });

        return $paginado->appends($request->query());
    }

    private function paginadorVazio($pageName, $pageSize = 10)
    {
        return new \Illuminate\Pagination\LengthAwarePaginator(
            collect(),
            0,
            $pageSize,
            \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage($pageName),
            [
                'path' => request()->url(),
                'pageName' => $pageName,
            ]
        );
    }
    private function buscarItensPainelCliente($idCliente, Carbon $hoje, Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Page size
        |--------------------------------------------------------------------------
        | Igual ao legado:
        | 10, 25, 50 ou 100 linhas por página.
        */
        $pageSize = (int) $request->get('itens_page_size', 10);

        if (!in_array($pageSize, [10, 25, 50, 100])) {
            $pageSize = 10;
        }

        /*
        |--------------------------------------------------------------------------
        | Última leitura por tablet
        |--------------------------------------------------------------------------
        */
        $ultimaLeituraSub = DB::table('leitura')
            ->select('idprod', DB::raw('MAX(id) as ultimo_id'))
            ->groupBy('idprod');

        /*
        |--------------------------------------------------------------------------
        | Itens do cliente
        |--------------------------------------------------------------------------
        */
        $query = DB::table('tablet')
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
            ->where('tablet.id_apoio', $idCliente)
            ->orderBy('tablet.idprod');

        /*
        |--------------------------------------------------------------------------
        | Filtro ativo se existir
        |--------------------------------------------------------------------------
        */
        try {
            if (DB::getSchemaBuilder()->hasColumn('tablet', 'ativo')) {
                $query->where('tablet.ativo', 1);
            }
        } catch (\Throwable $e) {
            // Se der erro ao verificar coluna, segue sem o filtro.
        }

        $itens = $query
            ->paginate($pageSize, ['*'], 'itens_page')
            ->appends($request->query());

        $itens->getCollection()->transform(function ($item) use ($hoje) {
            $creditos = $this->normalizarCreditoPainelCliente($item->creditos ?? 0);

            $item->credito_texto = 'Créditos: ' . number_format($creditos, 2, ',', '.');
            $item->fecha = $hoje->copy()->format('d/m/Y') . ' 23:59';

            $item->total_1 = '0,00';
            $item->total_2 = '0,00';
            $item->total_3 = '0,00';

            return $item;
        });

        return $itens;
    }

    private function buscarFaturasPendentes($idCliente, $porcentagemCliente)
    {
        $faturas = DB::table('cobranca_agregado')
            ->where('id_cliente', $idCliente)
            ->where('ativo', 1)
            ->where('cobranca_fechada', 1)
            ->where('pago', 0)
            ->orderByDesc('id_cobranca')
            ->get();

        return $faturas->map(function ($fatura) use ($porcentagemCliente) {
            $valorTotalAcerto = $this->buscarValorTotalAcertoDaFatura($fatura);

            if ($valorTotalAcerto == 0) {
                $valorTotalAcerto = (float) ($fatura->valor_total ?? 0);
            }

            $fatura->valor_total_acerto = $valorTotalAcerto;
            $fatura->porcentagem_cliente = $porcentagemCliente;
            $fatura->comissao_cliente = $this->calcularComissaoCliente($valorTotalAcerto, $porcentagemCliente);

            return $fatura;
        });
    }

    private function buscarValorTotalAcertoDaFatura($fatura)
    {
        $idsMetricas = [];

        if (!empty($fatura->lote_fechamento_id_cobrancas)) {
            $idsMetricas = collect(explode(',', $fatura->lote_fechamento_id_cobrancas))
                ->map(function ($id) {
                    return (int) trim($id);
                })
                ->filter(function ($id) {
                    return $id > 0;
                })
                ->values()
                ->toArray();
        }

        if (!empty($idsMetricas)) {
            return (float) DB::table('metricas')
                ->whereIn('id', $idsMetricas)
                ->sum('saldo_total');
        }

        $idsLocacoes = DB::table('cobranca_locacao')
            ->where('id_cobranca_agregado', $fatura->id_cobranca)
            ->pluck('id_cobranca')
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) {
                return $id > 0;
            })
            ->values()
            ->toArray();

        if (!empty($idsLocacoes)) {
            return (float) DB::table('metricas')
                ->whereIn('id_cobranca', $idsLocacoes)
                ->sum('saldo_total');
        }

        return 0;
    }

    private function calcularComissaoCliente($valorTotal, $porcentagemCliente)
    {
        $valorTotal = (float) ($valorTotal ?? 0);
        $porcentagemCliente = (float) ($porcentagemCliente ?? 0);

        return $valorTotal * ($porcentagemCliente / 100);
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

        $creditosJogador = $this->normalizarCreditoPainelCliente($tablet->creditos_jogador ?? 0);
        $creditosLeitura = $this->normalizarCreditoPainelCliente($tablet->creditos_leitura ?? 0);

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
                    'creditos_jogador_original' => $tablet->creditos_jogador ?? null,
                    'creditos_leitura_original' => $tablet->creditos_leitura ?? null,
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

        if (!empty($usuario->password) && Hash::check($senhaAtual, $usuario->password)) {
            $senhaValida = true;
        }

        if (!$senhaValida && property_exists($usuario, 'legacy_passwd')) {
            if (!empty($usuario->legacy_passwd) && md5($senhaAtual) === $usuario->legacy_passwd) {
                $senhaValida = true;
            }
        }

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
            ->route('cliente.painel-teste', [
                'aba' => 'configuracao',
                'data_inicial' => $request->input('data_inicial'),
                'data_final' => $request->input('data_final'),
            ])
            ->with('swal_success', 'Senha alterada com sucesso.');
    }

    private function buscarMovimentosResumoPaginado($idCliente, $dataInicial, $dataFinal, $porcentagemCliente, Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Page size
        |--------------------------------------------------------------------------
        | Igual ao legado:
        | 10, 25, 50 ou 100 linhas por página.
        */
        $pageSize = (int) $request->get('resumo_page_size', 10);

        if (!in_array($pageSize, [10, 25, 50, 100])) {
            $pageSize = 10;
        }

        /*
        |--------------------------------------------------------------------------
        | Movimentos por data
        |--------------------------------------------------------------------------
        | Esta aba é resumo por dia.
        |
        | Fonte:
        | transacoes
        |
        | Motivo:
        | Aqui o cliente quer ver o movimento por data, igual ao legado:
        | 16/06, 15/06, 14/06...
        |
        | A conta corrente e faturas continuam usando metricas.
        */
        $query = DB::table('transacoes')
            ->join('tablet', 'tablet.idprod', '=', 'transacoes.idprod')
            ->where('tablet.id_apoio', $idCliente)
            ->whereDate('transacoes.data_hora', '>=', $dataInicial)
            ->whereDate('transacoes.data_hora', '<=', $dataFinal)
            ->selectRaw('
            DATE(transacoes.data_hora) as data_movimento,

            COALESCE(SUM(CASE WHEN transacoes.tipo = 1 THEN transacoes.valor ELSE 0 END), 0) as entradas,

            COALESCE(SUM(CASE WHEN transacoes.tipo = 2 THEN transacoes.valor ELSE 0 END), 0) as saidas,

            COALESCE(SUM(CASE WHEN transacoes.tipo = 1 THEN transacoes.valor ELSE 0 END), 0)
            -
            COALESCE(SUM(CASE WHEN transacoes.tipo = 2 THEN transacoes.valor ELSE 0 END), 0) as diferenca
        ')
            ->groupByRaw('DATE(transacoes.data_hora)')
            ->orderByRaw('DATE(transacoes.data_hora) DESC');

        $movimentos = $query
            ->paginate($pageSize, ['*'], 'resumo_page')
            ->appends($request->query());

        $movimentos->getCollection()->transform(function ($movimento) use ($porcentagemCliente) {
            $entradas = (float) ($movimento->entradas ?? 0);
            $saidas = (float) ($movimento->saidas ?? 0);
            $diferenca = (float) ($movimento->diferenca ?? 0);

            $movimento->entradas = $entradas;
            $movimento->saidas = $saidas;
            $movimento->diferenca = $diferenca;

            /*
            |--------------------------------------------------------------------------
            | Porcentagem
            |--------------------------------------------------------------------------
            | Mantém o padrão que já estávamos usando:
            | saída / entrada * 100
            */
            $movimento->porcentagem = $entradas > 0
                ? ($saidas / $entradas) * 100
                : 0;

            /*
            |--------------------------------------------------------------------------
            | Comissão do cliente
            |--------------------------------------------------------------------------
            | Continua usando a porcentagem cadastrada do cliente.
            */
            $movimento->porcentagem_cliente = $porcentagemCliente;
            $movimento->comissao_cliente = $this->calcularComissaoCliente($diferenca, $porcentagemCliente);

            return $movimento;
        });

        return $movimentos;
    }
}