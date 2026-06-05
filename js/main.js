document.addEventListener('DOMContentLoaded', function () {

  // ─── SHARED MODAL HELPERS ───
  function createModal() {
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    document.body.appendChild(overlay);
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(() => overlay.classList.add('active'));

    function close() {
      overlay.classList.remove('active');
      setTimeout(() => { overlay.remove(); document.body.style.overflow = ''; }, 300);
    }
    overlay.addEventListener('click', ev => { if (ev.target === overlay) close(); });
    document.addEventListener('keydown', function esc(e) {
      if (e.key === 'Escape') { close(); document.removeEventListener('keydown', esc); }
    });
    return { overlay, close };
  }

  function addCloseBtn(parent, closeFn) {
    const btn = document.createElement('button');
    btn.className = 'modal-close';
    btn.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>';
    btn.setAttribute('aria-label', 'Close');
    btn.onclick = closeFn;
    parent.appendChild(btn);
    return btn;
  }

  function addNavBtn(parent, direction, onClick) {
    const btn = document.createElement('button');
    btn.className = 'modal-nav modal-nav-' + direction;
    const d = direction === 'prev' ? 'M15 4l-8 8 8 8' : 'M9 4l8 8-8 8';
    btn.innerHTML = '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="' + d + '"/></svg>';
    btn.setAttribute('aria-label', direction === 'prev' ? 'Previous' : 'Next');
    btn.onclick = function(e) { e.stopPropagation(); onClick(); };
    parent.appendChild(btn);
    return btn;
  }

  // ─── MOBILE NAV ───
  const toggle = document.getElementById('nav-toggle');
  const nav    = document.getElementById('site-nav');
  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      nav.classList.toggle('open');
      document.body.style.overflow = nav.classList.contains('open') ? 'hidden' : '';
    });
    nav.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
      nav.classList.remove('open');
      document.body.style.overflow = '';
    }));
  }

  // ─── HERO SLIDER (slide transition + arrows) ───
  (function() {
    const slider = document.getElementById('hero-slider');
    if (!slider) return;

    const track  = slider.querySelector('.hero-track');
    const slides = Array.from(slider.querySelectorAll('.hero-slide'));
    const dots   = Array.from(slider.querySelectorAll('.hero-dot'));
    const prevBtn = slider.querySelector('.hero-arrow-prev');
    const nextBtn = slider.querySelector('.hero-arrow-next');
    if (slides.length <= 1) return;

    let current  = 0;
    let timer    = null;
    let animating = false;
    const INTERVAL = 6000;

    // Make all slides visible for slide layout
    slides.forEach(s => { s.style.display = 'flex'; });

    function goTo(index) {
      if (animating || index === current) return;
      animating = true;
      if (dots[current]) dots[current].classList.remove('active');
      slides[current].classList.remove('active');
      current = index;
      track.style.transform = 'translateX(-' + (current * 100) + '%)';
      slides[current].classList.add('active');
      if (dots[current]) dots[current].classList.add('active');
      setTimeout(() => { animating = false; }, 600);
    }

    function goNext() { goTo((current + 1) % slides.length); }
    function goPrev() { goTo((current - 1 + slides.length) % slides.length); }

    function startAutoplay() {
      stopAutoplay();
      timer = setInterval(goNext, INTERVAL);
    }
    function stopAutoplay() {
      if (timer) { clearInterval(timer); timer = null; }
    }

    // Arrow clicks
    if (prevBtn) prevBtn.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); goPrev(); startAutoplay(); });
    if (nextBtn) nextBtn.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); goNext(); startAutoplay(); });

    // Dot clicks
    dots.forEach(dot => {
      dot.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const idx = parseInt(this.dataset.index, 10);
        goTo(idx);
        startAutoplay();
      });
    });

    // Hero trailer buttons — intercept click, open video modal, prevent navigation
    slider.querySelectorAll('.hero-trailer-btn').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const url = this.dataset.trailer;
        if (!url) return;
        const m = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
        if (m) openVideoModal(m[1]);
        else window.open(url, '_blank');
      });
    });

    // Touch / swipe support
    let touchStartX = 0;
    let touchEndX = 0;
    slider.addEventListener('touchstart', e => { touchStartX = e.changedTouches[0].screenX; }, { passive: true });
    slider.addEventListener('touchend', e => {
      touchEndX = e.changedTouches[0].screenX;
      const diff = touchStartX - touchEndX;
      if (Math.abs(diff) > 50) {
        if (diff > 0) goNext(); else goPrev();
        startAutoplay();
      }
    }, { passive: true });

    // Pause on hover
    slider.addEventListener('mouseenter', stopAutoplay);
    slider.addEventListener('mouseleave', startAutoplay);

    startAutoplay();
  })();

  // ─── VIDEO MODAL (trailer buttons + thumbnail clicks) ───
  function openVideoModal(videoId) {
    const { overlay, close } = createModal();
    const wrap = document.createElement('div');
    wrap.className = 'modal-video-wrap';
    wrap.innerHTML = '<div class="modal-video-ratio">'
      + '<iframe src="https://www.youtube.com/embed/' + videoId + '?autoplay=1&rel=0" allowfullscreen allow="autoplay"></iframe>'
      + '</div>';
    overlay.appendChild(wrap);
    addCloseBtn(overlay, close);
  }

  // Trailer CTA buttons (single-film page)
  document.querySelectorAll('.btn-primary[href*="youtu"], .btn-ghost[href*="youtu"]').forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      const m = link.href.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
      if (!m) { window.open(link.href, '_blank'); return; }
      openVideoModal(m[1]);
    });
  });

  // Video thumbnails
  document.querySelectorAll('.video-thumb').forEach(thumb => {
    thumb.addEventListener('click', () => {
      const vid = thumb.dataset.vid;
      if (vid) openVideoModal(vid);
    });
  });

  // ─── VIDEO CAROUSEL ───
  document.querySelectorAll('.video-carousel').forEach(carousel => {
    const track  = carousel.querySelector('.carousel-track');
    const slides = Array.from(track.querySelectorAll('.carousel-slide'));
    const prev   = carousel.querySelector('.carousel-prev');
    const next   = carousel.querySelector('.carousel-next');
    let current  = 0;

    function getSlidesPerView() {
      const w = window.innerWidth;
      if (w <= 540) return 1;
      if (w <= 900) return 2;
      return 3;
    }

    function update() {
      const perView  = getSlidesPerView();
      const maxIndex = Math.max(0, slides.length - perView);
      if (current > maxIndex) current = maxIndex;

      if (slides.length <= perView) {
        track.style.transform = '';
        track.classList.add('centered');
        prev.classList.add('hidden');
        next.classList.add('hidden');
      } else {
        track.classList.remove('centered');
        prev.classList.remove('hidden');
        next.classList.remove('hidden');
        const gap = 16;
        const slideW = slides[0].getBoundingClientRect().width;
        track.style.transform = 'translateX(-' + (current * (slideW + gap)) + 'px)';
        prev.disabled = current === 0;
        next.disabled = current >= maxIndex;
      }
    }

    prev.addEventListener('click', () => { current--; update(); });
    next.addEventListener('click', () => { current++; update(); });
    window.addEventListener('resize', update);
    update();
  });

  // ─── PHOTO CAROUSEL ───
  document.querySelectorAll('.photo-carousel').forEach(carousel => {
    const track  = carousel.querySelector('.photo-track');
    const slides = Array.from(track.querySelectorAll('.photo-slide'));
    const prev   = carousel.querySelector('.carousel-prev');
    const next   = carousel.querySelector('.carousel-next');
    const counter = carousel.parentElement.querySelector('.photo-counter');
    let current  = 0;

    function update() {
      const maxIndex = Math.max(0, slides.length - 1);
      if (current > maxIndex) current = maxIndex;
      if (current < 0) current = 0;

      if (slides.length <= 1) {
        prev.classList.add('hidden');
        next.classList.add('hidden');
      } else {
        prev.classList.remove('hidden');
        next.classList.remove('hidden');
        prev.disabled = current === 0;
        next.disabled = current >= maxIndex;
      }

      const slideW = slides[0].getBoundingClientRect().width;
      track.style.transform = 'translateX(-' + (current * slideW) + 'px)';
      if (counter) counter.textContent = (current + 1) + ' / ' + slides.length;
    }

    prev.addEventListener('click', () => { current--; update(); });
    next.addEventListener('click', () => { current++; update(); });
    window.addEventListener('resize', update);
    update();
  });

  // ─── PHOTO LIGHTBOX (with prev/next navigation) ───
  (function() {
    const allPhotos = Array.from(document.querySelectorAll('.photo-item'));
    if (!allPhotos.length) return;

    function openLightbox(index) {
      const { overlay, close } = createModal();
      let current = index;

      const container = document.createElement('div');
      container.className = 'modal-photo-wrap';
      overlay.appendChild(container);

      const img = document.createElement('img');
      img.className = 'modal-photo-img';
      container.appendChild(img);

      const counterEl = document.createElement('div');
      counterEl.className = 'modal-counter';
      overlay.appendChild(counterEl);

      addCloseBtn(overlay, close);

      let prevBtn, nextBtn;
      if (allPhotos.length > 1) {
        prevBtn = addNavBtn(overlay, 'prev', () => { current = (current - 1 + allPhotos.length) % allPhotos.length; show(); });
        nextBtn = addNavBtn(overlay, 'next', () => { current = (current + 1) % allPhotos.length; show(); });
      }

      function show() {
        const item = allPhotos[current];
        const src = item.dataset.full || item.querySelector('img').src;
        img.src = src;
        counterEl.textContent = (current + 1) + ' / ' + allPhotos.length;
      }

      // Keyboard nav
      function keyNav(e) {
        if (e.key === 'ArrowLeft' && allPhotos.length > 1) { current = (current - 1 + allPhotos.length) % allPhotos.length; show(); }
        if (e.key === 'ArrowRight' && allPhotos.length > 1) { current = (current + 1) % allPhotos.length; show(); }
      }
      document.addEventListener('keydown', keyNav);
      overlay.addEventListener('click', function cleanup() {
        document.removeEventListener('keydown', keyNav);
      }, { once: true });

      show();
    }

    allPhotos.forEach((item, i) => {
      item.addEventListener('click', () => openLightbox(i));
    });
  })();

  // ─── SCROLL REVEAL ───
  if ('IntersectionObserver' in window) {
    const s = document.createElement('style');
    s.textContent = '.reveal{opacity:0;transform:translateY(20px);transition:opacity 0.6s ease,transform 0.6s ease}.reveal.visible{opacity:1;transform:none}';
    document.head.appendChild(s);
    const obs = new IntersectionObserver(entries => {
      entries.forEach(e => { if(e.isIntersecting){e.target.classList.add('visible');obs.unobserve(e.target);} });
    }, {threshold:0.1});
    document.querySelectorAll('.film-card,.coming-card,.partner-cat,.cap-item').forEach(el => {
      el.classList.add('reveal'); obs.observe(el);
    });
  }
});
