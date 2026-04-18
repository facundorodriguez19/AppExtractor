<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotaController;
use Illuminate\Support\Facades\Route;

Route::get('/notas/{nota}/status', [NotaController::class, 'status']);
Route::get('/notas/estatisticas', [DashboardController::class, 'estatisticas']);
