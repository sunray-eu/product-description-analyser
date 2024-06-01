<?php

use Illuminate\Support\Facades\Route;
use SunrayEu\ProductDescriptionAnalyser\App\Http\Controllers\FileController;


Route::get('/', [FileController::class, 'index']);
Route::post('/upload', [FileController::class, 'upload'])->name('upload');
