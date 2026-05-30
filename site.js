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

  // 3) Reveal on scroll (JS-gated — content is visible by default in CSS)
  document.documentElement.classList.add('js-reveal');
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

  // 4) Active-page nav highlight
  const page = document.body.dataset.page;
  const map = {
    practice: 'Practice.html',
    who: 'Whos-Who.html',
    city: 'The-City.html',
    summit: 'Summit.html',
    articles: 'Articles.html',
    ethics: 'Ethics.html',
  };
  if (map[page]) {
    document.querySelectorAll(`.nav a[href="${map[page]}"]`).forEach((a) =>
      a.classList.add('is-active')
    );
  }

  // 5) Testimonial rotator (Articles page) — only if present
  const tq = document.getElementById('t-quote');
  if (tq) {
    const data = window.__TESTIMONIALS || [];
    const aEl = document.getElementById('t-attrib');
    const cEl = document.getElementById('t-count');
    let i = 0;
    function render() {
      const t = data[i];
      tq.style.opacity = 0;
      aEl.style.opacity = 0;
      setTimeout(() => {
        tq.innerHTML = '<span class="open-q">“</span>' + t.q + '<span class="close-q">”</span>';
        aEl.innerHTML = '<span class="name">' + t.name + '</span> · ' + t.org;
        if (cEl) cEl.textContent = String(i + 1).padStart(2, '0') + ' / ' + String(data.length).padStart(2, '0');
        tq.style.opacity = 1;
        aEl.style.opacity = 1;
      }, 220);
    }
    tq.style.transition = 'opacity 320ms ease';
    aEl.style.transition = 'opacity 320ms ease';
    const prev = document.getElementById('t-prev');
    const next = document.getElementById('t-next');
    if (prev) prev.addEventListener('click', () => { i = (i - 1 + data.length) % data.length; render(); });
    if (next) next.addEventListener('click', () => { i = (i + 1) % data.length; render(); });
    if (data.length) render();
  }
})();
