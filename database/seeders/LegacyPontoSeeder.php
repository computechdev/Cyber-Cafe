<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegacyPontoSeeder extends Seeder
{
    public function run()
    {
        $rows = [
[
                    'id' => 1,
                    'nome' => 'Clube Paty',
                    'porcent_ponto' => 0,
                    'cadastro' => '2025-03-20 18:51:15',
                    'id_apoio' => 66,
                    'status' => 1,
                    'passwd' => '102030',
                    'modo' => 0,
                ]
        ];

        foreach ($rows as $row) {
            DB::table('ponto')->updateOrInsert(
                ['id' => $row['id']],
                $row
            );
        }
    }
}
