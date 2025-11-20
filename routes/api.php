<?php

use App\Http\Controllers\Api\EmailController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::middleware('auth:api')->group(function () {
        Route::post('/emails', [EmailController::class, 'send']);
    });
});
