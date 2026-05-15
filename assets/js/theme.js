(() => {
  const cfg = window.VOIDAIRO || { options: {}, i18n: {} };
  const root = document.documentElement;
  let searchFormsReady = false;

  function initThemeMode() {
    const mode = cfg.options.darkMode || (cfg.options.autoDark === false ? 'light' : 'system');
    const saved = mode === 'system' ? localStorage.getItem('voidairo-theme') : null;
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (mode === 'dark' || saved === 'dark' || (mode === 'system' && !saved && prefersDark)) root.dataset.theme = 'dark';
    else root.dataset.theme = 'light';
    document.querySelectorAll('.theme-toggle').forEach((toggle) => {
      toggle.onclick = () => {
        root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
        if (mode === 'system') localStorage.setItem('voidairo-theme', root.dataset.theme);
      };
    });
  }

  function initNav() {
    const navToggle = document.querySelector('.nav-toggle');
    const nav = document.getElementById('site-navigation');
    if (navToggle && nav) navToggle.onclick = () => {
      const open = nav.classList.toggle('is-open');
      document.body.classList.toggle('nav-open', open);
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
    list.textContent = '';
    headings.forEach((h, i) => {
      if (!h.id) h.id = `toc-${i}-${h.textContent.trim().toLowerCase().replace(/[^a-z0-9\u4e00-\u9fa5]+/gi, '-').replace(/^-|-$/g, '')}`;
      const link = document.createElement('a');
      link.className = `toc-link toc-${h.tagName.toLowerCase()}`;
      link.setAttribute('href', `#${h.id}`);
      link.textContent = h.textContent.trim();
      list.appendChild(link);
    });
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
      const heart = btn.querySelector('span');
      if (localStorage.getItem(`voidairo-liked-${postId}`)) {
        btn.classList.add('is-liked');
        if (heart) heart.textContent = '♥';
      }
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
          if (heart) heart.textContent = '♥';
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
    if (url.pathname === location.pathname && url.search === location.search && url.hash) return false;
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
      const currentNav = document.querySelector('#site-navigation');
      const nextNav = doc.querySelector('#site-navigation');
      if (currentNav && nextNav) currentNav.replaceWith(nextNav);
      currentMain.replaceWith(nextMain);
      document.title = doc.title;
      document.body.className = doc.body.className;
      document.body.classList.remove('nav-open');
      const navToggle = document.querySelector('.nav-toggle');
      if (navToggle) navToggle.setAttribute('aria-expanded', 'false');
      if (push) history.pushState({ pjax: true }, doc.title, url);
      const targetUrl = new URL(url, location.href);
      if (targetUrl.hash) {
        const rawId = decodeURIComponent(targetUrl.hash.slice(1));
        const escapedId = window.CSS && CSS.escape ? CSS.escape(rawId) : '';
        const target = document.getElementById(rawId) || (escapedId ? document.querySelector(`[name="${escapedId}"]`) : null);
        if (target) target.scrollIntoView({ block: 'start' });
        else window.scrollTo({ top: 0, behavior: 'instant' in window ? 'instant' : 'auto' });
      } else {
        window.scrollTo({ top: 0, behavior: 'instant' in window ? 'instant' : 'auto' });
      }
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

  function escapeHtml(text) {
    return text.replace(/[&<>]/g, (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[ch]));
  }

  function tokenizeCode(text) {
    const keywords = new Set('abstract async await break case catch class const continue default def do echo else export extends false final finally for foreach from function if implements import interface let namespace new null private protected public return self static switch this throw true try use var while yield'.split(' '));
    const out = [];
    let i = 0;
    const push = (type, value) => out.push(type ? `<span class="${type}">${escapeHtml(value)}</span>` : escapeHtml(value));
    while (i < text.length) {
      const rest = text.slice(i);
      const ch = text[i];
      if (rest.startsWith('//') || rest.startsWith('#')) {
        const j = text.indexOf('\n', i);
        const end = j === -1 ? text.length : j;
        push('tok-comment', text.slice(i, end)); i = end; continue;
      }
      if (rest.startsWith('/*')) {
        const j = text.indexOf('*/', i + 2);
        const end = j === -1 ? text.length : j + 2;
        push('tok-comment', text.slice(i, end)); i = end; continue;
      }
      if (ch === '"' || ch === "'" || ch === '`') {
        const quote = ch; let j = i + 1; let esc = false;
        for (; j < text.length; j++) {
          const c = text[j];
          if (esc) { esc = false; continue; }
          if (c === '\\') { esc = true; continue; }
          if (c === quote) { j++; break; }
        }
        push('tok-string', text.slice(i, j)); i = j; continue;
      }
      const num = rest.match(/^\b\d+(?:\.\d+)?\b/);
      if (num) { push('tok-number', num[0]); i += num[0].length; continue; }
      const word = rest.match(/^[A-Za-z_$][\w$]*/);
      if (word) {
        const value = word[0];
        const after = text.slice(i + value.length).match(/^\s*\(/);
        push(keywords.has(value) ? 'tok-keyword' : (after ? 'tok-function' : ''), value);
        i += value.length; continue;
      }
      push('', ch); i++;
    }
    return out.join('');
  }

  function initCodeHighlight(scope = document) {
    scope.querySelectorAll('.entry-content pre code:not([data-va-highlighted])').forEach((code) => {
      code.dataset.vaHighlighted = '1';
      const original = code.textContent;
      const pre = code.closest('pre');
      const className = code.className || (pre && pre.className) || '';
      const match = className.match(/language-([a-z0-9+#-]+)/i);
      if (pre && match) pre.setAttribute('data-lang', match[1].toUpperCase());
      code.innerHTML = tokenizeCode(original);
      if (code.textContent !== original) {
        console.warn('VOIDairo code highlighter preserved text fallback triggered.');
        code.textContent = original;
      }
    });
  }

  function initSearchForms() {
    if (cfg.options.pjax === false || searchFormsReady) return;
    searchFormsReady = true;
    document.addEventListener('submit', (event) => {
      const form = event.target.closest('form.search-form');
      if (!form) return;
      const data = new FormData(form);
      const query = String(data.get('s') || '').trim();
      if (!query) return;
      event.preventDefault();
      const url = new URL(form.getAttribute('action') || '/', location.href);
      url.searchParams.set('s', query);
      pjaxVisit(url.toString());
    });
  }

  function initAll(scope = document) {
    initThemeMode();
    initNav();
    initCards(scope);
    initToc(scope);
    initLikes(scope);
    initAjaxComments(scope);
    initCodeHighlight(scope);
    initBackToTop();
    initSearchForms();
  }

  document.addEventListener('DOMContentLoaded', () => {
    initAll(document);
    initPjax();
  });
})();
