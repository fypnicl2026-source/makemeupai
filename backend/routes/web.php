<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'MakemeupAI API',
        'data' => [
            'version' => '1.0',
            'docs' => '/api/auth',
        ],
    ]);
});

Route::get('/sanctum/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->middleware('web');
