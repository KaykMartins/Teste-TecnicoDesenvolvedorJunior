<?php

use App\Http\Controllers\EnderecoController;
use Illuminate\Support\Facades\Route;

Route::post('/enderecos', [EnderecoController::class, 'store']);
