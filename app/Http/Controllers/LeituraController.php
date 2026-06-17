<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeituraController extends Controller
{
    public function consultar(Request $request)
    {
        $usuario = auth()->user();
        $nivel = (int) ($usuario->nivel ?? 0);

        $clientes = collect();
        $pontos = collect();

        $leituras = collect();

        $totalEntrada = 0;
        $totalSaida = 0;
        $saldoTotal = 0;

        $clienteSelecionado = $request->get('cliente');
        $pontoSelecionado = $request->get('ponto');
        $statusLeitura = $request->get('status_leitura');

        $pesquisou = $request->has('pesquisar');

        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        | Admin escolhe Cliente.
        */
        if (in_array($nivel, [1, 2])) {
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
        | Cliente escolhe Ponto.
        */
        if ($nivel === 3) {
            $pontos = DB::table('ponto')
                ->where('id_apoio', $usuario->id)
                ->where('status', 1)
                ->orderBy('nome')
                ->get();
        }

        if ($pesquisou) {
            $query = DB::table('metricas')
                ->join('tablet', 'tablet.idprod', '=', 'metricas.idprod')
                ->leftJoin('ponto', 'ponto.id', '=', 'tablet.id_ponto')
                ->select(
                    'metricas.id',
                    'metricas.idprod',
                    'metricas.dataorder',

                    'metricas.entrada',
                    'metricas.saida',
                    'metricas.entrada_anterior',
                    'metricas.saida_anterior',
                    'metricas.saldo_total',

                    'metricas.status',
                    'metricas.status_leitura',

                    'tablet.cliente',
                    'tablet.id_apoio',
                    'tablet.id_ponto',

                    'ponto.nome as ponto_nome'
                )
                ->where('metricas.ativo', 1);

            /*
            |--------------------------------------------------------------------------
            | FILTRO ADMIN
            |--------------------------------------------------------------------------
            */
            if (in_array($nivel, [1, 2])) {
                if (!empty($clienteSelecionado)) {
                    $query->where('tablet.id_apoio', $clienteSelecionado);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | FILTRO CLIENTE
            |--------------------------------------------------------------------------
            */
            if ($nivel === 3) {
                $query->where('tablet.id_apoio', $usuario->id);

                if (!empty($pontoSelecionado)) {
                    $query->where('tablet.id_ponto', $pontoSelecionado);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | STATUS DA LEITURA
            |--------------------------------------------------------------------------
            | 1 = Aberta
            | 2 = Fechada
            */
            if ($statusLeitura !== null && $statusLeitura !== '') {
                $query->where('metricas.status_leitura', $statusLeitura);
            }

            $leituras = $query
                ->orderBy('metricas.dataorder')
                ->get()
                ->map(function ($leitura) {
                    $entradaAtual = (float) ($leitura->entrada ?? 0);
                    $saidaAtual = (float) ($leitura->saida ?? 0);

                    $entradaAnterior = (float) ($leitura->entrada_anterior ?? 0);
                    $saidaAnterior = (float) ($leitura->saida_anterior ?? 0);

                    $leitura->entrada_acerto = $entradaAtual - $entradaAnterior;
                    $leitura->saida_acerto = $saidaAtual - $saidaAnterior;
                    $leitura->saldo_acerto = (float) ($leitura->saldo_total ?? 0);

                    $leitura->status_leitura_nome = $this->nomeStatusLeitura($leitura->status_leitura);

                    return $leitura;
                });

            $totalEntrada = $leituras->sum('entrada_acerto');
            $totalSaida = $leituras->sum('saida_acerto');
            $saldoTotal = $leituras->sum('saldo_acerto');
        }

        return view('leituras.consultar', compact(
            'nivel',
            'clientes',
            'pontos',
            'leituras',
            'totalEntrada',
            'totalSaida',
            'saldoTotal',
            'pesquisou',
            'clienteSelecionado',
            'pontoSelecionado',
            'statusLeitura'
        ));
    }

    public function periodo(Request $request)
    {
        $usuario = auth()->user();
        $nivel = (int) ($usuario->nivel ?? 0);

        $clientes = collect();
        $pontos = collect();

        $leituras = collect();

        $totalEntrada = 0;
        $totalSaida = 0;
        $saldoTotal = 0;

        $clienteSelecionado = $request->get('cliente');
        $pontoSelecionado = $request->get('ponto');

        $dataInicial = $request->get('data_inicial');
        $dataFinal = $request->get('data_final');

        $pesquisou = $request->has('pesquisar');

        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        | Admin escolhe Cliente.
        */
        if (in_array($nivel, [1, 2])) {
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
        | Cliente escolhe Ponto.
        */
        if ($nivel === 3) {
            $pontos = DB::table('ponto')
                ->where('id_apoio', $usuario->id)
                ->where('status', 1)
                ->orderBy('nome')
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDAÇÕES
        |--------------------------------------------------------------------------
        */
        if ($pesquisou && empty($dataInicial)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('swal_error', 'Selecione a data inicial para pesquisar.');
        }

        if ($pesquisou && empty($dataFinal)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('swal_error', 'Selecione a data final para pesquisar.');
        }

        if ($pesquisou && in_array($nivel, [1, 2]) && empty($clienteSelecionado)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('swal_error', 'Selecione o cliente para pesquisar.');
        }

        if ($pesquisou) {
            $query = DB::table('metricas')
                ->join('tablet', 'tablet.idprod', '=', 'metricas.idprod')
                ->leftJoin('ponto', 'ponto.id', '=', 'tablet.id_ponto')
                ->select(
                    'metricas.id',
                    'metricas.idprod',
                    'metricas.dataorder',

                    'metricas.entrada',
                    'metricas.saida',
                    'metricas.entrada_anterior',
                    'metricas.saida_anterior',
                    'metricas.saldo_total',

                    'metricas.status',
                    'metricas.status_leitura',

                    'tablet.cliente',
                    'tablet.id_apoio',
                    'tablet.id_ponto',

                    'ponto.nome as ponto_nome'
                )
                ->where('metricas.ativo', 1);

            /*
            |--------------------------------------------------------------------------
            | FILTRO ADMIN
            |--------------------------------------------------------------------------
            */
            if (in_array($nivel, [1, 2])) {
                $query->where('tablet.id_apoio', $clienteSelecionado);
            }

            /*
            |--------------------------------------------------------------------------
            | FILTRO CLIENTE
            |--------------------------------------------------------------------------
            */
            if ($nivel === 3) {
                $query->where('tablet.id_apoio', $usuario->id);

                if (!empty($pontoSelecionado)) {
                    $query->where('tablet.id_ponto', $pontoSelecionado);
                }
            }

            $query->whereDate('metricas.dataorder', '>=', $dataInicial);
            $query->whereDate('metricas.dataorder', '<=', $dataFinal);

            $leituras = $query
                ->orderBy('metricas.dataorder')
                ->get()
                ->map(function ($leitura) {
                    $entradaAtual = (float) ($leitura->entrada ?? 0);
                    $saidaAtual = (float) ($leitura->saida ?? 0);

                    $entradaAnterior = (float) ($leitura->entrada_anterior ?? 0);
                    $saidaAnterior = (float) ($leitura->saida_anterior ?? 0);

                    $leitura->entrada_acerto = $entradaAtual - $entradaAnterior;
                    $leitura->saida_acerto = $saidaAtual - $saidaAnterior;
                    $leitura->saldo_acerto = (float) ($leitura->saldo_total ?? 0);

                    $leitura->status_leitura_nome = $this->nomeStatusLeitura($leitura->status_leitura);

                    return $leitura;
                });

            $totalEntrada = $leituras->sum('entrada_acerto');
            $totalSaida = $leituras->sum('saida_acerto');
            $saldoTotal = $leituras->sum('saldo_acerto');

            if ($leituras->isEmpty()) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('swal_warning', 'Nenhuma leitura encontrada para o período informado.');
            }
        }

        return view('leituras.periodo', compact(
            'nivel',
            'clientes',
            'pontos',
            'leituras',
            'totalEntrada',
            'totalSaida',
            'saldoTotal',
            'pesquisou',
            'clienteSelecionado',
            'pontoSelecionado',
            'dataInicial',
            'dataFinal'
        ));
    }

    private function nomeStatusLeitura($statusLeitura)
    {
        if ((int) $statusLeitura === 1) {
            return 'Aberta';
        }

        if ((int) $statusLeitura === 2) {
            return 'Fechada';
        }

        return 'Indefinida';
    }
}