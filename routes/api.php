<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProposalController;
use App\Http\Controllers\Api\ProposalReviewController;
use App\Http\Controllers\Api\TagController;

// Public health
Route::get('/health', fn() => response()->json(['ok' => true]));

// AUTH (public)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed'])
        ->name('verification.verify');
});

// AUTH (protected)
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification']);
});

/**
 * Protected API (role/permission based)
 */
Route::middleware(['auth:sanctum', 'verified.email'])->group(function () {
    Route::get('/tags', [TagController::class, 'index'])
        ->middleware('permission:tags.read');

    Route::get('/proposals', [ProposalController::class, 'index'])
        ->middleware(['permission:proposals.read.own|proposals.read.any', 'can:viewAny,App\Models\Proposal']);

    Route::get('/proposals/{proposal}', [ProposalController::class, 'show'])
        ->middleware(['permission:proposals.read.own|proposals.read.any', 'can:view,proposal']);

    Route::post('/proposals', [ProposalController::class, 'store'])
        ->middleware(['permission:proposals.create', 'can:create,App\Models\Proposal']);

    Route::patch('/proposals/{proposal}/status', [ProposalController::class, 'changeStatus'])
        ->middleware(['permission:proposals.status.change', 'can:changeStatus,proposal']);

    Route::get('/proposals/{proposal}/reviews', [ProposalReviewController::class, 'index'])
        ->middleware(['permission:reviews.read.own_proposal|reviews.read.any', 'can:viewReviews,proposal']);

    Route::put('/proposals/{proposal}/reviews/me', [ProposalReviewController::class, 'upsert'])
        ->middleware(['permission:reviews.upsert', 'can:upsertReview,proposal']);
});
