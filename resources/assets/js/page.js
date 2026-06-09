// ========== RENDER FUNCTIONS ==========

function renderSourceList() {
  const container = document.getElementById('source-bar');
  container.innerHTML = '';

  AppState.sources.forEach(src => {
    const pill = document.createElement('span');
    pill.className = 'source-pill';
    if (AppState.currentSource === src.slug) {
      pill.classList.add('active');
    }
    pill.dataset.source = src.slug;
    pill.textContent = src.name;
    container.appendChild(pill);
  });
}

function renderCategoryBar() {
  const bar = document.getElementById('category-bar');
  bar.innerHTML = '';

  const source = AppState.sources.find(s => s.slug === AppState.currentSource);
  if (!source || !source.hasType) return;

  const allBtn = document.createElement('span');
  allBtn.className = 'category-pill' + (AppState.currentType === null ? ' active': '');
  allBtn.dataset.type = '';
  allBtn.textContent = 'Semua';
  bar.appendChild(allBtn);

  source.types.forEach(type => {
    const pill = document.createElement('span');
    pill.className = 'category-pill' + (AppState.currentType === type ? ' active': '');
    pill.dataset.type = type;
    pill.textContent = type.replace(/-/g, ' ');
    bar.appendChild(pill);
  });
}

function renderArticles() {
  const container = document.getElementById('news-container');
  if (AppState.articles.length === 0 && !AppState.isLoading) {
    container.innerHTML = `
    <div class="empty-state text-center py-4">
    <i class="bi bi-inbox display-4" style="color: var(--tg-theme-hint-color); opacity:0.5;"></i>
    <h6 class="mt-2">Tidak ada berita</h6>
    <small style="color: var(--tg-theme-hint-color);">Coba pilih kategori lain</small>
    </div>`;
    return;
  }

  let html = '<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-2">';
  AppState.articles.forEach(article => {
    const imgSrc = (article.image?.small && article.image.small.startsWith('http'))
    ? article.image.small: 'https://via.placeholder.com/360x200?text=No+Image';
    const title = api.escapeHtml(article.title || 'Tanpa Judul');
    const snippet = api.escapeHtml(article.contentSnippet || '').substring(0, 100);
    const link = article.link || '#';
    const date = article.isoDate ? new Date(article.isoDate).toLocaleDateString('id-ID', {
      day: 'numeric', month: 'short', year: 'numeric'
    }): '';

    html += `
    <div class="col">
    <div class="news-card" onclick="window.open('${link}', '_blank')">
    <img src="${imgSrc}" class="news-img" alt="${title}">
    <div class="card-body">
    <h5 class="card-title">${title}</h5>
    <p class="card-text">${snippet}...</p>
    <div class="d-flex justify-content-between align-items-center">
    <small style="color: var(--tg-theme-hint-color);">${date}</small>
    <span class="badge" style="background: var(--accent); color: white; font-size:0.65rem;">Baca</span>
    </div>
    </div>
    </div>
    </div>`;
  });
  html += '</div>';
  container.innerHTML = html;
}

// Tidak ada updateNavLabel lagi
function renderAll() {
  renderSourceList();
  renderCategoryBar();
  if (AppState.currentSource) {
    renderArticles();
  }
}