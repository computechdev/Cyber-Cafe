<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransacaoController extends Controller
{
    public function entradasSaidas(Request $request)
    {
        $usuario = auth()->user();
        $nivel = (int) ($usuario->nivel ?? 0);

        $dataInicial = $request->get('data_inicial') ?: now()->format('Y-m-d');
        $dataFinal = $request->get('data_final') ?: now()->format('Y-m-d');

        $tipo = $request->get('tipo', '1');
        $idprod = $request->get('idprod');
        $statusFatura = $request->get('status_fatura', 'todos');

        $pesquisou = $request->has('pesquisar');

        $tabletsQuery = DB::table('tablet')
            ->orderBy('idprod');

        if ($this->colunaExiste('tablet', 'ativo')) {
            $tabletsQuery->where('ativo', 1);
        }

        if ($nivel === 3) {
            $tabletsQuery->where('id_apoio', $usuario->id);
        }

        $tablets = $tabletsQuery->get();

        $transacoes = collect();
        $total = 0;

        if ($pesquisou) {
            $inicio = $this->converterDataParaBanco($dataInicial, '00:00:00');
            $fim = $this->converterDataParaBanco($dataFinal, '23:59:59');

            $ultimoFechamentoSub = DB::table('metricas')
                ->select(
                    'idprod',
                    DB::raw('MAX(data_fechamento_leitura) as ultimo_fechamento')
                )
                ->whereNotNull('data_fechamento_leitura')
                ->groupBy('idprod');

            $metricasAbertasSub = DB::table('metricas')
                ->select('idprod')
                ->where('ativo', 1)
                ->where('status', 1)
                ->where('status_leitura', 1)
                ->groupBy('idprod');

            $query = DB::table('transacoes')
                ->leftJoin('tablet', 'tablet.idprod', '=', 'transacoes.idprod')
                ->leftJoinSub($ultimoFechamentoSub, 'ultimo_fechamento', function ($join) {
                    $join->on('ultimo_fechamento.idprod', '=', 'transacoes.idprod');
                })
                ->leftJoinSub($metricasAbertasSub, 'metricas_abertas', function ($join) {
                    $join->on('metricas_abertas.idprod', '=', 'transacoes.idprod');
                })
                ->select(
                    'transacoes.id',
                    'transacoes.tipo',
                    'transacoes.valor',
                    'transacoes.idprod',
                    'transacoes.data_hora',
                    'tablet.cliente',
                    'tablet.id_apoio',
                    'ultimo_fechamento.ultimo_fechamento',
                    'metricas_abertas.idprod as idprod_aberto'
                );

            if ($nivel === 3) {
                $query->where('tablet.id_apoio', $usuario->id);
            }

            if (!empty($idprod)) {
                $query->where('transacoes.idprod', $idprod);
            }

            if ($tipo !== '' && $tipo !== null && $tipo !== 'todos') {
                $query->where('transacoes.tipo', $tipo);
            }

            if ($inicio && $fim) {
                $query->whereBetween('transacoes.data_hora', [$inicio, $fim]);
            }

            if ($statusFatura === 'pendente') {
                $query->whereNotNull('metricas_abertas.idprod')
                    ->where(function ($q) {
                        $q->whereNull('ultimo_fechamento.ultimo_fechamento')
                            ->orWhereColumn('transacoes.data_hora', '>', 'ultimo_fechamento.ultimo_fechamento');
                    });
            }

            if ($statusFatura === 'fechada') {
                $query->whereNotNull('ultimo_fechamento.ultimo_fechamento')
                    ->whereColumn('transacoes.data_hora', '<=', 'ultimo_fechamento.ultimo_fechamento');
            }

            $transacoes = $query
                ->orderByDesc('transacoes.data_hora')
                ->orderByDesc('transacoes.id')
                ->get()
                ->map(function ($transacao) {
                    $transacao->valor = (float) ($transacao->valor ?? 0);

                    $transacao->status_fatura = $this->definirStatusFaturaTransacao(
                        $transacao->data_hora,
                        $transacao->ultimo_fechamento,
                        $transacao->idprod_aberto
                    );

                    return $transacao;
                });

            $total = $transacoes->sum('valor');
        }

        return view('transacoes.entradas-saidas', compact(
            'tablets',
            'transacoes',
            'total',
            'pesquisou',
            'dataInicial',
            'dataFinal',
            'tipo',
            'idprod',
            'statusFatura'
        ));
    }

    public function movimentacaoPeriodo(Request $request)
    {
        $usuario = auth()->user();
        $nivel = (int) ($usuario->nivel ?? 0);

        /*
        |--------------------------------------------------------------------------
        | Datas para input type="date"
        |--------------------------------------------------------------------------
        */
        $dataInicial = $request->get('data_inicial') ?: now()->startOfMonth()->format('Y-m-d');
        $dataFinal = $request->get('data_final') ?: now()->format('Y-m-d');

        $clienteSelecionado = $request->get('cliente');
        $pontoSelecionado = $request->get('ponto');
        $statusFatura = $request->get('status_fatura', 'todos');

        $pesquisou = $request->has('pesquisar');

        $clientes = collect();
        $pontos = collect();
        $movimentacoes = collect();

        $totalEntrada = 0;
        $totalSaida = 0;
        $saldoTotal = 0;

        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */
        if ($nivel === 1 || $nivel === 2) {
            $clientes = DB::table('users')
                ->where('nivel', 3)
                ->where('status', 1)
                ->orderBy('name')
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | CLIENTE
        |--------------------------------------------------------------------------
        */
        if ($nivel === 3) {
            $pontos = DB::table('ponto')
                ->where('id_apoio', $usuario->id)
                ->where('status', 1)
                ->orderBy('nome')
                ->get();
        }

        if ($pesquisou) {
            $inicio = $this->converterDataParaBanco($dataInicial, '00:00:00');
            $fim = $this->converterDataParaBanco($dataFinal, '23:59:59');

            if ($inicio && $fim) {
                /*
                |--------------------------------------------------------------------------
                | Último fechamento por tablet
                |--------------------------------------------------------------------------
                */
                $ultimoFechamentoSub = DB::table('metricas')
                    ->select(
                        'idprod',
                        DB::raw('MAX(data_fechamento_leitura) as ultimo_fechamento')
                    )
                    ->whereNotNull('data_fechamento_leitura')
                    ->groupBy('idprod');

                /*
                |--------------------------------------------------------------------------
                | Tablets com métrica aberta
                |--------------------------------------------------------------------------
                */
                $metricasAbertasSub = DB::table('metricas')
                    ->select('idprod')
                    ->where('ativo', 1)
                    ->where('status', 1)
                    ->where('status_leitura', 1)
                    ->groupBy('idprod');

                $query = DB::table('transacoes')
                    ->join('tablet', 'tablet.idprod', '=', 'transacoes.idprod')
                    ->leftJoin('ponto', 'ponto.id', '=', 'tablet.id_ponto')
                    ->leftJoinSub($ultimoFechamentoSub, 'ultimo_fechamento', function ($join) {
                        $join->on('ultimo_fechamento.idprod', '=', 'transacoes.idprod');
                    })
                    ->leftJoinSub($metricasAbertasSub, 'metricas_abertas', function ($join) {
                        $join->on('metricas_abertas.idprod', '=', 'transacoes.idprod');
                    })
                    ->select(
                        'tablet.idprod',
                        'tablet.cliente',
                        'ponto.nome as ponto_nome',
                        'ultimo_fechamento.ultimo_fechamento',
                        'metricas_abertas.idprod as idprod_aberto',
                        DB::raw("SUM(CASE WHEN transacoes.tipo = 1 THEN transacoes.valor ELSE 0 END) as total_entrada"),
                        DB::raw("SUM(CASE WHEN transacoes.tipo = 2 THEN transacoes.valor ELSE 0 END) as total_saida")
                    )
                    ->whereBetween('transacoes.data_hora', [$inicio, $fim]);

                /*
                |--------------------------------------------------------------------------
                | Filtro por nível
                |--------------------------------------------------------------------------
                */
                if ($nivel === 1 || $nivel === 2) {
                    if (!empty($clienteSelecionado)) {
                        $query->where('tablet.id_apoio', $clienteSelecionado);
                    }
                }

                if ($nivel === 3) {
                    $query->where('tablet.id_apoio', $usuario->id);

                    if (!empty($pontoSelecionado)) {
                        $query->where('tablet.id_ponto', $pontoSelecionado);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Filtro de status da fatura
                |--------------------------------------------------------------------------
                */
                if ($statusFatura === 'pendente') {
                    $query->whereNotNull('metricas_abertas.idprod')
                        ->where(function ($q) {
                            $q->whereNull('ultimo_fechamento.ultimo_fechamento')
                                ->orWhereColumn('transacoes.data_hora', '>', 'ultimo_fechamento.ultimo_fechamento');
                        });
                }

                if ($statusFatura === 'fechada') {
                    $query->whereNotNull('ultimo_fechamento.ultimo_fechamento')
                        ->whereColumn('transacoes.data_hora', '<=', 'ultimo_fechamento.ultimo_fechamento');
                }

                $movimentacoes = $query
                    ->groupBy(
                        'tablet.idprod',
                        'tablet.cliente',
                        'ponto.nome',
                        'ultimo_fechamento.ultimo_fechamento',
                        'metricas_abertas.idprod'
                    )
                    ->orderBy('tablet.idprod')
                    ->get()
                    ->map(function ($item) {
                        $item->total_entrada = (float) ($item->total_entrada ?? 0);
                        $item->total_saida = (float) ($item->total_saida ?? 0);
                        $item->saldo = $item->total_entrada - $item->total_saida;

                        $item->status_fatura = $this->definirStatusAgrupadoMovimentacao(
                            $item->ultimo_fechamento,
                            $item->idprod_aberto
                        );

                        return $item;
                    });

                $totalEntrada = $movimentacoes->sum('total_entrada');
                $totalSaida = $movimentacoes->sum('total_saida');
                $saldoTotal = $totalEntrada - $totalSaida;
            }
        }

        return view('transacoes.movimentacao-periodo', compact(
            'nivel',
            'clientes',
            'pontos',
            'movimentacoes',
            'totalEntrada',
            'totalSaida',
            'saldoTotal',
            'pesquisou',
            'dataInicial',
            'dataFinal',
            'clienteSelecionado',
            'pontoSelecionado',
            'statusFatura'
        ));
    }

    private function definirStatusFaturaTransacao($dataHora, $ultimoFechamento, $idprodAberto)
    {
        if (!empty($ultimoFechamento)) {
            try {
                $dataTransacao = Carbon::parse($dataHora);
                $dataFechamento = Carbon::parse($ultimoFechamento);

                if ($dataTransacao->lessThanOrEqualTo($dataFechamento)) {
                    return 'Fechada';
                }
            } catch (\Throwable $e) {
                return 'Indefinido';
            }
        }

        if (!empty($idprodAberto)) {
            return 'Pendente';
        }

        return 'Sem fatura';
    }

    private function definirStatusAgrupadoMovimentacao($ultimoFechamento, $idprodAberto)
    {
        if (!empty($idprodAberto)) {
            return 'Pendente';
        }

        if (!empty($ultimoFechamento)) {
            return 'Fechada';
        }

        return 'Sem fatura';
    }

    private function converterDataParaBanco($data, $hora)
    {
        if (!$data) {
            return null;
        }

        try {
            if (strpos($data, '/') !== false) {
                return Carbon::createFromFormat('d/m/Y H:i:s', $data . ' ' . $hora)
                    ->format('Y-m-d H:i:s');
            }

            return Carbon::parse($data . ' ' . $hora)
                ->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function colunaExiste($tabela, $coluna)
    {
        try {
            return DB::getSchemaBuilder()->hasColumn($tabela, $coluna);
        } catch (\Throwable $e) {
            return false;
        }
    }
}