<?php

use App\Http\Controllers\Api\v1\AIAnalysisController;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\ConsultationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Auth Routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/consultations', [ConsultationController::class, 'index']);
        Route::post('/consultations', [ConsultationController::class, 'store']);
        Route::get('/consultations/{id}', [ConsultationController::class, 'show']);
        Route::post('/consultations/{id}/images', [ConsultationController::class, 'uploadImages']);

        // AI Analysis
        Route::post('/consultations/{id}/analyse', [AIAnalysisController::class, 'analyse']);
        Route::get('/consultations/{id}/result', [AIAnalysisController::class, 'show']);
    });

});
