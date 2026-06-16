<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegacyUserStatusSeeder extends Seeder
{
    public function run()
    {
        $rows = [
[
                    'id_user_status' => 1,
                    'status_txt' => 'Ativo',
                    'ativo' => 1,
                ],
[
                    'id_user_status' => 2,
                    'status_txt' => 'Bloqueado',
                    'ativo' => 1,
                ],
[
                    'id_user_status' => 4,
                    'status_txt' => 'Excluído',
                    'ativo' => 1,
                ]
        ];

        foreach ($rows as $row) {
            DB::table('user_status')->updateOrInsert(
                ['id_user_status' => $row['id_user_status']],
                $row
            );
        }
    }
}
