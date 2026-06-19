const contentList = document.getElementById('contentList');
const imageList = document.getElementById('imageList');
const sectionList = document.getElementById('sectionList');
const contentTemplate = document.getElementById('contentTemplate');
const imageTemplate = document.getElementById('imageTemplate');
const sectionTemplate = document.getElementById('sectionTemplate');

const pageLabels = {
  home: 'Homepage',
  practice: 'Practice',
  who: 'Who',
  city: 'The City',
  summit: 'Summit',
  agenda: 'Agenda',
  articles: 'Articles',
  ethics: 'Ethics',
};

document.querySelectorAll('.tab').forEach((tab) => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.tab').forEach((item) => item.classList.remove('is-active'));
    document.querySelectorAll('.panel').forEach((panel) => panel.classList.remove('is-active'));
    tab.classList.add('is-active');
    document.getElementById(`${tab.dataset.tab}-panel`).classList.add('is-active');
  });
});

function setStatus(el, message, isError = false) {
  el.textContent = message;
  el.classList.toggle('error', isError);
}

async function request(url, options = {}) {
  const res = await fetch(url, options);
  if (!res.ok) throw new Error(await res.text());
  return res.json();
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

  node.querySelector('.save').addEventListener('click', async () => {
    if (!key.value.trim()) return setStatus(status, 'Key is required.', true);
    setStatus(status, 'Saving...');
    try {
      await request(`/api/admin/content/${encodeURIComponent(key.value.trim())}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          page: page.value.trim() || 'Global',
          label: label.value.trim() || key.value.trim(),
          type: type.value,
          value: value.value,
        }),
      });
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
      meta.textContent = saved.has_data
        ? `Current file: ${saved.filename || 'uploaded image'}`
        : 'Metadata saved. No image file uploaded.';
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
  page.value = record.page || pageLabels[pageSlug.value] || 'Homepage';
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
    ? 'Existing static section: delete hides it from visitors; save can re-enable it.'
    : 'CMS-created section: delete removes it from the database.';

  pageSlug.addEventListener('change', () => {
    page.value = pageLabels[pageSlug.value] || page.value;
  });

  node.querySelector('.save').addEventListener('click', async () => {
    if (!key.value.trim()) return setStatus(status, 'Key is required.', true);
    setStatus(status, 'Saving...');
    try {
      await request(`/api/admin/sections/${encodeURIComponent(key.value.trim())}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          pageSlug: pageSlug.value,
          page: page.value.trim() || pageLabels[pageSlug.value] || 'Homepage',
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
      await request(`/api/admin/sections/${encodeURIComponent(key.value.trim())}`, {
        method: 'DELETE',
      });
      if (isStatic) {
        enabled.checked = false;
        setStatus(status, 'Hidden.');
      } else {
        node.remove();
      }
    } catch {
      setStatus(status, 'Delete failed.', true);
    }
  });

  sectionList.appendChild(node);
}

async function load() {
  try {
    const data = await request('/api/admin/content');
    contentList.replaceChildren();
    imageList.replaceChildren();
    sectionList.replaceChildren();
    data.content.forEach(renderContentRecord);
    data.media.forEach(renderImageRecord);
    data.sections.forEach(renderSectionRecord);
  } catch {
    contentList.textContent = 'Unable to load CMS content.';
  }
}

document.getElementById('addContent').addEventListener('click', () => renderContentRecord());
document.getElementById('addImage').addEventListener('click', () => renderImageRecord());
document.getElementById('addSection').addEventListener('click', () => {
  const id = `custom.${Date.now()}`;
  renderSectionRecord({
    key: id,
    page_slug: 'home',
    page: 'Homepage',
    label: 'New section',
    sort_order: 100,
    enabled: true,
    is_static: false,
    layout: 'text',
  });
});

load();
