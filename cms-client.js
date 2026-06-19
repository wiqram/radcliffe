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

  function mediaUrl(media, key) {
    const record = media[key];
    if (!record || !record.has_data) return '';
    const version = record.updated_at ? `?v=${encodeURIComponent(record.updated_at)}` : '';
    return `/api/media/${encodeURIComponent(record.key)}${version}`;
  }

  function sectionMarkup(section, media) {
    const dark = section.layout === 'dark';
    const feature = section.layout === 'feature' || section.image_key;
    const image = mediaUrl(media, section.image_key);
    const classes = ['section', 'cms-dynamic-section'];
    if (dark) classes.push('on-navy', 'cms-dynamic-dark');
    if (feature) classes.push('cms-dynamic-feature');

    return `
      <section class="${classes.join(' ')}" data-cms-rendered-section="${section.key}">
        <div class="container">
          <div class="cms-dynamic-inner">
            <div class="cms-dynamic-copy">
              ${section.eyebrow ? `<div class="label">${section.eyebrow}</div>` : ''}
              ${section.title ? `<h2>${section.title}</h2>` : ''}
              ${section.body_html ? `<div class="cms-dynamic-body">${section.body_html}</div>` : ''}
            </div>
            ${image ? `<div class="cms-dynamic-media"><img src="${image}" alt="" loading="lazy" /></div>` : ''}
          </div>
        </div>
      </section>
    `;
  }

  function applySections(sections, media) {
    const page = document.body.dataset.page;
    const pageSections = (sections || [])
      .filter((section) => section.page_slug === page)
      .sort((a, b) => Number(a.sort_order || 0) - Number(b.sort_order || 0));

    pageSections
      .filter((section) => section.is_static && !section.enabled)
      .forEach((section) => {
        document.querySelectorAll(`[data-cms-section="${section.key}"]`).forEach((el) => {
          el.remove();
        });
      });

    const dynamicSections = pageSections.filter((section) => !section.is_static && section.enabled);
    if (!dynamicSections.length) return;

    let target = document.querySelector('[data-cms-sections]');
    if (!target) {
      target = document.createElement('div');
      target.setAttribute('data-cms-sections', '');
      document.querySelector('.footer')?.before(target);
    }
    target.innerHTML = dynamicSections.map((section) => sectionMarkup(section, media)).join('');
  }

  fetch('/api/content', { headers: { Accept: 'application/json' } })
    .then((res) => {
      if (!res.ok) throw new Error('CMS unavailable');
      return res.json();
    })
    .then((data) => {
      applyContent(data.content || {});
      applyMedia(data.media || {});
      applySections(data.sections || [], data.media || {});
      document.dispatchEvent(new CustomEvent('cms:loaded', { detail: data }));
    })
    .catch(() => {
      document.documentElement.classList.add('cms-unavailable');
    });
})();
