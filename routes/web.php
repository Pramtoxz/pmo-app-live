<?php

use Illuminate\Support\Facades\Route;

// Minimal web routes - hanya untuk keperluan dasar Laravel
Route::get('/', function () {
    return response()->json([
        'message' => 'API is running',
        'status' => 'success'
    ]);
});
