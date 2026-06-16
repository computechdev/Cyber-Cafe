<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class TabletController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'todos');
        $busca = $request->get('busca');

        $agora = now();

        $query = DB::table('tablet')
            ->leftJoin('ponto', 'ponto.id', '=', 'tablet.id_ponto')
            ->leftJoin('leitura', 'leitura.idprod', '=', 'tablet.idprod')
            ->select(
                'tablet.*',
                'ponto.nome as ponto_nome',
                'leitura.creditos as creditos_leitura'
            )
            ->orderBy('tablet.idprod');

        if (!empty($busca)) {
            $query->where(function ($q) use ($busca) {
                $q->where('tablet.idprod', 'LIKE', "%{$busca}%")
                    ->orWhere('tablet.cliente', 'LIKE', "%{$busca}%")
                    ->orWhere('ponto.nome', 'LIKE', "%{$busca}%");
            });
        }

        $tabletsTodos = $query->get()->map(function ($tablet) use ($agora) {
            $ultimoContato = $tablet->ultimo_contato
                ? \Carbon\Carbon::parse($tablet->ultimo_contato)
                : null;

            $tablet->esta_online = false;

            if ($ultimoContato) {
                $tablet->esta_online = $ultimoContato->diffInSeconds($agora) <= 30;
            }

            $tablet->esta_desabilitado = (int) ($tablet->ativo ?? 1) === 0;

            $tablet->status_conexao = $tablet->esta_online ? 'Online' : 'Offline';
            $tablet->status_sistema = $tablet->esta_desabilitado ? 'Desabilitado' : 'Habilitado';

            $tablet->credito_atual = $tablet->creditos_leitura ?? 0;

            return $tablet;
        });

        $resumo = [
            'total' => $tabletsTodos->count(),
            'online' => $tabletsTodos->where('esta_online', true)->count(),
            'offline' => $tabletsTodos->where('esta_online', false)->where('esta_desabilitado', false)->count(),
            'desabilitados' => $tabletsTodos->where('esta_desabilitado', true)->count(),
        ];

        $tablets = $tabletsTodos;

        if ($status === 'online') {
            $tablets = $tablets->where('esta_online', true);
        }

        if ($status === 'offline') {
            $tablets = $tablets->where('esta_online', false)->where('esta_desabilitado', false);
        }

        if ($status === 'desabilitados') {
            $tablets = $tablets->where('esta_desabilitado', true);
        }

        return view('tablets.index', compact(
            'tablets',
            'resumo',
            'status',
            'busca'
        ));
    }
    public function toggleAtivo($id)
    {
        $tablet = DB::table('tablet')
            ->where('id', $id)
            ->first();

        if (!$tablet) {
            return redirect()
                ->route('tablets.index')
                ->with('error', 'Tablet não encontrado.');
        }

        $novoStatus = (int) $tablet->ativo === 1 ? 0 : 1;

        DB::table('tablet')
            ->where('id', $id)
            ->update([
                'ativo' => $novoStatus,
            ]);

        $mensagem = $novoStatus === 1
            ? 'Tablet habilitado com sucesso.'
            : 'Tablet desabilitado com sucesso.';

        return redirect()
            ->route('tablets.index')
            ->with('success', $mensagem);
    }

    public function creditos($id)
    {
        $tablet = DB::table('tablet')
            ->leftJoin('users', 'users.id', '=', 'tablet.id_apoio')
            ->leftJoin('ponto', 'ponto.id', '=', 'tablet.id_ponto')
            ->select(
                'tablet.*',
                'users.name as cliente_nome',
                'ponto.nome as ponto_nome'
            )
            ->where('tablet.id', $id)
            ->first();

        if (!$tablet) {
            return redirect()
                ->route('tablets.index')
                ->with('error', 'Tablet não encontrado.');
        }

        return view('tablets.creditos', compact('tablet'));
    }

    public function storeCreditos(Request $request, $id)
    {
        $tablet = DB::table('tablet')
            ->where('id', $id)
            ->first();

        if (!$tablet) {
            return redirect()
                ->route('tablets.index')
                ->with('error', 'Tablet não encontrado.');
        }

        $request->validate([
            'valor' => 'required|integer|min:1',
        ], [
            'valor.required' => 'Informe o valor do crédito.',
            'valor.integer' => 'O valor do crédito deve ser um número inteiro.',
            'valor.min' => 'O valor do crédito precisa ser maior que zero.',
        ]);

        $creditoExistente = DB::table('creditos')
            ->where('idprod', $tablet->idprod)
            ->orderByDesc('id')
            ->first();

        if ($creditoExistente) {
            DB::table('creditos')
                ->where('id', $creditoExistente->id)
                ->update([
                    'data' => now(),
                    'valor' => $request->valor,
                    'status' => 1,
                    'tipo' => 1,
                ]);
        } else {
            DB::table('creditos')->insert([
                'idprod' => $tablet->idprod,
                'data' => now(),
                'valor' => $request->valor,
                'status' => 1,
                'tipo' => 1,
            ]);
        }

        return redirect()
            ->route('tablets.index')
            ->with('success', 'Crédito enviado com sucesso.');
    }

    public function getTabletInfo(Request $request)
    {
        $idprod = $request->get('idprod');

        if (!$idprod) {
            return response()->json([
                'success' => false,
                'message' => 'Tablet não informado.',
            ]);
        }

        $tablet = DB::table('tablet')
            ->leftJoin('leitura', 'leitura.idprod', '=', 'tablet.idprod')
            ->select(
                'tablet.idprod',
                'tablet.ultimo_contato',
                'tablet.ativo',
                'tablet.ligado',
                'tablet.status',
                'leitura.creditos'
            )
            ->where('tablet.idprod', $idprod)
            ->first();

        if (!$tablet) {
            return response()->json([
                'success' => false,
                'message' => 'Tablet não encontrado.',
            ]);
        }

        $ultimoContato = $tablet->ultimo_contato
            ? \Carbon\Carbon::parse($tablet->ultimo_contato)
            : null;

        /*
        |--------------------------------------------------------------------------
        | Tempo para considerar online
        |--------------------------------------------------------------------------
        | A Unity chama verificar_realtime.php a cada 7 segundos.
        | Então 15 segundos é um bom tempo.
        */
        $online = false;

        if ($ultimoContato) {
            $online = $ultimoContato->diffInSeconds(now()) <= 15;
        }

        $valorOriginal = $tablet->creditos ?? '0';

        // 1. Remove o "R$" e os espaços em branco
        $apenasNumeros = str_replace(['R$', ' '], '', $valorOriginal);

        // 2. Troca a vírgula por ponto
        $comPonto = str_replace(',', '.', $apenasNumeros);

        // 3. Agora sim, faz o cast para float com segurança!
        $creditos = (float) $comPonto;
        return response()
            ->json([
                'success' => true,
                'idprod' => $tablet->idprod,
                'creditos' => $creditos,
                'creditos_formatado' => 'R$ ' . number_format($creditos, 2, ',', '.'),
                'ultimo_contato' => $ultimoContato ? $ultimoContato->format('d/m/Y H:i:s') : '-',
                'online' => $online,
                'status_conexao' => $online ? 'Online' : 'Offline',
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }
    public function detalhes($id)
    {
        $tablet = DB::table('tablet')
            ->leftJoin('users', 'users.id', '=', 'tablet.id_apoio')
            ->leftJoin('ponto', 'ponto.id', '=', 'tablet.id_ponto')
            ->select(
                'tablet.*',
                'users.name as cliente_nome',
                'ponto.nome as ponto_nome'
            )
            ->where('tablet.id', $id)
            ->first();

        if (!$tablet) {
            return redirect()
                ->route('tablets.index')
                ->with('error', 'Tablet não encontrado.');
        }

        $leitura = DB::table('leitura')
            ->where('idprod', $tablet->idprod)
            ->where('ativo', true)
            ->orderByDesc('id')
            ->first();

        $ultimaTransacao = DB::table('transacoes')
            ->where('idprod', $tablet->idprod)
            ->orderByDesc('data_hora')
            ->first();

        return view('tablets.detalhes', compact(
            'tablet',
            'leitura',
            'ultimaTransacao'
        ));
    }

    public function detalhesModal($id)
    {
        $tablet = DB::table('tablet')
            ->leftJoin('users', 'users.id', '=', 'tablet.id_apoio')
            ->leftJoin('ponto', 'ponto.id', '=', 'tablet.id_ponto')
            ->select(
                'tablet.*',
                'users.name as cliente_nome',
                'ponto.nome as ponto_nome'
            )
            ->where('tablet.id', $id)
            ->first();

        if (!$tablet) {
            return response()->json([
                'success' => false,
                'message' => 'Tablet não encontrado.'
            ]);
        }

        $leitura = DB::table('leitura')
            ->where('idprod', $tablet->idprod)
            ->where('ativo', true)
            ->orderByDesc('id')
            ->first();

        $ultimaTransacao = DB::table('transacoes')
            ->where('idprod', $tablet->idprod)
            ->orderByDesc('data_hora')
            ->first();

        $html = view('tablets.partials.detalhes-modal', compact(
            'tablet',
            'leitura',
            'ultimaTransacao'
        ))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    public function zerarLeituraVirtual($id)
    {
        $tablet = DB::table('tablet')
            ->where('id', $id)
            ->first();

        if (!$tablet) {
            return redirect()
                ->route('tablets.index')
                ->with('error', 'Tablet não encontrado.');
        }

        DB::table('tablet')
            ->where('id', $id)
            ->update([
                'zerarleituravirtual' => true,
            ]);

        return redirect()
            ->route('tablets.index')
            ->with('success', 'Comando para zerar leitura virtual enviado com sucesso.');
    }

    public function edit($id)
    {
        $tablet = DB::table('tablet')
            ->leftJoin('ponto', 'ponto.id', '=', 'tablet.id_ponto')
            ->select(
                'tablet.*',
                'ponto.nome as ponto_nome'
            )
            ->where('tablet.id', $id)
            ->first();

        if (!$tablet) {
            return redirect()
                ->route('tablets.index')
                ->with('error', 'Tablet não encontrado.');
        }

        $pontos = DB::table('ponto')
            ->join('users', 'users.id', '=', 'ponto.id_apoio')
            ->where('users.username', $tablet->cliente)
            ->where('ponto.id', '<>', $tablet->id_ponto)
            ->orderBy('ponto.nome')
            ->select('ponto.id', 'ponto.nome')
            ->get();

        return view('tablets.edit', compact('tablet', 'pontos'));
    }

    // public function update(Request $request, $id)
    // {
    //     $tablet = DB::table('tablet')
    //         ->where('id', $id)
    //         ->first();

    //     if (!$tablet) {
    //         return redirect()
    //             ->route('tablets.index')
    //             ->with('error', 'Tablet não encontrado.');
    //     }

    //     $request->validate([
    //         'pendrive_id' => 'required|integer|min:1',
    //         'id_ponto' => 'nullable|integer',
    //         'centavosbingo' => 'required|integer|min:0',
    //         'porcent_acumu' => 'required|numeric|min:0',
    //         'apostamaxhalloween' => 'required|integer|min:0',
    //         'dificuldade' => 'required|integer|min:40',


    //         'acum1min' => 'required|numeric',
    //         'acum1med' => 'required|numeric',
    //         'acum1max' => 'required|numeric',

    //         'acum2min' => 'required|numeric',
    //         'acum2med' => 'required|numeric',
    //         'acum2max' => 'required|numeric',

    //         'acum3min' => 'required|numeric',
    //         'acum3med' => 'required|numeric',
    //         'acum3max' => 'required|numeric',

    //         'acum4min' => 'required|numeric',
    //         'acum4med' => 'required|numeric',
    //         'acum4max' => 'required|numeric',

    //         'acum5min' => 'required|numeric',
    //         'acum5med' => 'required|numeric',
    //         'acum5max' => 'required|numeric',

    //         'acum6min' => 'required|numeric',
    //         'acum6med' => 'required|numeric',
    //         'acum6max' => 'required|numeric',
    //     ]);

    //     DB::table('tablet')
    //         ->where('id', $id)
    //         ->update([
    //             'pendrive_id' => $request->pendrive_id,
    //             'id_ponto' => $request->id_ponto ?? 0,

    //             'centavosbingo' => $request->centavosbingo,
    //             'zerar' => (int) $request->zerar,
    //             'porcent_acumu' => $request->porcent_acumu,
    //             'apostamaxhalloween' => $request->apostamaxhalloween,

    //             'destrava' => (int) $request->destrava,
    //             'creditoteclado' => (int) $request->creditoteclado,
    //             'matematica_slot' => (int) $request->matematica_slot,
    //             'modo_slot' => (int) $request->modo_slot,
    //             'zerarleituraparcial' => (int) $request->zerarleituraparcial,
    //             'zerarleituraoficialbackup' => (int) $request->zerarleituraoficialbackup,
    //             'leituraonlinesincronizada' => (int) $request->leituraonlinesincronizada,

    //             'dificuldade' => $request->dificuldade,
    //             'lastversion' => DB::raw('lastversion + 1'),

    //             'acum1min' => $request->acum1min,
    //             'acum1med' => $request->acum1med,
    //             'acum1max' => $request->acum1max,

    //             'acum2min' => $request->acum2min,
    //             'acum2med' => $request->acum2med,
    //             'acum2max' => $request->acum2max,

    //             'acum3min' => $request->acum3min,
    //             'acum3med' => $request->acum3med,
    //             'acum3max' => $request->acum3max,

    //             'acum4min' => $request->acum4min,
    //             'acum4med' => $request->acum4med,
    //             'acum4max' => $request->acum4max,

    //             'acum5min' => $request->acum5min,
    //             'acum5med' => $request->acum5med,
    //             'acum5max' => $request->acum5max,

    //             'acum6min' => $request->acum6min,
    //             'acum6med' => $request->acum6med,
    //             'acum6max' => $request->acum6max,
    //         ]);

    //     return redirect()
    //         ->route('tablets.index')
    //         ->with('success', 'Tablet atualizado com sucesso.');
    // }


    public function update(Request $request, $id)
    {
        $tablet = DB::table('tablet')
            ->where('id', $id)
            ->first();

        if (!$tablet) {
            return redirect()
                ->route('tablets.index')
                ->with('error', 'Tablet não encontrado.');
        }

        $dados = $this->montarDadosUpdateTablet($request, $tablet);
        //dd($dados);
        DB::table('tablet')
            ->where('id', $tablet->id)
            ->update($dados);

        return redirect()
            ->route('tablets.index')
            ->with('success', 'Tablet alterado com sucesso!');
    }
    public function destroy($id)
    {
        $tablet = DB::table('tablet')
            ->where('id', $id)
            ->first();

        if (!$tablet) {
            return redirect()
                ->route('tablets.index')
                ->with('error', 'Tablet não encontrado.');
        }

        $nivelUsuario = (int) auth()->user()->nivel;
        $idprod = $tablet->idprod;

        try {
            DB::beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | ADMINISTRADOR
            |--------------------------------------------------------------------------
            | No legado:
            | nivel 1 deleta totalmente o tablet e os dados vinculados ao idprod.
            */
            if ($nivelUsuario === 1) {
                DB::table('metricas')
                    ->where('idprod', $idprod)
                    ->delete();

                DB::table('metrica_parcial')
                    ->where('idprod', $idprod)
                    ->delete();

                DB::table('transacoes')
                    ->where('idprod', $idprod)
                    ->delete();

                DB::table('leitura')
                    ->where('idprod', $idprod)
                    ->delete();

                DB::table('creditos')
                    ->where('idprod', $idprod)
                    ->delete();

                if (Schema::hasTable('crashlog')) {
                    DB::table('crashlog')
                        ->where('idprod', $idprod)
                        ->delete();
                }

                DB::table('tablet')
                    ->where('idprod', $idprod)
                    ->delete();

                DB::commit();

                return redirect()
                    ->route('tablets.index')
                    ->with('success', 'Tablet deletado com sucesso.');
            }

            /*
            |--------------------------------------------------------------------------
            | CLIENTE / OUTROS NÍVEIS OPERACIONAIS
            |--------------------------------------------------------------------------
            | No legado:
            | nivel 3 não deleta, apenas desativa.
            |
            | Aqui deixamos nível 2 e 3 como desativação por segurança.
            */
            if (in_array($nivelUsuario, [2, 3])) {
                DB::table('tablet')
                    ->where('idprod', $idprod)
                    ->update([
                        'ativo' => 0,
                        'status' => 0,
                        'ligado' => 0,
                    ]);

                DB::commit();

                return redirect()
                    ->route('tablets.index')
                    ->with('success', 'Tablet desativado com sucesso.');
            }

            DB::rollBack();

            return redirect()
                ->route('tablets.index')
                ->with('error', 'Você não tem permissão para excluir este tablet.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->route('tablets.index')
                ->with('error', 'Erro ao deletar o tablet: ' . $e->getMessage());
        }
    }

    private function montarDadosUpdateTablet(Request $request, $tablet)
    {
        //dd($request->all());

        return [
            'porcent_acumu' => $this->valorRequest($request, 'porcent_acumu', $tablet->porcent_acumu ?? 0),

            'acum1min' => $this->valorRequest($request, 'acum1min', $tablet->acum1min ?? 0),
            'acum1med' => $this->valorRequest($request, 'acum1med', $tablet->acum1med ?? 0),
            'acum1max' => $this->valorRequest($request, 'acum1max', $tablet->acum1max ?? 0),

            'acum2min' => $this->valorRequest($request, 'acum2min', $tablet->acum2min ?? 0),
            'acum2med' => $this->valorRequest($request, 'acum2med', $tablet->acum2med ?? 0),
            'acum2max' => $this->valorRequest($request, 'acum2max', $tablet->acum2max ?? 0),

            'acum3min' => $this->valorRequest($request, 'acum3min', $tablet->acum3min ?? 0),
            'acum3med' => $this->valorRequest($request, 'acum3med', $tablet->acum3med ?? 0),
            'acum3max' => $this->valorRequest($request, 'acum3max', $tablet->acum3max ?? 0),

            'acum4min' => $this->valorRequest($request, 'acum4min', $tablet->acum4min ?? 0),
            'acum4med' => $this->valorRequest($request, 'acum4med', $tablet->acum4med ?? 0),
            'acum4max' => $this->valorRequest($request, 'acum4max', $tablet->acum4max ?? 0),

            'acum5min' => $this->valorRequest($request, 'acum5min', $tablet->acum5min ?? 0),
            'acum5med' => $this->valorRequest($request, 'acum5med', $tablet->acum5med ?? 0),
            'acum5max' => $this->valorRequest($request, 'acum5max', $tablet->acum5max ?? 0),

            'acum6min' => $this->valorRequest($request, 'acum6min', $tablet->acum6min ?? 0),
            'acum6med' => $this->valorRequest($request, 'acum6med', $tablet->acum6med ?? 0),
            'acum6max' => $this->valorRequest($request, 'acum6max', $tablet->acum6max ?? 0),

            'acumSenaMin' => $this->valorRequest($request, 'acumSenaMin', $tablet->acumSenaMin ?? 0),
            'acumSenaAtu' => $this->valorRequest($request, 'acumSenaAtu', $tablet->acumSenaAtu ?? 0),
            'acumSenaMax' => $this->valorRequest($request, 'acumSenaMax', $tablet->acumSenaMax ?? 0),

            'acumBombaMin' => $this->valorRequest($request, 'acumBombaMin', $tablet->acumBombaMin ?? 0),
            'acumBombaAtu' => $this->valorRequest($request, 'acumBombaAtu', $tablet->acumBombaAtu ?? 0),
            'acumBombaMax' => $this->valorRequest($request, 'acumBombaMax', $tablet->acumBombaMax ?? 0),

            'creditoteclado' => $this->valorRequest($request, 'creditoteclado', $tablet->creditoteclado ?? 0),
            'centavosbingo' => $this->valorRequest($request, 'centavosbingo', $tablet->centavosbingo ?? 10),
            'apostamaxhalloween' => $this->valorRequest($request, 'apostamaxhalloween', $tablet->apostamaxhalloween ?? 50),

            'leituraonlinesincronizada' => $this->valorRequest($request, 'leituraonlinesincronizada', $tablet->leituraonlinesincronizada ?? 1),
            'zerarleituraparcial' => $this->valorRequest($request, 'zerarleituraparcial', $tablet->zerarleituraparcial ?? 0),
            'zerarleituraoficialbackup' => $this->valorRequest($request, 'zerarleituraoficialbackup', $tablet->zerarleituraoficialbackup ?? 0),

            'dificuldade' => $this->valorRequest($request, 'dificuldade', $tablet->dificuldade ?? 95),
            'zerar' => $this->valorRequest($request, 'zerar', $tablet->zerar ?? 0),
            'destrava' => $this->valorRequest($request, 'destrava', $tablet->destrava ?? 0),

            'id_ponto' => $this->valorRequest($request, 'ponto', $tablet->id_ponto ?? null),
            'pendrive_id' => $this->valorRequest($request, 'pendrive', $tablet->pendrive_id ?? 1),

            'matematica_slot' => $this->valorRequest($request, 'matematica_slot', $tablet->matematica_slot ?? 1),
            'modo_slot' => $this->valorRequest($request, 'modoSlot', $tablet->modo_slot ?? 3),

            'retencao_slots' => $this->valorRequest($request, 'retencao', $tablet->retencao_slots ?? 1),
            'dificul_bonus' => $this->valorRequest($request, 'dificul_bonus', $tablet->dificul_bonus ?? 10000),

            /*
             * No legado, estes campos vêm da tela em porcentagem.
             * Exemplo:
             * 55 na tela vira 0.55 no banco.
             */
            'taxa_maxima' => $this->percentualRequest($request, 'taxa_maxima', $tablet->taxa_maxima ?? 0.55),
            'acrecimo_taxa_max' => $this->percentualRequest($request, 'acrecimo_taxa_max', $tablet->acrecimo_taxa_max ?? 0.03),

            'periodo_reducao' => $this->valorRequest($request, 'periodo_reducao', $tablet->periodo_reducao ?? 10000),
            'extras_min_rotina' => $this->valorRequest($request, 'extras_min_rotina', $tablet->extras_min_rotina ?? 30),
            'saldo_para_acumulao' => $this->valorRequest($request, 'saldo_para_acumulao', $tablet->saldo_para_acumulao ?? 100000),

            'premio_do_acumulado' => $this->valorRequest($request, 'premio_do_acumulado', $tablet->premio_do_acumulado ?? 200),
            'tam_premio_max' => $this->valorRequest($request, 'tam_premio_max', $tablet->tam_premio_max ?? 3),

            'saldo_min_bingo_g1' => $this->valorRequest($request, 'saldo_min_bingo_g1', $tablet->saldo_min_bingo_g1 ?? 35000),
            'saldo_min_bingo_g2' => $this->valorRequest($request, 'saldo_min_bingo_g2', $tablet->saldo_min_bingo_g2 ?? 50000),
            'saldo_min_bingo_g3' => $this->valorRequest($request, 'saldo_min_bingo_g3', $tablet->saldo_min_bingo_g3 ?? 100000),

            'qtn_jogos_azar' => $this->valorRequest($request, 'qtn_jogos_azar', $tablet->qtn_jogos_azar ?? 8),
            'probabilidada_premios_azar' => $this->percentualRequest($request, 'probabilidada_premios_azar', $tablet->probabilidada_premios_azar ?? 0.25),
            'taxa_max_chance_azar' => $this->percentualRequest($request, 'taxa_max_chance_azar', $tablet->taxa_max_chance_azar ?? 0),

            'qtn_jogos_normal' => $this->valorRequest($request, 'qtn_jogos_normal', $tablet->qtn_jogos_normal ?? 4),
            'probabilidada_premios_normal' => $this->percentualRequest($request, 'probabilidada_premios_normal', $tablet->probabilidada_premios_normal ?? 0.5),
            'taxa_max_chance_normal' => $this->percentualRequest($request, 'taxa_max_chance_normal', $tablet->taxa_max_chance_normal ?? 0.03),

            'qtn_jogos_neutro' => $this->valorRequest($request, 'qtn_jogos_neutro', $tablet->qtn_jogos_neutro ?? 3),
            'probabilidade_premios_neutro' => $this->percentualRequest($request, 'probabilidade_premios_neutro', $tablet->probabilidade_premios_neutro ?? 0.25),
            'taxa_max_chance_neutro' => $this->percentualRequest($request, 'taxa_max_chance_neutro', $tablet->taxa_max_chance_neutro ?? 0),

            'qtn_jogos_sorte' => $this->valorRequest($request, 'qtn_jogos_sorte', $tablet->qtn_jogos_sorte ?? 5),
            'probabilidade_premios_sorte' => $this->percentualRequest($request, 'probabilidade_premios_sorte', $tablet->probabilidade_premios_sorte ?? 0.5),
            'taxa_max_chance_sorte' => $this->percentualRequest($request, 'taxa_max_chance_sorte', $tablet->taxa_max_chance_sorte ?? 0.04),

            'qtn_jogos_moderado' => $this->valorRequest($request, 'qtn_jogos_moderado', $tablet->qtn_jogos_moderado ?? 8),
            'probabilidade_premios_moderado' => $this->percentualRequest($request, 'probabilidade_premios_moderado', $tablet->probabilidade_premios_moderado ?? 0.5),
            'taxa_max_chance_moderado' => $this->percentualRequest($request, 'taxa_max_chance_moderado', $tablet->taxa_max_chance_moderado ?? 0.01),

            'limites_nivel_show_n2' => $this->percentualRequest($request, 'limites_nivel_show_n2', $tablet->limites_nivel_show_n2 ?? 0.4),
            'limites_nivel_show_n3' => $this->percentualRequest($request, 'limites_nivel_show_n3', $tablet->limites_nivel_show_n3 ?? 0.2),
            'limites_nivel_show_n4' => $this->percentualRequest($request, 'limites_nivel_show_n4', $tablet->limites_nivel_show_n4 ?? 0.2),

            'status_bonus' => $this->valorRequest($request, 'status_bonus', $tablet->status_bonus ?? '0'),
            'valor' => $this->valorRequest($request, 'valor_bonus_bau', $tablet->valor ?? 0),

            'kiosk_sis' => $this->valorRequest($request, 'kiosk_sis', $tablet->kiosk_sis ?? 0),
            'tipo_tela' => $this->valorRequest($request, 'tipo_tela', $tablet->tipo_tela ?? 1),
            'tipo_inter' => $this->valorRequest($request, 'tipo_inter', $tablet->tipo_inter ?? 0),
            'senha' => substr((string) $this->valorRequest($request, 'senha', $tablet->senha ?? '8520'), 0, 4),

            /*
             * Importante:
             * toda alteração precisa incrementar a versão,
             * porque a Unity usa lastversion para saber que mudou.
             */
            'lastversion' => DB::raw('lastversion + 1'),
        ];
    }

    private function valorRequest(Request $request, $campo, $padrao = null)
    {
        if ($request->has($campo)) {
            return $request->input($campo);
        }

        return $padrao;
    }

    private function percentualRequest(Request $request, $campo, $padrao = 0)
    {
        if (!$request->has($campo)) {
            return $padrao;
        }

        $valor = $request->input($campo);

        if ($valor === null || $valor === '') {
            return $padrao;
        }

        return ((float) $valor) / 100;
    }

    public function retiradaCreditos($id)
    {
        $tablet = DB::table('tablet')
            ->where('id', $id)
            ->first();

        if (!$tablet) {
            return redirect()
                ->route('tablets.index')
                ->with('error', 'Tablet não encontrado.');
        }

        $creditoExistente = DB::table('creditos')
            ->where('idprod', $tablet->idprod)
            ->orderByDesc('id')
            ->first();

        if ($creditoExistente) {
            DB::table('creditos')
                ->where('id', $creditoExistente->id)
                ->update([
                    'data' => now(),
                    'valor' => -7,
                    'status' => 1,
                    'tipo' => 2,
                ]);
        } else {
            DB::table('creditos')->insert([
                'idprod' => $tablet->idprod,
                'data' => now(),
                'valor' => -7,
                'status' => 1,
                'tipo' => 2,
            ]);
        }

        return redirect()
            ->route('tablets.index')
            ->with('success', 'Retirada enviada com sucesso para o tablet ' . $tablet->idprod . '.');
    }

}