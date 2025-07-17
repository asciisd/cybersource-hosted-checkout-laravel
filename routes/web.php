<?php

use Asciisd\Cybersource\Http\Controllers\CybersourceController;
use Illuminate\Support\Facades\Route;

Route::group(['as' => 'cybersource.', 'prefix' => 'cybersource'], function () {
    Route::post('response', [CybersourceController::class, 'handleResponse'])->name('response');
    Route::post('notification', [CybersourceController::class, 'handleNotification'])->name('notification');
});
