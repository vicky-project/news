<?php

namespace Modules\News\Http\Controllers;

use Illuminate\Routing\Controller;

class NewsController extends Controller
{
  /**
  * Tampilkan halaman utama (SPA).
  */
  public function index() {
    return view('news::index');
  }
}