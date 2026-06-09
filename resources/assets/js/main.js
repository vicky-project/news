// ========== INISIALISASI & EVENT DELEGATION ==========
document.addEventListener('DOMContentLoaded', async () => {
  await fetchSources();
  renderAll();

  document.body.addEventListener('click', async (e) => {
    // Klik source pill
    if (e.target.closest('.source-pill')) {
      e.preventDefault();
      const el = e.target.closest('.source-pill');
      const slug = el.dataset.source;

      if (AppState.currentSource === slug) return;

      AppState.currentSource = slug;
      AppState.currentType = null;

      await fetchArticles();
      renderAll();
    }

    // Klik category pill
    if (e.target.closest('.category-pill')) {
      const pill = e.target.closest('.category-pill');
      const type = pill.dataset.type;
      AppState.currentType = type === '' ? null: type;

      await fetchArticles();
      renderAll();
    }
  });
});