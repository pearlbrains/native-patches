<?php

use Illuminate\Support\Facades\Route;
use Native\Mobile\Http\Controllers\DispatchEventFromAppController;
use Native\Mobile\Http\Controllers\NativeCallController;

Route::post('_native/api/events', DispatchEventFromAppController::class);
Route::post('_native/api/call', NativeCallController::class);
