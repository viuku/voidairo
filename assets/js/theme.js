(() => {
  const cfg = window.VOIDAIRO || { options: {}, i18n: {} };
  const root = document.documentElement;

  function initThemeMode() {
    const saved = localStorage.getItem('voidairo-theme');
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (saved === 'dark' || (!saved && cfg.options.autoDark !== false && prefersDark)) root.dataset.theme = 'dark';
    if (saved === 'light') root.dataset.theme = 'light';
    document.querySelectorAll('.theme-toggle').forEach((toggle) => {
      toggle.onclick = () => {
        root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
        localStorage.setItem('voidairo-theme', root.dataset.theme);
      };
    });
  }

  function initNav() {
    const navToggle = document.querySelector('.nav-toggle');
    const nav = document.getElementById('site-navigation');
    if (navToggle && nav) navToggle.onclick = () => {
      const open = nav.classList.toggle('is-open');
      navToggle.setAttribute('aria-expanded', String(open));
    };
  }

  function sampleAccent(card, img) {
    try {
      const canvas = document.createElement('canvas');
      const ctx = canvas.getContext('2d', { willReadFrequently: true });
      if (!ctx) return;
      canvas.width = canvas.height = 1;
      ctx.drawImage(img, 0, 0, 1, 1);
      const [r, g, b] = ctx.getImageData(0, 0, 1, 1).data;
      card.style.setProperty('--card-accent', `rgb(${Math.min(235, Math.max(60, r))} ${Math.min(235, Math.max(60, g))} ${Math.min(235, Math.max(60, b))})`);
    } catch (e) {}
  }

  function initCards(scope = document) {
    const cards = scope.querySelectorAll('.post-card:not([data-va-ready])');
    if ('IntersectionObserver' in window) {
      const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        });
      }, { threshold: 0.18, rootMargin: '60px 0px' });
      cards.forEach((card) => io.observe(card));
    } else {
      cards.forEach((card) => card.classList.add('is-visible'));
    }
    cards.forEach((card) => {
      card.dataset.vaReady = '1';
      const img = card.querySelector('.post-card__image');
      if (img && img.complete) sampleAccent(card, img);
      if (img) img.addEventListener('load', () => sampleAccent(card, img), { once: true });
      card.addEventListener('pointermove', (event) => {
        const r = card.getBoundingClientRect();
        card.style.setProperty('--mx', `${((event.clientX - r.left) / r.width) * 100}%`);
        card.style.setProperty('--my', `${((event.clientY - r.top) / r.height) * 100}%`);
      }, { passive: true });
    });
  }

  function initToc(scope = document) {
    if (cfg.options.toc === false) return;
    const panel = scope.querySelector('.toc-panel');
    const list = scope.querySelector('.toc-list');
    const content = scope.querySelector('.entry-content');
    if (!panel || !list || !content) return;
    const headings = [...content.querySelectorAll('h2,h3')].filter((h) => h.textContent.trim());
    if (headings.length < 2) { panel.remove(); return; }
    list.innerHTML = headings.map((h, i) => {
      if (!h.id) h.id = `toc-${i}-${h.textContent.trim().toLowerCase().replace(/[^a-z0-9\u4e00-\u9fa5]+/gi, '-').replace(/^-|-$/g, '')}`;
      return `<a class="toc-link toc-${h.tagName.toLowerCase()}" href="#${h.id}">${h.textContent.trim()}</a>`;
    }).join('');
    if ('IntersectionObserver' in window) {
      const links = new Map([...list.querySelectorAll('a')].map((a) => [a.getAttribute('href').slice(1), a]));
      const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          links.forEach((a) => a.classList.remove('is-active'));
          const active = links.get(entry.target.id);
          if (active) active.classList.add('is-active');
        });
      }, { rootMargin: '-20% 0px -70% 0px' });
      headings.forEach((h) => io.observe(h));
    }
  }

  function initLikes(scope = document) {
    if (cfg.options.likes === false) return;
    scope.querySelectorAll('.voidairo-like:not([data-va-ready])').forEach((btn) => {
      btn.dataset.vaReady = '1';
      const postId = btn.dataset.postId;
      if (localStorage.getItem(`voidairo-liked-${postId}`)) btn.classList.add('is-liked');
      btn.addEventListener('click', async () => {
        if (btn.disabled || localStorage.getItem(`voidairo-liked-${postId}`)) return;
        btn.disabled = true;
        const body = new URLSearchParams({ action: 'voidairo_like', nonce: cfg.likeNonce || '', post_id: postId });
        try {
          const res = await fetch(cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body });
          const json = await res.json();
          if (!json.success) throw new Error(json.data && json.data.message || 'like failed');
          btn.querySelector('strong').textContent = json.data.likes;
          btn.classList.add('is-liked');
          localStorage.setItem(`voidairo-liked-${postId}`, '1');
        } catch (e) { console.warn(e); }
        btn.disabled = false;
      });
    });
  }

  function initAjaxComments(scope = document) {
    if (cfg.options.ajaxComments === false) return;
    const form = scope.querySelector('#commentform');
    if (!form || form.dataset.vaReady) return;
    form.dataset.vaReady = '1';
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const submit = form.querySelector('[type="submit"]');
      const original = submit ? submit.value : '';
      if (submit) { submit.disabled = true; submit.value = cfg.i18n.loading || 'Loading…'; }
      const data = new FormData(form);
      data.append('action', 'voidairo_comment');
      try {
        const res = await fetch(cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data });
        const json = await res.json();
        if (!json.success) throw new Error(json.data && json.data.message || cfg.i18n.commentError);
        let list = document.querySelector('.comment-list');
        if (!list) {
          const area = document.querySelector('.comments-area');
          list = document.createElement('ol');
          list.className = 'comment-list';
          area.insertBefore(list, form.parentNode);
        }
        list.insertAdjacentHTML('beforeend', json.data.html);
        form.reset();
        const note = document.createElement('p');
        note.className = 'comment-ajax-note';
        note.textContent = json.data.approved === '0' ? (cfg.i18n.commentPending || 'Pending moderation.') : 'Comment posted.';
        form.prepend(note);
        setTimeout(() => note.remove(), 5000);
      } catch (e) {
        alert(e.message || cfg.i18n.commentError || 'Comment failed.');
      }
      if (submit) { submit.disabled = false; submit.value = original; }
    });
  }

  function initBackToTop() {
    const top = document.querySelector('.back-to-top');
    if (!top) return;
    const updateTop = () => top.classList.toggle('is-visible', window.scrollY > 600);
    window.removeEventListener('scroll', updateTop);
    window.addEventListener('scroll', updateTop, { passive: true });
    updateTop();
    top.onclick = () => window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function shouldPjax(anchor) {
    if (cfg.options.pjax === false || !anchor || anchor.target || anchor.hasAttribute('download')) return false;
    if (anchor.closest('#wpadminbar, .comment-reply-link')) return false;
    const url = new URL(anchor.href, location.href);
    if (url.origin !== location.origin) return false;
    if (url.pathname.includes('/wp-admin') || url.pathname.includes('/wp-login.php')) return false;
    if (/\.(zip|rar|7z|pdf|jpg|jpeg|png|gif|webp|mp4|mp3)$/i.test(url.pathname)) return false;
    return true;
  }

  async function pjaxVisit(url, push = true) {
    document.body.classList.add('is-pjax-loading');
    try {
      const res = await fetch(url, { headers: { 'X-Requested-With': 'VOIDairo-PJAX' }, credentials: 'same-origin' });
      const html = await res.text();
      const doc = new DOMParser().parseFromString(html, 'text/html');
      const nextMain = doc.querySelector('#primary');
      const currentMain = document.querySelector('#primary');
      if (!nextMain || !currentMain) { location.href = url; return; }
      const currentHero = document.querySelector('.hero');
      const nextHero = doc.querySelector('.hero');
      if (currentHero && nextHero) currentHero.replaceWith(nextHero);
      else if (currentHero && !nextHero) currentHero.remove();
      else if (!currentHero && nextHero) document.querySelector('.site-header').after(nextHero);
      currentMain.replaceWith(nextMain);
      document.title = doc.title;
      document.body.className = doc.body.className;
      if (push) history.pushState({ pjax: true }, doc.title, url);
      window.scrollTo({ top: 0, behavior: 'instant' in window ? 'instant' : 'auto' });
      initAll(document);
      document.dispatchEvent(new CustomEvent('voidairo:pjax-complete'));
    } catch (e) {
      console.warn(e);
      location.href = url;
    } finally {
      document.body.classList.remove('is-pjax-loading');
    }
  }

  function initPjax() {
    if (cfg.options.pjax === false || document.body.dataset.pjaxReady) return;
    document.body.dataset.pjaxReady = '1';
    document.addEventListener('click', (event) => {
      const anchor = event.target.closest('a');
      if (!shouldPjax(anchor)) return;
      event.preventDefault();
      pjaxVisit(anchor.href);
    });
    window.addEventListener('popstate', () => pjaxVisit(location.href, false));
  }

  function initAll(scope = document) {
    initThemeMode();
    initNav();
    initCards(scope);
    initToc(scope);
    initLikes(scope);
    initAjaxComments(scope);
    initBackToTop();
  }

  document.addEventListener('DOMContentLoaded', () => {
    initAll(document);
    initPjax();
  });
})();
