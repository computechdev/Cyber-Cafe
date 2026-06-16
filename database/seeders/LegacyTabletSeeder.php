<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegacyTabletSeeder extends Seeder
{
    public function run()
    {
        $rows = [
[
                    'id' => 6,
                    'idprod' => '050B',
                    'cliente' => 'paty',
                    'cadastro' => '2026-06-03 01:32:43',
                    'validade' => 0,
                    'status' => 0,
                    'ligado' => 1,
                    'zerar' => 0,
                    'destrava' => 0,
                    'expiracao' => '0',
                    'id_ponto' => 98,
                    'id_apoio' => 66,
                    'dificuldade' => 55,
                    'pendrive_id' => 1,
                    'antecipado' => 0,
                    'creditoteclado' => 0,
                    'centavosbingo' => 1,
                    'apostamaxhalloween' => 10,
                    'leituraonlinesincronizada' => 1,
                    'zerarleituraparcial' => 0,
                    'zerarleituraoficialbackup' => 0,
                    'acum1min' => 200.0,
                    'acum2min' => 300.0,
                    'acum3min' => 400.0,
                    'acum1med' => 200.0,
                    'acum2med' => 300.0,
                    'acum3med' => 400.0,
                    'acum1max' => 500.0,
                    'acum2max' => 600.0,
                    'acum3max' => 700.0,
                    'acum4min' => 100.0,
                    'acum4med' => 100.0,
                    'acum4max' => 200.0,
                    'acum5min' => 100.0,
                    'acum5med' => 100.0,
                    'acum5max' => 100.0,
                    'acum6min' => 100.0,
                    'acum6med' => 100.0,
                    'acum6max' => 100.0,
                    'porcent_acumu' => 3,
                    'lastversion' => 1,
                    'ativo' => 1,
                    'zerarleituravirtual' => 0,
                    'matematica_slot' => 0,
                    'modo_slot' => 1,
                    'ultimo_contato' => '2026-06-02 22:33:19',
                ]
        ];

        foreach ($rows as $row) {
            DB::table('tablet')->updateOrInsert(
                ['id' => $row['id']],
                $row
            );
        }
    }
}
