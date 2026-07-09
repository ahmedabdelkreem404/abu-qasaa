<?php

use Illuminate\Support\Facades\Route;

Route::get('/cms', fn () => response()->json(['data' => [], 'meta' => ['todo' => 'CMS pages and media endpoints']]));
