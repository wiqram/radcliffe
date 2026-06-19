const contentList = document.getElementById('contentList');
const imageList = document.getElementById('imageList');
const sectionList = document.getElementById('sectionList');
const contentTemplate = document.getElementById('contentTemplate');
const imageTemplate = document.getElementById('imageTemplate');
const sectionTemplate = document.getElementById('sectionTemplate');
const pageFilter = document.getElementById('pageFilter');
const workspaceSearch = document.getElementById('workspaceSearch');
const currentScope = document.getElementById('currentScope');

const pageLabels = {
  home: 'Homepage',
  practice: 'Practice',
  who: 'Who',
  city: 'The City',
  summit: 'Summit',
  agenda: 'Agenda',
  articles: 'Articles',
  ethics: 'Ethics',
  Global: 'Global',
};
const labelToPageSlug = Object.fromEntries(
  Object.entries(pageLabels).map(([slug, label]) => [label, slug])
);

const state = {
  activeTab: 'content',
  content: [],
  media: [],
  sections: [],
};

document.querySelectorAll('.tab').forEach((tab) => {
  tab.addEventListener('click', () => {
    state.activeTab = tab.dataset.tab;
    document.querySelectorAll('.tab').forEach((item) => item.classList.remove('is-active'));
    document.querySelectorAll('.panel').forEach((panel) => panel.classList.remove('is-active'));
    tab.classList.add('is-active');
    document.getElementById(`${tab.dataset.tab}-panel`).classList.add('is-active');
    applyFilters();
  });
});

pageFilter.addEventListener('change', applyFilters);
workspaceSearch.addEventListener('input', applyFilters);

function setStatus(el, message, isError = false) {
  el.textContent = message;
  el.classList.toggle('error', isError);
}

async function request(url, options = {}) {
  const res = await fetch(url, options);
  if (!res.ok) throw new Error(await res.text());
  return res.json();
}

function textForSearch(...values) {
  return values.filter(Boolean).join(' ').toLowerCase();
}

function pageValue(record) {
  return record.page_slug || labelToPageSlug[record.page] || record.page || 'Global';
}

function pageLabel(value) {
  return pageLabels[value] || value || 'Global';
}

function updateRecordChrome(node, record, options = {}) {
  const title = node.querySelector('.record-title');
  const key = node.querySelector('.record-key');
  const pageBadge = node.querySelector('.page-badge');
  const typePill = node.querySelector('.type-pill');
  const mediaPill = node.querySelector('.media-pill');
  const enabledPill = node.querySelector('.enabled-pill');
  const originPill = node.querySelector('.origin-pill');

  if (title) title.textContent = record.label || record.title || record.key || options.fallbackTitle || 'Untitled';
  if (key) key.textContent = record.key || '';
  if (pageBadge) pageBadge.textContent = pageLabel(pageValue(record));
  if (typePill) typePill.textContent = record.type === 'html' ? 'HTML' : 'Text';
  if (mediaPill) {
    mediaPill.textContent = record.has_data ? 'Uploaded' : 'Empty';
    mediaPill.classList.toggle('is-success', Boolean(record.has_data));
    mediaPill.classList.toggle('is-muted', !record.has_data);
  }
  if (enabledPill) {
    enabledPill.textContent = record.enabled === false ? 'Hidden' : 'Visible';
    enabledPill.classList.toggle('is-success', record.enabled !== false);
    enabledPill.classList.toggle('is-danger', record.enabled === false);
  }
  if (originPill) {
    originPill.textContent = record.is_static ? 'Static' : 'CMS';
    originPill.classList.toggle('is-muted', Boolean(record.is_static));
  }

  node.dataset.page = pageValue(record);
  node.dataset.search = textForSearch(
    record.key,
    record.page,
    record.page_slug,
    record.label,
    record.title,
    record.value,
    record.alt_text,
    record.body_html
  );
}

function bindChromeRefresh(node, getRecord) {
  node.querySelectorAll('input, textarea, select').forEach((field) => {
    field.addEventListener('input', () => updateRecordChrome(node, getRecord()));
    field.addEventListener('change', () => updateRecordChrome(node, getRecord()));
  });
}

function renderContentRecord(record = {}) {
  const node = contentTemplate.content.firstElementChild.cloneNode(true);
  const key = node.querySelector('.key');
  const page = node.querySelector('.page');
  const label = node.querySelector('.label');
  const type = node.querySelector('.type');
  const value = node.querySelector('.value');
  const status = node.querySelector('.status');

  key.value = record.key || '';
  page.value = record.page || 'Global';
  label.value = record.label || '';
  type.value = record.type || 'text';
  value.value = record.value || '';

  const currentRecord = () => ({
    key: key.value.trim(),
    page: page.value.trim() || 'Global',
    label: label.value.trim(),
    type: type.value,
    value: value.value,
  });

  bindChromeRefresh(node, currentRecord);
  updateRecordChrome(node, currentRecord(), { fallbackTitle: 'New text key' });

  node.querySelector('.save').addEventListener('click', async () => {
    if (!key.value.trim()) return setStatus(status, 'Key is required.', true);
    setStatus(status, 'Saving...');
    try {
      const saved = await request(`/api/admin/content/${encodeURIComponent(key.value.trim())}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          page: page.value.trim() || 'Global',
          label: label.value.trim() || key.value.trim(),
          type: type.value,
          value: value.value,
        }),
      });
      updateRecordChrome(node, saved);
      setStatus(status, 'Saved.');
    } catch {
      setStatus(status, 'Save failed.', true);
    }
  });

  contentList.appendChild(node);
}

function renderImageRecord(record = {}) {
  const node = imageTemplate.content.firstElementChild.cloneNode(true);
  const key = node.querySelector('.key');
  const page = node.querySelector('.page');
  const label = node.querySelector('.label');
  const alt = node.querySelector('.alt');
  const file = node.querySelector('.file');
  const status = node.querySelector('.status');
  const meta = node.querySelector('.media-meta');

  key.value = record.key || '';
  page.value = record.page || 'Global';
  label.value = record.label || '';
  alt.value = record.alt_text || '';
  meta.textContent = record.has_data
    ? `Current file: ${record.filename || 'uploaded image'}`
    : 'No database image uploaded yet. The static image remains visible.';

  const currentRecord = () => ({
    key: key.value.trim(),
    page: page.value.trim() || 'Global',
    label: label.value.trim(),
    alt_text: alt.value.trim(),
    has_data: Boolean(record.has_data),
  });

  bindChromeRefresh(node, currentRecord);
  updateRecordChrome(node, currentRecord(), { fallbackTitle: 'New image key' });

  node.querySelector('.save').addEventListener('click', async () => {
    if (!key.value.trim()) return setStatus(status, 'Key is required.', true);
    setStatus(status, 'Saving...');
    const form = new FormData();
    form.append('page', page.value.trim() || 'Global');
    form.append('label', label.value.trim() || key.value.trim());
    form.append('altText', alt.value.trim());
    if (file.files[0]) form.append('image', file.files[0]);

    try {
      const saved = await request(`/api/admin/media/${encodeURIComponent(key.value.trim())}`, {
        method: 'POST',
        body: form,
      });
      record.has_data = saved.has_data;
      meta.textContent = saved.has_data
        ? `Current file: ${saved.filename || 'uploaded image'}`
        : 'Metadata saved. No image file uploaded.';
      updateRecordChrome(node, saved);
      setStatus(status, 'Saved.');
    } catch {
      setStatus(status, 'Save failed.', true);
    }
  });

  imageList.appendChild(node);
}

function renderSectionRecord(record = {}) {
  const node = sectionTemplate.content.firstElementChild.cloneNode(true);
  const key = node.querySelector('.key');
  const pageSlug = node.querySelector('.pageSlug');
  const page = node.querySelector('.page');
  const label = node.querySelector('.label');
  const sortOrder = node.querySelector('.sortOrder');
  const layout = node.querySelector('.layoutMode');
  const eyebrow = node.querySelector('.eyebrow');
  const title = node.querySelector('.title');
  const imageKey = node.querySelector('.imageKey');
  const enabled = node.querySelector('.enabled');
  const bodyHtml = node.querySelector('.bodyHtml');
  const note = node.querySelector('.section-note');
  const status = node.querySelector('.status');
  const deleteButton = node.querySelector('.delete');

  key.value = record.key || '';
  pageSlug.value = record.page_slug || 'home';
  page.value = record.page || pageLabel(pageSlug.value);
  label.value = record.label || '';
  sortOrder.value = record.sort_order ?? 100;
  layout.value = record.layout || 'text';
  eyebrow.value = record.eyebrow || '';
  title.value = record.title || '';
  imageKey.value = record.image_key || '';
  enabled.checked = record.enabled !== false;
  bodyHtml.value = record.body_html || '';

  const isStatic = Boolean(record.is_static);
  key.readOnly = isStatic;
  note.textContent = isStatic
    ? 'Existing static section: Delete hides it from visitors; turn Enabled back on and save to restore it.'
    : 'CMS-created section: Delete removes it from the database.';

  const currentRecord = () => ({
    key: key.value.trim(),
    page_slug: pageSlug.value,
    page: page.value.trim() || pageLabel(pageSlug.value),
    label: label.value.trim(),
    eyebrow: eyebrow.value,
    title: title.value,
    body_html: bodyHtml.value,
    image_key: imageKey.value.trim(),
    layout: layout.value,
    sort_order: Number(sortOrder.value) || 100,
    enabled: enabled.checked,
    is_static: isStatic,
  });

  pageSlug.addEventListener('change', () => {
    page.value = pageLabel(pageSlug.value);
  });

  bindChromeRefresh(node, currentRecord);
  updateRecordChrome(node, currentRecord(), { fallbackTitle: 'New section' });

  node.querySelector('.save').addEventListener('click', async () => {
    if (!key.value.trim()) return setStatus(status, 'Key is required.', true);
    setStatus(status, 'Saving...');
    try {
      const saved = await request(`/api/admin/sections/${encodeURIComponent(key.value.trim())}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          pageSlug: pageSlug.value,
          page: page.value.trim() || pageLabel(pageSlug.value),
          label: label.value.trim() || key.value.trim(),
          eyebrow: eyebrow.value,
          title: title.value,
          bodyHtml: bodyHtml.value,
          imageKey: imageKey.value.trim(),
          layout: layout.value,
          sortOrder: Number(sortOrder.value) || 100,
          enabled: enabled.checked,
        }),
      });
      updateRecordChrome(node, saved);
      updateMetrics();
      setStatus(status, 'Saved.');
    } catch {
      setStatus(status, 'Save failed.', true);
    }
  });

  deleteButton.addEventListener('click', async () => {
    if (!key.value.trim()) return setStatus(status, 'Key is required.', true);
    const action = isStatic ? 'hide this existing section' : 'delete this CMS section';
    if (!window.confirm(`Are you sure you want to ${action}?`)) return;
    setStatus(status, isStatic ? 'Hiding...' : 'Deleting...');
    try {
      const result = await request(`/api/admin/sections/${encodeURIComponent(key.value.trim())}`, {
        method: 'DELETE',
      });
      if (isStatic) {
        enabled.checked = false;
        updateRecordChrome(node, result);
        setStatus(status, 'Hidden.');
      } else {
        node.remove();
        updateMetrics();
      }
    } catch {
      setStatus(status, 'Delete failed.', true);
    }
  });

  sectionList.appendChild(node);
}

function populatePageFilter(data) {
  const values = new Map();
  [...data.content, ...data.media, ...data.sections].forEach((item) => {
    values.set(pageValue(item), pageLabel(pageValue(item)));
  });
  pageFilter.replaceChildren(new Option('All pages', ''));
  [...values.entries()]
    .sort((a, b) => a[1].localeCompare(b[1]))
    .forEach(([value, label]) => pageFilter.appendChild(new Option(label, value)));
}

function applyFilters() {
  const query = workspaceSearch.value.trim().toLowerCase();
  const page = pageFilter.value;
  currentScope.textContent = page ? pageLabel(page) : 'All pages';

  document.querySelectorAll('.record-grid-list').forEach((list) => {
    let visible = 0;
    list.querySelectorAll('.record').forEach((record) => {
      const kindMatches = record.dataset.kind === state.activeTab;
      const pageMatches = !page || record.dataset.page === page;
      const queryMatches = !query || record.dataset.search.includes(query);
      const show = kindMatches && pageMatches && queryMatches;
      record.hidden = !show;
      if (show) visible += 1;
    });
    list.classList.toggle('is-empty', list.closest('.panel')?.classList.contains('is-active') && visible === 0);
  });
}

function updateMetrics() {
  const contentTotal = contentList.querySelectorAll('.record').length;
  const imageTotal = imageList.querySelectorAll('.record').length;
  const sectionRecords = sectionList.querySelectorAll('.record');
  const sectionTotal = sectionRecords.length;
  const enabledTotal = [...sectionRecords].filter((record) => {
    const enabled = record.querySelector('.enabled');
    return enabled ? enabled.checked : false;
  }).length;

  document.getElementById('contentCount').textContent = contentTotal;
  document.getElementById('imageCount').textContent = imageTotal;
  document.getElementById('sectionCount').textContent = sectionTotal;
  document.getElementById('metricContent').textContent = contentTotal;
  document.getElementById('metricImages').textContent = imageTotal;
  document.getElementById('metricSections').textContent = sectionTotal;
  document.getElementById('metricEnabled').textContent = enabledTotal;
}

async function load() {
  try {
    const data = await request('/api/admin/content');
    state.content = data.content;
    state.media = data.media;
    state.sections = data.sections;
    contentList.replaceChildren();
    imageList.replaceChildren();
    sectionList.replaceChildren();
    populatePageFilter(data);
    data.content.forEach(renderContentRecord);
    data.sections.forEach(renderSectionRecord);
    data.media.forEach(renderImageRecord);
    updateMetrics();
    applyFilters();
  } catch {
    contentList.textContent = 'Unable to load CMS content.';
  }
}

document.getElementById('addContent').addEventListener('click', () => {
  renderContentRecord({ key: `custom.text.${Date.now()}`, page: 'Global', label: 'New text key' });
  updateMetrics();
  applyFilters();
});

document.getElementById('addImage').addEventListener('click', () => {
  renderImageRecord({ key: `custom.image.${Date.now()}`, page: 'Global', label: 'New image key' });
  updateMetrics();
  applyFilters();
});

document.getElementById('addSection').addEventListener('click', () => {
  renderSectionRecord({
    key: `custom.section.${Date.now()}`,
    page_slug: 'home',
    page: 'Homepage',
    label: 'New section',
    sort_order: 100,
    enabled: true,
    is_static: false,
    layout: 'text',
  });
  updateMetrics();
  applyFilters();
});

load();
