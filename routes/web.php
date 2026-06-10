<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VeiculoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\CheckOutController;
use App\Http\Controllers\ManutencaoController;
use App\Http\Controllers\DocumentoVeiculoController;
use App\Http\Controllers\ValorExtraController;
use App\Http\Controllers\AlarmeController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\LogAtividadeController;
use App\Http\Controllers\ProfileController;

// redireciona raiz para login
Route::get('/', function () {
    return redirect()->route('login');
});

// dashboard do breeze vira redirect
Route::get('/dashboard', function () {
    return redirect()->route('veiculos.index');
})->middleware(['auth'])->name('dashboard');

// rotas de autenticação do breeze
require __DIR__.'/auth.php';

// -----------------------------------------------
// ROTAS PROTEGIDAS
// -----------------------------------------------
Route::middleware('auth')->group(function () {
    //editar o perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // TODOS OS PERFIS — veículos (somente leitura para cliente)
    Route::resource('veiculos', VeiculoController::class);

    // TODOS OS PERFIS — contratos
    Route::resource('contratos', ContratoController::class);

    // OPERADOR E GERENTE
    Route::middleware('can:is-staff')->group(function () {
        Route::resource('usuarios', UsuarioController::class)->names('users');
        Route::resource('manutencao', ManutencaoController::class);
        Route::resource('documentos', DocumentoVeiculoController::class);
        Route::resource('alarmes', AlarmeController::class);
        Route::resource('valor-extra', ValorExtraController::class);
        
        Route::get('check-in/{id}', [CheckInController::class, 'create'])->name('check-in.create');
        Route::post('check-in/{id}', [CheckInController::class, 'store'])->name('check-in.store');

        Route::get('check-out/{id}', [CheckOutController::class, 'create'])->name('check-out.create');
        Route::post('check-out/{id}', [CheckOutController::class, 'store'])->name('check-out.store');
        Route::get('logs', [LogAtividadeController::class, 'index'])
             ->name('logs.index');
    });

    // SÓ GERENTE
    Route::middleware('can:is-gerente')->group(function () {
        Route::get('relatorios', [RelatorioController::class, 'index'])
             ->name('relatorios');
        Route::get('relatorios/faturamento', [RelatorioController::class, 'faturamento'])
             ->name('relatorios.faturamento');
        Route::get('relatorios/frota', [RelatorioController::class, 'frota'])
             ->name('relatorios.frota');
        Route::get('relatorios/manutencao', [RelatorioController::class, 'manutencao'])
             ->name('relatorios.manutencao');
    });

});