@extends('telegram::layouts.mini-app')

@section('title', 'NewsHub')

@section('content')
{{-- Navbar --}}
<nav class="navbar navbar-dark sticky-top mb-4" style="background: var(--primary);">
  <div class="container">
    <span class="navbar-brand mb-0 h1">
      <i class="bi bi-newspaper me-2"></i>NewsHub
    </span>
    <span class="navbar-text text-white-50 d-none d-md-inline" id="current-source-label">
      Pilih sumber berita
    </span>
  </div>
</nav>

<div class="container">
  <div class="row">
    {{-- Sidebar Sumber --}}
    <div class="col-md-3 mb-4 mb-md-0">
      <div class="source-sidebar" id="source-list">
        <h5 class="fw-bold mb-3"><i class="bi bi-grid-3x3-gap-fill me-2"></i>Sumber</h5>
        {{-- diisi oleh JS --}}
      </div>
    </div>

    {{-- Konten Utama --}}
    <div class="col-md-9">
      {{-- Kategori (dinamis) --}}
      <div id="category-bar" class="d-flex flex-wrap gap-2 mb-4"></div>

      {{-- Loading --}}
      <div id="loading-indicator" class="text-center py-5" style="display: none;">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Memuat...</span>
        </div>
      </div>

      {{-- Area Berita --}}
      <div id="news-container">
        <div class="empty-state">
          <i class="bi bi-journal-text display-3" style="color: var(--tg-theme-hint-color);"></i>
          <h4 class="mt-3">Jelajahi Berita Terkini</h4>
          <p style="color: var(--tg-theme-hint-color);">
            Klik salah satu sumber berita di samping untuk mulai membaca.
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="//cdn.jsdelivr.net/npm/eruda"></script>
<script>
  eruda.init();
</script>
<script>
  const BASE_URL = '{{ rtrim(config("app.url"), "/") }}';

  {!! file_get_contents(module_path('news', 'resources/assets/js/core.js')); !!}
  {!! file_get_contents(module_path('news', 'resources/assets/js/page.js')); !!}
  {!! file_get_contents(module_path('news', 'resources/assets/js/main.js')); !!}
</script>
@endpush

@push('styles')
{{-- CSS kustom kita --}}
<style>
:root {
  --primary: #1E3A5F;
  --primary-light: #2B4F7A;
  --accent: #F39C12;
  --accent-hover: #E67E22;
  --bg-light: var(--tg-theme-bg-color, #F8F9FA);
  --text-dark: var(--tg-theme-text-color, #2C3E50);
  --card-shadow: 0 4px 12px rgba(0,0,0,0.08);
  }

  body {
  background-color: var(--bg-light);
  color: var(--text-dark);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  }

  .source-sidebar {
  background: var(--tg-theme-secondary-bg-color, white);
  border-radius: 1rem;
  box-shadow: var(--card-shadow);
  padding: 1.25rem;
  height: fit-content;
  }

  .source-item {
  display: block;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  margin-bottom: 0.35rem;
  color: var(--text-dark);
  text-decoration: none;
  transition: all 0.2s ease;
  cursor: pointer;
  font-weight: 500;
  border: 1px solid transparent;
  }

  .source-item:hover {
  background-color: rgba(30, 58, 95, 0.05);
  border-color: var(--primary-light);
  }

  .source-item.active {
  background-color: var(--primary);
  color: white !important;
  border-color: var(--primary);
  }

  .category-pill {
  background: var(--tg-theme-secondary-bg-color, white);
  border: 1px solid var(--tg-theme-section-separator-color, #dee2e6);
  border-radius: 50px;
  padding: 0.4rem 1.2rem;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.15s ease;
  white-space: nowrap;
  color: var(--text-dark);
  }

  .category-pill.active {
  background: var(--accent);
  color: white;
  border-color: var(--accent);
  font-weight: 600;
  }

  .news-card {
  background: var(--tg-theme-secondary-bg-color, white);
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: var(--card-shadow);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  height: 100%;
  cursor: pointer;
  }

  .news-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0,0,0,0.1);
  }

  .news-img {
  height: 180px;
  object-fit: cover;
  width: 100%;
  }

  .news-card .card-body {
  padding: 1rem 1.25rem;
  }

  .card-title {
  font-size: 1rem;
  font-weight: 600;
  line-height: 1.4;
  }

  .card-text {
  font-size: 0.85rem;
  color: var(--tg-theme-hint-color, #6c757d);
  }

  .empty-state {
  background: var(--tg-theme-secondary-bg-color, white);
  border-radius: 1rem;
  box-shadow: var(--card-shadow);
  padding: 3rem;
  text-align: center;
  }
  </style>
  @endpush