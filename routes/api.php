<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotaController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notas/{nota}/status', [NotaController::class, 'status']);
    Route::get('/notas/estatisticas', [DashboardController::class, 'estatisticas']);
});
