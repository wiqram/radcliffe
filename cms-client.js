(function () {
  function applyContent(content) {
    document.querySelectorAll('[data-cms-key]').forEach((el) => {
      const record = content[el.dataset.cmsKey];
      if (!record || record.value == null) return;
      if (el.dataset.cmsMode === 'html' || record.type === 'html') {
        el.innerHTML = record.value;
      } else {
        el.textContent = record.value;
      }
    });
  }

  function applyMedia(media) {
    document.querySelectorAll('img[data-cms-image]').forEach((img) => {
      const record = media[img.dataset.cmsImage];
      if (!record || !record.has_data) return;
      const version = record.updated_at ? `?v=${encodeURIComponent(record.updated_at)}` : '';
      img.src = `/api/media/${encodeURIComponent(record.key)}${version}`;
      if (record.alt_text) img.alt = record.alt_text;
    });
  }

  fetch('/api/content', { headers: { Accept: 'application/json' } })
    .then((res) => {
      if (!res.ok) throw new Error('CMS unavailable');
      return res.json();
    })
    .then((data) => {
      applyContent(data.content || {});
      applyMedia(data.media || {});
      document.dispatchEvent(new CustomEvent('cms:loaded', { detail: data }));
    })
    .catch(() => {
      document.documentElement.classList.add('cms-unavailable');
    });
})();
