<?php

use App\Http\Controllers\Api\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
Route::get('/verify', [SubscriptionController::class, 'verify']);
Route::get('/subscriptions', [SubscriptionController::class, 'listByEmail']);
