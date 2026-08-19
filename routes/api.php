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

Route::post('/auth', [DBController::class, 'API_Auth']);
Route::post('/getuser', [DBController::class, 'API_getUser']);
Route::post('/auth/addwitness', [DBController::class, 'API_AddWitness']);
Route::post('/getapprover', [DBController::class, 'API_getApprover']);
Route::post('/getapproverdepartment', [DBController::class, 'API_getApprover_Department']);

// Department
Route::post('/get/departments', [DBController::class, 'API_getDepartments']);
Route::post('/get/departments/users', [DBController::class, 'API_getDepartmentsUsers']);
Route::post('/get/departments/positions', [DBController::class, 'API_getDepartmentsPositions']);
Route::post('/get/departments/users/position', [DBController::class, 'API_getDepartmentsUsersPosition']);

// Consent
Route::post('/patient/consent', [DBController::class, 'API_PatientConsent']);
