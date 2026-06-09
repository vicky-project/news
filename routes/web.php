<?php

use Illuminate\Support\Facades\Route;
use Modules\News\Http\Controllers\NewsController;

Route::prefix('apps')->name('apps.')->group(function () {
  Route::get('news', [NewsController::class, 'index'])->name('news');
});