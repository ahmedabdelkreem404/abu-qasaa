<?php

use Illuminate\Support\Facades\Route;

Route::get('/payments', fn () => response()->json(['data' => [], 'meta' => ['todo' => 'Generic payment endpoints and Paymob placeholders']]));
