<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegacyUserNivelSeeder extends Seeder
{
    public function run()
    {
        $rows = [
[
                    'id_user_nivel' => 1,
                    'nivel_user' => 'Administrador',
                    'nivel_sigla' => 'admin',
                    'ativo' => 1,
                ],
[
                    'id_user_nivel' => 2,
                    'nivel_user' => 'Sub-Administrador',
                    'nivel_sigla' => 'subadmin',
                    'ativo' => 1,
                ],
[
                    'id_user_nivel' => 3,
                    'nivel_user' => 'Cliente',
                    'nivel_sigla' => 'cliente',
                    'ativo' => 1,
                ],
[
                    'id_user_nivel' => 4,
                    'nivel_user' => 'Sócio',
                    'nivel_sigla' => 'socio',
                    'ativo' => 1,
                ],
[
                    'id_user_nivel' => 5,
                    'nivel_user' => 'Funcionário',
                    'nivel_sigla' => 'funcionario',
                    'ativo' => 1,
                ],
[
                    'id_user_nivel' => 6,
                    'nivel_user' => 'Operador',
                    'nivel_sigla' => 'operador',
                    'ativo' => 1,
                ]
        ];

        foreach ($rows as $row) {
            DB::table('user_nivel')->updateOrInsert(
                ['id_user_nivel' => $row['id_user_nivel']],
                $row
            );
        }
    }
}
