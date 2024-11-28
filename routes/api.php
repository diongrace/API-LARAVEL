<?php
use App\Http\Controllers\API\AuthAgricooController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use Illuminate\Http\Request;
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

// Middleware pour obtenir les informations de l'utilisateur authentifié
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Routes d'authentification
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    // Routes de réinitialisation de mot de passe
    Route::post('forgot-password', [PasswordResetController::class, 'forgotPassword']);
    Route::post('reset-password', [PasswordResetController::class, 'resetPassword']);
});

// Routes pour l'authentification Agricoo
Route::prefix('auth/agricoo')->group(function () {
    Route::post('register', [AuthAgricooController::class, 'register']);
    Route::post('login', [AuthAgricooController::class, 'login']);
    Route::post('logout', [AuthAgricooController::class, 'logout'])->middleware('auth:sanctum');
});

