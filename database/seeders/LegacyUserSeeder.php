<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LegacyUserSeeder extends Seeder
{
    public function run()
    {
        /*
         * Importa a tabela legada `user` para a tabela padrão `users` do Laravel.
         *
         * Importante:
         * - `passwd` legado fica salvo em `legacy_passwd`.
         * - `password` recebe uma senha temporária bcrypt.
         * - Depois podemos criar login híbrido para validar a senha antiga
         *   e converter para bcrypt no primeiro acesso.
         */

        $rows = [
            [
                'legacy_user_id' => 1,
                'name' => 'admin',
                'email' => 'admin@admin.gmail.com',
                'username' => 'admin',
                'legacy_passwd' => '4badaee57fed5610012a296273158f5f',
                'nivel' => 1,
                'cadastro_legado' => null,
                'id_apoio' => 0,
                'porcentagem' => 0,
                'id_pais' => 0,
                'data_corte' => '2000-01-01 00:00:00',
                'validade' => 0,
                'revalidar' => 0,
                'data_validacao' => null,
                'status' => 1,
                'afiliado' => 0,
                'fechar_faturas_ponto' => 0,
                // 'cpf' => "0",
                // 'chave_pix' => "0",
            ],
            // [
            //     'legacy_user_id' => 0,
            //     'name' => 'Patricia',
            //     'email' => 'paty.com',
            //     'username' => 'paty',
            //     'legacy_passwd' => '4badaee57fed5610012a296273158f5f',
            //     'nivel' => 3,
            //     'cadastro_legado' => '2025-03-20 15:50:49',
            //     'id_apoio' => 1,
            //     'porcentagem' => 0,
            //     'id_pais' => 33,
            //     'data_corte' => '2000-01-01 00:00:00',
            //     'validade' => 0,
            //     'revalidar' => 0,
            //     'data_validacao' => null,
            //     'status' => 1,
            //     'afiliado' => 0,
            //     'fechar_faturas_ponto' => 0,
            // ]
        ];

        foreach ($rows as $row) {
            DB::table('users')->updateOrInsert(
                ['legacy_user_id' => $row['legacy_user_id']],
                array_merge($row, [
                    'password' => Hash::make('12345678'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
