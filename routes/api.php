<?php

use App\Http\Controllers\Api\v1\ApiPostController;
use App\Http\Controllers\Api\v1\ApiFooController;
use App\Http\Controllers\Api\v1\ApiPollController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('v1/posts', ApiPostController::class)
    ->middlewareFor(['index', 'show'], ['auth:sanctum', 'abilities:posts:read'])
    ->middlewareFor(['store'], ['auth:sanctum', 'abilities:posts:create'])
    ->middlewareFor(['update'], ['auth:sanctum', 'abilities:posts:update'])
    ->middlewareFor(['destroy'], ['auth:sanctum', 'abilities:posts:delete']);

// Routes protégées : nécessitent d'être connecté
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/v1/polls/{poll}/show', [ApiPollController::class, 'showById']);

    Route::get('/v1/foo', [ApiFooController::class, 'show']);
    Route::post('/v1/foo', [ApiFooController::class, 'store']);

    Route::get('/v1/polls', [ApiPollController::class, 'index']);
    Route::post('/v1/polls', [ApiPollController::class, 'store']);
    Route::put('/v1/polls/{poll}', [ApiPollController::class, 'update']);
    Route::delete('/v1/polls/{poll}', [ApiPollController::class, 'destroy']);

    Route::post('/v1/polls/{poll}/options', [ApiPollController::class, 'storeOption']);
    Route::put('/v1/polls/{poll}/options/{option}', [ApiPollController::class, 'updateOption']);
    Route::delete('/v1/polls/{poll}/options/{option}', [ApiPollController::class, 'destroyOption']);

    Route::post('/v1/polls/{token}/vote', [ApiPollController::class, 'vote']);
});

// Route publique EN DERNIER pour éviter les conflits
Route::get('/v1/polls/{token}', [ApiPollController::class, 'show']);
