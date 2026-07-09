<?php

use Illuminate\Support\Facades\Route;

Route::get('/orders', fn () => response()->json(['data' => [], 'meta' => ['todo' => 'Commerce order endpoints']]));
