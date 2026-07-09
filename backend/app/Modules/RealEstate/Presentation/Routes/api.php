<?php

use Illuminate\Support\Facades\Route;

Route::get('/real-estate', fn () => response()->json(['data' => [], 'meta' => ['todo' => 'Projects, properties, units, leads, and appointments']]));
