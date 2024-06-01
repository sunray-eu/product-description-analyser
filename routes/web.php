<?php

use Illuminate\Support\Facades\Route;
use SunrayEu\ProductDescriptionAnalyser\App\Http\Controllers\FileController;


Route::get('/', [FileController::class, 'index'])->name('index');
Route::post('/upload', [FileController::class, 'upload'])->name('upload');

Route::post('/reanalyse', [FileController::class, 'reanalyse'])->name('re-analyse');
Route::post('/file/unselect', [FileController::class, 'deselect'])->name('file-unselect');
