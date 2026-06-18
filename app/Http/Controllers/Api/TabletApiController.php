<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TabletApiController extends Controller
{
    public function verificaUser(Request $request)
    {
        if (!$request->filled('login') || !$request->filled('passwd')) {
            return response('V#', 200)
                ->header('Content-Type', 'text/plain');
        }

        $login = $request->get('login');
        $senha = $request->get('passwd');

        $user = User::where('username', $login)->first();

        if (!$user) {
            return response('NC#', 200)
                ->header('Content-Type', 'text/plain');
        }

        $senhaValida = false;

        if ($user->password && Hash::check($senha, $user->password)) {
            $senhaValida = true;
        }

        if (!$senhaValida && $user->legacy_passwd && md5($senha) === $user->legacy_passwd) {
            $senhaValida = true;
        }

        if (!$senhaValida) {
            return response('NC#', 200)
                ->header('Content-Type', 'text/plain');
        }

        $pontos = DB::table('ponto')
            ->where('id_apoio', $user->id)
            ->where('status', 1)
            ->orderBy('nome')
            ->pluck('nome');

        $resposta = 'C#';

        foreach ($pontos as $ponto) {
            $resposta .= $ponto . '#';
        }

        return response($resposta, 200)
            ->header('Content-Type', 'text/plain');
    }

    public function cadastrar(Request $request)
    {
        if (!$request->filled('idprod') || !$request->filled('dono') || !$request->filled('ponto')) {
            return response('V#', 200)
                ->header('Content-Type', 'text/plain');
        }

        $idprod = $request->get('idprod');
        $dono = $request->get('dono');
        $pontoNome = $request->get('ponto');

        $tablet = DB::table('tablet')
            ->where('idprod', $idprod)
            ->first();

        if (!$tablet) {
            $ponto = DB::table('ponto')
                ->where('nome', $pontoNome)
                ->first();

            if (!$ponto) {
                return response('NP#', 200)
                    ->header('Content-Type', 'text/plain');
            }

            $cliente = User::where('username', $dono)->first();

            if (!$cliente) {
                return response('NC#', 200)
                    ->header('Content-Type', 'text/plain');
            }

            DB::table('tablet')->insert([
                'idprod' => $idprod,
                'cliente' => $dono,
                'cadastro' => now(),

                'validade' => 0,
                'status' => 0,
                'ligado' => 1,
                'zerar' => 0,
                'destrava' => 0,

                'expiracao' => '0',

                'id_ponto' => $ponto->id,
                'id_apoio' => $cliente->id,

                'dificuldade' => 70,
                'pendrive_id' => 1,
                'antecipado' => 0,

                'creditoteclado' => false,
                'centavosbingo' => 1,
                'apostamaxhalloween' => 10,
                'leituraonlinesincronizada' => true,

                'zerarleituraparcial' => false,
                'zerarleituraoficialbackup' => false,

                'acum1min' => 200.00,
                'acum2min' => 300.00,
                'acum3min' => 400.00,

                'acum1med' => 200.00,
                'acum2med' => 300.00,
                'acum3med' => 400.00,

                'acum1max' => 500.00,
                'acum2max' => 600.00,
                'acum3max' => 700.00,

                'acum4min' => 100.00,
                'acum4med' => 100.00,
                'acum4max' => 200.00,

                'acum5min' => 100.00,
                'acum5med' => 100.00,
                'acum5max' => 100.00,

                'acum6min' => 100.00,
                'acum6med' => 100.00,
                'acum6max' => 100.00,

                'porcent_acumu' => 3,
                'lastversion' => 1,
                'ativo' => true,

                'zerarleituravirtual' => false,
                'matematica_slot' => 1,
                'modo_slot' => true,

                'ultimo_contato' => now(),
            ]);

            return response('C#' . $ponto->id . '#', 200)
                ->header('Content-Type', 'text/plain');
        }

        $metrica = DB::table('metricas')
            ->where('idprod', $idprod)
            ->orderByDesc('id')
            ->first();

        $entrada = 0;
        $saida = 0;

        if ($metrica) {
            $entrada = $metrica->entrada;
            $saida = $metrica->saida;
        }

        return response('U#' . $entrada . '#' . $saida . '#', 200)
            ->header('Content-Type', 'text/plain');
    }

    public function verificarParametros(Request $request)
    {
        $idprod = strtoupper(substr($request->get('idprod', ''), 0, 20));

        if (!$idprod) {
            return response('NA#', 200)
                ->header('Content-Type', 'text/plain');
        }

        $tablet = DB::table('tablet')
            ->where('idprod', $idprod)
            ->first();

        if (!$tablet) {
            return response('NA#', 200)
                ->header('Content-Type', 'text/plain');
        }

        $campos = [
            $tablet->lastversion ?? 0,                      // 0
            $tablet->dificuldade ?? 0,                      // 1

            /*
             * No legado novo aparece matematicaSlot na saída,
             * mas o campo real da tabela é matematica_slot.
             */
            $tablet->matematica_slot ?? 0,                  // 2

            $tablet->acum1min ?? 0,                         // 3
            $tablet->acum1med ?? 0,                         // 4
            $tablet->acum1max ?? 0,                         // 5

            $tablet->acum2min ?? 0,                         // 6
            $tablet->acum2med ?? 0,                         // 7
            $tablet->acum2max ?? 0,                         // 8

            $tablet->acum3min ?? 0,                         // 9
            $tablet->acum3med ?? 0,                         // 10
            $tablet->acum3max ?? 0,                         // 11

            $tablet->porcent_acumu ?? 0,                    // 12
            $tablet->destrava ?? 0,                         // 13

            $tablet->acum4min ?? 0,                         // 14
            $tablet->acum4med ?? 0,                         // 15
            $tablet->acum4max ?? 0,                         // 16

            $tablet->acum5min ?? 0,                         // 17
            $tablet->acum5med ?? 0,                         // 18
            $tablet->acum5max ?? 0,                         // 19

            $tablet->acum6min ?? 0,                         // 20
            $tablet->acum6med ?? 0,                         // 21
            $tablet->acum6max ?? 0,                         // 22

            $tablet->creditoteclado ?? 0,                   // 23
            $tablet->centavosbingo ?? 0,                    // 24
            $tablet->apostamaxhalloween ?? 0,               // 25

            $tablet->leituraonlinesincronizada ?? 0,        // 26
            $tablet->zerarleituraparcial ?? 0,              // 27
            $tablet->zerarleituraoficialbackup ?? 0,        // 28
            $tablet->zerarleituravirtual ?? 0,              // 29

            $tablet->matematica_slot ?? 0,                  // 30
            $tablet->modo_slot ?? 0,                        // 31
            $tablet->retencao_slots ?? 0,                   // 32
            $tablet->dificul_bonus ?? 0,                    // 33

            $tablet->taxa_maxima ?? 0,                      // 34
            $tablet->acrecimo_taxa_max ?? 0,                // 35
            $tablet->periodo_reducao ?? 0,                  // 36
            $tablet->extras_min_rotina ?? 0,                // 37
            $tablet->saldo_para_acumulao ?? 0,              // 38
            $tablet->premio_do_acumulado ?? 0,              // 39
            $tablet->tam_premio_max ?? 0,                   // 40

            $tablet->saldo_min_bingo_g1 ?? 0,               // 41
            $tablet->saldo_min_bingo_g2 ?? 0,               // 42
            $tablet->saldo_min_bingo_g3 ?? 0,               // 43

            $tablet->qtn_jogos_azar ?? 0,                  // 44
            $tablet->probabilidada_premios_azar ?? 0,       // 45
            $tablet->taxa_max_chance_azar ?? 0,             // 46

            $tablet->qtn_jogos_normal ?? 0,                // 47
            $tablet->probabilidada_premios_normal ?? 0,     // 48
            $tablet->taxa_max_chance_normal ?? 0,           // 49

            $tablet->qtn_jogos_neutro ?? 0,                // 50
            $tablet->probabilidade_premios_neutro ?? 0,     // 51
            $tablet->taxa_max_chance_neutro ?? 0,           // 52

            $tablet->qtn_jogos_sorte ?? 0,                 // 53
            $tablet->probabilidade_premios_sorte ?? 0,      // 54
            $tablet->taxa_max_chance_sorte ?? 0,            // 55

            $tablet->qtn_jogos_moderado ?? 0,              // 56
            $tablet->probabilidade_premios_moderado ?? 0,   // 57
            $tablet->taxa_max_chance_moderado ?? 0,         // 58

            $tablet->limites_nivel_show_n2 ?? 0,            // 59
            $tablet->limites_nivel_show_n3 ?? 0,            // 60
            $tablet->limites_nivel_show_n4 ?? 0,            // 61

            $tablet->status_bonus ?? 0,                    // 62
            $tablet->valor ?? 0,                           // 63
            $tablet->kiosk_sis ?? 0,                       // 64
            $tablet->tipo_tela ?? 0,                       // 65

            $tablet->acumSenaMin ?? 0,                     // 66
            $tablet->acumSenaAtu ?? 0,                     // 67
            $tablet->acumSenaMax ?? 0,                     // 68

            $tablet->acumBombaMin ?? 0,                    // 69
            $tablet->acumBombaAtu ?? 0,                    // 70
            $tablet->acumBombaMax ?? 0,                    // 71

            $tablet->tipo_inter ?? 0,                      // 72
            $tablet->senha ?? '8520',                      // 73
        ];

        return response(implode('#', $campos) . '#', 200)
            ->header('Content-Type', 'text/plain');
    }

    public function verificarRealtime(Request $request)
    {
        try {
            $idprod = strtoupper(substr($request->get('idprod', ''), 0, 20));

            $dispversion = $request->get('dispversion', 0);

            $acum1med = $this->normalizarDecimalUnity($request->get('acum1', 0));
            $acum2med = $this->normalizarDecimalUnity($request->get('acum2', 0));
            $acum3med = $this->normalizarDecimalUnity($request->get('acum3', 0));
            $acum4med = $this->normalizarDecimalUnity($request->get('acum4', 0));

            $creditos = 'SC';
            $servidor = 'OFF';
            $lastversion = 0;
            $destrava = 0;

            if (!$idprod) {
                return response($creditos . '#' . $servidor . '#' . $lastversion . '#' . $destrava . '#', 200)
                    ->header('Content-Type', 'text/plain');
            }

            DB::table('tablet')
                ->where('idprod', $idprod)
                ->update([
                    'ultimo_contato' => now()->format('Y-m-d H:i:s'),
                ]);

            $tablet = DB::table('tablet')
                ->where('idprod', $idprod)
                ->where('ligado', 1)
                ->first();

            if (!$tablet) {
                return response($creditos . '#' . $servidor . '#' . $lastversion . '#' . $destrava . '#', 200)
                    ->header('Content-Type', 'text/plain');
            }

            $servidor = 'ON';
            $lastversion = $tablet->lastversion ?? 0;
            $destrava = $tablet->destrava ?? 0;


            DB::table('tablet')
                ->where('idprod', $idprod)
                ->update([
                    'acum1med' => $acum1med,
                    'acum2med' => $acum2med,
                    'acum3med' => $acum3med,
                    'acum4med' => $acum4med,
                ]);


            $credito = DB::table('creditos')
                ->where('idprod', $idprod)
                ->orderByDesc('data')
                ->first();

            if ($credito && (int) $credito->status !== 0) {
                $creditos = $credito->valor;

                DB::table('creditos')
                    ->where('id', $credito->id)
                    ->update([
                        'status' => 0,
                    ]);
            }

            return response($creditos . '#' . $servidor . '#' . $lastversion . '#' . $destrava . '#', 200)
                ->header('Content-Type', 'text/plain');

        } catch (\Throwable $e) {
            \Log::error('Erro verificar_realtime.php', [
                'erro' => $e->getMessage(),
                'linha' => $e->getLine(),
                'arquivo' => $e->getFile(),
                'request' => $request->all(),
            ]);

            return response('SC#OFF#0#0#', 200)
                ->header('Content-Type', 'text/plain');
        }
    }

    public function sendLeituraRealtime(Request $request)
    {
        $dataNow = now()->format('Y-m-d H:i:s');

        $idprod = strtoupper(substr($request->get('idprod', ''), 0, 4));

        if (!$idprod) {
            return response('NA#', 200)
                ->header('Content-Type', 'text/plain');
        }

        $tablet = DB::table('tablet')
            ->where('idprod', $idprod)
            ->first();

        if (!$tablet) {
            return response('NA#', 200)
                ->header('Content-Type', 'text/plain');
        }

        /*
        |--------------------------------------------------------------------------
        | Regra igual ao legado
        |--------------------------------------------------------------------------
        | A Unity envia entrada/saída multiplicado por 100.
        */
        $entrada = (float) $request->get('entrada', 0) / 100;
        $saida = (float) $request->get('saida', 0) / 100;
        $entradaVirtual = (float) $request->get('entrada_virtual', 0) / 100;
        $saidaVirtual = (float) $request->get('saida_virtual', 0) / 100;

        $data = $request->get('data', now()->format('d/m/Y'));
        $status = (int) $request->get('status', $tablet->status ?? 0);

        $acao = 0;
        $idMetrica = null;

        $entradaAnterior = 0;
        $saidaAnterior = 0;

        $idCobrancaAgregado = null;
        $idCobranca = null;
        $idCobrancaPonto = null;

        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | Atualiza último contato do tablet
            |--------------------------------------------------------------------------
            */
            DB::table('tablet')
                ->where('idprod', $idprod)
                ->update([
                    'ultimo_contato' => $dataNow,
                ]);

            /*
            |--------------------------------------------------------------------------
            | Fluxo financeiro só roda se entrada > 0
            |--------------------------------------------------------------------------
            | Igual ao legado:
            | if ($entrada > 0) { ... }
            */
            if ($entrada > 0) {
                $metrica = DB::table('metricas')
                    ->where('idprod', $idprod)
                    ->orderByDesc('dataorder')
                    ->orderByDesc('id')
                    ->first();

                /*
                |--------------------------------------------------------------------------
                | 1. Não existe métrica
                |--------------------------------------------------------------------------
                */
                if (!$metrica) {
                    $entradaAnterior = 0;
                    $saidaAnterior = 0;
                    $acao = 1;

                    DB::table('transacoes')->insert([
                        'tipo' => 1,
                        'valor' => $entrada,
                        'idprod' => $idprod,
                        'data_hora' => $dataNow,
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | 2. Última métrica aberta
                |--------------------------------------------------------------------------
                */ elseif ((int) $metrica->status_leitura === 1) {
                    $idMetrica = $metrica->id;

                    $entradaAnterior = (float) $metrica->entrada_anterior;
                    $saidaAnterior = (float) $metrica->saida_anterior;

                    $entradaTrans = (float) $metrica->entrada;
                    $saidaTrans = (float) $metrica->saida;

                    if ($entrada == $entradaTrans && $saida == $saidaTrans) {
                        $acao = 0;
                    } else {
                        $acao = 2;

                        if ($entrada != $entradaTrans) {
                            $valorEntrada = $entrada - $entradaTrans;

                            if ($valorEntrada > 0) {
                                DB::table('transacoes')->insert([
                                    'tipo' => 1,
                                    'valor' => $valorEntrada,
                                    'idprod' => $idprod,
                                    'data_hora' => $dataNow,
                                ]);
                            }
                        }

                        if ($saida != $saidaTrans) {
                            $valorSaida = $saida - $saidaTrans;

                            if ($valorSaida > 0) {
                                DB::table('transacoes')->insert([
                                    'tipo' => 2,
                                    'valor' => $valorSaida,
                                    'idprod' => $idprod,
                                    'data_hora' => $dataNow,
                                ]);
                            }
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Igual ao legado
                        |--------------------------------------------------------------------------
                        | Remove transações zeradas ou negativas.
                        */
                        DB::table('transacoes')
                            ->where('idprod', $idprod)
                            ->where('valor', '<=', 0)
                            ->delete();
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | 3. Última métrica já baixada/paga
                |--------------------------------------------------------------------------
                */ elseif ((int) $metrica->status_leitura === 2) {
                    $entradaAnterior = (float) $metrica->entrada;
                    $saidaAnterior = (float) $metrica->saida;

                    if ($entrada == $entradaAnterior && $saida == $saidaAnterior) {
                        $acao = 0;
                        $idMetrica = $metrica->id;
                    } else {
                        $acao = 1;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Porcentagens
                |--------------------------------------------------------------------------
                | Equivalente ao legado:
                | tablet + ponto + user/users.
                */
                $porcentagens = DB::table('tablet as t')
                    ->join('ponto as p', 'p.id', '=', 't.id_ponto')
                    ->join('users as u', 'u.id', '=', 't.id_apoio')
                    ->where('t.idprod', $idprod)
                    ->select(
                        't.id_apoio',
                        't.id_ponto',
                        'p.porcent_ponto as p_ponto',
                        'u.porcentagem as locacao',
                        'u.nivel',
                        'u.id_apoio as id_apoio_acima',
                        'u.id_pais'
                    )
                    ->first();

                if (!$porcentagens) {
                    DB::rollBack();

                    return response('NA#', 200)
                        ->header('Content-Type', 'text/plain');
                }

                if ((int) $porcentagens->nivel === 4 && (float) $porcentagens->locacao != 0) {
                    $usuarioAcima = DB::table('users')
                        ->where('id', $porcentagens->id_apoio_acima)
                        ->first();

                    $porcentagemAcima = $usuarioAcima
                        ? (float) $usuarioAcima->porcentagem
                        : 0;

                    $diffSocio = (float) $porcentagens->locacao - $porcentagemAcima;

                    $pLocacao = $porcentagemAcima / 100;
                    $pPonto = (float) $porcentagens->p_ponto / 100;
                    $pCliente = $diffSocio / 100;
                } else {
                    $pLocacao = (float) $porcentagens->locacao / 100;
                    $pPonto = (float) $porcentagens->p_ponto / 100;
                    $pCliente = (100 - ((float) $porcentagens->locacao + (float) $porcentagens->p_ponto)) / 100;
                }

                /*
                |--------------------------------------------------------------------------
                | Cálculo igual ao legado
                |--------------------------------------------------------------------------
                */
                $totalEntrada = $entrada - $entradaAnterior;
                $totalSaida = $saida - $saidaAnterior;
                $saldoTotal = $totalEntrada - $totalSaida;

                $acerto = $saldoTotal;
                $comissaoLocacao = $acerto * $pLocacao;
                $comissaoPonto = $acerto * $pPonto;
                $comissaoDono = $acerto * $pCliente;

                /*
                |--------------------------------------------------------------------------
                | Período da cobrança
                |--------------------------------------------------------------------------
                | Igual ao legado:
                | último dia do mês atual.
                */
                $ultimoDiaMes = now()->endOfMonth()->toDateString();

                /*
                |--------------------------------------------------------------------------
                | Cria ou busca cobranças somente se houve ação
                |--------------------------------------------------------------------------
                */
                if ($acao > 0) {
                    $dadosCobranca = $this->obterOuCriarCobrancasLegado(
                        $tablet,
                        $ultimoDiaMes,
                        $dataNow
                    );

                    $idCobrancaAgregado = $dadosCobranca['id_cobranca_agregado'];
                    $idCobranca = $dadosCobranca['id_cobranca_locacao'];
                    $idCobrancaPonto = $dadosCobranca['id_cobranca_ponto'];
                }

                /*
                |--------------------------------------------------------------------------
                | Ação 1: criar nova métrica
                |--------------------------------------------------------------------------
                */
                if ($acao == 1) {
                    $idMetrica = DB::table('metricas')->insertGetId([
                        'id_cobranca' => $idCobranca,
                        'comissao_locacao' => $comissaoLocacao,
                        'comissao_ponto' => $comissaoPonto,
                        'comissao_dono' => $comissaoDono,
                        'acerto' => $acerto,
                        'idprod' => $idprod,
                        'saldo_total' => $saldoTotal,
                        'entrada' => $entrada,
                        'saida' => $saida,
                        'entrada_anterior' => $entradaAnterior,
                        'saida_anterior' => $saidaAnterior,
                        'dataorder' => $dataNow,
                        'status' => 1,
                        'status_leitura' => 1,
                    ]);

                    if ((int) $porcentagens->id_ponto > 0 && $idCobrancaPonto) {
                        DB::table('metrica_parcial')->insert([
                            'id_metrica_fk' => $idMetrica,
                            'id_cobranca' => $idCobrancaPonto,
                            'comissao_locacao' => $comissaoLocacao,
                            'comissao_ponto' => $comissaoPonto,
                            'comissao_dono' => $comissaoDono,
                            'acerto' => $acerto,
                            'idprod' => $idprod,
                            'saldo_total' => $saldoTotal,
                            'entrada' => $entrada,
                            'saida' => $saida,
                            'entrada_anterior' => $entradaAnterior,
                            'saida_anterior' => $saidaAnterior,
                            'data_zerou_parcial' => null,
                            'ativo' => 1,
                        ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Ação 2: atualizar métrica aberta
                |--------------------------------------------------------------------------
                */ elseif ($acao == 2) {
                    DB::table('metricas')
                        ->where('id', $idMetrica)
                        ->where('status_leitura', 1)
                        ->update([
                            'id_cobranca' => $idCobranca,
                            'comissao_locacao' => $comissaoLocacao,
                            'comissao_ponto' => $comissaoPonto,
                            'comissao_dono' => $comissaoDono,
                            'acerto' => $acerto,
                            'saldo_total' => $saldoTotal,
                            'entrada' => $entrada,
                            'saida' => $saida,
                            'dataorder' => $dataNow,
                        ]);

                    if ((int) $porcentagens->id_ponto > 0 && $idCobrancaPonto) {
                        $metricaParcial = DB::table('metrica_parcial')
                            ->where('id_metrica_fk', $idMetrica)
                            ->first();

                        if ($metricaParcial) {
                            DB::table('metrica_parcial')
                                ->where('id_metrica_fk', $idMetrica)
                                ->update([
                                    'id_cobranca' => $idCobrancaPonto,
                                    'comissao_locacao' => $comissaoLocacao,
                                    'comissao_ponto' => $comissaoPonto,
                                    'comissao_dono' => $comissaoDono,
                                    'acerto' => $acerto,
                                    'saldo_total' => $saldoTotal,
                                    'entrada' => $entrada,
                                    'saida' => $saida,
                                ]);
                        } else {
                            DB::table('metrica_parcial')->insert([
                                'id_metrica_fk' => $idMetrica,
                                'id_cobranca' => $idCobrancaPonto,
                                'comissao_locacao' => $comissaoLocacao,
                                'comissao_ponto' => $comissaoPonto,
                                'comissao_dono' => $comissaoDono,
                                'acerto' => $acerto,
                                'idprod' => $idprod,
                                'saldo_total' => $saldoTotal,
                                'entrada' => $entrada,
                                'saida' => $saida,
                                'entrada_anterior' => $entradaAnterior,
                                'saida_anterior' => $saidaAnterior,
                                'data_zerou_parcial' => null,
                                'ativo' => 1,
                            ]);
                        }
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Atualiza totais das cobranças
                |--------------------------------------------------------------------------
                */
                if ($acao > 0 && $idCobranca) {
                    $valorTotalLocacao = DB::table('metricas')
                        ->where('id_cobranca', $idCobranca)
                        ->selectRaw('COALESCE(SUM(comissao_locacao + comissao_dono), 0) as total')
                        ->value('total');

                    DB::table('cobranca_locacao')
                        ->where('id_cobranca', $idCobranca)
                        ->update([
                            'valor_total' => $valorTotalLocacao,
                        ]);

                    $valorTotalAgregado = DB::table('cobranca_locacao')
                        ->where('id_cobranca_agregado', $idCobrancaAgregado)
                        ->selectRaw('COALESCE(SUM(valor_total), 0) as total')
                        ->value('total');

                    DB::table('cobranca_agregado')
                        ->where('id_cobranca', $idCobrancaAgregado)
                        ->update([
                            'valor_total' => $valorTotalAgregado,
                        ]);

                    if ((int) $porcentagens->id_ponto > 0 && $idCobrancaPonto) {
                        $valorTotalPonto = DB::table('metrica_parcial')
                            ->where('id_cobranca', $idCobrancaPonto)
                            ->selectRaw('COALESCE(SUM(comissao_ponto), 0) as total')
                            ->value('total');

                        DB::table('cobranca_ponto')
                            ->where('id_cobranca', $idCobrancaPonto)
                            ->update([
                                'valor_total' => $valorTotalPonto,
                            ]);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Zerar leitura virtual
            |--------------------------------------------------------------------------
            */
            if ($entradaVirtual == 0 && $saidaVirtual == 0) {
                DB::table('tablet')
                    ->where('idprod', $idprod)
                    ->update([
                        'zerarleituravirtual' => 0,
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Garante id_metrica para tabela leitura
            |--------------------------------------------------------------------------
            */
            if (!$idMetrica) {
                $ultimaMetrica = DB::table('metricas')
                    ->where('idprod', $idprod)
                    ->orderByDesc('dataorder')
                    ->orderByDesc('id')
                    ->first();

                $idMetrica = $ultimaMetrica ? $ultimaMetrica->id : null;
            }

            /*
            |--------------------------------------------------------------------------
            | Insere ou atualiza leitura
            |--------------------------------------------------------------------------
            */
            $leitura = DB::table('leitura')
                ->where('idprod', $idprod)
                ->first();

            if (!$leitura) {
                DB::table('leitura')->insert([
                    'id_metrica' => $idMetrica,
                    'idprod' => $idprod,
                    'creditos' => '0',
                    'entrada' => $entrada,
                    'saida' => $saida,
                    'entrada_virtual' => $entradaVirtual,
                    'saida_virtual' => $saidaVirtual,
                    'apostado' => 0,
                    'premiado' => 0,
                    'data' => substr($data, 0, 16),
                    'status' => $status,
                    'dataorder' => $dataNow,
                    'ativo' => 1,
                ]);
            } else {
                DB::table('leitura')
                    ->where('idprod', $idprod)
                    ->update([
                        'id_metrica' => $idMetrica,
                        'entrada' => $entrada,
                        'saida' => $saida,
                        'entrada_virtual' => $entradaVirtual,
                        'saida_virtual' => $saidaVirtual,
                        'data' => substr($data, 0, 16),
                        'status' => $status,
                        'dataorder' => $dataNow,
                        'ativo' => 1,
                    ]);
            }

            DB::commit();

            return response('OK---splithere---', 200)
                ->header('Content-Type', 'text/plain');

        } catch (\Throwable $e) {
            DB::rollBack();

            \Log::error('Erro sendleiturarealtime.php', [
                'erro' => $e->getMessage(),
                'linha' => $e->getLine(),
                'arquivo' => $e->getFile(),
                'request' => $request->all(),
            ]);

            return response('ERRO#' . $e->getMessage() . '#', 200)
                ->header('Content-Type', 'text/plain');
        }
    }

    private function obterOuCriarCobrancasLegado($tablet, $periodoPrestacao, $dataNow = null)
    {
        $dataNow = $dataNow ?: now()->format('Y-m-d H:i:s');

        $clienteId = (int) ($tablet->id_apoio ?? 0);
        $pontoId = (int) ($tablet->id_ponto ?? 0);

        if ($clienteId <= 0) {
            throw new \Exception('Cliente do tablet não informado.');
        }

        /*
        |--------------------------------------------------------------------------
        | Confirma se o cliente/dono existe
        |--------------------------------------------------------------------------
        */
        $cliente = DB::table('users')
            ->where('id', $clienteId)
            ->first();

        if (!$cliente) {
            throw new \Exception('Cliente do tablet não encontrado.');
        }

        /*
        |--------------------------------------------------------------------------
        | Regra igual ao legado
        |--------------------------------------------------------------------------
        | O legado usa:
        | tipo_cobranca = Mensal
        | periodo_prestacao = último dia do mês atual
        | data_vencimento = periodo_prestacao + 5 dias
        */
        $tipoCobranca = 'Mensal';

        $dataVencimento = \Carbon\Carbon::parse($periodoPrestacao)
            ->addDays(5)
            ->toDateString();

        /*
        |--------------------------------------------------------------------------
        | 1. Busca ou cria cobranca_agregado aberta
        |--------------------------------------------------------------------------
        | Só pode reaproveitar cobrança aberta:
        | pago = 0
        | cobranca_fechada = 0
        */
        $cobrancaAgregado = DB::table('cobranca_agregado')
            ->where('id_cliente', $clienteId)
            ->where('periodo_prestacao', $periodoPrestacao)
            ->where('pago', 0)
            ->where('cobranca_fechada', 0)
            ->orderByDesc('id_cobranca')
            ->first();

        if ($cobrancaAgregado) {
            $idCobrancaAgregado = $cobrancaAgregado->id_cobranca;
        } else {
            $idCobrancaAgregado = DB::table('cobranca_agregado')->insertGetId([
                'id_cliente' => $clienteId,
                'data_processamento' => $dataNow,
                'periodo_prestacao' => $periodoPrestacao,
                'tipo_cobranca' => $tipoCobranca,
                'data_vencimento' => $dataVencimento,
                'valor_total' => 0,
                'pago' => 0,
                'data_pagamento' => null,
                'cobranca_fechada' => 0,
                'ativo' => 1,
            ], 'id_cobranca');
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Busca ou cria cobranca_locacao aberta
        |--------------------------------------------------------------------------
        | Correção importante:
        | Agora só reaproveita locação que esteja vinculada à agregada aberta atual.
        |
        | Isso evita:
        | cobranca_locacao apontando para cobranca_agregado inexistente.
        */
        $cobrancaLocacao = DB::table('cobranca_locacao')
            ->where('id_cliente', $clienteId)
            ->where('id_cobranca_agregado', $idCobrancaAgregado)
            ->where('periodo_prestacao', $periodoPrestacao)
            ->where('pago', 0)
            ->where('cobranca_fechada', 0)
            ->orderByDesc('id_cobranca')
            ->first();

        if ($cobrancaLocacao) {
            $idCobrancaLocacao = $cobrancaLocacao->id_cobranca;
        } else {
            $idCobrancaLocacao = DB::table('cobranca_locacao')->insertGetId([
                'id_cliente' => $clienteId,
                'id_cobranca_agregado' => $idCobrancaAgregado,
                'data_processamento' => $dataNow,
                'periodo_prestacao' => $periodoPrestacao,
                'tipo_cobranca' => $tipoCobranca,
                'data_vencimento' => $dataVencimento,
                'valor_total' => 0,
                'pago' => 0,
                'data_pagamento' => null,
                'cobranca_fechada' => 0,
                'ativo' => 1,
            ], 'id_cobranca');
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Busca ou cria cobranca_ponto aberta
        |--------------------------------------------------------------------------
        | Só cria se o tablet tiver ponto.
        |
        | Correção importante:
        | Agora só reaproveita cobrança de ponto vinculada à locação aberta atual.
        */
        $idCobrancaPonto = null;

        if ($pontoId > 0) {
            $cobrancaPonto = DB::table('cobranca_ponto')
                ->where('id_ponto', $pontoId)
                ->where('id_cobranca_locacao', $idCobrancaLocacao)
                ->where('periodo_prestacao', $periodoPrestacao)
                ->where('pago', 0)
                ->where('cobranca_fechada', 0)
                ->orderByDesc('id_cobranca')
                ->first();

            if ($cobrancaPonto) {
                $idCobrancaPonto = $cobrancaPonto->id_cobranca;
            } else {
                $idCobrancaPonto = DB::table('cobranca_ponto')->insertGetId([
                    'id_ponto' => $pontoId,
                    'id_cobranca_locacao' => $idCobrancaLocacao,
                    'data_processamento' => $dataNow,
                    'periodo_prestacao' => $periodoPrestacao,
                    'tipo_cobranca' => $tipoCobranca,
                    'data_vencimento' => $dataVencimento,
                    'valor_total' => 0,
                    'pago' => 0,
                    'data_pagamento' => null,
                    'cobranca_fechada' => 0,
                    'ativo' => 1,
                ], 'id_cobranca');
            }
        }

        return [
            'id_cobranca_agregado' => $idCobrancaAgregado,
            'id_cobranca_locacao' => $idCobrancaLocacao,
            'id_cobranca_ponto' => $idCobrancaPonto,
        ];
    }

    public function verificarStatus(Request $request)
    {
        if (!$request->filled('idprod') || !$request->filled('dono')) {
            return response('V#', 200)
                ->header('Content-Type', 'text/plain');
        }

        $idprod = strtoupper(substr($request->get('idprod'), 0, 4));
        $dono = $request->get('dono');

        $tablet = DB::table('tablet')
            ->where('idprod', $idprod)
            ->where('cliente', $dono)
            ->first();

        if (!$tablet) {
            return response('NC#', 200)
                ->header('Content-Type', 'text/plain');
        }

        DB::table('tablet')
            ->where('id', $tablet->id)
            ->update([
                'ultimo_contato' => now(),
            ]);

        if ((int) $tablet->ativo === 0) {
            return response('OFF#', 200)
                ->header('Content-Type', 'text/plain');
        }

        return response('ON#1.0.2', 200)
            ->header('Content-Type', 'text/plain');
    }

    public function sendLeituraRealtimeDbSync(Request $request)
    {
        $idprod = strtoupper(substr($request->get('idprod', ''), 0, 4));

        if (!$idprod) {
            return response('', 200)
                ->header('Content-Type', 'text/plain');
        }

        $creditos = (string) $request->get('creditos', '0');
        $apostado = (float) $request->get('apostado', 0);
        $premiado = (float) $request->get('premiado', 0);

        DB::table('leitura')
            ->where('idprod', $idprod)
            ->update([
                'creditos' => substr($creditos, 0, 12),
                'apostado' => $apostado,
                'premiado' => $premiado,
            ]);

        return response('', 200)
            ->header('Content-Type', 'text/plain');
    }

    private function normalizarDecimalUnity($valor)
    {
        if ($valor === null || $valor === '') {
            return 0;
        }

        $valor = trim((string) $valor);

        /*
         * A Unity pode mandar:
         * 400,12
         * 400.12
         * 1.400,12
         * 1,400.12
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

    public function travarAcumulado(Request $request)
    {
        $idprod = strtoupper(trim($request->get('idprod', '')));

        if (empty($idprod)) {
            return response('idprod não informado', 400)
                ->header('Content-Type', 'text/plain');
        }

        $tablet = DB::table('tablet')
            ->where('idprod', $idprod)
            ->first();

        if (!$tablet) {
            return response('Tablet inexistente', 400)
                ->header('Content-Type', 'text/plain');
        }

        DB::table('tablet')
            ->where('idprod', $idprod)
            ->update([
                'destrava' => 0,
            ]);

        return response('OK', 200)
            ->header('Content-Type', 'text/plain');
    }

    public function travarBonusBau(Request $request)
    {
        $idprod = strtoupper(trim($request->get('idprod', '')));

        if (empty($idprod)) {
            return response('idprod não informado', 400)
                ->header('Content-Type', 'text/plain');
        }

        $tablet = DB::table('tablet')
            ->where('idprod', $idprod)
            ->first();

        if (!$tablet) {
            return response('Tablet inexistente', 400)
                ->header('Content-Type', 'text/plain');
        }

        DB::table('tablet')
            ->where('idprod', $idprod)
            ->update([
                'status_bonus' => 0,
                'valor' => 0,
            ]);

        return response('OK', 200)
            ->header('Content-Type', 'text/plain');
    }

    public function pesquisaUltimasTransacoes(Request $request)
    {
        $idprod = strtoupper(substr(trim($request->get('idprod', '')), 0, 20));
        $tipo = trim($request->get('tipo', ''));

        /*
        |--------------------------------------------------------------------------
        | Compatibilidade com legado
        |--------------------------------------------------------------------------
        | No legado não havia validação forte.
        | Se faltar parâmetro, retornamos NULL para não quebrar a Unity.
        */
        if (empty($idprod) || $tipo === '') {
            return response('NULL', 200)
                ->header('Content-Type', 'text/plain');
        }

        $transacoes = DB::table('transacoes')
            ->where('idprod', $idprod)
            ->where('tipo', $tipo)
            ->orderByDesc('data_hora')
            ->limit(14)
            ->get();

        if ($transacoes->isEmpty()) {
            return response('NULL', 200)
                ->header('Content-Type', 'text/plain');
        }

        $resposta = '';

        foreach ($transacoes as $transacao) {
            $dataHora = $transacao->data_hora ?? '';
            $valor = $this->formatarValorTransacaoLegado($transacao->valor ?? 0);

            $resposta .= $dataHora
                . '----------------------------------------------------------------------'
                . 'R$ '
                . $valor
                . '#';
        }

        return response($resposta, 200)
            ->header('Content-Type', 'text/plain');
    }

    private function formatarValorTransacaoLegado($valor)
    {
        $valor = (float) ($valor ?? 0);

        /*
        |--------------------------------------------------------------------------
        | Formato legado
        |--------------------------------------------------------------------------
        | O legado imprimia:
        | R$ 10,00
        |
        | Mesmo quando o valor vinha inteiro.
        */
        return number_format($valor, 2, ',', '.');
    }

    public function addCrashLog(Request $request)
    {
        try {
            $idprod = strtoupper(substr(trim($request->get('idprod', '')), 0, 20));
            $platform = substr(trim($request->get('platform', '')), 0, 100);
            $version = substr(trim($request->get('version', '')), 0, 100);
            $text = trim($request->get('text', ''));

            /*
            |--------------------------------------------------------------------------
            | Compatibilidade com legado
            |--------------------------------------------------------------------------
            | O legado gravava direto e retornava o resultado do mysql_query.
            | Aqui, se faltar algo importante, retornamos 0 em text/plain.
            */
            if (empty($idprod) || empty($text)) {
                return response('0', 200)
                    ->header('Content-Type', 'text/plain');
            }

            $result = DB::table('crashlog')->insert([
                'datetime' => now()->format('Y-m-d H:i:s'),
                'idprod' => $idprod,
                'platform' => $platform,
                'version' => $version,
                'text' => $text,
            ]);

            return response($result ? '1' : '0', 200)
                ->header('Content-Type', 'text/plain');

        } catch (\Throwable $e) {
            \Log::error('Erro add_crashlog.php', [
                'erro' => $e->getMessage(),
                'linha' => $e->getLine(),
                'arquivo' => $e->getFile(),
                'request' => $request->all(),
            ]);

            return response('0', 200)
                ->header('Content-Type', 'text/plain');
        }
    }
}