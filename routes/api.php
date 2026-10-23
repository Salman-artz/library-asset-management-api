<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BorrowingController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\FineController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

// === Auth Routes ===
Route::prefix('auth')->group(function (): void {
    Route::middleware('throttle:60,1')->group(function (): void {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
    });
    Route::post('forgot-password', [PasswordResetController::class, 'sendResetLinkEmail']);
    Route::post('reset-password', [PasswordResetController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::patch('profile', [ProfileController::class, 'update']);
    });
});

// === Category Routes (public read, protected write) ===
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);
Route::middleware(['auth:sanctum', 'role:admin,staff'])->group(function (): void {
    Route::post('categories', [CategoryController::class, 'store']);
    Route::patch('categories/{category}', [CategoryController::class, 'update']);
    Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
});

// === Collection Routes (public read, protected write) ===
Route::get('collections', [CollectionController::class, 'index']);
Route::get('collections/{collection}', [CollectionController::class, 'show']);
Route::middleware(['auth:sanctum', 'role:admin,staff'])->group(function (): void {
    Route::post('collections', [CollectionController::class, 'store']);
    Route::patch('collections/{collection}', [CollectionController::class, 'update']);
    Route::delete('collections/{collection}', [CollectionController::class, 'destroy']);
});

// === Borrowing Routes ===
Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('borrowings', [BorrowingController::class, 'index']);
    Route::post('borrowings', [BorrowingController::class, 'store']);
    Route::get('borrowings/{borrowing}', [BorrowingController::class, 'show']);
    Route::patch('borrowings/{borrowing}/return', [BorrowingController::class, 'returnCollection'])
        ->middleware('role:admin,staff');
});

// === Fine Routes ===
Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::get('fines/mine', [FineController::class, 'mine']);

    Route::middleware(['role:admin,staff'])->group(function (): void {
        Route::get('fines', [FineController::class, 'index']);
        Route::get('fines/{fine}', [FineController::class, 'show']);
        Route::patch('fines/{fine}/pay', [FineController::class, 'pay']);
    });
});

// === Report Routes ===
Route::middleware(['auth:sanctum', 'role:admin,staff'])->prefix('reports')->group(function (): void {
    Route::get('popular-collections', [ReportController::class, 'popularCollections']);
    Route::get('active-members', [ReportController::class, 'activeMembers']);
    Route::get('export', [ReportController::class, 'export']);
});
