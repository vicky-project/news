<?php

namespace Modules\News\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class NewsService
{
    protected string $baseApi = 'https://berita-indo-api-next.vercel.app/api';

    protected int $cacheTtl = 300; // 5 menit

    /**
     * Daftar sumber berita yang dikecualikan karena tidak tersedia di API.
     */
    protected array $excludedSources = [
        'BBC News',
        'Tribun News',
        'Zetizen Jawapos News',
        'Suara News',
        'VOA Indonesia',
    ];

    /**
     * Ambil daftar sumber berita (sudah difilter).
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

            foreach ($data['data'] ?? [] as $name => $info) {
                if (in_array($name, $this->excludedSources)) {
                    continue;
                }

                $sources[] = [
                    'name'    => $name,
                    'hasType' => isset($info['listType']),
                    'types'   => $info['listType'] ?? [],
                    'hasAll'  => isset($info['all']),
                    'slug'    => strtolower(str_replace(' ', '-', $name)),
                ];
            }

            return $sources;
        });
    }

    /**
     * Ambil artikel dari sumber tertentu (sudah dinormalisasi).
     */
    public function getArticles(string $source, ?string $type = null): array
    {
        $cacheKey = 'news:articles:' . $source . ($type ? ':' . $type : '');

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($source, $type) {
            // 1. Dapatkan info sumber dari API utama
            $sourceListResponse = Http::get($this->baseApi);
            if (!$sourceListResponse->successful()) {
                return ['error' => 'Gagal mengambil daftar sumber', 'data' => []];
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
                return ['error' => 'Sumber tidak ditemukan', 'data' => []];
            }

            // 2. Bangun endpoint artikel
            if ($type && isset($sourceInfo['type'])) {
                $endpoint = str_replace(':type', $type, $sourceInfo['type']);
            } elseif (isset($sourceInfo['all'])) {
                $endpoint = $sourceInfo['all'];
            } else {
                $firstType = $sourceInfo['listType'][0] ?? '';
                $endpoint  = str_replace(':type', $firstType, $sourceInfo['type'] ?? '');
            }

            // 3. Ambil data artikel dari API eksternal
            $url      = $this->baseApi . $endpoint;
            $response = Http::get($url);

            if (!$response->successful()) {
                return ['error' => 'Gagal mengambil artikel', 'data' => []];
            }

            $result = $response->json();
            $rawArticles = $result['data'] ?? [];

            // 4. Normalisasi setiap artikel
            $articles = array_map(function ($item) {
                return [
                    'title'          => $item['title'] ?? 'Tanpa Judul',
                    'link'           => $item['link'] ?? '#',
                    'contentSnippet' => $this->getSnippet($item),
                    'isoDate'        => $item['isoDate'] ?? null,
                    'image'          => $this->normalizeImage($item['image'] ?? null, $item['link'] ?? ''),
                ];
            }, $rawArticles);

            return [
                'messages' => $result['messages'] ?? ($result['message'] ?? ''),
                'total'    => $result['total'] ?? 0,
                'data'     => $articles,
            ];
        });
    }

    /**
     * Ambil cuplikan teks dari artikel dengan fallback bertingkat.
     */
    private function getSnippet(array $item): string
    {
        if (!empty($item['contentSnippet'])) {
            return $item['contentSnippet'];
        }

        if (!empty($item['content'])) {
            $text = strip_tags($item['content']);
            return mb_strlen($text) > 200 ? mb_substr($text, 0, 200) . '...' : $text;
        }

        if (!empty($item['description'])) {
            $text = strip_tags($item['description']);
            return mb_strlen($text) > 200 ? mb_substr($text, 0, 200) . '...' : $text;
        }

        return '';
    }

    /**
     * Normalisasi gambar ke format konsisten ['small', 'large'].
     */
    private function normalizeImage($image, string $articleLink): array
    {
        if (!$image) {
            return ['small' => null, 'large' => null];
        }

        // Jika string (contoh: Antara, VOA)
        if (is_string($image)) {
            $url = $this->fixRelativeUrl($image, $articleLink);
            return ['small' => $url, 'large' => $url];
        }

        // Jika array, ambil small, medium, large, extraLarge dengan prioritas
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

        return ['small' => $small, 'large' => $large];
    }

    /**
     * Ubah URL relatif menjadi absolut berdasarkan domain artikel.
     */
    private function fixRelativeUrl(?string $url, string $articleLink): ?string
    {
        if (!$url) {
            return null;
        }

        // Sudah absolut
        if (preg_match('/^https?:\/\//i', $url)) {
            return $url;
        }

        // Relatif terhadap domain artikel
        $parsed = parse_url($articleLink);
        if (isset($parsed['scheme'], $parsed['host'])) {
            $base = $parsed['scheme'] . '://' . $parsed['host'];
            return $base . ($url[0] === '/' ? '' : '/') . $url;
        }

        return null;
    }
}