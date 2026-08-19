<?php

use App\Http\Controllers\Api\ApproverController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\UserController;
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

Route::middleware('api.token')->group(function () {
    Route::post('/auth', [AuthController::class, 'auth']);
    Route::post('/getuser', [UserController::class, 'show']);
    Route::post('/getapprover', [ApproverController::class, 'show']);
    Route::post('/getapproverdepartment', [ApproverController::class, 'showByDepartment']);

    Route::post('/get/departments', [DepartmentController::class, 'index']);
    Route::post('/get/departments/users', [DepartmentController::class, 'users']);
    Route::post('/get/departments/positions', [DepartmentController::class, 'positions']);
    Route::post('/get/departments/users/position', [DepartmentController::class, 'usersByPosition']);
});
