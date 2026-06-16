<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TabletParametroController;
use App\Http\Controllers\Api\TabletApiController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/verificauser.php', [TabletApiController::class, 'verificaUser'])
    ->name('api.legacy.verificauser');

Route::get('/cadastrar.php', [TabletApiController::class, 'cadastrar'])
    ->name('api.legacy.cadastrar');

Route::get('/verificar_parametros.php', [TabletApiController::class, 'verificarParametros'])
    ->name('api.legacy.verificar-parametros');

Route::get('/verificar_realtime.php', [TabletApiController::class, 'verificarRealtime'])
    ->name('api.legacy.verificar-realtime');

Route::get('/sendleiturarealtime.php', [TabletApiController::class, 'sendLeituraRealtime'])
    ->name('api.legacy.send-leitura-realtime');

Route::post('/sendleiturarealtime.php', [TabletApiController::class, 'sendLeituraRealtime'])
    ->name('api.legacy.send-leitura-realtime.post');

Route::get('/verificar_status.php', [TabletApiController::class, 'verificarStatus'])
    ->name('api.legacy.verificar-status');

Route::get('/sendleiturarealtime_dbsync.php', [TabletApiController::class, 'sendLeituraRealtimeDbSync'])
    ->name('api.legacy.send-leitura-realtime-dbsync');

Route::get('/travar_acumulado.php', [TabletApiController::class, 'travarAcumulado'])
    ->name('api.legacy.travar-acumulado');

Route::get('/travar_bonus_bau.php', [TabletApiController::class, 'travarBonusBau'])
    ->name('api.legacy.travar-bonus-bau');