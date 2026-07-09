<?php

use Illuminate\Support\Facades\Route;

Route::get('/services-rfq', fn () => response()->json(['data' => [], 'meta' => ['todo' => 'Services and RFQ workflow endpoints']]));
