// Redcliffe Advisory — shared site script (v2, blue & white)
(function () {
  // 1) Header — condense on scroll + retract announcement bar
  const header = document.getElementById('header');
  function onScroll() {
    const scrolled = window.scrollY > 24;
    if (header) header.classList.toggle('scrolled', scrolled);
    document.body.classList.toggle('is-scrolled', scrolled);
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // 2) Mobile nav toggle
  const btn = document.getElementById('navToggle');
  const nav = document.getElementById('nav');
  if (btn && nav) {
    btn.addEventListener('click', () => {
      const open = nav.classList.toggle('open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    nav.querySelectorAll('a').forEach((a) =>
      a.addEventListener('click', () => {
        nav.classList.remove('open');
        btn.setAttribute('aria-expanded', 'false');
      })
    );
  }

  // 2a) A menu with more links than fit beside the logo. The owner can add as many as they like, so when the
  //     links no longer fit on one line the header switches to the menu button that phones use, at any width.
  const headerInner = header && header.querySelector('.header-inner');
  const root = document.documentElement;
  function fitNav() {
    if (!headerInner || !nav || !btn) return;
    root.classList.remove('nav-collapsed');
    if (window.matchMedia('(max-width: 980px)').matches) return; // the phone layout already
    const links = Array.from(nav.children);
    if (!links.length) return;
    const style = (el) => getComputedStyle(el);
    const brand = headerInner.querySelector('.brand');
    const need = links.reduce((sum, el) => sum + el.getBoundingClientRect().width, 0) + (parseFloat(style(nav).columnGap) || 0) * (links.length - 1);
    const room = headerInner.clientWidth - (brand ? brand.getBoundingClientRect().width : 0) - (parseFloat(style(headerInner).columnGap) || 0);
    if (need + 32 > room) root.classList.add('nav-collapsed'); // 32px in hand: the logo is a little smaller once scrolled
  }
  let fitQueued = false;
  function queueFitNav() {
    if (fitQueued) return;
    fitQueued = true;
    requestAnimationFrame(() => { fitQueued = false; fitNav(); });
  }
  if (btn && nav) {
    fitNav();
    window.addEventListener('resize', queueFitNav);
    window.addEventListener('load', queueFitNav);
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(queueFitNav);
    btn.addEventListener('click', () => {
      // The open menu hangs from the bottom edge of the header, wherever that is, and scrolls if the screen is short.
      const box = header.getBoundingClientRect();
      nav.style.setProperty('--nav-top', Math.round(box.height) + 'px'); // fixed inside the header, so measured from its top
      nav.style.setProperty('--nav-room', Math.max(160, Math.round(window.innerHeight - box.bottom)) + 'px');
    }, true);
  }

  // 2b) A sponsor logo the browser cannot show (a format it does not read, a file removed from the server) is replaced by
  //     the sponsor's name, instead of a broken-picture icon.
  document.querySelectorAll('.sponsor-logo img').forEach((img) => {
    const swap = () => {
      const tile = img.closest('.sponsor');
      if (!tile || tile.classList.contains('is-image-missing')) return;
      tile.classList.add('is-image-missing', 'is-name-only');
      if (!tile.querySelector('.sponsor-name')) {
        const name = document.createElement('span');
        name.className = 'sponsor-name';
        name.textContent = img.dataset.name || img.alt || '';
        tile.querySelector('.sponsor-card').appendChild(name);
      }
    };
    img.addEventListener('error', swap);
    if (img.complete && img.naturalWidth === 0 && img.currentSrc) swap();
  });

  // 3) Reveal on scroll (JS-gated — content is visible by default in CSS)
  document.documentElement.classList.add('js-reveal');

  // 3a) Auto-stagger: let the children of common grids/lists cascade in
  //     sequence rather than popping as one block.
  const STAGGER_SEL = [
    '.entries', '.enter-grid', '.journal-list', '.practice-list', '.partner-grid',
    '.partner-roles', '.cv-list', '.other-list', '.agenda-block',
    '.audience-list', '.meta-row', '.hero-facts'
  ].join(', ');
  document.querySelectorAll(STAGGER_SEL).forEach((group) => {
    const kids = Array.from(group.children);
    if (kids.length < 2) return;
    group.classList.add('reveal', 'reveal-stagger');
    kids.forEach((c, i) => {
      // The parent now drives the cascade — drop any per-child reveal so the
      // two hidden-states don't fight each other.
      c.classList.remove('reveal');
      c.style.setProperty('--si', Math.min(i, 8));
    });
  });

  const io = new IntersectionObserver(
    (entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) {
          e.target.classList.add('in');
          io.unobserve(e.target);
        }
      });
    },
    { threshold: 0.12, rootMargin: '0px 0px -8% 0px' }
  );
  document.querySelectorAll('.reveal').forEach((el) => io.observe(el));
  // Safety: if anything stalls, reveal everything after 2.5s
  setTimeout(() => {
    document.querySelectorAll('.reveal:not(.in)').forEach((el) => el.classList.add('in'));
  }, 2500);

  // 4) Active-page nav highlight is applied server-side by the theme.

  // 5) Testimonial rotator — only if present.
  // Every quote is already on the page, one per slot, so each can be edited on
  // its own; this only changes which one is showing. It never writes the words
  // itself, or an edited quote would be replaced the moment the page loaded.
  const tSet = document.getElementById('t-set');
  if (tSet) {
    const items = Array.from(tSet.querySelectorAll('.test-item'));
    const cEl = document.getElementById('t-count');
    const controls = document.querySelector('.test-controls');
    const pad = (n) => String(n).padStart(2, '0');
    let i = 0;
    function show() {
      items.forEach((item, n) => { item.hidden = n !== i; });
      if (cEl) cEl.textContent = pad(i + 1) + ' / ' + pad(items.length);
      tSet.style.opacity = 1;
    }
    function render() {
      tSet.style.opacity = 0;
      setTimeout(show, 220);
    }
    tSet.style.transition = 'opacity 320ms ease';
    const prev = document.getElementById('t-prev');
    const next = document.getElementById('t-next');
    if (prev) prev.addEventListener('click', () => { i = (i - 1 + items.length) % items.length; render(); });
    if (next) next.addEventListener('click', () => { i = (i + 1) % items.length; render(); });
    if (items.length > 1) show();
    else if (controls) controls.hidden = true; // one quote needs no arrows
  }

  // 6) LinkedIn posts (SociableKit) — the widget loads once it is close to
  // being seen, so visitors who never scroll that far do not download it. The
  // feed lists every recent post, so the page shows the first rows and a
  // button opens the rest (the button only appears when there is more).
  const linkedin = document.querySelector('.linkedin-feed[data-linkedin-src]');
  if (linkedin) {
    const toggle = document.querySelector('.linkedin-toggle');
    const loadLinkedIn = () => {
      if (linkedin.dataset.loaded) return;
      linkedin.dataset.loaded = '1';
      const script = document.createElement('script');
      script.src = linkedin.dataset.linkedinSrc;
      script.async = true;
      linkedin.appendChild(script);
    };
    if (toggle) {
      const sync = () => {
        toggle.hidden = !(linkedin.classList.contains('is-collapsed') && linkedin.scrollHeight > linkedin.clientHeight + 24);
      };
      toggle.addEventListener('click', () => {
        linkedin.classList.remove('is-collapsed');
        toggle.setAttribute('aria-expanded', 'true');
        toggle.hidden = true;
      });
      if ('ResizeObserver' in window) new ResizeObserver(sync).observe(linkedin.firstElementChild);
      else toggle.hidden = false;
      window.addEventListener('resize', sync);
    }
    if ('IntersectionObserver' in window) {
      const watcher = new IntersectionObserver((entries) => {
        if (entries.some((entry) => entry.isIntersecting)) { watcher.disconnect(); loadLinkedIn(); }
      }, { rootMargin: '400px 0px' });
      watcher.observe(linkedin);
    } else {
      loadLinkedIn();
    }
  }
})();
