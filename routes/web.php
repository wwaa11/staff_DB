<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DataCleanupController;
use App\Http\Controllers\Web\DepartmentApproverController;
use App\Http\Controllers\Web\HrisController;
use App\Http\Controllers\Web\ManualUserController;
use App\Http\Controllers\Web\UserApproverController;
use App\Http\Controllers\Web\UserController;
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

    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users/email', [UserController::class, 'updateEmail']);

    Route::get('/data-cleanup', [DataCleanupController::class, 'index']);
    Route::post('/data-cleanup/emails/delete-all', [DataCleanupController::class, 'destroyOrphans']);
    Route::post('/data-cleanup/emails/{id}/delete', [DataCleanupController::class, 'destroyOrphan']);
    Route::post('/data-cleanup/departments/delete-all', [DataCleanupController::class, 'destroyAllUnused']);
    Route::post('/data-cleanup/departments/{id}/delete', [DataCleanupController::class, 'destroyUnused']);
    Route::post('/data-cleanup/approvers/delete-all', [DataCleanupController::class, 'destroyIncompleteApprovers']);
    Route::post('/data-cleanup/approvers/{id}/delete', [DataCleanupController::class, 'destroyIncompleteApprover']);

    Route::get('/user-manual', [ManualUserController::class, 'index']);
    Route::get('/user-manual/search', [ManualUserController::class, 'search']);
    Route::post('/user-manual', [ManualUserController::class, 'store']);
    Route::post('/user-manual/{id}/delete', [ManualUserController::class, 'destroy']);
});
