<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegacyCobrancaPontoSeeder extends Seeder
{
    public function run()
    {
        $rows = [
[
                    'id_cobranca' => 1,
                    'id_ponto' => 98,
                    'id_cobranca_locacao' => 1,
                    'data_processamento' => '2025-03-20 20:53:22',
                    'periodo_prestacao' => '2025-03-31',
                    'tipo_cobranca' => 'Mensal',
                    'data_vencimento' => '2025-04-05',
                    'valor_total' => 0,
                    'pago' => 0,
                    'data_pagamento' => null,
                    'cobranca_fechada' => 0,
                    'cliente_fechamento' => null,
                    'operador_fechamento' => null,
                    'ativo' => 1,
                    'lote_fechamento_id_cobrancas' => null,
                    'lote_fechamento_hash' => null,
                ]
        ];

        foreach ($rows as $row) {
            DB::table('cobranca_ponto')->updateOrInsert(
                ['id_cobranca' => $row['id_cobranca']],
                $row
            );
        }
    }
}
