<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DepartmentApproverController;
use App\Http\Controllers\Web\DepartmentController;
use App\Http\Controllers\Web\HrisController;
use App\Http\Controllers\Web\ManualUserController;
use App\Http\Controllers\Web\UserApproverController;
use App\Http\Controllers\Web\UserEmailController;
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

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/user-approver', [UserApproverController::class, 'index']);
    Route::get('/user-approver/search', [UserApproverController::class, 'search']);
    Route::post('/user-approver', [UserApproverController::class, 'store']);
    Route::post('/user-approver/{id}/delete', [UserApproverController::class, 'destroy']);

    Route::get('/department-approver', [DepartmentApproverController::class, 'index']);
    Route::get('/department-approver/search', [DepartmentApproverController::class, 'search']);
    Route::post('/department-approver', [DepartmentApproverController::class, 'store']);
    Route::post('/department-approver/{id}/delete', [DepartmentApproverController::class, 'destroy']);

    Route::get('/hris-update', [HrisController::class, 'index']);
    Route::post('/hris-update', [HrisController::class, 'run']);

    Route::get('/department-cleanup', [DepartmentController::class, 'cleanup']);
    Route::post('/department-cleanup/delete-all', [DepartmentController::class, 'destroyAllUnused']);
    Route::post('/department-cleanup/{id}/delete', [DepartmentController::class, 'destroyUnused']);

    Route::get('/user-email', [UserEmailController::class, 'index']);
    Route::get('/user-email/search', [UserEmailController::class, 'search']);
    Route::post('/user-email', [UserEmailController::class, 'store']);
    Route::post('/user-email/clear-orphans', [UserEmailController::class, 'destroyOrphans']);
    Route::post('/user-email/orphan/{id}/delete', [UserEmailController::class, 'destroyOrphan']);
    Route::post('/user-email/{id}/delete', [UserEmailController::class, 'destroy']);

    Route::get('/user-manual', [ManualUserController::class, 'index']);
    Route::get('/user-manual/search', [ManualUserController::class, 'search']);
    Route::post('/user-manual', [ManualUserController::class, 'store']);
    Route::post('/user-manual/{id}/delete', [ManualUserController::class, 'destroy']);
});
