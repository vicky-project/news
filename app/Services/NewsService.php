<?php

namespace Modules\News\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class NewsService
{
  protected string $baseApi = 'https://berita-indo-api-next.vercel.app/api';

  /**
  * Durasi cache dalam detik.
  * 5 menit cukup untuk agregator berita; sesuaikan kebutuhan.
  */
  protected int $cacheTtl = 300; // 5 menit

  /**
  * Ambil daftar sumber berita.
  */
  public function getSources(): array
  {
    $cacheKey = 'news:sources';

    return Cache::remember($cacheKey, $this->cacheTtl, function () {
      $response = Http::get($this->baseApi);

      if (!$response->successful()) {
        return []; // Jangan simpan ke cache jika gagal (opsional)
      }

      $data = $response->json();
      $sources = [];

      foreach ($data['data'] ?? [] as $name => $info) {
        $sources[] = [
          'name' => $name,
          'hasType' => isset($info['listType']),
          'types' => $info['listType'] ?? [],
          'hasAll' => isset($info['all']),
          'slug' => strtolower(str_replace(' ', '-', $name)),
        ];
      }

      return $sources;
    });
  }

  /**
  * Ambil artikel dari sumber tertentu.
  */
  public function getArticles(string $source,
    ?string $type = null): array
  {
    // Buat cache key unik berdasarkan source & type
    $cacheKey = 'news:articles:' . $source . ($type ? ':' . $type : '');

    return Cache::remember($cacheKey,
      $this->cacheTtl,
      function () use ($source, $type) {
        // 1. Ambil daftar sumber untuk mendapatkan info endpoint
        $sourceListResponse = Http::get($this->baseApi);
        if (!$sourceListResponse->successful()) {
          return ['error' => 'Gagal mengambil daftar sumber',
            'data' => []];
        }

        $sourceList = $sourceListResponse->json()['data'] ?? [];
        $sourceInfo = null;

        foreach ($sourceList as $name => $info) {
          if (strtolower(str_replace(' ', '-', $name)) === $source) {
            $sourceInfo = $info;
            break;
          }
        }

        if (!$sourceInfo) {
          return ['error' => 'Sumber tidak ditemukan',
            'data' => []];
        }

        // 2. Bangun endpoint artikel
        if ($type && isset($sourceInfo['type'])) {
          $endpoint = str_replace(':type', $type, $sourceInfo['type']);
        } elseif (isset($sourceInfo['all'])) {
          $endpoint = $sourceInfo['all'];
        } else {
          // Fallback ke tipe pertama jika hanya punya 'type'
          $firstType = $sourceInfo['listType'][0] ?? '';
          $endpoint = str_replace(':type', $firstType, $sourceInfo['type'] ?? '');
        }

        // 3. Ambil data artikel dari API eksternal
        $url = $this->baseApi . str_replace('/api', '', $endpoint);
        $response = Http::get($url);

        if (!$response->successful()) {
          return ['error' => 'Gagal mengambil artikel',
            'data' => []];
        }

        $result = $response->json();

        return [
          'messages' => $result['messages'] ?? '',
          'total' => $result['total'] ?? 0,
          'data' => $result['data'] ?? [],
        ];
      });
  }
}