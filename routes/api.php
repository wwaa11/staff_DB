<?php

use App\Http\Controllers\DBController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
 */

Route::post('/getuser', [DBController::class, 'API_getUser']);
Route::post('/auth', [DBController::class, 'API_Auth']);
Route::post('/auth/addwitness', [DBController::class, 'API_AddWitness']);
