<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::redirect('/dashboard', '/');

Route::prefix('notas')->name('notas.')->group(function () {
    Route::get('/', [NotaController::class, 'index'])->name('index');
    Route::get('/criar', [NotaController::class, 'create'])->name('create');
    Route::post('/', [NotaController::class, 'store'])->name('store');
    Route::get('/exportar/csv', [NotaController::class, 'exportarCSV'])->name('exportar.csv');
    Route::get('/{nota}', [NotaController::class, 'show'])->name('show');
    Route::get('/{nota}/editar', [NotaController::class, 'edit'])->name('edit');
    Route::put('/{nota}', [NotaController::class, 'update'])->name('update');
    Route::delete('/{nota}', [NotaController::class, 'destroy'])->name('destroy');
});

Route::redirect('/login', '/');
Route::redirect('/register', '/');
