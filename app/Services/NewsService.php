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
        return [];
      }

      $data = $response->json();
      $sources = [];

      // Daftar nama sumber yang akan diabaikan
      $excludedSources = [
        'BBC News',
        'Tribun News',
        'Zetizen Jawapos News',
        'Suara News',
        'VOA Indonesia',
      ];

      foreach ($data['data'] ?? [] as $name => $info) {
        // Lewati sumber yang dikecualikan
        if (in_array($name, $excludedSources)) {
          continue;
        }

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
    $cacheKey = 'news:articles:' . $source . ($type ? ':' . $type : '');

    return Cache::remember($cacheKey,
      $this->cacheTtl,
      function () use ($source, $type) {
        // ... kode untuk mendapatkan $result dari API eksternal (sama seperti sebelumnya)

        // Ambil data mentah
        $rawArticles = $result['data'] ?? [];

        // Normalisasi tiap artikel
        $articles = array_map(function ($item) {
          return [
            'title' => $item['title'] ?? 'Tanpa Judul',
            'link' => $item['link'] ?? '#',
            'contentSnippet' => $this->getSnippet($item),
            'isoDate' => $item['isoDate'] ?? null,
            'image' => $this->normalizeImage($item['image'] ?? null, $item['link'] ?? ''),
          ];
        }, $rawArticles);

        return [
          'messages' => $result['messages'] ?? '',
          'total' => $result['total'] ?? 0,
          'data' => $articles,
        ];
      });
  }

  /**
  * Ambil cuplikan teks dari artikel (fallback bertingkat).
  */
  private function getSnippet(array $item): string
  {
    // 1. contentSnippet (CNN, dll)
    if (!empty($item['contentSnippet'])) {
      return $item['contentSnippet'];
    }

    // 2. content (Okezone, Tempo, dll) → bersihkan tag & batasi
    if (!empty($item['content'])) {
      $text = strip_tags($item['content']);
      return mb_strlen($text) > 200 ? mb_substr($text, 0, 200) . '...' : $text;
    }

    // 3. description (Antara, Republika) → bersihkan tag & batasi
    if (!empty($item['description'])) {
      $text = strip_tags($item['description']);
      return mb_strlen($text) > 200 ? mb_substr($text, 0, 200) . '...' : $text;
    }

    return '';
  }

  /**
  * Normalisasi gambar: tangani string (Antara), array (CNN, Republika), atau tidak ada (Tempo).
  */
  private function normalizeImage($image, string $articleLink): array
  {
    if (!$image) {
      return ['small' => null,
        'large' => null];
    }

    if (is_string($image)) {
      $url = $this->fixRelativeUrl($image, $articleLink);
      return ['small' => $url,
        'large' => $url];
    }

    // Ambil small, medium, large, extraLarge dengan prioritas
    $small = $image['small']
    ?? $image['medium']
    ?? $image['large']
    ?? $image['extraLarge']
    ?? null;

    $large = $image['large']
    ?? $image['extraLarge']
    ?? $image['medium']
    ?? $image['small']
    ?? null;

    $small = $this->fixRelativeUrl($small, $articleLink);
    $large = $this->fixRelativeUrl($large, $articleLink);

    return ['small' => $small,
      'large' => $large];
  }

  /**
  * Jika URL dimulai dengan '/' atau tanpa 'http', buat absolut dari domain artikel.
  */
  private function fixRelativeUrl(?string $url, string $articleLink): ?string
  {
    if (!$url) return null;

    // Sudah absolut
    if (preg_match('/^https?:\/\//i', $url)) {
      return $url;
    }

    // URL relatif terhadap domain artikel
    $parsed = parse_url($articleLink);
    if (isset($parsed['scheme'], $parsed['host'])) {
      $base = $parsed['scheme'] . '://' . $parsed['host'];
      return $base . ($url[0] === '/' ? '' : '/') . $url;
    }

    // Tidak bisa diubah
    return null;
  }

  /**
  * Coba membangun URL gambar absolut dari query string berdasarkan domain artikel.
  * Pendekatan: ganti path artikel dengan path gambar yang diasumsikan.
  * Jika gagal, kembalikan null.
  */
  private function buildAbsoluteImageUrl(string $queryOrPath, string $articleLink): ?string
  {
    // Jika sudah diawali '/', mungkin relatif terhadap domain
    if (str_starts_with($queryOrPath, '/')) {
      $parsed = parse_url($articleLink);
      if (isset($parsed['scheme'], $parsed['host'])) {
        return $parsed['scheme'] . '://' . $parsed['host'] . $queryOrPath;
      }
      return null;
    }

    // Jika hanya query string (?w=300), tidak bisa ditebak path sebenarnya → kembalikan null
    if (str_starts_with($queryOrPath, '?')) {
      return null;
    }

    // Jika berbentuk path relatif tanpa leading slash, coba gabungkan
    $parsed = parse_url($articleLink);
    if (isset($parsed['scheme'], $parsed['host'])) {
      $base = $parsed['scheme'] . '://' . $parsed['host'] . '/';
      return $base . ltrim($queryOrPath, '/');
    }

    return null;
  }
}