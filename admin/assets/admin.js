const contentList = document.getElementById('contentList');
const imageList = document.getElementById('imageList');
const contentTemplate = document.getElementById('contentTemplate');
const imageTemplate = document.getElementById('imageTemplate');

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
    } catch (error) {
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
    } catch (error) {
      setStatus(status, 'Save failed.', true);
    }
  });

  imageList.appendChild(node);
}

async function load() {
  try {
    const data = await request('/api/admin/content');
    contentList.replaceChildren();
    imageList.replaceChildren();
    data.content.forEach(renderContentRecord);
    data.media.forEach(renderImageRecord);
  } catch (error) {
    contentList.textContent = 'Unable to load CMS content.';
  }
}

document.getElementById('addContent').addEventListener('click', () => renderContentRecord());
document.getElementById('addImage').addEventListener('click', () => renderImageRecord());

load();
