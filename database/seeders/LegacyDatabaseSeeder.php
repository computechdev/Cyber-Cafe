<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegacyDatabaseSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->call([
            LegacyUserNivelSeeder::class,
            LegacyUserStatusSeeder::class,
            LegacyRegiaoSeeder::class,
            LegacyUserSeeder::class,
            //LegacyCobrancaAgregadoSeeder::class,
            //LegacyCobrancaLocacaoSeeder::class,
            //LegacyCobrancaPontoSeeder::class,
            //LegacyLeituraSeeder::class,
           // LegacyPontoSeeder::class,
           // LegacyTabletSeeder::class,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
