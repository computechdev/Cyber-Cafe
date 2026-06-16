<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegacyLeituraSeeder extends Seeder
{
    public function run()
    {
        $rows = [
[
                    'id' => 6,
                    'id_metrica' => 0,
                    'idprod' => '050B',
                    'creditos' => '$0.00',
                    'entrada' => 0,
                    'saida' => 0,
                    'entrada_virtual' => 0,
                    'saida_virtual' => 0,
                    'apostado' => 0,
                    'premiado' => 0,
                    'status' => 0,
                    'data' => '',
                    'dataorder' => '2026-06-03 01:32:49',
                    'ativo' => 1,
                ]
        ];

        foreach ($rows as $row) {
            DB::table('leitura')->updateOrInsert(
                ['id' => $row['id']],
                $row
            );
        }
    }
}
