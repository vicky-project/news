<?php

namespace Modules\News\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\News\Services\NewsService;

class NewsController extends Controller
{
  protected NewsService $newsService;

  public function __construct(NewsService $newsService) {
    $this->newsService = $newsService;
  }

  /**
  * Endpoint JSON: daftar sumber.
  */
  public function sources(): JsonResponse
  {
    $sources = $this->newsService->getSources();
    return response()->json(['sources' => $sources]);
  }

  /**
  * Endpoint JSON: artikel berdasarkan sumber & kategori.
  */
  public function articles(string $source, Request $request): JsonResponse
  {
    $type = $request->query('type'); // null jika tidak dikirim
    $data = $this->newsService->getArticles($source, $type);

    if (isset($data['error'])) {
      return response()->json($data, 404);
    }

    return response()->json($data);
  }
}