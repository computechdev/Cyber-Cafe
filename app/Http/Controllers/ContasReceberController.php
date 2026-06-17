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

        /*
        |--------------------------------------------------------------------------
        | Contas a receber
        |--------------------------------------------------------------------------
        | Mostra cobranças fechadas.
        |
        | Agora também buscamos:
        | - porcentagem do cliente
        | - porcentagem do admin ligado ao cliente
        |
        | cliente.id_apoio aponta para o admin.
        */
        $query = DB::table('cobranca_agregado')
            ->join('users as cliente_user', 'cliente_user.id', '=', 'cobranca_agregado.id_cliente')
            ->leftJoin('users as admin_user', 'admin_user.id', '=', 'cliente_user.id_apoio')
            ->select(
                'cobranca_agregado.*',

                'cliente_user.name as cliente_nome',
                'cliente_user.username as cliente_username',
                'cliente_user.porcentagem as cliente_porcentagem',

                'admin_user.id as admin_id',
                'admin_user.name as admin_nome',
                'admin_user.porcentagem as admin_porcentagem'
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

        /*
        |--------------------------------------------------------------------------
        | Calcula valores reais da fatura
        |--------------------------------------------------------------------------
        | Valor Total:
        | saldo_total das métricas fechadas nessa fatura.
        |
        | Valor Admin:
        | valor_total_acerto * admin.porcentagem / 100
        |
        | Valor Cliente:
        | valor_total_acerto * cliente.porcentagem / 100
        */
        $contas->getCollection()->transform(function ($conta) {
            $valorTotalAcerto = $this->buscarValorTotalAcertoContasReceber($conta);

            if ($valorTotalAcerto == 0) {
                $valorTotalAcerto = (float) ($conta->valor_total ?? 0);
            }

            $porcentagemAdmin = (float) ($conta->admin_porcentagem ?? 0);
            $porcentagemCliente = (float) ($conta->cliente_porcentagem ?? 0);

            $valorAdmin = $valorTotalAcerto * ($porcentagemAdmin / 100);
            $valorCliente = $valorTotalAcerto * ($porcentagemCliente / 100);

            $conta->valor_total_acerto = $valorTotalAcerto;

            $conta->porcentagem_admin = $porcentagemAdmin;
            $conta->porcentagem_cliente = $porcentagemCliente;

            $conta->valor_admin = $valorAdmin;
            $conta->valor_cliente = $valorCliente;

            return $conta;
        });

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
                        'metricas.entrada_anterior',
                        'metricas.saida_anterior',
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
                | Monta os valores corretos do acerto
                |--------------------------------------------------------------------------
                | Importante:
                | A tela não deve mostrar entrada/saída bruta como se fosse o acerto.
                |
                | entrada_acerto = entrada atual - entrada anterior
                | saida_acerto   = saida atual - saida anterior
                | saldo_acerto   = entrada_acerto - saida_acerto
                */
                $metricas = $metricas->map(function ($metrica) {
                    $entradaAtual = (float) ($metrica->entrada ?? 0);
                    $saidaAtual = (float) ($metrica->saida ?? 0);

                    $entradaAnterior = (float) ($metrica->entrada_anterior ?? 0);
                    $saidaAnterior = (float) ($metrica->saida_anterior ?? 0);

                    $entradaAcerto = $entradaAtual - $entradaAnterior;
                    $saidaAcerto = $saidaAtual - $saidaAnterior;
                    $saldoAcerto = $entradaAcerto - $saidaAcerto;

                    /*
                    |--------------------------------------------------------------------------
                    | Proteção
                    |--------------------------------------------------------------------------
                    | Se por algum motivo o cálculo do acerto vier diferente do saldo_total,
                    | mantemos o saldo_total como referência oficial da métrica.
                    |
                    | Mas a entrada/saída exibida continuam sendo as diferenças do período.
                    */
                    $saldoOficial = (float) ($metrica->saldo_total ?? $saldoAcerto);

                    $porcentagemAdmin = (float) ($metrica->admin_porcentagem ?? 0);
                    $porcentagemCliente = (float) ($metrica->cliente_porcentagem ?? 0);
                    $porcentagemPonto = (float) ($metrica->ponto_porcentagem ?? 0);

                    $porcentagemDistribuida = $porcentagemAdmin + $porcentagemCliente + $porcentagemPonto;
                    $porcentagemSobra = 100 - $porcentagemDistribuida;

                    $valorAdmin = $saldoOficial * ($porcentagemAdmin / 100);
                    $valorCliente = $saldoOficial * ($porcentagemCliente / 100);
                    $valorPonto = $saldoOficial * ($porcentagemPonto / 100);

                    $valorDistribuido = $valorAdmin + $valorCliente + $valorPonto;
                    $valorSobra = $saldoOficial - $valorDistribuido;

                    $metrica->entrada_atual = $entradaAtual;
                    $metrica->saida_atual = $saidaAtual;
                    $metrica->entrada_anterior_numero = $entradaAnterior;
                    $metrica->saida_anterior_numero = $saidaAnterior;

                    $metrica->entrada_acerto = $entradaAcerto;
                    $metrica->saida_acerto = $saidaAcerto;
                    $metrica->saldo_acerto = $saldoOficial;

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
                    'entrada' => (float) $metricas->sum('entrada_acerto'),
                    'saida' => (float) $metricas->sum('saida_acerto'),
                    'saldo' => (float) $metricas->sum('saldo_acerto'),

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
        $cobranca = DB::table('cobranca_agregado')
            ->where('id_cobranca', $id)
            ->where('ativo', 1)
            ->first();

        if (!$cobranca) {
            return redirect()
                ->back()
                ->with('swal_error', 'Cobrança não encontrada.');
        }

        if ((int) $cobranca->cobranca_fechada !== 1) {
            return redirect()
                ->back()
                ->with('swal_warning', 'Essa cobrança ainda não foi fechada.');
        }

        if ((int) $cobranca->pago === 1) {
            return redirect()
                ->route('contas-receber.index')
                ->with('swal_warning', 'Esta cobrança já está paga e baixada.');
        }

        /*
        |--------------------------------------------------------------------------
        | Busca locações vinculadas à cobrança agregada
        |--------------------------------------------------------------------------
        | No fluxo atual:
        | cobranca_agregado.id_cobranca
        |      ↓
        | cobranca_locacao.id_cobranca_agregado
        |      ↓
        | metricas.id_cobranca
        */
        $locacoes = DB::table('cobranca_locacao')
            ->where('id_cobranca_agregado', $cobranca->id_cobranca)
            ->get();

        $idsLocacoes = $locacoes
            ->pluck('id_cobranca')
            ->map(function ($idLocacao) {
                return (int) $idLocacao;
            })
            ->filter(function ($idLocacao) {
                return $idLocacao > 0;
            })
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | IDs das métricas salvas no fechamento
        |--------------------------------------------------------------------------
        | No fechamento salvamos os IDs em:
        | cobranca_agregado.lote_fechamento_id_cobrancas
        */
        $idsMetricas = [];

        if (!empty($cobranca->lote_fechamento_id_cobrancas)) {
            $idsMetricas = collect(explode(',', $cobranca->lote_fechamento_id_cobrancas))
                ->map(function ($idMetrica) {
                    return (int) trim($idMetrica);
                })
                ->filter(function ($idMetrica) {
                    return $idMetrica > 0;
                })
                ->values()
                ->toArray();
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback de segurança
        |--------------------------------------------------------------------------
        | Se por algum motivo o lote não estiver preenchido,
        | busca as métricas pelas locações vinculadas.
        */
        if (empty($idsMetricas) && !empty($idsLocacoes)) {
            $idsMetricas = DB::table('metricas')
                ->whereIn('id_cobranca', $idsLocacoes)
                ->pluck('id')
                ->map(function ($idMetrica) {
                    return (int) $idMetrica;
                })
                ->filter(function ($idMetrica) {
                    return $idMetrica > 0;
                })
                ->values()
                ->toArray();
        }

        if (empty($idsMetricas)) {
            return redirect()
                ->back()
                ->with('swal_error', 'Não foi possível localizar as métricas dessa cobrança.');
        }

        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | Baixa da cobrança agregada
            |--------------------------------------------------------------------------
            | Depois disso ela não aparece mais em Contas a Receber,
            | porque o index filtra pago = 0.
            */
            DB::table('cobranca_agregado')
                ->where('id_cobranca', $cobranca->id_cobranca)
                ->update([
                    'pago' => 1,
                    'data_pagamento' => now()->toDateString(),
                ]);

            /*
            |--------------------------------------------------------------------------
            | Baixa das cobranças de locação vinculadas
            |--------------------------------------------------------------------------
            */
            if (!empty($idsLocacoes)) {
                DB::table('cobranca_locacao')
                    ->whereIn('id_cobranca', $idsLocacoes)
                    ->update([
                        'pago' => 1,
                        'data_pagamento' => now()->toDateString(),
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Baixa das métricas
            |--------------------------------------------------------------------------
            | status = 0
            | status_leitura = 2
            |
            | Assim elas não voltam para fechamento/pesquisa de abertas.
            */
            DB::table('metricas')
                ->whereIn('id', $idsMetricas)
                ->update([
                    'status' => 0,
                    'status_leitura' => 2,
                ]);

            DB::commit();

            return redirect()
                ->route('contas-receber.index')
                ->with('swal_success', 'Fatura baixada com sucesso. Ela não aparecerá mais nas pesquisas pendentes.');
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

    private function buscarValorTotalAcertoContasReceber($conta)
{
    $idsMetricas = [];

    /*
    |--------------------------------------------------------------------------
    | Preferência 1: IDs salvos no fechamento
    |--------------------------------------------------------------------------
    | No fechamento salvamos:
    | cobranca_agregado.lote_fechamento_id_cobrancas
    */
    if (!empty($conta->lote_fechamento_id_cobrancas)) {
        $idsMetricas = collect(explode(',', $conta->lote_fechamento_id_cobrancas))
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

    /*
    |--------------------------------------------------------------------------
    | Preferência 2: locações vinculadas
    |--------------------------------------------------------------------------
    | cobranca_agregado.id_cobranca
    |      ↓
    | cobranca_locacao.id_cobranca_agregado
    |      ↓
    | metricas.id_cobranca
    */
    $idsLocacoes = DB::table('cobranca_locacao')
        ->where('id_cobranca_agregado', $conta->id_cobranca)
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
}