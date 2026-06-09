<?php

use Illuminate\Support\Facades\Route;
use Modules\News\Http\Controllers\Api\NewsController;

Route::middleware(['auth:sanctum'])->prefix('news')->name('news.')->group(function () {
  Route::get('/sources', [NewsController::class, 'sources']);
  Route::get('/articles/{source}', [NewsController::class, 'articles']);
});