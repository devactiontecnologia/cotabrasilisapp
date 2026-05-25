<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\QuotaApiController;
use App\Http\Controllers\Api\RentalOfferApiController;
use App\Http\Controllers\Api\HotelApiController;
use App\Http\Controllers\Api\DashboardApiController;

/*
|--------------------------------------------------------------------------
| API Routes - Cota Brasilis Mobile
|--------------------------------------------------------------------------
*/

// Rotas públicas
Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/register', [AuthApiController::class, 'register']);

// Rotas públicas (sem auth) - cotas e hotéis para busca
Route::get('/quotas/search', [QuotaApiController::class, 'search']);
Route::get('/quotas/featured', [QuotaApiController::class, 'featured']);
Route::get('/quotas/{quota}', [QuotaApiController::class, 'show'])->name('api.quotas.show');
Route::get('/rental-offers', [RentalOfferApiController::class, 'index']);
Route::get('/rental-offers/{rentalOffer}', [RentalOfferApiController::class, 'show']);
Route::get('/hotels', [HotelApiController::class, 'index']);
Route::get('/hotels/{hotel}', [HotelApiController::class, 'show']);
// Uploads (temporary) - used by web registration AJAX
Route::post('/uploads', [\App\Http\Controllers\Api\UploadApiController::class, 'store']);

// Rotas protegidas (token Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/user', [UserApiController::class, 'me']);

    // Dashboard
    Route::get('/dashboard', [DashboardApiController::class, 'index']);

    // Minhas cotas
    Route::get('/quotas/my', [QuotaApiController::class, 'myQuotas']);
    Route::get('/quotas/my/list', [QuotaApiController::class, 'myQuotasList']);

    // Minhas ofertas de aluguel
    Route::get('/rental-offers/my', [RentalOfferApiController::class, 'myOffers']);
});
