const pageGrid = document.getElementById('pageGrid');
const pageEditor = document.getElementById('pageEditor');
const pageEditorTitle = document.getElementById('pageEditorTitle');
const pageEditorSummary = document.getElementById('pageEditorSummary');
const pageEditorLive = document.getElementById('pageEditorLive');
const sectionList = document.getElementById('sectionList');
const contentList = document.getElementById('contentList');
const imageList = document.getElementById('imageList');
const messageList = document.getElementById('messageList');
const settingsList = document.getElementById('settingsList');
const workspaceSearch = document.getElementById('workspaceSearch');
const currentScope = document.getElementById('currentScope');
const overlay = document.getElementById('editorOverlay');
const sectionForm = document.getElementById('sectionEditor');

const pages = [
  { slug: 'home', label: 'Homepage', url: '/Homepage.html' },
  { slug: 'practice', label: 'Practice', url: '/Practice.html' },
  { slug: 'who', label: "Who's Who", url: '/Whos-Who.html', aliases: ['Who'] },
  { slug: 'city', label: 'The City', url: '/The-City.html' },
  { slug: 'summit', label: 'Summit', url: '/Summit.html' },
  { slug: 'agenda', label: 'Agenda', url: '/Agenda.html' },
  { slug: 'articles', label: 'Articles', url: '/Articles.html' },
  { slug: 'ethics', label: 'Ethics', url: '/Ethics.html' },
  { slug: 'contact', label: 'Contact', url: '/Contact.html' },
];

const pageBySlug = Object.fromEntries(pages.map((page) => [page.slug, page]));
const labelToSlug = pages.reduce((acc, page) => {
  acc[page.label.toLowerCase()] = page.slug;
  (page.aliases || []).forEach((alias) => {
    acc[alias.toLowerCase()] = page.slug;
  });
  return acc;
}, {});

const state = {
  activeTab: 'pages',
  selectedPage: null,
  sectionEditing: null,
  content: [],
  media: [],
  sections: [],
  messages: [],
};

function escapeHtml(value = '') {
  return String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function slugify(value = '') {
  const slug = String(value)
    .toLowerCase()
    .replace(/&/g, ' and ')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
  return slug || 'section';
}

function textToHtml(value = '') {
  return String(value)
    .split(/\n{2,}/)
    .map((part) => part.trim())
    .filter(Boolean)
    .map((part) => `<p>${escapeHtml(part).replace(/\n/g, '<br />')}</p>`)
    .join('');
}

function htmlToText(value = '') {
  const holder = document.createElement('div');
  holder.innerHTML = value || '';
  holder.querySelectorAll('br').forEach((br) => br.replaceWith('\n'));
  holder.querySelectorAll('p, div, h1, h2, h3, h4, li').forEach((el) => {
    el.append(document.createTextNode('\n\n'));
  });
  return holder.textContent.replace(/\n{3,}/g, '\n\n').trim();
}

function pageSlugForRecord(record = {}) {
  if (record.page_slug) return record.page_slug;
  return labelToSlug[String(record.page || '').toLowerCase()] || 'global';
}

function pageLabel(slug) {
  return pageBySlug[slug]?.label || slug || 'Global';
}

function pageUrl(slug) {
  return pageBySlug[slug]?.url || '/';
}

function textForSearch(...values) {
  return values.filter(Boolean).join(' ').toLowerCase();
}

function setStatus(el, message, isError = false) {
  if (!el) return;
  el.textContent = message;
  el.classList.toggle('error', isError);
}

async function request(url, options = {}) {
  const res = await fetch(url, options);
  if (!res.ok) throw new Error(await res.text());
  return res.json();
}

function switchTab(tabName) {
  state.activeTab = tabName;
  document.querySelectorAll('.tab').forEach((tab) => {
    tab.classList.toggle('is-active', tab.dataset.tab === tabName);
  });
  document.querySelectorAll('.panel').forEach((panel) => {
    panel.classList.toggle('is-active', panel.id === `${tabName}-panel`);
  });
  if (tabName !== 'pages') closePageEditor();
  currentScope.textContent = tabName === 'pages' ? 'All pages' : document.querySelector(`[data-tab="${tabName}"] span:nth-child(2)`)?.textContent || 'Website';
  applySearch();
}

document.querySelectorAll('.tab').forEach((tab) => {
  tab.addEventListener('click', () => switchTab(tab.dataset.tab));
});

workspaceSearch.addEventListener('input', applySearch);

function renderPages() {
  pageGrid.replaceChildren();
  pages.forEach((page) => {
    const sections = state.sections.filter((section) => section.page_slug === page.slug);
    const visible = sections.filter((section) => section.enabled !== false).length;
    const hidden = sections.length - visible;
    const media = state.media.filter((item) => pageSlugForRecord(item) === page.slug);
    const text = state.content.filter((item) => pageSlugForRecord(item) === page.slug);

    const card = document.createElement('article');
    card.className = 'page-card searchable';
    card.dataset.search = textForSearch(page.label, sections.map((s) => s.label).join(' '), media.map((m) => m.label).join(' '), text.map((c) => c.label).join(' '));
    card.innerHTML = `
      <div class="page-card-head">
        <div>
          <span class="record-kicker">Website page</span>
          <h3>${escapeHtml(page.label)}</h3>
        </div>
        <span class="pill ${hidden ? 'is-danger' : 'is-success'}">${visible} visible</span>
      </div>
      <div class="page-card-stats">
        <span>${sections.length} sections</span>
        <span>${media.length} images</span>
        <span>${text.length} text fields</span>
      </div>
      <div class="record-actions">
        <a class="button-link secondary-link" href="${page.url}" target="_blank" rel="noreferrer">View</a>
        <button type="button">Edit page</button>
      </div>
    `;
    card.querySelector('button').addEventListener('click', () => openPageEditor(page.slug));
    pageGrid.appendChild(card);
  });
}

function closePageEditor() {
  state.selectedPage = null;
  pageGrid.hidden = false;
  pageEditor.hidden = true;
  currentScope.textContent = 'All pages';
}

function openPageEditor(slug) {
  state.selectedPage = slug;
  const page = pageBySlug[slug];
  pageGrid.hidden = true;
  pageEditor.hidden = false;
  pageEditorTitle.textContent = page.label;
  pageEditorLive.href = page.url;
  currentScope.textContent = page.label;
  renderPageEditor();
  pageEditor.scrollIntoView({ block: 'start' });
}

document.getElementById('backToPages').addEventListener('click', closePageEditor);

function sortedSections(slug) {
  return state.sections
    .filter((section) => section.page_slug === slug)
    .sort((a, b) => (a.sort_order || 100) - (b.sort_order || 100) || a.label.localeCompare(b.label));
}

function renderPageEditor() {
  const slug = state.selectedPage;
  if (!slug) return;
  const sections = sortedSections(slug);
  const visible = sections.filter((section) => section.enabled !== false).length;
  const dynamic = sections.filter((section) => !section.is_static);
  pageEditorSummary.textContent = `${visible} visible sections, ${dynamic.length} added in admin.`;
  renderSectionList(slug);
  renderContentList(slug);
}

function renderSectionList(slug) {
  sectionList.replaceChildren();
  const sections = sortedSections(slug);
  if (!sections.length) {
    sectionList.innerHTML = '<div class="empty-state">No sections on this page yet.</div>';
    return;
  }

  sections.forEach((section) => {
    const card = document.createElement('article');
    card.className = 'section-card searchable';
    card.dataset.search = textForSearch(section.label, section.title, section.eyebrow, section.body_html);
    const visible = section.enabled !== false;
    card.innerHTML = `
      <div class="section-main">
        <span class="drag-handle">${section.is_static ? 'Built-in' : 'Added'}</span>
        <div>
          <h4>${escapeHtml(section.label || section.title || 'Untitled section')}</h4>
          <p>${section.is_static ? 'This section is part of the original page. Some text or images may be editable below.' : escapeHtml(section.title || 'Custom page section')}</p>
        </div>
      </div>
      <div class="section-actions">
        <span class="pill ${visible ? 'is-success' : 'is-danger'}">${visible ? 'Visible' : 'Hidden'}</span>
      </div>
    `;

    const actions = card.querySelector('.section-actions');
    const toggle = document.createElement('button');
    toggle.className = 'secondary';
    toggle.type = 'button';
    toggle.textContent = visible ? 'Hide' : 'Show';
    toggle.addEventListener('click', () => toggleSection(section));
    actions.appendChild(toggle);

    if (section.is_static) {
      const findText = document.createElement('button');
      findText.className = 'secondary';
      findText.type = 'button';
      findText.textContent = 'See page text';
      findText.addEventListener('click', () => contentList.scrollIntoView({ behavior: 'smooth', block: 'start' }));
      actions.appendChild(findText);
    } else {
      const edit = document.createElement('button');
      edit.type = 'button';
      edit.textContent = 'Edit';
      edit.addEventListener('click', () => openSectionEditor(section));
      actions.appendChild(edit);

      const moveUp = document.createElement('button');
      moveUp.className = 'secondary icon-action';
      moveUp.type = 'button';
      moveUp.textContent = 'Up';
      moveUp.addEventListener('click', () => moveSection(section.key, -1));
      actions.appendChild(moveUp);

      const moveDown = document.createElement('button');
      moveDown.className = 'secondary icon-action';
      moveDown.type = 'button';
      moveDown.textContent = 'Down';
      moveDown.addEventListener('click', () => moveSection(section.key, 1));
      actions.appendChild(moveDown);
    }

    sectionList.appendChild(card);
  });
}

function renderContentList(slug) {
  contentList.replaceChildren();
  const rows = state.content
    .filter((item) => pageSlugForRecord(item) === slug)
    .sort((a, b) => a.label.localeCompare(b.label));

  if (!rows.length) {
    contentList.innerHTML = '<div class="empty-state">This page has no editable text fields yet.</div>';
    return;
  }

  rows.forEach((record) => contentList.appendChild(contentEditor(record)));
}

function contentEditor(record) {
  const item = document.createElement('article');
  item.className = 'simple-editor searchable';
  item.dataset.search = textForSearch(record.page, record.label, record.value);

  const editorId = `editor-${slugify(record.key)}`;
  item.innerHTML = `
    <div class="simple-editor-head">
      <div>
        <h4>${escapeHtml(record.label || 'Editable text')}</h4>
        <p>${record.type === 'html' ? 'Formatted text' : 'Plain text'}</p>
      </div>
      <button type="button">Save</button>
    </div>
    <div class="content-field" id="${editorId}"></div>
    <details class="advanced-fields">
      <summary>Advanced technical details</summary>
      <code>${escapeHtml(record.key)}</code>
    </details>
    <span class="status"></span>
  `;

  const field = item.querySelector('.content-field');
  if (record.type === 'html') {
    field.contentEditable = 'true';
    field.classList.add('rich-field');
    field.innerHTML = record.value || '';
  } else {
    const textarea = document.createElement('textarea');
    textarea.rows = 4;
    textarea.value = record.value || '';
    field.replaceWith(textarea);
  }

  item.querySelector('button').addEventListener('click', async () => {
    const status = item.querySelector('.status');
    const value = record.type === 'html' ? item.querySelector('.rich-field').innerHTML : item.querySelector('textarea').value;
    setStatus(status, 'Saving...');
    try {
      const saved = await request(`/api/admin/content/${encodeURIComponent(record.key)}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          page: record.page,
          label: record.label,
          type: record.type,
          value,
        }),
      });
      Object.assign(record, saved);
      setStatus(status, 'Saved.');
    } catch {
      setStatus(status, 'Save failed.', true);
    }
  });

  return item;
}

async function saveSection(section) {
  const saved = await request(`/api/admin/sections/${encodeURIComponent(section.key)}`, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      pageSlug: section.page_slug,
      page: section.page,
      label: section.label,
      eyebrow: section.eyebrow || '',
      title: section.title || '',
      bodyHtml: section.body_html || '',
      imageKey: section.image_key || '',
      layout: section.layout || 'text',
      sortOrder: Number(section.sort_order) || 100,
      enabled: section.enabled !== false,
    }),
  });
  const index = state.sections.findIndex((item) => item.key === saved.key);
  if (index >= 0) state.sections[index] = saved;
  else state.sections.push(saved);
  return saved;
}

async function toggleSection(section) {
  section.enabled = section.enabled === false;
  await saveSection(section);
  renderAll();
  if (state.selectedPage) openPageEditor(state.selectedPage);
}

async function moveSection(key, direction) {
  const slug = state.selectedPage;
  const dynamic = sortedSections(slug).filter((section) => !section.is_static);
  const index = dynamic.findIndex((section) => section.key === key);
  const nextIndex = index + direction;
  if (index < 0 || nextIndex < 0 || nextIndex >= dynamic.length) return;
  const reordered = [...dynamic];
  const [item] = reordered.splice(index, 1);
  reordered.splice(nextIndex, 0, item);
  await Promise.all(
    reordered.map((section, idx) => {
      section.sort_order = 100 + idx * 10;
      return saveSection(section);
    })
  );
  renderAll();
  openPageEditor(slug);
}

function populateSectionPageOptions() {
  const select = document.getElementById('sectionPage');
  select.replaceChildren(...pages.map((page) => new Option(page.label, page.slug)));
}

function populateSectionImageOptions(selected = '') {
  const select = document.getElementById('sectionImage');
  select.replaceChildren(new Option('No image', ''));
  state.media
    .slice()
    .sort((a, b) => `${a.page} ${a.label}`.localeCompare(`${b.page} ${b.label}`))
    .forEach((item) => {
      select.appendChild(new Option(`${item.page} - ${item.label}`, item.key));
    });
  select.value = selected || '';
}

function openSectionEditor(section = null) {
  const isNew = !section;
  const slug = section?.page_slug || state.selectedPage || 'home';
  const page = pageBySlug[slug];
  state.sectionEditing = section;
  overlay.hidden = false;
  document.body.classList.add('has-overlay');
  document.getElementById('sectionEditorTitle').textContent = isNew ? 'Add section' : 'Edit section';
  document.getElementById('sectionPage').value = slug;
  document.getElementById('sectionName').value = section?.label || '';
  document.getElementById('sectionLayout').value = section?.layout || 'text';
  document.getElementById('sectionVisible').checked = section?.enabled !== false;
  document.getElementById('sectionEyebrow').value = section?.eyebrow || '';
  document.getElementById('sectionHeading').value = section?.title || '';
  document.getElementById('sectionBody').value = htmlToText(section?.body_html || '');
  document.getElementById('sectionKey').value = section?.key || `${slug}.section.${Date.now().toString(36)}`;
  document.getElementById('sectionSort').value = section?.sort_order ?? nextSectionOrder(slug);
  populateSectionImageOptions(section?.image_key || '');
  document.getElementById('deleteSection').hidden = isNew;
  setStatus(document.getElementById('sectionEditorStatus'), '');
  if (isNew) document.getElementById('sectionName').focus();
  if (page) document.getElementById('sectionPage').value = page.slug;
}

function closeSectionEditor() {
  overlay.hidden = true;
  document.body.classList.remove('has-overlay');
  sectionForm.reset();
  state.sectionEditing = null;
}

function nextSectionOrder(slug) {
  const orders = state.sections.filter((section) => section.page_slug === slug).map((section) => Number(section.sort_order) || 100);
  return orders.length ? Math.max(...orders) + 10 : 100;
}

sectionForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  const status = document.getElementById('sectionEditorStatus');
  const slug = document.getElementById('sectionPage').value;
  const name = document.getElementById('sectionName').value.trim() || 'New section';
  const key = state.sectionEditing?.key || `${slug}.section.${slugify(name)}.${Date.now().toString(36)}`;
  const section = {
    key,
    page_slug: slug,
    page: pageLabel(slug),
    label: name,
    eyebrow: document.getElementById('sectionEyebrow').value.trim(),
    title: document.getElementById('sectionHeading').value.trim(),
    body_html: textToHtml(document.getElementById('sectionBody').value),
    image_key: document.getElementById('sectionImage').value,
    layout: document.getElementById('sectionLayout').value,
    sort_order: Number(document.getElementById('sectionSort').value) || nextSectionOrder(slug),
    enabled: document.getElementById('sectionVisible').checked,
    is_static: false,
  };
  setStatus(status, 'Saving...');
  try {
    await saveSection(section);
    closeSectionEditor();
    renderAll();
    openPageEditor(slug);
  } catch {
    setStatus(status, 'Save failed.', true);
  }
});

document.getElementById('addSection').addEventListener('click', () => openSectionEditor());
document.getElementById('closeSectionEditor').addEventListener('click', closeSectionEditor);
overlay.addEventListener('click', (event) => {
  if (event.target === overlay) closeSectionEditor();
});

document.getElementById('deleteSection').addEventListener('click', async () => {
  const section = state.sectionEditing;
  if (!section) return;
  if (!window.confirm('Delete this added section from the website editor?')) return;
  const status = document.getElementById('sectionEditorStatus');
  setStatus(status, 'Deleting...');
  try {
    await request(`/api/admin/sections/${encodeURIComponent(section.key)}`, { method: 'DELETE' });
    state.sections = state.sections.filter((item) => item.key !== section.key);
    const slug = section.page_slug;
    closeSectionEditor();
    renderAll();
    openPageEditor(slug);
  } catch {
    setStatus(status, 'Delete failed.', true);
  }
});

function renderMedia() {
  imageList.replaceChildren();
  const records = state.media.slice().sort((a, b) => `${a.page} ${a.label}`.localeCompare(`${b.page} ${b.label}`));
  records.forEach((record) => imageList.appendChild(mediaCard(record)));
}

function mediaCard(record) {
  const card = document.createElement('article');
  card.className = 'media-card searchable';
  card.dataset.search = textForSearch(record.page, record.label, record.filename, record.alt_text);
  const hasImage = Boolean(record.has_data);
  card.innerHTML = `
    <div class="media-preview ${hasImage ? '' : 'is-empty'}">
      ${hasImage ? `<img src="/api/media/${encodeURIComponent(record.key)}" alt="${escapeHtml(record.alt_text || record.label || '')}" loading="lazy" />` : '<span>Original website image</span>'}
    </div>
    <div class="media-body">
      <span class="record-kicker">${escapeHtml(record.page || 'Website')}</span>
      <h3>${escapeHtml(record.label || 'Website image')}</h3>
      <p>${hasImage ? escapeHtml(record.filename || 'Uploaded image') : 'No uploaded replacement. The original website image is active.'}</p>
      <label><span>Display name</span><input class="media-label" value="${escapeHtml(record.label || '')}" /></label>
      <label><span>Alt text</span><input class="media-alt" value="${escapeHtml(record.alt_text || '')}" /></label>
      <label><span>Replace image</span><input class="media-file" type="file" accept="image/*" /></label>
      <details class="advanced-fields">
        <summary>Advanced technical details</summary>
        <code>${escapeHtml(record.key)}</code>
      </details>
      <div class="record-actions">
        <span class="status"></span>
        ${hasImage ? '<button class="danger restore" type="button">Restore original</button>' : ''}
        <button class="save" type="button">Save image</button>
      </div>
    </div>
  `;

  card.querySelector('.save').addEventListener('click', () => saveMediaRecord(record, card));
  card.querySelector('.restore')?.addEventListener('click', () => restoreMediaRecord(record, card));
  return card;
}

async function saveMediaRecord(record, card) {
  const status = card.querySelector('.status');
  const form = new FormData();
  form.append('page', record.page || 'Global');
  form.append('label', card.querySelector('.media-label').value.trim() || record.label || 'Website image');
  form.append('altText', card.querySelector('.media-alt').value.trim());
  const file = card.querySelector('.media-file').files[0];
  if (file) form.append('image', file);
  setStatus(status, 'Saving...');
  try {
    const saved = await request(`/api/admin/media/${encodeURIComponent(record.key)}`, {
      method: 'POST',
      body: form,
    });
    replaceMedia(saved);
    renderAll();
    switchTab('media');
  } catch {
    setStatus(status, 'Save failed.', true);
  }
}

async function restoreMediaRecord(record, card) {
  if (!window.confirm('Restore the original website image for this slot?')) return;
  const status = card.querySelector('.status');
  setStatus(status, 'Restoring...');
  try {
    const restored = await request(`/api/admin/media/${encodeURIComponent(record.key)}`, { method: 'DELETE' });
    replaceMedia(restored);
    renderAll();
    switchTab('media');
  } catch {
    setStatus(status, 'Restore failed.', true);
  }
}

function replaceMedia(saved) {
  const index = state.media.findIndex((item) => item.key === saved.key);
  if (index >= 0) state.media[index] = saved;
  else state.media.push(saved);
}

document.getElementById('addImage').addEventListener('click', () => {
  const slug = state.selectedPage || 'home';
  const key = `${slug}.media.${Date.now().toString(36)}`;
  const record = { key, page: pageLabel(slug), label: 'New image', alt_text: '', has_data: false };
  state.media.unshift(record);
  renderMedia();
  imageList.firstElementChild?.scrollIntoView({ behavior: 'smooth', block: 'start' });
});

function renderMessages() {
  messageList.replaceChildren();
  if (!state.messages.length) {
    messageList.innerHTML = '<div class="empty-state">No contact messages yet.</div>';
    return;
  }

  state.messages.forEach((record) => {
    const item = document.createElement('article');
    item.className = 'record message-card searchable';
    item.dataset.search = textForSearch(record.name, record.email, record.organisation, record.topic, record.message);
    item.innerHTML = `
      <div class="record-head">
        <div>
          <span class="record-kicker">Message</span>
          <h3 class="record-title">${escapeHtml(record.name || 'Anonymous enquiry')}</h3>
          <code class="record-key">${escapeHtml(record.email || 'No email')}</code>
        </div>
        <span class="pill ${record.delivery_status === 'sent' ? 'is-success' : 'is-muted'}">${escapeHtml(record.delivery_status || 'stored')}</span>
      </div>
      <div class="record-body">
        <div class="message-meta"></div>
        <p class="message-body">${escapeHtml(record.message || '')}</p>
      </div>
    `;
    const meta = item.querySelector('.message-meta');
    [record.topic, record.organisation, record.recipient_email ? `To: ${record.recipient_email}` : '', record.created_at ? new Date(record.created_at).toLocaleString() : '']
      .filter(Boolean)
      .forEach((value) => {
        const span = document.createElement('span');
        span.textContent = value;
        meta.appendChild(span);
      });
    messageList.appendChild(item);
  });
}

function renderSettings() {
  settingsList.replaceChildren();
  const keys = new Set([
    'global.announcement.text',
    'global.announcement.cta',
    'contact.recipient.email',
    'contact.details.email',
    'contact.details.location',
    'contact.details.response',
  ]);
  state.content
    .filter((record) => keys.has(record.key))
    .sort((a, b) => a.label.localeCompare(b.label))
    .forEach((record) => settingsList.appendChild(contentEditor(record)));
}

function updateMetrics() {
  const visible = state.sections.filter((section) => section.enabled !== false).length;
  document.getElementById('pageCount').textContent = pages.length;
  document.getElementById('imageCount').textContent = state.media.length;
  document.getElementById('messageCount').textContent = state.messages.length;
  document.getElementById('settingCount').textContent = settingsList.children.length || 0;
  document.getElementById('metricPages').textContent = pages.length;
  document.getElementById('metricVisible').textContent = visible;
  document.getElementById('metricImages').textContent = state.media.length;
  document.getElementById('metricMessages').textContent = state.messages.length;
}

function renderAll() {
  renderPages();
  renderMedia();
  renderMessages();
  renderSettings();
  updateMetrics();
  applySearch();
}

function applySearch() {
  const query = workspaceSearch.value.trim().toLowerCase();
  document.querySelectorAll('.searchable').forEach((item) => {
    item.hidden = Boolean(query) && !item.dataset.search.includes(query);
  });
}

async function load() {
  try {
    populateSectionPageOptions();
    const data = await request('/api/admin/content');
    state.content = data.content || [];
    state.media = data.media || [];
    state.sections = data.sections || [];
    state.messages = data.messages || [];
    renderAll();
  } catch {
    pageGrid.innerHTML = '<div class="empty-state">Unable to load the website editor.</div>';
  }
}

load();
