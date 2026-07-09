<?php

use Illuminate\Support\Facades\Route;

Route::get('/inventory', fn () => response()->json(['data' => [], 'meta' => ['todo' => 'Warehouses, stock items, and stock movements']]));
