<?php

use App\Http\Controllers\Api\v1\ApiPostController;
use App\Http\Controllers\Api\v1\ApiFooController;
use App\Http\Controllers\Api\v1\ApiPollController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes API
|--------------------------------------------------------------------------
| Les routes sans middleware sont accessibles par tout le monde (anonyme).
| Les routes dans le groupe auth:sanctum nécessitent d'être connecté.
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Routes des posts (déjà existantes, on ne touche pas)
Route::apiResource('v1/posts', ApiPostController::class)
    ->middlewareFor(['index', 'show'], ['auth:sanctum', 'abilities:posts:read'])
    ->middlewareFor(['store'], ['auth:sanctum', 'abilities:posts:create'])
    ->middlewareFor(['update'], ['auth:sanctum', 'abilities:posts:update'])
    ->middlewareFor(['destroy'], ['auth:sanctum', 'abilities:posts:delete']);

// Route publique : afficher un sondage via son token (sans être connecté)
Route::get('/v1/polls/{token}', [ApiPollController::class, 'show']);

// Routes protégées : nécessitent d'être connecté
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/v1/polls/{poll}/show', [ApiPollController::class, 'showById']);

    // Route de test foo (déjà existante)
    Route::get('/v1/foo', [ApiFooController::class, 'show']);
    Route::post('/v1/foo', [ApiFooController::class, 'store']);

    // Sondages : liste, création, modification, suppression
    Route::get('/v1/polls', [ApiPollController::class, 'index']);
    Route::post('/v1/polls', [ApiPollController::class, 'store']);
    Route::put('/v1/polls/{poll}', [ApiPollController::class, 'update']);
    Route::delete('/v1/polls/{poll}', [ApiPollController::class, 'destroy']);

    // Options d'un sondage : ajout, modification, suppression
    Route::post('/v1/polls/{poll}/options', [ApiPollController::class, 'storeOption']);
    Route::put('/v1/polls/{poll}/options/{option}', [ApiPollController::class, 'updateOption']);
    Route::delete('/v1/polls/{poll}/options/{option}', [ApiPollController::class, 'destroyOption']);

    // Vote
    Route::post('/v1/polls/{token}/vote', [ApiPollController::class, 'vote']);
});
