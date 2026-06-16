<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransacaoController extends Controller
{
    public function entradasSaidas(Request $request)
    {
        $tablets = DB::table('tablet')
            ->orderBy('idprod')
            ->get();

        $dataInicial = $request->get('data_inicial', now()->format('d/m/Y'));
        $dataFinal = $request->get('data_final', now()->format('d/m/Y'));

        $tipo = $request->get('tipo', '1');
        $idprod = $request->get('idprod');

        $transacoes = collect();
        $total = 0;

        $pesquisou = $request->has('pesquisar');

        if ($pesquisou) {
            $inicio = $this->converterDataParaBanco($dataInicial, '00:00:00');
            $fim = $this->converterDataParaBanco($dataFinal, '23:59:59');

            $query = DB::table('transacoes')
                ->leftJoin('tablet', 'tablet.idprod', '=', 'transacoes.idprod')
                ->select(
                    'transacoes.id',
                    'transacoes.tipo',
                    'transacoes.valor',
                    'transacoes.idprod',
                    'transacoes.data_hora',
                    'tablet.cliente'
                );

            if (!empty($idprod)) {
                $query->where('transacoes.idprod', $idprod);
            }

            if ($tipo !== '' && $tipo !== null) {
                $query->where('transacoes.tipo', $tipo);
            }

            if ($inicio && $fim) {
                $query->whereBetween('transacoes.data_hora', [$inicio, $fim]);
            }

            $transacoes = $query
                ->orderByDesc('transacoes.data_hora')
                ->get();

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
            'idprod'
        ));
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

    public function movimentacaoPeriodo(Request $request)
    {
        $usuario = auth()->user();
        $nivel = (int) $usuario->nivel;

        $dataInicial = $request->get('data_inicial', now()->startOfMonth()->format('d/m/Y'));
        $dataFinal = $request->get('data_final', now()->format('d/m/Y'));

        $clienteSelecionado = $request->get('cliente');
        $pontoSelecionado = $request->get('ponto');

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
        | Admin escolhe Cliente.
        */
        if ($nivel === 1 || $nivel === 2) {
            $clientes = DB::table('users')
                ->where('nivel', 3)
                ->where('status', true)
                ->orderBy('name')
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | CLIENTE
        |--------------------------------------------------------------------------
        | Cliente escolhe Ponto.
        */
        if ($nivel === 3) {
            $pontos = DB::table('ponto')
                ->where('id_apoio', $usuario->id)
                ->where('status', true)
                ->orderBy('nome')
                ->get();
        }

        if ($pesquisou) {
            $inicio = $this->converterDataParaBanco($dataInicial, '00:00:00');
            $fim = $this->converterDataParaBanco($dataFinal, '23:59:59');

            $query = DB::table('transacoes')
                ->join('tablet', 'tablet.idprod', '=', 'transacoes.idprod')
                ->leftJoin('ponto', 'ponto.id', '=', 'tablet.id_ponto')
                ->select(
                    'tablet.idprod',
                    DB::raw("SUM(CASE WHEN transacoes.tipo = 1 THEN transacoes.valor ELSE 0 END) as total_entrada"),
                    DB::raw("SUM(CASE WHEN transacoes.tipo = 2 THEN transacoes.valor ELSE 0 END) as total_saida")
                )
                ->whereBetween('transacoes.data_hora', [$inicio, $fim]);

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

            $movimentacoes = $query
                ->groupBy('tablet.idprod')
                ->orderBy('tablet.idprod')
                ->get()
                ->map(function ($item) {
                    $item->total_entrada = (float) $item->total_entrada;
                    $item->total_saida = (float) $item->total_saida;
                    $item->saldo = $item->total_entrada - $item->total_saida;

                    return $item;
                });

            $totalEntrada = $movimentacoes->sum('total_entrada');
            $totalSaida = $movimentacoes->sum('total_saida');
            $saldoTotal = $totalEntrada - $totalSaida;
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
            'pontoSelecionado'
        ));
    }
}