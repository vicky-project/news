// ========== INISIALISASI & EVENT DELEGATION ==========
document.addEventListener('DOMContentLoaded', async () => {
  // Ambil daftar sumber pertama kali
  await fetchSources();
  renderAll();

  // Delegasi event klik
  document.body.addEventListener('click', async (e) => {
    // Klik item sumber
    if (e.target.closest('.source-item')) {
      e.preventDefault();
      const el = e.target.closest('.source-item');
      const slug = el.dataset.source;

      if (AppState.currentSource === slug) return;

      AppState.currentSource = slug;
      AppState.currentType = null;

      await fetchArticles();
      renderAll();
    }

    // Klik kategori pill
    if (e.target.closest('.category-pill')) {
      const pill = e.target.closest('.category-pill');
      const type = pill.dataset.type;
      AppState.currentType = type === '' ? null: type;

      await fetchArticles();
      renderAll();
    }
  });
});