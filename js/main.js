/* =============================================================
   CHAPTER 1 – Reading Room & Study Space
   main.js — Premium Interactions & Animations
   ============================================================= */

'use strict';

/* ── 1. NAVIGATION ─────────────────────────────────────────── */
(function initNav() {
  const nav         = document.querySelector('.nav');
  const hamburger   = document.querySelector('.nav__hamburger');
  const mobileNav   = document.querySelector('.nav-mobile');
  const mobileLinks = document.querySelectorAll('.nav-mobile__links a');

  if (!nav) return;

  // Scroll-based state
  function updateNavState() {
    if (window.scrollY > 48) {
      nav.classList.add('is-scrolled');
    } else {
      nav.classList.remove('is-scrolled');
    }
  }

  window.addEventListener('scroll', updateNavState, { passive: true });
  updateNavState();

  // Mobile menu toggle
  function toggleMobileMenu(open) {
    nav.classList.toggle('is-open', open);
    if (mobileNav) mobileNav.classList.toggle('is-open', open);
    document.body.style.overflow = open ? 'hidden' : '';
  }

  if (hamburger) {
    hamburger.addEventListener('click', () => {
      const isOpen = nav.classList.contains('is-open');
      toggleMobileMenu(!isOpen);
    });
  }

  // Close mobile menu on link click
  mobileLinks.forEach(link => {
    link.addEventListener('click', () => toggleMobileMenu(false));
  });

  // Close on Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && nav.classList.contains('is-open')) {
      toggleMobileMenu(false);
    }
  });
})();

/* ── 2. SCROLL REVEAL (IntersectionObserver) ───────────────── */
(function initReveal() {
  // Skip if browser supports scroll-driven animations natively
  // (CSS handles it with progressive enhancement)
  // But still use IO as primary/fallback for all browsers
  const selectors = ['.reveal', '.reveal-left', '.reveal-right', '.reveal-scale'];
  const elements  = document.querySelectorAll(selectors.join(','));

  if (!elements.length) return;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('revealed');
          observer.unobserve(entry.target);
        }
      });
    },
    {
      threshold: 0.12,
      rootMargin: '0px 0px -60px 0px',
    }
  );

  elements.forEach(el => observer.observe(el));
})();

/* ── 3. SMOOTH SCROLL for CTA / NAV LINKS ─────────────────── */
(function initSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const href = this.getAttribute('href');
      if (href === '#') return;
      const target = document.querySelector(href);
      if (!target) return;
      e.preventDefault();
      const navHeight = document.querySelector('.nav')?.offsetHeight ?? 80;
      const top = target.getBoundingClientRect().top + window.scrollY - navHeight;
      window.scrollTo({ top, behavior: 'smooth' });
    });
  });
})();

/* ── 4. HERO PARALLAX (subtle, performant) ─────────────────── */
(function initHeroParallax() {
  const heroBg = document.querySelector('.hero__bg img');
  if (!heroBg) return;

  // Only run if user hasn't requested reduced motion
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  let ticking = false;

  function updateParallax() {
    const scrollY = window.scrollY;
    const heroHeight = document.querySelector('.hero')?.offsetHeight ?? 0;

    if (scrollY <= heroHeight) {
      // Subtle 20% parallax
      heroBg.style.transform = `translateY(${scrollY * 0.18}px)`;
    }
    ticking = false;
  }

  window.addEventListener('scroll', () => {
    if (!ticking) {
      requestAnimationFrame(updateParallax);
      ticking = true;
    }
  }, { passive: true });
})();

/* ── 5. COUNTER ANIMATION ──────────────────────────────────── */
(function initCounters() {
  const counters = document.querySelectorAll('[data-count]');
  if (!counters.length) return;

  function animateCounter(el) {
    const target   = parseInt(el.getAttribute('data-count'), 10);
    const duration = 1600;
    const start    = performance.now();

    function step(now) {
      const elapsed  = now - start;
      const progress = Math.min(elapsed / duration, 1);
      // Ease out expo
      const eased = 1 - Math.pow(2, -10 * progress);
      el.textContent = Math.round(eased * target).toLocaleString('en-IN');
      if (progress < 1) requestAnimationFrame(step);
    }

    requestAnimationFrame(step);
  }

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.5 }
  );

  counters.forEach(el => observer.observe(el));
})();

/* ── 6. STAGGERED FEATURE GRID REVEAL ─────────────────────── */
(function initFeatureStagger() {
  const items = document.querySelectorAll('.feature-item');
  if (!items.length) return;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const index = parseInt(entry.target.getAttribute('data-index') ?? '0', 10);
          setTimeout(() => {
            entry.target.classList.add('revealed');
          }, index * 60);
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.08, rootMargin: '0px 0px -40px 0px' }
  );

  items.forEach((item, i) => {
    item.setAttribute('data-index', String(i));
    item.classList.add('reveal');
    observer.observe(item);
  });
})();

/* ── 7. GALLERY IMAGE REVEAL ───────────────────────────────── */
(function initGalleryReveal() {
  const galleryItems = document.querySelectorAll('.gallery__item');
  if (!galleryItems.length) return;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry, i) => {
        if (entry.isIntersecting) {
          setTimeout(() => {
            entry.target.classList.add('gallery-visible');
          }, i * 80);
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.1 }
  );

  galleryItems.forEach(item => {
    item.style.opacity = '0';
    item.style.transform = 'scale(0.97)';
    item.style.transition = 'opacity 0.75s var(--ease-out), transform 0.75s var(--ease-out)';
    observer.observe(item);
  });

  // Inject visible class style
  const style = document.createElement('style');
  style.textContent = '.gallery-visible { opacity: 1 !important; transform: scale(1) !important; }';
  document.head.appendChild(style);
})();

/* ── 8. ACTIVE NAV LINK ON SCROLL ─────────────────────────── */
(function initActiveNavLink() {
  const sections  = document.querySelectorAll('section[id]');
  const navLinks  = document.querySelectorAll('.nav__links a[href^="#"]');

  if (!sections.length || !navLinks.length) return;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const id = entry.target.getAttribute('id');
          navLinks.forEach(link => {
            link.style.color = '';
            if (link.getAttribute('href') === `#${id}`) {
              link.style.color = 'var(--gold)';
            }
          });
        }
      });
    },
    { threshold: 0.4 }
  );

  sections.forEach(section => observer.observe(section));
})();

/* ── 9. MEMBERSHIP CARD ENTRANCE ───────────────────────────── */
(function initMembershipCard() {
  const card = document.querySelector('.membership__card');
  if (!card) return;

  card.style.opacity = '0';
  card.style.transform = 'translateY(40px)';
  card.style.transition = 'opacity 1s var(--ease-out), transform 1s var(--ease-out)';

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.2 }
  );

  observer.observe(card);
})();

/* ── 10. LIGHTBOX ──────────────────────────────────────────── */
(function initLightbox() {
  const lightbox   = document.getElementById('lightbox');
  if (!lightbox) return;

  const imgEl      = lightbox.querySelector('.lightbox__img');
  const loader     = lightbox.querySelector('.lightbox__loader');
  const caption    = lightbox.querySelector('.lightbox__caption');
  const counter    = lightbox.querySelector('.lightbox__counter');
  const closeBtn   = lightbox.querySelector('.lightbox__close');
  const prevBtn    = lightbox.querySelector('.lightbox__nav--prev');
  const nextBtn    = lightbox.querySelector('.lightbox__nav--next');
  const backdrop   = lightbox.querySelector('.lightbox__backdrop');

  let items   = [];
  let current = 0;

  // Collect only VISIBLE [data-lightbox] triggers (respects tab/view filtering)
  function collectItems() {
    items = Array.from(document.querySelectorAll('[data-lightbox]')).filter(el => {
      // Skip if the element itself or any ancestor is hidden
      if (el.offsetParent === null) return false;
      const style = window.getComputedStyle(el);
      if (style.display === 'none' || style.visibility === 'hidden') return false;
      return true;
    });
  }

  function open(index) {
    collectItems();
    if (!items.length) return;
    current = Math.max(0, Math.min(index, items.length - 1));
    loadImage(current);
    lightbox.classList.add('is-open');
    lightbox.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    closeBtn.focus();
  }

  function close() {
    lightbox.classList.remove('is-open');
    lightbox.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    imgEl.classList.remove('is-loaded');
    // Return focus to the triggering element
    if (items[current]) items[current].focus();
  }

  function loadImage(index) {
    const el  = items[index];
    const src = el.getAttribute('data-src');
    const cap = el.getAttribute('data-caption') || '';

    // Reset
    imgEl.classList.remove('is-loaded');
    loader.classList.remove('is-hidden');
    caption.textContent = cap;
    counter.textContent = `${index + 1} / ${items.length}`;

    // Prev/next visibility
    prevBtn.style.opacity = index === 0 ? '0.3' : '1';
    nextBtn.style.opacity = index === items.length - 1 ? '0.3' : '1';

    const newImg = new Image();
    newImg.onload = () => {
      imgEl.src = src;
      imgEl.alt = el.querySelector('img')?.alt || cap;
      loader.classList.add('is-hidden');
      imgEl.classList.add('is-loaded');
    };
    newImg.onerror = () => {
      loader.classList.add('is-hidden');
      imgEl.src = src; // still set so fallback onerror runs
      imgEl.classList.add('is-loaded');
    };
    newImg.src = src;
  }

  function prev() {
    if (current > 0) { current--; loadImage(current); }
  }

  function next() {
    if (current < items.length - 1) { current++; loadImage(current); }
  }

  // Click triggers
  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-lightbox]');
    if (!trigger) return;
    collectItems();
    const index = items.indexOf(trigger);
    open(index >= 0 ? index : 0);
  });

  // Keyboard triggers (Enter / Space on focused items)
  document.addEventListener('keydown', (e) => {
    if ((e.key === 'Enter' || e.key === ' ') && e.target.hasAttribute('data-lightbox')) {
      e.preventDefault();
      collectItems();
      const index = items.indexOf(e.target);
      open(index >= 0 ? index : 0);
      return;
    }

    if (!lightbox.classList.contains('is-open')) return;

    switch (e.key) {
      case 'Escape':    close(); break;
      case 'ArrowLeft': prev();  break;
      case 'ArrowRight':next();  break;
    }
  });

  // Nav buttons
  prevBtn.addEventListener('click', (e) => { e.stopPropagation(); prev(); });
  nextBtn.addEventListener('click', (e) => { e.stopPropagation(); next(); });

  // Close on backdrop or close button
  closeBtn.addEventListener('click', close);
  backdrop.addEventListener('click', close);

  // Touch swipe support
  let touchStartX = 0;
  lightbox.addEventListener('touchstart', (e) => {
    touchStartX = e.changedTouches[0].clientX;
  }, { passive: true });
  lightbox.addEventListener('touchend', (e) => {
    const dx = e.changedTouches[0].clientX - touchStartX;
    if (Math.abs(dx) > 50) {
      dx < 0 ? next() : prev();
    }
  }, { passive: true });
})();
