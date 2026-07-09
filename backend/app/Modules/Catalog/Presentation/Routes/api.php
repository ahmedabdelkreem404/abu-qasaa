<?php

use Illuminate\Support\Facades\Route;

Route::get('/products', fn () => response()->json(['data' => [], 'meta' => ['todo' => 'Catalog product endpoints']]));
