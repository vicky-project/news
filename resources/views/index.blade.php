@extends('telegram::layouts.mini-app')

@section('title', 'NewsHub')

@section('content')
<div class="container-fluid px-2 py-2">
  {{-- Sumber berita sebagai pills horizontal --}}
  <div id="source-bar" class="d-flex flex-nowrap overflow-auto gap-1 mb-2 pb-1">
    {{-- diisi JS --}}
  </div>

  {{-- Kategori --}}
  <div id="category-bar" class="d-flex flex-nowrap overflow-auto gap-1 mb-3"></div>

  {{-- Loading --}}
  <div id="loading-indicator" class="text-center py-3" style="display: none;">
    <div class="spinner-border spinner-border-sm text-primary" role="status">
      <span class="visually-hidden">Memuat...</span>
    </div>
  </div>

  {{-- Area Berita --}}
  <div id="news-container">
    <div class="empty-state text-center py-4">
      <i class="bi bi-journal-text display-4" style="color: var(--tg-theme-hint-color); opacity:0.5;"></i>
      <h6 class="mt-2">Pilih sumber berita</h6>
      <small style="color: var(--tg-theme-hint-color);">Gulir sumber di atas untuk mulai membaca</small>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const BASE_URL = '{{ rtrim(config("app.url"), "/") }}';

  {!! file_get_contents(module_path('news', 'resources/assets/js/core.js')); !!}
  {!! file_get_contents(module_path('news', 'resources/assets/js/page.js')); !!}
  {!! file_get_contents(module_path('news', 'resources/assets/js/main.js')); !!}
</script>
@endpush

@push('styles')
<style>
:root {
  --primary: #1E3A5F;
  --primary-light: #2B4F7A;
  --accent: #F39C12;
  --accent-hover: #E67E22;
  --bg-light: var(--tg-theme-bg-color, #F8F9FA);
  --text-dark: var(--tg-theme-text-color, #2C3E50);
  --card-shadow: 0 1px 3px rgba(0,0,0,0.08);
  }

  body {
  background-color: var(--bg-light);
  color: var(--text-dark);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  font-size: 0.875rem;
  }

  .source-pill {
  display: inline-block;
  background: var(--tg-theme-secondary-bg-color, white);
  border: 1px solid var(--tg-theme-section-separator-color, #dee2e6);
  border-radius: 50px;
  padding: 0.35rem 0.9rem;
  font-size: 0.8rem;
  font-weight: 500;
  white-space: nowrap;
  cursor: pointer;
  transition: all 0.15s ease;
  color: var(--text-dark);
  flex-shrink: 0;
  }

  .source-pill.active {
  background: var(--primary);
  color: white;
  border-color: var(--primary);
  }

  .category-pill {
  display: inline-block;
  background: var(--tg-theme-secondary-bg-color, white);
  border: 1px solid var(--tg-theme-section-separator-color, #dee2e6);
  border-radius: 50px;
  padding: 0.25rem 0.75rem;
  font-size: 0.75rem;
  cursor: pointer;
  transition: all 0.15s ease;
  white-space: nowrap;
  color: var(--text-dark);
  flex-shrink: 0;
  }

  .category-pill.active {
  background: var(--accent);
  color: white;
  border-color: var(--accent);
  font-weight: 600;
  }

  .news-card {
  background: var(--tg-theme-secondary-bg-color, white);
  border-radius: 0.75rem;
  overflow: hidden;
  box-shadow: var(--card-shadow);
  transition: transform 0.15s ease;
  height: 100%;
  cursor: pointer;
  }

  .news-card:active {
  transform: scale(0.98);
  }

  .news-img {
  height: 140px;
  object-fit: cover;
  width: 100%;
  }

  .news-card .card-body {
  padding: 0.6rem 0.75rem;
  }

  .card-title {
  font-size: 0.85rem;
  font-weight: 600;
  line-height: 1.3;
  margin-bottom: 0.25rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  }

  .card-text {
  font-size: 0.75rem;
  color: var(--tg-theme-hint-color, #6c757d);
  margin-bottom: 0.25rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  }

  .empty-state i {
  opacity: 0.5;
  }
  </style>
  @endpush