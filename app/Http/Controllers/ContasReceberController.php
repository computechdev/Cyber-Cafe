<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContasReceberController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pendente');
        $clienteId = $request->get('cliente_id');

        $clientes = DB::table('users')
            ->where('nivel', 3)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        $query = DB::table('cobranca_agregado')
            ->join('users', 'users.id', '=', 'cobranca_agregado.id_cliente')
            ->select(
                'cobranca_agregado.*',
                'users.name as cliente_nome',
                'users.username as cliente_username'
            )
            ->where('cobranca_agregado.ativo', 1)
            ->where('cobranca_agregado.cobranca_fechada', 1);

        if ($status === 'pendente') {
            $query->where('cobranca_agregado.pago', 0);
        }

        if ($status === 'pago') {
            $query->where('cobranca_agregado.pago', 1);
        }

        if (!empty($clienteId)) {
            $query->where('cobranca_agregado.id_cliente', $clienteId);
        }

        $contas = $query
            ->orderByDesc('cobranca_agregado.id_cobranca')
            ->paginate(20)
            ->appends($request->query());

        return view('financeiro.contas-receber.index', compact(
            'contas',
            'clientes',
            'status',
            'clienteId'
        ));
    }

    public function fecharIndex(Request $request)
    {
        $clienteId = $request->get('cliente_id');

        $clientes = DB::table('users')
            ->where('nivel', 3)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        $cliente = null;
        $metricas = collect();
        $cobrancasAbertas = collect();

        $totais = [
            'entrada' => 0,
            'saida' => 0,
            'saldo' => 0,

            'valor_admin' => 0,
            'valor_cliente' => 0,
            'valor_ponto' => 0,
            'valor_distribuido' => 0,
            'valor_sobra' => 0,

            'creditos' => 0,
            'porcentagem_invalida' => false,
        ];

        if (!empty($clienteId)) {
            $cliente = DB::table('users')
                ->where('id', $clienteId)
                ->where('nivel', 3)
                ->first();

            if ($cliente) {
                $cobrancasAbertas = DB::table('cobranca_agregado')
                    ->where('id_cliente', $cliente->id)
                    ->where('ativo', 1)
                    ->where('pago', 0)
                    ->where('cobranca_fechada', 0)
                    ->orderBy('id_cobranca')
                    ->get();

                /*
                |--------------------------------------------------------------------------
                | Leituras abertas do cliente
                |--------------------------------------------------------------------------
                | tablet.id_apoio = cliente nível 3
                | cliente.id_apoio = admin nível 1
                */
                $metricas = DB::table('metricas')
                    ->join('tablet', 'tablet.idprod', '=', 'metricas.idprod')
                    ->leftJoin('users as cliente_user', 'cliente_user.id', '=', 'tablet.id_apoio')
                    ->leftJoin('users as admin_user', 'admin_user.id', '=', 'cliente_user.id_apoio')
                    ->leftJoin('ponto', 'ponto.id', '=', 'tablet.id_ponto')
                    ->leftJoin('leitura', 'leitura.idprod', '=', 'metricas.idprod')
                    ->select(
                        'metricas.id',
                        'metricas.id_cobranca',
                        'metricas.idprod',
                        'metricas.dataorder',

                        'metricas.entrada',
                        'metricas.saida',
                        'metricas.saldo_total',

                        'metricas.status',
                        'metricas.status_leitura',

                        'tablet.cliente',
                        'tablet.id_apoio',
                        'tablet.id_ponto',

                        'cliente_user.name as cliente_nome',
                        'cliente_user.porcentagem as cliente_porcentagem',

                        'admin_user.id as admin_id',
                        'admin_user.name as admin_nome',
                        'admin_user.porcentagem as admin_porcentagem',

                        'ponto.nome as ponto_nome',
                        'ponto.porcent_ponto as ponto_porcentagem',

                        'leitura.creditos as creditos'
                    )
                    ->where('tablet.id_apoio', $cliente->id)
                    ->where('metricas.ativo', 1)
                    ->where('metricas.status', 1)
                    ->where('metricas.status_leitura', 1)
                    ->orderBy('metricas.dataorder')
                    ->get();

                /*
                |--------------------------------------------------------------------------
                | Monta os valores para exibição do ADMIN
                |--------------------------------------------------------------------------
                | Cada um recebe exatamente pela porcentagem cadastrada.
                */
                $metricas = $metricas->map(function ($metrica) {
                    $saldo = (float) $metrica->saldo_total;

                    $porcentagemAdmin = (float) ($metrica->admin_porcentagem ?? 0);
                    $porcentagemCliente = (float) ($metrica->cliente_porcentagem ?? 0);
                    $porcentagemPonto = (float) ($metrica->ponto_porcentagem ?? 0);

                    $porcentagemDistribuida = $porcentagemAdmin + $porcentagemCliente + $porcentagemPonto;
                    $porcentagemSobra = 100 - $porcentagemDistribuida;

                    $valorAdmin = $saldo * ($porcentagemAdmin / 100);
                    $valorCliente = $saldo * ($porcentagemCliente / 100);
                    $valorPonto = $saldo * ($porcentagemPonto / 100);

                    $valorDistribuido = $valorAdmin + $valorCliente + $valorPonto;
                    $valorSobra = $saldo - $valorDistribuido;

                    $metrica->porcentagem_admin = $porcentagemAdmin;
                    $metrica->porcentagem_cliente = $porcentagemCliente;
                    $metrica->porcentagem_ponto = $porcentagemPonto;
                    $metrica->porcentagem_distribuida = $porcentagemDistribuida;
                    $metrica->porcentagem_sobra = $porcentagemSobra;

                    $metrica->valor_admin = $valorAdmin;
                    $metrica->valor_cliente = $valorCliente;
                    $metrica->valor_ponto = $valorPonto;
                    $metrica->valor_distribuido = $valorDistribuido;
                    $metrica->valor_sobra = $valorSobra;

                    $metrica->creditos_numero = $this->normalizarCreditoFechamento($metrica->creditos ?? 0);

                    $metrica->porcentagem_invalida = $porcentagemDistribuida > 100;

                    return $metrica;
                });

                $totais = [
                    'entrada' => (float) $metricas->sum('entrada'),
                    'saida' => (float) $metricas->sum('saida'),
                    'saldo' => (float) $metricas->sum('saldo_total'),

                    'valor_admin' => (float) $metricas->sum('valor_admin'),
                    'valor_cliente' => (float) $metricas->sum('valor_cliente'),
                    'valor_ponto' => (float) $metricas->sum('valor_ponto'),
                    'valor_distribuido' => (float) $metricas->sum('valor_distribuido'),
                    'valor_sobra' => (float) $metricas->sum('valor_sobra'),

                    'creditos' => (float) $metricas->sum('creditos_numero'),
                    'porcentagem_invalida' => $metricas->contains('porcentagem_invalida', true),
                ];
            }
        }

        return view('financeiro.fechar-faturas.index', compact(
            'clientes',
            'cliente',
            'clienteId',
            'metricas',
            'cobrancasAbertas',
            'totais'
        ));
    }

    public function fecharFatura(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|integer',
        ], [
            'cliente_id.required' => 'Selecione um cliente para fechar a fatura.',
        ]);

        $clienteId = (int) $request->input('cliente_id');

        $cliente = DB::table('users')
            ->where('id', $clienteId)
            ->where('nivel', 3)
            ->first();

        if (!$cliente) {
            return redirect()
                ->back()
                ->withInput()
                ->with('swal_error', 'Cliente não encontrado.');
        }

        /*
        |--------------------------------------------------------------------------
        | Busca cobranças agregadas abertas já criadas pela API
        |--------------------------------------------------------------------------
        | Regra correta:
        | A tela NÃO cria cobrança nova.
        | A tela apenas fecha cobrança existente.
        */
        $cobrancasAbertas = DB::table('cobranca_agregado')
            ->where('id_cliente', $clienteId)
            ->where('ativo', 1)
            ->where('pago', 0)
            ->where('cobranca_fechada', 0)
            ->orderBy('id_cobranca')
            ->get();

        if ($cobrancasAbertas->isEmpty()) {
            return redirect()
                ->back()
                ->withInput()
                ->with('swal_warning', 'Não existe cobrança aberta para fechar deste cliente.');
        }

        $idsCobrancasAgregado = $cobrancasAbertas
            ->pluck('id_cobranca')
            ->map(function ($idCobranca) {
                return (int) $idCobranca;
            })
            ->filter(function ($idCobranca) {
                return $idCobranca > 0;
            })
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Busca cobranças de locação vinculadas às agregadas abertas
        |--------------------------------------------------------------------------
        | Importante:
        | metricas.id_cobranca aponta para cobranca_locacao.id_cobranca.
        */
        $cobrancasLocacao = DB::table('cobranca_locacao')
            ->whereIn('id_cobranca_agregado', $idsCobrancasAgregado)
            ->where('ativo', 1)
            ->where('pago', 0)
            ->where('cobranca_fechada', 0)
            ->orderBy('id_cobranca')
            ->get();

        if ($cobrancasLocacao->isEmpty()) {
            return redirect()
                ->back()
                ->withInput()
                ->with('swal_warning', 'Não existe cobrança de locação aberta vinculada a este cliente.');
        }

        $idsCobrancasLocacao = $cobrancasLocacao
            ->pluck('id_cobranca')
            ->map(function ($idCobranca) {
                return (int) $idCobranca;
            })
            ->filter(function ($idCobranca) {
                return $idCobranca > 0;
            })
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Busca métricas abertas vinculadas às cobranças de locação
        |--------------------------------------------------------------------------
        | Não buscamos id_cobranca NULL.
        | No legado, a métrica já nasce vinculada à cobrança.
        */
        $metricas = DB::table('metricas')
            ->whereIn('id_cobranca', $idsCobrancasLocacao)
            ->where('ativo', 1)
            ->where('status', 1)
            ->where('status_leitura', 1)
            ->orderBy('dataorder')
            ->get();

        if ($metricas->isEmpty()) {
            return redirect()
                ->back()
                ->withInput()
                ->with('swal_warning', 'Não existem leituras abertas para fechar deste cliente.');
        }

        $idsMetricas = $metricas
            ->pluck('id')
            ->map(function ($idMetrica) {
                return (int) $idMetrica;
            })
            ->filter(function ($idMetrica) {
                return $idMetrica > 0;
            })
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Proteção: não fechar fatura com créditos em aberto
        |--------------------------------------------------------------------------
        | Regra de segurança:
        | Se o jogo ainda possui créditos na tabela leitura, não podemos fechar
        | a fatura, porque isso pode quebrar o acerto.
        */
        $idprodsMetricas = $metricas
            ->pluck('idprod')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $leiturasComCredito = DB::table('leitura')
            ->whereIn('idprod', $idprodsMetricas)
            ->select('idprod', 'creditos')
            ->get()
            ->filter(function ($leitura) {
                return $this->normalizarCreditoFechamento($leitura->creditos ?? 0) > 0;
            })
            ->values();

        if ($leiturasComCredito->isNotEmpty()) {
            $listaCreditos = $leiturasComCredito
                ->map(function ($leitura) {
                    return $leitura->idprod . ' com ' . $leitura->creditos . ' crédito(s)';
                })
                ->implode(', ');

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'swal_warning',
                    'Não é possível fechar esta fatura. Existem máquinas com créditos em aberto: ' . $listaCreditos
                );
        }

        $dataFechamento = now()->format('Y-m-d H:i:s');

        /*
        |--------------------------------------------------------------------------
        | Lote numérico
        |--------------------------------------------------------------------------
        | Não usamos hash em metricas.lote_fechamento.
        */
        $loteFechamento = now()->format('YmdHis');

        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | Fecha cobranças agregadas existentes
            |--------------------------------------------------------------------------
            | Não recalcula valor_total aqui.
            | O valor já vem da API sendLeituraRealtime().
            */
            DB::table('cobranca_agregado')
                ->whereIn('id_cobranca', $idsCobrancasAgregado)
                ->update([
                    'cobranca_fechada' => 1,
                    'data_processamento' => $dataFechamento,
                    'lote_fechamento_id_cobrancas' => implode(',', $idsMetricas),
                ]);

            /*
            |--------------------------------------------------------------------------
            | Fecha cobranças de locação vinculadas
            |--------------------------------------------------------------------------
            */
            DB::table('cobranca_locacao')
                ->whereIn('id_cobranca', $idsCobrancasLocacao)
                ->update([
                    'cobranca_fechada' => 1,
                    'lote_fechamento_id_cobrancas' => implode(',', $idsMetricas),
                ]);

            /*
            |--------------------------------------------------------------------------
            | Congela as métricas fechadas
            |--------------------------------------------------------------------------
            | Isso impede a Unity de atualizar a métrica fechada.
            |
            | status = 0
            | status_leitura = 2
            */
            DB::table('metricas')
                ->whereIn('id', $idsMetricas)
                ->update([
                    'status' => 0,
                    'status_leitura' => 2,
                    'data_fechamento_leitura' => $dataFechamento,
                    'lote_fechamento' => $loteFechamento,
                    'operador_fechamento' => auth()->id(),
                    'cliente_fechamento' => $clienteId,
                ]);

            /*
            |--------------------------------------------------------------------------
            | Congela metrica_parcial vinculada
            |--------------------------------------------------------------------------
            | Se existir leitura parcial, ela acompanha a métrica principal.
            */
            DB::table('metrica_parcial')
                ->whereIn('id_metrica_fk', $idsMetricas)
                ->update([
                    'status_leitura' => 2,
                    'data_fechamento_leitura' => $dataFechamento,
                    'lote_fechamento' => $loteFechamento,
                    'cliente_fechamento' => $clienteId,
                ]);

            DB::commit();

            return redirect()
                ->route('contas-receber.index', ['status' => 'pendente'])
                ->with('swal_success', 'Fatura fechada com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('swal_error', 'Erro ao fechar fatura: ' . $e->getMessage());
        }
    }

    public function marcarPago($id)
    {
        /*
        |--------------------------------------------------------------------------
        | Busca a cobrança agregada
        |--------------------------------------------------------------------------
        | Esta tela é Contas a Receber do admin.
        | Portanto a baixa começa pela cobranca_agregado.
        */
        $cobranca = DB::table('cobranca_agregado')
            ->where('id_cobranca', $id)
            ->where('ativo', 1)
            ->first();

        if (!$cobranca) {
            return redirect()
                ->back()
                ->with('swal_error', 'Cobrança não encontrada.');
        }

        /*
        |--------------------------------------------------------------------------
        | Só pode baixar cobrança fechada
        |--------------------------------------------------------------------------
        | Aberta:
        | pago = 0
        | cobranca_fechada = 0
        |
        | Fechada pendente:
        | pago = 0
        | cobranca_fechada = 1
        |
        | Paga:
        | pago = 1
        | cobranca_fechada = 1
        */
        if ((int) $cobranca->cobranca_fechada !== 1) {
            return redirect()
                ->back()
                ->with('swal_warning', 'Essa cobrança ainda não foi fechada.');
        }

        if ((int) $cobranca->pago === 1) {
            return redirect()
                ->back()
                ->with('swal_warning', 'Esta cobrança já está paga.');
        }

        /*
        |--------------------------------------------------------------------------
        | Busca as cobranças de locação vinculadas à agregada
        |--------------------------------------------------------------------------
        | Importante:
        | metricas.id_cobranca aponta para cobranca_locacao.id_cobranca,
        | não para cobranca_agregado.id_cobranca.
        */
        $cobrancasLocacao = DB::table('cobranca_locacao')
            ->where('id_cobranca_agregado', $cobranca->id_cobranca)
            ->where('ativo', 1)
            ->orderBy('id_cobranca')
            ->get();

        if ($cobrancasLocacao->isEmpty()) {
            return redirect()
                ->back()
                ->with('swal_error', 'Não foi encontrada nenhuma cobrança de locação vinculada a esta fatura.');
        }

        $idsCobrancasLocacao = $cobrancasLocacao
            ->pluck('id_cobranca')
            ->map(function ($idCobranca) {
                return (int) $idCobranca;
            })
            ->filter(function ($idCobranca) {
                return $idCobranca > 0;
            })
            ->values()
            ->toArray();

        if (empty($idsCobrancasLocacao)) {
            return redirect()
                ->back()
                ->with('swal_error', 'Não foi possível localizar os IDs das cobranças de locação.');
        }

        /*
        |--------------------------------------------------------------------------
        | Busca as métricas vinculadas às cobranças de locação
        |--------------------------------------------------------------------------
        */
        $idsMetricas = DB::table('metricas')
            ->whereIn('id_cobranca', $idsCobrancasLocacao)
            ->pluck('id')
            ->map(function ($idMetrica) {
                return (int) $idMetrica;
            })
            ->filter(function ($idMetrica) {
                return $idMetrica > 0;
            })
            ->values()
            ->toArray();

        if (empty($idsMetricas)) {
            return redirect()
                ->back()
                ->with('swal_error', 'Não foi possível localizar as métricas dessa cobrança.');
        }

        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | Marca cobrança agregada como paga
            |--------------------------------------------------------------------------
            | Igual ao legado:
            | pago = 1
            | data_pagamento = data atual
            */
            DB::table('cobranca_agregado')
                ->where('id_cobranca', $cobranca->id_cobranca)
                ->update([
                    'pago' => 1,
                    'data_pagamento' => now()->toDateString(),
                ]);

            /*
            |--------------------------------------------------------------------------
            | Marca cobranças de locação como pagas também
            |--------------------------------------------------------------------------
            | No legado a baixa principal do admin é na agregada.
            | Aqui mantemos a locação sincronizada para evitar fatura parcialmente aberta.
            */
            DB::table('cobranca_locacao')
                ->whereIn('id_cobranca', $idsCobrancasLocacao)
                ->update([
                    'pago' => 1,
                    'data_pagamento' => now()->toDateString(),
                ]);

            /*
            |--------------------------------------------------------------------------
            | Baixa das métricas
            |--------------------------------------------------------------------------
            | Igual ao legado:
            | status_leitura = 2
            | status = 0
            */
            DB::table('metricas')
                ->whereIn('id', $idsMetricas)
                ->update([
                    'status_leitura' => 2,
                    'status' => 0,
                ]);

            DB::commit();

            return redirect()
                ->back()
                ->with('swal_success', 'Cobrança marcada como paga com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('swal_error', 'Erro ao baixar cobrança: ' . $e->getMessage());
        }
    }

    private function normalizarCreditoFechamento($valor)
    {
        if ($valor === null || $valor === '') {
            return 0;
        }

        $valor = trim((string) $valor);

        $valor = str_replace('R$', '', $valor);
        $valor = str_replace(' ', '', $valor);

        /*
        |--------------------------------------------------------------------------
        | Trata formatos possíveis:
        |--------------------------------------------------------------------------
        | 10
        | 10.50
        | 10,50
        | 1.000,50
        | 1,000.50
        */
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
}