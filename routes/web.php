<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PontoController;
use App\Http\Controllers\TabletController;
use App\Http\Controllers\LeituraController;
use App\Http\Controllers\TransacaoController;
use App\Http\Controllers\ClientePainelController;
use App\Http\Controllers\ContasReceberController;

Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->name('login.post');

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout');



Route::middleware(['auth'])->group(function () {

    // Route::get('/dashboard', [DashboardController::class, 'index'])
    //     ->name('dashboard');

});

Route::middleware(['auth', 'nivel:3'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('cliente.painel');
    });

    Route::get('/usuarios/clientes', [ClienteController::class, 'index'])
        ->name('clientes.index');

    Route::get('/usuarios/clientes/create', [ClienteController::class, 'create'])
        ->name('clientes.create');

    Route::post('/usuarios/clientes', [ClienteController::class, 'store'])
        ->name('clientes.store');

    Route::get('/usuarios/clientes/{id}/edit', [ClienteController::class, 'edit'])
        ->name('clientes.edit');

    Route::put('/usuarios/clientes/{id}', [ClienteController::class, 'update'])
        ->name('clientes.update');

    Route::patch('/usuarios/clientes/{id}/status', [ClienteController::class, 'toggleStatus'])
        ->name('clientes.toggle-status');

    Route::get('/pontos', [PontoController::class, 'index'])
        ->name('pontos.index');

    Route::get('/pontos/create', [PontoController::class, 'create'])
        ->name('pontos.create');

    Route::post('/pontos', [PontoController::class, 'store'])
        ->name('pontos.store');

    Route::get('/pontos/{id}/edit', [PontoController::class, 'edit'])
        ->name('pontos.edit');

    Route::put('/pontos/{id}', [PontoController::class, 'update'])
        ->name('pontos.update');

    Route::patch('/pontos/{id}/status', [PontoController::class, 'toggleStatus'])
        ->name('pontos.toggle-status');

    Route::get('/tablets', [TabletController::class, 'index'])
        ->name('tablets.index');

    // Route::get('/tablets/{id}/edit', [TabletController::class, 'edit'])
    //     ->name('tablets.edit');

    // Route::put('/tablets/{id}', [TabletController::class, 'update'])
    //     ->name('tablets.update');

    // Route::delete('/tablets/{id}', [TabletController::class, 'destroy'])
    //     ->name('tablets.destroy');

    // Route::patch('/tablets/{id}/ativo', [TabletController::class, 'toggleAtivo'])
    //     ->name('tablets.toggle-ativo');

    // Route::get('/tablets/{id}/creditos', [TabletController::class, 'creditos'])
    //     ->name('tablets.creditos');

    // Route::post('/tablets/{id}/creditos', [TabletController::class, 'storeCreditos'])
    //     ->name('tablets.creditos.store');

    // Route::get('/get_tablet_info.php', [TabletController::class, 'getTabletInfo'])
    //     ->name('tablets.info');

    // Route::get('/tablets/{id}/detalhes', [TabletController::class, 'detalhes'])
    //     ->name('tablets.detalhes');

    // Route::get('/tablets/{id}/detalhes-modal', [TabletController::class, 'detalhesModal'])
    //     ->name('tablets.detalhes-modal');

    // Route::patch('/tablets/{id}/zerar-leitura-virtual', [TabletController::class, 'zerarLeituraVirtual'])
    //     ->name('tablets.zerar-leitura-virtual');

    Route::get('/leituras/consultar', [LeituraController::class, 'consultar'])
        ->name('leituras.consultar');

    Route::get('/leituras/periodo', [LeituraController::class, 'periodo'])
        ->name('leituras.periodo');

    Route::get('/transacoes/entradas-saidas', [TransacaoController::class, 'entradasSaidas'])
        ->name('transacoes.entradas-saidas');

    Route::get('/transacoes/movimentacao-periodo', [TransacaoController::class, 'movimentacaoPeriodo'])
        ->name('transacoes.movimentacao-periodo');


    Route::get('/cliente/painel-teste', [ClientePainelController::class, 'painelTeste'])
        ->name('cliente.painel-teste');

    Route::get('/tablets/cards-teste', [TabletController::class, 'cardsTeste'])
        ->name('tablets.cards-teste');

    Route::get('/cliente/credito-jogador-realtime', [ClientePainelController::class, 'creditoJogadorRealtime'])
        ->name('cliente.credito-jogador-realtime');

    Route::post('/cliente/painel/alterar-senha', [ClientePainelController::class, 'alterarSenha'])
        ->name('cliente.painel.alterar-senha');
});

Route::middleware(['auth', 'nivel:1'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/usuarios', [UserController::class, 'index'])
        ->name('usuarios.index');

    Route::get('/usuarios/create', [UserController::class, 'create'])
        ->name('usuarios.create');

    Route::post('/usuarios', [UserController::class, 'store'])
        ->name('usuarios.store');

    Route::get('/usuarios/{id}/edit', [UserController::class, 'edit'])
        ->name('usuarios.edit');

    Route::put('/usuarios/{id}', [UserController::class, 'update'])
        ->name('usuarios.update');

    Route::patch('/usuarios/{id}/status', [UserController::class, 'toggleStatus'])
        ->name('usuarios.toggle-status');

    Route::get('/usuarios/clientes', [ClienteController::class, 'index'])
        ->name('clientes.index');

    Route::get('/usuarios/clientes/create', [ClienteController::class, 'create'])
        ->name('clientes.create');

    Route::post('/usuarios/clientes', [ClienteController::class, 'store'])
        ->name('clientes.store');

    Route::get('/usuarios/clientes/{id}/edit', [ClienteController::class, 'edit'])
        ->name('clientes.edit');

    Route::put('/usuarios/clientes/{id}', [ClienteController::class, 'update'])
        ->name('clientes.update');

    Route::patch('/usuarios/clientes/{id}/status', [ClienteController::class, 'toggleStatus'])
        ->name('clientes.toggle-status');

    Route::view('/usuarios/administradores', 'em-desenvolvimento')
        ->name('usuarios.administradores');

    Route::get('/pontos', [PontoController::class, 'index'])
        ->name('pontos.index');

    Route::get('/pontos/create', [PontoController::class, 'create'])
        ->name('pontos.create');

    Route::post('/pontos', [PontoController::class, 'store'])
        ->name('pontos.store');

    Route::get('/pontos/{id}/edit', [PontoController::class, 'edit'])
        ->name('pontos.edit');

    Route::put('/pontos/{id}', [PontoController::class, 'update'])
        ->name('pontos.update');

    Route::patch('/pontos/{id}/status', [PontoController::class, 'toggleStatus'])
        ->name('pontos.toggle-status');

    // Route::view('/usuarios/socios', 'em-desenvolvimento')
    //     ->name('usuarios.socios');

    // Route::view('/usuarios/funcionarios', 'em-desenvolvimento')
    //     ->name('usuarios.funcionarios');

    // Route::view('/usuarios/operadores', 'em-desenvolvimento')
    //     ->name('usuarios.operadores');

    Route::view('/configuracoes', 'em-desenvolvimento')
        ->name('configuracoes.index');

    Route::view('/crash-logs', 'em-desenvolvimento')
        ->name('crash-logs.index');

    Route::get('/leituras/consultar', [LeituraController::class, 'consultar'])
        ->name('leituras.consultar');

    Route::get('/leituras/periodo', [LeituraController::class, 'periodo'])
        ->name('leituras.periodo');

    Route::get('/transacoes/entradas-saidas', [TransacaoController::class, 'entradasSaidas'])
        ->name('transacoes.entradas-saidas');

    Route::get('/transacoes/movimentacao-periodo', [TransacaoController::class, 'movimentacaoPeriodo'])
        ->name('transacoes.movimentacao-periodo');

    // Route::get('/cliente/painel-teste', [ClientePainelController::class, 'painelTeste'])
    //     ->name('cliente.painel-teste');

    Route::get('/cliente/painel', [ClientePainelController::class, 'painelTeste'])
        ->name('cliente.painel');

    Route::get('/tablets', [TabletController::class, 'index'])
        ->name('tablets.index');

    Route::get('/tablets/{id}/edit', [TabletController::class, 'edit'])
        ->name('tablets.edit');

    Route::put('/tablets/{id}', [TabletController::class, 'update'])
        ->name('tablets.update');

    Route::delete('/tablets/{id}', [TabletController::class, 'destroy'])
        ->name('tablets.destroy');

    Route::patch('/tablets/{id}/ativo', [TabletController::class, 'toggleAtivo'])
        ->name('tablets.toggle-ativo');

    Route::get('/tablets/{id}/creditos', [TabletController::class, 'creditos'])
        ->name('tablets.creditos');

    Route::post('/tablets/{id}/creditos', [TabletController::class, 'storeCreditos'])
        ->name('tablets.creditos.store');

    Route::get('/get_tablet_info.php', [TabletController::class, 'getTabletInfo'])
        ->name('tablets.info');

    Route::get('/tablets/{id}/detalhes', [TabletController::class, 'detalhes'])
        ->name('tablets.detalhes');

    Route::get('/tablets/{id}/detalhes-modal', [TabletController::class, 'detalhesModal'])
        ->name('tablets.detalhes-modal');

    Route::patch('/tablets/{id}/zerar-leitura-virtual', [TabletController::class, 'zerarLeituraVirtual'])
        ->name('tablets.zerar-leitura-virtual');

    Route::get('/tablets/cards-teste', [TabletController::class, 'cardsTeste'])
        ->name('tablets.cards-teste');

    Route::post('/tablets/{id}/retirada-creditos', [TabletController::class, 'retiradaCreditos'])
        ->name('tablets.retirada-creditos');

    
        Route::get('/financeiro/fechar-faturas', [ContasReceberController::class, 'fecharIndex'])
            ->name('contas-receber.fechar.index');

        Route::post('/financeiro/fechar-faturas', [ContasReceberController::class, 'fecharFatura'])
            ->name('contas-receber.fechar.store');

        Route::get('/financeiro/contas-receber', [ContasReceberController::class, 'index'])
            ->name('contas-receber.index');

        Route::post('/financeiro/contas-receber/{id}/marcar-pago', [ContasReceberController::class, 'marcarPago'])
            ->name('contas-receber.marcar-pago');
   

});


Route::get('/artisan/{comando}', function ($comando) {
    // Lista de comandos permitidos por segurança
    $comandosPermitidos = ['migrate', 'db:seed', 'migrate:rollback', 'migrate:reset'];

    if (!in_array($comando, $comandosPermitidos)) {
        return 'Comando não permitido!';
    }

    try {
        // Roda o comando enviado na URL
        Artisan::call($comando);

        // Pega a resposta do terminal
        $resposta = Artisan::output();

        return '<pre>' . $resposta . '<br>Comando executado com sucesso!</pre>';
    } catch (\Exception $e) {
        return 'Erro ao rodar o comando: ' . $e->getMessage();
    }
});