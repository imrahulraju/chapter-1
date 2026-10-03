<?php
/* ============================================================
   Chapter 1 — Gallery Page v2
   Tab filter, list/grid toggle, lightbox scoped to active tab
   ============================================================ */
require_once __DIR__ . '/admin/gallery-data.php';

$tabs       = galleryGetTabs();
$activeTab  = $_GET['tab'] ?? '';
// Validate tab
$validTabIds = array_column($tabs, 'id');
if ($activeTab && !in_array($activeTab, $validTabIds)) $activeTab = '';

$images     = galleryGetImages($activeTab ?: null);
$allImages  = galleryGetImages();
$total      = count($images);
$totalAll   = count($allImages);

// Tab counts
$tabCounts = [];
foreach ($allImages as $img) {
    $t = $img['tab'] ?? '';
    $tabCounts[$t] = ($tabCounts[$t] ?? 0) + 1;
}

// Active tab name
$activeTabName = 'All Photos';
foreach ($tabs as $t) {
    if ($t['id'] === $activeTab) { $activeTabName = $t['name']; break; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="description" content="Photo gallery of Chapter 1 — premium reading room in Tripunithura, Ernakulam."/>
  <meta name="theme-color" content="#111A2E"/>
  <title><?php echo htmlspecialchars($activeTabName); ?> — Chapter 1 Gallery</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="css/style.css"/>
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' fill='%23111A2E' rx='6'/><text x='50%' y='62%' font-size='18' text-anchor='middle' fill='%23B99452' font-family='Georgia' font-weight='bold'>C</text></svg>"/>
</head>
<body>

<!-- Mobile nav -->
<div class="nav-mobile" role="dialog" aria-label="Mobile navigation" aria-hidden="true">
  <nav class="nav-mobile__links" aria-label="Mobile menu">
    <a href="index.html">Home</a>
    <a href="index.html#about">About</a>
    <a href="index.html#space">The Space</a>
    <a href="index.html#features">Features</a>
    <a href="index.html#membership">Membership</a>
    <a href="index.html#contact">Contact</a>
  </nav>
  <div class="nav-mobile__contact">
    <p class="eyebrow">Get in touch</p>
    <p>7907020744</p>
  </div>
</div>

<!-- Nav (always scrolled state since no hero) -->
<header class="nav is-scrolled" role="banner">
  <div class="container">
    <div class="nav__inner">
      <a href="index.html" class="nav__logo" aria-label="Chapter 1 — Home">
        <img src="assets/images/logo.png" alt="Chapter 1" class="nav__logo-img" width="160" height="54"/>
      </a>
      <nav class="nav__links" aria-label="Primary navigation">
        <a href="index.html">Home</a>
        <a href="index.html#about">About</a>
        <a href="index.html#space">The Space</a>
        <a href="index.html#features">Features</a>
        <a href="index.html#membership">Membership</a>
        <a href="index.html#contact">Contact</a>
      </nav>
      <div class="nav__cta">
        <a href="index.html#membership" class="btn btn--outline">Book Your Seat</a>
      </div>
      <button class="nav__hamburger" aria-label="Toggle navigation menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>

<!-- PAGE HERO -->
<div class="gallery-page__hero">
  <div class="container">
    <nav class="gallery-page__breadcrumb" aria-label="Breadcrumb">
      <a href="index.html">Home</a>
      <span aria-hidden="true">›</span>
      <span aria-current="page">Gallery</span>
    </nav>
    <p class="eyebrow mb-sm">The Space</p>
    <h1 class="display-lg gallery-page__title">Our Gallery</h1>
    <p class="body-lg gallery-page__sub">Every corner of Chapter 1 is designed to help you focus.</p>
    <div class="gallery-page__meta">
      <span><?php echo $totalAll; ?> Photos</span>
      <span aria-hidden="true">·</span>
      <span>Tripunithura, Ernakulam</span>
    </div>
  </div>
</div>

<!-- MAIN CONTENT -->
<main class="gallery-page__section" id="main-content">
  <div class="container">

    <!-- Tab filter bar + view toggle row -->
    <div class="gallery-page__toolbar">

      <!-- Tab pills — scrollable row, no page shake -->
      <?php if (!empty($tabs)): ?>
      <div class="gallery-tabs-wrap">
        <nav class="gallery-tabs" aria-label="Gallery tabs" id="gallery-tabs-nav">
          <a href="gallery.php"
             class="gallery-tab <?php echo !$activeTab ? 'is-active' : ''; ?>"
             <?php echo !$activeTab ? 'aria-current="true"' : ''; ?>>
            All
            <span class="gallery-tab__count"><?php echo $totalAll; ?></span>
          </a>
          <?php foreach ($tabs as $tab): ?>
          <a href="gallery.php?tab=<?php echo urlencode($tab['id']); ?>"
             class="gallery-tab <?php echo $tab['id'] === $activeTab ? 'is-active' : ''; ?>"
             <?php echo $tab['id'] === $activeTab ? 'aria-current="true"' : ''; ?>>
            <?php echo htmlspecialchars($tab['name']); ?>
            <span class="gallery-tab__count"><?php echo $tabCounts[$tab['id']] ?? 0; ?></span>
          </a>
          <?php endforeach; ?>
        </nav>
        <!-- Fade edges to hint scroll -->
        <div class="gallery-tabs-wrap__fade-right" aria-hidden="true"></div>
      </div>
      <?php endif; ?>

      <!-- View toggle -->
      <div class="gallery-view-toggle" role="group" aria-label="View mode">
        <button class="gallery-view-btn is-active" id="btn-grid" title="Grid view" aria-pressed="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        </button>
        <button class="gallery-view-btn" id="btn-list" title="List view" aria-pressed="false">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        </button>
      </div>
    </div>

    <?php if (empty($images)): ?>
    <div class="gallery-page__empty">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
      <p>No images in this tab yet.</p>
    </div>
    <?php else: ?>

    <!-- GRID VIEW -->
    <div class="gallery-page__grid" id="gallery-grid" role="list"
         aria-label="Gallery: <?php echo htmlspecialchars($activeTabName); ?>">
      <?php foreach ($images as $i => $img): ?>
      <?php $url = galleryImageUrl($img); ?>
      <div class="gallery-page__item reveal-scale"
           style="--delay:<?php echo ($i % 9) * 0.055; ?>s"
           role="listitem"
           data-lightbox
           data-src="<?php echo $url; ?>"
           data-caption="<?php echo htmlspecialchars($img['caption'] ?? ''); ?>"
           tabindex="0"
           aria-label="Open: <?php echo htmlspecialchars($img['alt'] ?? $img['filename']); ?>">
        <img src="<?php echo $url; ?>"
             alt="<?php echo htmlspecialchars($img['alt'] ?? ''); ?>"
             loading="<?php echo $i < 6 ? 'eager' : 'lazy'; ?>"
             decoding="async"
             onerror="this.parentElement.classList.add('gallery-page__item--error')"/>
        <div class="gallery__preview-overlay">
          <span class="gallery__zoom-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/><path d="M11 8v6M8 11h6"/></svg>
          </span>
          <?php if (!empty($img['caption'])): ?>
          <p><?php echo htmlspecialchars($img['caption']); ?></p>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- LIST VIEW -->
    <div class="gallery-page__list-view" id="gallery-list" style="display:none"
         role="list" aria-label="Gallery list: <?php echo htmlspecialchars($activeTabName); ?>">
      <?php foreach ($images as $img): ?>
      <?php $url = galleryImageUrl($img); ?>
      <div class="gallery-list-row" role="listitem">
        <div class="gallery-list-row__thumb"
             data-lightbox
             data-src="<?php echo $url; ?>"
             data-caption="<?php echo htmlspecialchars($img['caption'] ?? ''); ?>"
             tabindex="0"
             aria-label="Open: <?php echo htmlspecialchars($img['alt'] ?? $img['filename']); ?>">
          <img src="<?php echo $url; ?>"
               alt="<?php echo htmlspecialchars($img['alt'] ?? ''); ?>"
               loading="lazy" decoding="async"/>
          <div class="gallery__preview-overlay">
            <span class="gallery__zoom-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/><path d="M11 8v6M8 11h6"/></svg>
            </span>
          </div>
        </div>
        <div class="gallery-list-row__info">
          <p class="gallery-list-row__alt"><?php echo htmlspecialchars($img['alt'] ?? $img['filename']); ?></p>
          <?php if (!empty($img['caption'])): ?>
          <p class="gallery-list-row__caption"><?php echo htmlspecialchars($img['caption']); ?></p>
          <?php endif; ?>
          <?php
            $tabLabel = 'Unassigned';
            foreach ($tabs as $t) { if ($t['id'] === ($img['tab'] ?? '')) { $tabLabel = $t['name']; break; } }
          ?>
          <span class="gallery-list-row__tab"><?php echo htmlspecialchars($tabLabel); ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php endif; ?>

    <!-- Back CTA -->
    <div class="gallery-page__back">
      <a href="index.html#space" class="btn btn--outline-dark">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        Back to The Space
      </a>
      <a href="index.html#membership" class="btn btn--gold">Reserve Your Seat</a>
    </div>

  </div>
</main>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Image viewer" aria-hidden="true">
  <div class="lightbox__backdrop"></div>
  <div class="lightbox__container">
    <button class="lightbox__close" aria-label="Close">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <button class="lightbox__nav lightbox__nav--prev" aria-label="Previous">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
    <div class="lightbox__img-wrap">
      <img class="lightbox__img" src="" alt=""/>
      <div class="lightbox__loader" aria-hidden="true"></div>
    </div>
    <button class="lightbox__nav lightbox__nav--next" aria-label="Next">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    </button>
    <div class="lightbox__caption"></div>
    <div class="lightbox__counter" aria-live="polite"></div>
  </div>
</div>

<!-- FOOTER -->
<footer class="footer" role="contentinfo">
  <div class="container">
    <div class="footer__top">
      <div class="footer__brand">
        <a href="index.html"><img src="assets/images/logo.png" alt="Chapter 1" class="footer__brand-logo" width="150" height="54"/></a>
        <p class="footer__brand-desc">A premium reading room and study space. Tripunithura, Ernakulam.</p>
      </div>
      <div>
        <p class="footer__col-title">Navigate</p>
        <nav class="footer__nav">
          <a href="index.html">Home</a>
          <a href="index.html#about">About</a>
          <a href="gallery.php">Gallery</a>
          <a href="index.html#membership">Membership</a>
          <a href="index.html#contact">Contact</a>
        </nav>
      </div>
      <div>
        <p class="footer__col-title">Get in Touch</p>
        <div class="footer__contact">
          <a href="tel:7907020744">
            <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.07 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3 1.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.09 9a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7a2 2 0 0 1 1.72 2.01z"/></svg>
            7907020744
          </a>
          <a href="mailto:chapteronestudyspace@gmail.com">
            <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            chapteronestudyspace@gmail.com
          </a>
        </div>
      </div>
    </div>
    <div class="footer__bottom">
      <p class="footer__copyright">&copy; <?php echo date('Y'); ?> Chapter 1 Reading Room &amp; Study Space.</p>
      <div class="footer__open">Open Daily · 6:00 AM – 10:30 PM</div>
    </div>
  </div>
</footer>

<!-- WhatsApp float -->
<a href="https://wa.me/917907020744?text=Hi%2C%20I%27d%20like%20to%20book%20a%20seat." class="wa-float" target="_blank" rel="noopener noreferrer" aria-label="Chat on WhatsApp">
  <span class="wa-float__pulse" aria-hidden="true"></span>
  <span class="wa-float__icon" aria-hidden="true">
    <svg viewBox="0 0 32 32" fill="none"><path d="M16 1C7.716 1 1 7.716 1 16c0 2.628.676 5.1 1.86 7.252L1 31l7.956-1.832A14.93 14.93 0 0 0 16 31c8.284 0 15-6.716 15-15S24.284 1 16 1z" fill="#25D366"/><path d="M23.07 19.44c-.36-.18-2.13-1.05-2.46-1.17-.33-.12-.57-.18-.81.18-.24.36-.93 1.17-1.14 1.41-.21.24-.42.27-.78.09-.36-.18-1.52-.56-2.9-1.79-1.07-.96-1.79-2.14-2-2.5-.21-.36-.02-.55.16-.73.16-.16.36-.42.54-.63.18-.21.24-.36.36-.6.12-.24.06-.45-.03-.63-.09-.18-.81-1.95-1.11-2.67-.29-.7-.59-.6-.81-.61h-.69c-.24 0-.63.09-.96.45-.33.36-1.26 1.23-1.26 3s1.29 3.48 1.47 3.72c.18.24 2.54 3.88 6.15 5.44.86.37 1.53.59 2.05.76.86.27 1.64.23 2.26.14.69-.1 2.13-.87 2.43-1.71.3-.84.3-1.56.21-1.71-.09-.15-.33-.24-.69-.42z" fill="#fff"/></svg>
  </span>
  <span class="wa-float__label">Book Now</span>
</a>

<script>
/* ── View toggle ─────────────────────────────────────────── */
const gridEl  = document.getElementById('gallery-grid');
const listEl  = document.getElementById('gallery-list');
const btnGrid = document.getElementById('btn-grid');
const btnList = document.getElementById('btn-list');
const VK = 'c1_gallery_view';

function setGalleryView(mode) {
  if (!gridEl || !listEl) return;
  if (mode === 'list') {
    gridEl.style.display = 'none'; listEl.style.display = '';
    btnGrid.classList.remove('is-active'); btnGrid.setAttribute('aria-pressed','false');
    btnList.classList.add('is-active');    btnList.setAttribute('aria-pressed','true');
  } else {
    listEl.style.display = 'none'; gridEl.style.display = '';
    btnList.classList.remove('is-active'); btnList.setAttribute('aria-pressed','false');
    btnGrid.classList.add('is-active');    btnGrid.setAttribute('aria-pressed','true');
  }
  localStorage.setItem(VK, mode);
}
btnGrid?.addEventListener('click', () => setGalleryView('grid'));
btnList?.addEventListener('click', () => setGalleryView('list'));
setGalleryView(localStorage.getItem(VK) || 'grid');
</script>

<script src="js/main.js" defer></script>
</body>
</html>
