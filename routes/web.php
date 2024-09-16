<?php

use App\Http\Controllers\DBController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
 */

Route::get('/', [DBController::class, 'test']);

Route::get('/import', [DBController::class, 'Import_main']);
Route::get('/import2', [DBController::class, 'Import_approve']);
