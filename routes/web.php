<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscriptionController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/subscribe', [SubscriptionController::class, 'showSubscribeForm'])->name('subscribe.form');
Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscribe');

Route::get('/verify', [SubscriptionController::class, 'verify'])->name('verify');

Route::get('/subscriptions', [SubscriptionController::class, 'showSubscriptionsForm'])->name('subscriptions.form');
Route::post('/subscriptions', [SubscriptionController::class, 'listSubscriptions'])->name('subscriptions.list');
