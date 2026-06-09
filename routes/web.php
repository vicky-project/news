<?php

use Illuminate\Support\Facades\Route;
use Modules\News\Http\Controllers\NewsController;

Route::middleware(['auth'])->prefix('news')->name('news.')->group(function () {
  Route::get('/', [NewsController::class, 'index']);
});