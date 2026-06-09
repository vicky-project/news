// ========== GLOBAL STATE & API ==========
const AppState = {
  sources: [],
  currentSource: null,
  currentType: null,
  articles: [],
  isLoading: false
};

// Gunakan alias untuk kemudahan
const api = window.TelegramApp; // sudah tersedia dari layout

async function fetchSources() {
  try {
    const data = await api.fetchWithAuth(BASE_URL + '/api/news/sources');
    AppState.sources = data.sources;
  } catch (err) {
    console.error('Gagal mengambil sumber:', err);
    api.showToast('Gagal memuat daftar sumber', 'danger');
    AppState.sources = [];
  }
}

async function fetchArticles() {
  if (!AppState.currentSource) return;

  AppState.isLoading = true;
  api.showLoading('Memuat berita...');

  try {
    let url = `${BASE_URL}/api/news/articles/${AppState.currentSource}`;
    if (AppState.currentType) {
      url += `?type=${encodeURIComponent(AppState.currentType)}`;
    }
    const json = await api.fetchWithAuth(url);
    AppState.articles = json.data || [];
  } catch (err) {
    console.error('Gagal mengambil artikel:', err);
    api.showToast('Gagal memuat artikel', 'danger');
    AppState.articles = [];
  } finally {
    AppState.isLoading = false;
    api.hideLoading();
  }
}