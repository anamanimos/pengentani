<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/logs', function() {
    $path = storage_path('logs/laravel.log');
    if (!File::exists($path)) return 'No logs';
    
    // Get last 200 lines
    $lines = file($path);
    return response(implode("", array_slice($lines, -200)))->header('Content-Type', 'text/plain');
});
