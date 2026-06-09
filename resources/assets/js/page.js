// ========== RENDER FUNCTIONS ==========

function renderSourceList() {
  const container = document.getElementById('source-list');
  container.querySelectorAll('.source-item').forEach(el => el.remove());

  AppState.sources.forEach(src => {
    const a = document.createElement('a');
    a.className = 'source-item';
    if (AppState.currentSource === src.slug) {
      a.classList.add('active');
    }
    a.href = '#';
    a.dataset.source = src.slug;
    a.innerHTML = `<i class="bi bi-dot me-1"></i>${api.escapeHtml(src.name)}`;
    container.appendChild(a);
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
    container.innerHTML = `...`; // sama seperti sebelumnya
    return;
  }

  let html = '<div class="row g-4">';
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
    <div class="col-md-6 col-lg-4">
    <div class="news-card" onclick="window.open('${link}', '_blank')">
    <img src="${imgSrc}" class="news-img" alt="${title}">
    <div class="card-body">
    <h5 class="card-title">${title}</h5>
    <p class="card-text">${snippet}...</p>
    <div class="d-flex justify-content-between align-items-center mt-2">
    <small style="color: var(--tg-theme-hint-color);">${date}</small>
    <span class="badge" style="background: var(--accent); color: white;">Baca</span>
    </div>
    </div>
    </div>
    </div>`;
  });
  html += '</div>';
  container.innerHTML = html;
}

function updateNavLabel() {
  const label = document.getElementById('current-source-label');
  if (AppState.currentSource) {
    const src = AppState.sources.find(s => s.slug === AppState.currentSource);
    label.textContent = src ? `Sumber: ${src.name}`: '';
  } else {
    label.textContent = 'Pilih sumber berita';
  }
}

function renderAll() {
  renderSourceList();
  renderCategoryBar();
  updateNavLabel();
  if (AppState.currentSource) {
    renderArticles();
  }
}