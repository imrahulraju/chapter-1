<?php
/* ============================================================
   Chapter 1 — Admin Dashboard v3
   Two main tabs: "Gallery Tabs" | "Gallery Images"
   Fully responsive
   ============================================================ */
session_start();

if (empty($_SESSION['c1_admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/gallery-data.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$tabs         = galleryGetTabs();
$allImages    = galleryGetImages();

// Active page-tab: 'tabs' or 'images' (stored in URL param to survive reload)
$activePanel  = in_array($_GET['panel'] ?? '', ['tabs','images']) ? $_GET['panel'] : 'images';

// Active gallery-tab filter
$activeTabId  = $_GET['tab'] ?? '';
$validTabIds  = array_column($tabs, 'id');
if ($activeTabId && !in_array($activeTabId, $validTabIds)) $activeTabId = '';
$activeImages = galleryGetImages($activeTabId ?: null);

// Tab image counts
$tabCounts = [];
foreach ($allImages as $img) {
    $t = $img['tab'] ?? '';
    $tabCounts[$t] = ($tabCounts[$t] ?? 0) + 1;
}

$csrf = $_SESSION['csrf_token'];

// Helper: active tab name
$activeTabName = 'All Images';
foreach ($tabs as $t) {
    if ($t['id'] === $activeTabId) { $activeTabName = $t['name']; break; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="robots" content="noindex, nofollow"/>
  <title>Gallery Manager — Chapter 1 Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="admin.css"/>
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' fill='%23111A2E' rx='6'/><text x='50%' y='62%' font-size='18' text-anchor='middle' fill='%23B99452' font-family='Georgia' font-weight='bold'>C</text></svg>"/>
</head>
<body class="admin-body">

<!-- ════════════════════════════════
     SIDEBAR
════════════════════════════════ -->
<aside class="admin-sidebar" id="admin-sidebar">
  <div class="admin-sidebar__brand">
    <a href="../index.html" aria-label="View website">
      <img src="../assets/images/logo.png" alt="Chapter 1" class="admin-sidebar__logo" onerror="this.style.display='none'"/>
    </a>
    <span class="admin-sidebar__panel-label">Admin</span>
  </div>
  <nav class="admin-nav">
    <a href="index.php" class="admin-nav__item admin-nav__item--active">
      <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
      Gallery
    </a>
    <a href="../index.html" class="admin-nav__item" target="_blank" rel="noopener">
      <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      View Site
    </a>
    <a href="../gallery.php" class="admin-nav__item" target="_blank" rel="noopener">
      <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
      View Gallery
    </a>
  </nav>
  <div class="admin-sidebar__footer">
    <div class="admin-sidebar__user">
      <div class="admin-sidebar__avatar"><?php echo strtoupper(substr($_SESSION['c1_admin_user'] ?? 'A', 0, 1)); ?></div>
      <div class="admin-sidebar__user-info">
        <p class="admin-sidebar__username"><?php echo htmlspecialchars($_SESSION['c1_admin_user'] ?? 'Admin'); ?></p>
        <p class="admin-sidebar__role">Administrator</p>
      </div>
    </div>
    <a href="logout.php" class="admin-nav__item admin-nav__item--logout">
      <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Sign Out
    </a>
  </div>
</aside>

<!-- ════════════════════════════════
     MAIN
════════════════════════════════ -->
<main class="admin-main" id="main-content">

  <!-- Top bar -->
  <header class="admin-topbar">
    <div class="admin-topbar__left">
      <button class="admin-sidebar-toggle" id="sidebar-toggle" aria-label="Toggle sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>
      <div>
        <h1 class="admin-topbar__title">Gallery Manager</h1>
        <p class="admin-topbar__sub">
          <?php echo count($allImages); ?> photo<?php echo count($allImages) !== 1 ? 's' : ''; ?>
          &nbsp;·&nbsp;
          <?php echo count($tabs); ?> tab<?php echo count($tabs) !== 1 ? 's' : ''; ?>
        </p>
      </div>
    </div>
    <div class="admin-topbar__actions">
      <button class="admin-btn admin-btn--primary" id="btn-add-image">
        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Images
      </button>
    </div>
  </header>

  <div class="admin-content">

    <!-- Flash messages -->
    <div id="flash-zone" aria-live="polite"></div>

    <!-- ═══════════════════════════════
         PAGE-LEVEL TAB SWITCHER
    ═══════════════════════════════ -->
    <div class="admin-page-tabs" role="tablist" aria-label="Gallery manager sections">
      <button
        class="admin-page-tab <?php echo $activePanel === 'tabs'   ? 'is-active' : ''; ?>"
        role="tab"
        id="ptab-tabs"
        aria-controls="ppanel-tabs"
        aria-selected="<?php echo $activePanel === 'tabs'   ? 'true' : 'false'; ?>"
        data-panel="tabs">
        <svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h8m-8 6h16"/></svg>
        Gallery Tabs
        <span class="admin-page-tab__badge"><?php echo count($tabs); ?></span>
      </button>
      <button
        class="admin-page-tab <?php echo $activePanel === 'images' ? 'is-active' : ''; ?>"
        role="tab"
        id="ptab-images"
        aria-controls="ppanel-images"
        aria-selected="<?php echo $activePanel === 'images' ? 'true' : 'false'; ?>"
        data-panel="images">
        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        Gallery Images
        <span class="admin-page-tab__badge"><?php echo count($allImages); ?></span>
      </button>
    </div>

    <!-- ═══════════════════════════════
         PANEL 1: GALLERY TABS
    ═══════════════════════════════ -->
    <div
      class="admin-panel-content <?php echo $activePanel === 'tabs' ? 'is-active' : ''; ?>"
      id="ppanel-tabs"
      role="tabpanel"
      aria-labelledby="ptab-tabs">

      <div class="admin-card">
        <div class="admin-card__header">
          <div>
            <h2 class="admin-card__title">
              <svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h8m-8 6h16"/></svg>
              Gallery Tabs
            </h2>
            <p class="admin-card__desc">Tabs group your gallery images into categories shown on the website.</p>
          </div>
          <button class="admin-btn admin-btn--ghost admin-btn--sm" id="btn-add-tab">
            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Tab
          </button>
        </div>

        <!-- New tab inline form -->
        <div class="admin-new-tab-form" id="new-tab-form" style="display:none">
          <input type="text" id="new-tab-name"
                 class="admin-form__input admin-form__input--inline"
                 placeholder="e.g. Study Hall, Seating, Amenities…"
                 maxlength="50"/>
          <button class="admin-btn admin-btn--primary admin-btn--sm" id="btn-save-tab">Create Tab</button>
          <button class="admin-btn admin-btn--ghost   admin-btn--sm" id="btn-cancel-tab">Cancel</button>
        </div>

        <!-- Tab list -->
        <div class="admin-tab-list" id="tab-list">
          <?php if (empty($tabs)): ?>
          <div class="admin-empty">
            <svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h8m-8 6h16"/></svg>
            <p>No tabs yet.</p>
            <p class="admin-empty__sub">Click "New Tab" to create your first category.</p>
          </div>
          <?php else: ?>

          <!-- Tab list header -->
          <div class="admin-tab-list__header">
            <span>Tab Name</span>
            <span>Photos</span>
            <span>Actions</span>
          </div>

          <?php foreach ($tabs as $tab): ?>
          <div class="admin-tab-row" data-tab-id="<?php echo htmlspecialchars($tab['id']); ?>">

            <div class="admin-tab-row__name-cell">
              <span class="admin-tab-row__dot" aria-hidden="true"></span>
              <span class="admin-tab-row__name"><?php echo htmlspecialchars($tab['name']); ?></span>
            </div>

            <div class="admin-tab-row__count-cell">
              <span class="admin-tab-row__count">
                <?php echo $tabCounts[$tab['id']] ?? 0; ?>
                <span class="admin-tab-row__count-label"> photo<?php echo ($tabCounts[$tab['id']] ?? 0) !== 1 ? 's' : ''; ?></span>
              </span>
            </div>

            <div class="admin-tab-row__actions">
              <button class="admin-icon-btn btn-rename-tab"
                      title="Rename"
                      data-tab-id="<?php echo htmlspecialchars($tab['id']); ?>"
                      data-tab-name="<?php echo htmlspecialchars($tab['name']); ?>">
                <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
              <button class="admin-btn admin-btn--ghost admin-btn--sm btn-view-tab-images"
                      data-tab-id="<?php echo htmlspecialchars($tab['id']); ?>"
                      data-tab-name="<?php echo htmlspecialchars($tab['name']); ?>"
                      title="View images in this tab">
                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                View Images
              </button>
              <button class="admin-icon-btn admin-icon-btn--danger btn-delete-tab"
                      title="Delete tab"
                      data-tab-id="<?php echo htmlspecialchars($tab['id']); ?>"
                      data-tab-name="<?php echo htmlspecialchars($tab['name']); ?>">
                <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
              </button>
            </div>

          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div><!-- /ppanel-tabs -->

    <!-- ═══════════════════════════════
         PANEL 2: GALLERY IMAGES
    ═══════════════════════════════ -->
    <div
      class="admin-panel-content <?php echo $activePanel === 'images' ? 'is-active' : ''; ?>"
      id="ppanel-images"
      role="tabpanel"
      aria-labelledby="ptab-images">

      <div class="admin-card">

        <!-- Images card header -->
        <div class="admin-card__header admin-images-header">

          <!-- Left: title + filter pills -->
          <div class="admin-images-header__left">
            <h2 class="admin-card__title">
              <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
              <?php echo htmlspecialchars($activeTabName); ?>
            </h2>
            <!-- Scrollable filter pills -->
            <div class="admin-filter-pills-wrap">
              <div class="admin-filter-pills" id="filter-pills">
                <a href="?panel=images&tab="
                   class="admin-pill <?php echo !$activeTabId ? 'is-active' : ''; ?>">
                  All
                  <span class="admin-pill__count"><?php echo count($allImages); ?></span>
                </a>
                <?php foreach ($tabs as $tab): ?>
                <a href="?panel=images&tab=<?php echo urlencode($tab['id']); ?>"
                   class="admin-pill <?php echo $tab['id'] === $activeTabId ? 'is-active' : ''; ?>">
                  <?php echo htmlspecialchars($tab['name']); ?>
                  <span class="admin-pill__count"><?php echo $tabCounts[$tab['id']] ?? 0; ?></span>
                </a>
                <?php endforeach; ?>
              </div>
              <div class="admin-filter-pills-wrap__fade" aria-hidden="true"></div>
            </div>
          </div>

          <!-- Right: view toggle -->
          <div class="admin-view-toggle" role="group" aria-label="View mode">
            <button class="admin-view-btn" id="btn-grid-view" title="Grid view" aria-pressed="true">
              <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            </button>
            <button class="admin-view-btn" id="btn-list-view" title="List view" aria-pressed="false">
              <svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            </button>
          </div>
        </div>

        <!-- Empty state -->
        <?php if (empty($activeImages)): ?>
        <div class="admin-empty">
          <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          <p>No images in this tab yet.</p>
          <p class="admin-empty__sub">Click "Add Images" in the top-right to upload.</p>
        </div>
        <?php else: ?>

        <!-- GRID VIEW -->
        <div class="admin-gallery-grid" id="gallery-grid">
          <?php foreach ($activeImages as $img): ?>
          <?php $url = galleryImageUrl($img, '../'); ?>
          <?php
            $imgTabLabel = '—';
            foreach ($tabs as $t) {
              if ($t['id'] === ($img['tab'] ?? '')) { $imgTabLabel = $t['name']; break; }
            }
          ?>
          <div class="admin-gallery-item" data-id="<?php echo htmlspecialchars($img['id']); ?>">
            <div class="admin-gallery-item__thumb">
              <img src="<?php echo $url; ?>"
                   alt="<?php echo htmlspecialchars($img['alt'] ?? ''); ?>"
                   loading="lazy"
                   onerror="this.parentElement.classList.add('admin-gallery-item__thumb--error')"/>
              <div class="admin-gallery-item__overlay">
                <button class="admin-gallery-item__view btn-preview-img"
                        data-src="<?php echo $url; ?>"
                        data-alt="<?php echo htmlspecialchars($img['alt'] ?? ''); ?>"
                        aria-label="Preview">
                  <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
                <button class="admin-gallery-item__edit-btn btn-edit-img"
                        data-id="<?php echo htmlspecialchars($img['id']); ?>"
                        data-tab="<?php echo htmlspecialchars($img['tab'] ?? ''); ?>"
                        data-caption="<?php echo htmlspecialchars($img['caption'] ?? ''); ?>"
                        data-filename="<?php echo htmlspecialchars($img['filename']); ?>"
                        aria-label="Edit">
                  <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
              </div>
            </div>
            <div class="admin-gallery-item__info">
              <p class="admin-gallery-item__name" title="<?php echo htmlspecialchars($img['filename']); ?>">
                <?php echo htmlspecialchars($img['filename']); ?>
              </p>
              <p class="admin-gallery-item__meta"><?php echo htmlspecialchars($imgTabLabel); ?></p>
            </div>
            <button class="admin-gallery-item__delete-btn btn-delete-img"
                    data-id="<?php echo htmlspecialchars($img['id']); ?>"
                    data-name="<?php echo htmlspecialchars($img['filename']); ?>"
                    aria-label="Delete">
              <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
            </button>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- LIST VIEW -->
        <div class="admin-gallery-list" id="gallery-list" style="display:none">
          <div class="admin-list-header">
            <span>Image</span>
            <span>Filename</span>
            <span>Tab</span>
            <span class="hide-sm">Caption</span>
            <span>Actions</span>
          </div>
          <?php foreach ($activeImages as $img): ?>
          <?php $url = galleryImageUrl($img, '../'); ?>
          <div class="admin-list-row" data-id="<?php echo htmlspecialchars($img['id']); ?>">
            <div class="admin-list-row__thumb">
              <img src="<?php echo $url; ?>" alt="<?php echo htmlspecialchars($img['alt'] ?? ''); ?>" loading="lazy"/>
            </div>
            <div class="admin-list-row__name">
              <p><?php echo htmlspecialchars($img['filename']); ?></p>
            </div>
            <div class="admin-list-row__tab">
              <select class="admin-list-tab-select" data-id="<?php echo htmlspecialchars($img['id']); ?>">
                <option value="">— Unassigned —</option>
                <?php foreach ($tabs as $t): ?>
                <option value="<?php echo htmlspecialchars($t['id']); ?>"
                  <?php echo ($img['tab'] ?? '') === $t['id'] ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($t['name']); ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="admin-list-row__caption hide-sm">
              <input type="text"
                     class="admin-list-caption-input"
                     data-id="<?php echo htmlspecialchars($img['id']); ?>"
                     value="<?php echo htmlspecialchars($img['caption'] ?? ''); ?>"
                     placeholder="Add caption…"
                     maxlength="120"/>
            </div>
            <div class="admin-list-row__actions">
              <button class="admin-icon-btn btn-preview-img"
                      data-src="<?php echo $url; ?>"
                      data-alt="<?php echo htmlspecialchars($img['alt'] ?? ''); ?>"
                      title="Preview">
                <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
              <button class="admin-icon-btn btn-edit-img"
                      data-id="<?php echo htmlspecialchars($img['id']); ?>"
                      data-tab="<?php echo htmlspecialchars($img['tab'] ?? ''); ?>"
                      data-caption="<?php echo htmlspecialchars($img['caption'] ?? ''); ?>"
                      data-filename="<?php echo htmlspecialchars($img['filename']); ?>"
                      title="Edit">
                <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
              <button class="admin-icon-btn admin-icon-btn--danger btn-delete-img"
                      data-id="<?php echo htmlspecialchars($img['id']); ?>"
                      data-name="<?php echo htmlspecialchars($img['filename']); ?>"
                      title="Delete">
                <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
              </button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <?php endif; ?>
      </div>
    </div><!-- /ppanel-images -->

  </div><!-- /.admin-content -->
</main>

<!-- ═══════════════════════════════════════
     UPLOAD SLIDE PANEL
═══════════════════════════════════════ -->
<div class="admin-upload-panel" id="upload-panel" aria-hidden="true">
  <div class="admin-upload-panel__backdrop" id="upload-panel-backdrop"></div>
  <div class="admin-upload-panel__drawer" role="dialog" aria-label="Upload images" aria-modal="true">

    <div class="admin-upload-panel__header">
      <h2 class="admin-upload-panel__title">
        <svg viewBox="0 0 24 24"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
        Add Images
      </h2>
      <button class="admin-upload-panel__close" id="close-upload-panel" aria-label="Close">
        <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <div class="admin-upload-panel__body">
      <div class="admin-form__group">
        <label class="admin-form__label" for="upload-tab-select">Default Tab for All Images</label>
        <select id="upload-tab-select" class="admin-form__input admin-form__select">
          <option value="">— Unassigned —</option>
          <?php foreach ($tabs as $tab): ?>
          <option value="<?php echo htmlspecialchars($tab['id']); ?>"
            <?php echo $tab['id'] === $activeTabId ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($tab['name']); ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <p class="admin-upload-panel__hint">You can override the tab per image below.</p>

      <div class="admin-dropzone" id="upload-dropzone" role="button" tabindex="0" aria-label="Click or drag images here">
        <div class="admin-dropzone__inner">
          <div class="admin-dropzone__icon">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          </div>
          <p class="admin-dropzone__text">Drag &amp; drop images here</p>
          <p class="admin-dropzone__sub">JPG, PNG, WebP, GIF · Max 8 MB each · Multiple allowed</p>
        </div>
        <input type="file" id="upload-file-input" multiple
               accept="image/jpeg,image/png,image/webp,image/gif"
               class="admin-dropzone__input" tabindex="-1"/>
      </div>

      <div class="admin-upload-queue" id="upload-queue" aria-live="polite"></div>

      <div class="admin-upload-panel__actions" id="upload-panel-actions" style="display:none">
        <button class="admin-btn admin-btn--primary" id="btn-start-upload">
          <svg viewBox="0 0 24 24"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/></svg>
          Upload All
        </button>
        <button class="admin-btn admin-btn--ghost" id="btn-clear-queue">Clear All</button>
      </div>

      <div class="admin-overall-progress" id="overall-progress" style="display:none">
        <div class="admin-overall-progress__bar">
          <div class="admin-overall-progress__fill" id="overall-progress-fill"></div>
        </div>
        <p class="admin-overall-progress__label" id="overall-progress-label">Uploading…</p>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════
     MODALS
═══════════════════════════════════════ -->

<!-- Image preview -->
<div class="admin-preview-modal" id="preview-modal" aria-hidden="true" role="dialog" aria-label="Image preview">
  <div class="admin-preview-modal__backdrop" id="preview-backdrop"></div>
  <div class="admin-preview-modal__inner">
    <button class="admin-preview-modal__close" id="preview-close" aria-label="Close preview">
      <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <img src="" alt="" class="admin-preview-modal__img" id="preview-img"/>
  </div>
</div>

<!-- Edit image -->
<div class="admin-edit-modal" id="edit-modal" aria-hidden="true" role="dialog" aria-label="Edit image">
  <div class="admin-edit-modal__backdrop" id="edit-backdrop"></div>
  <div class="admin-edit-modal__box">
    <div class="admin-edit-modal__header">
      <h3 class="admin-rename-modal__title">Edit Image</h3>
      <button class="admin-upload-panel__close" id="edit-modal-close" aria-label="Close">
        <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <p class="admin-edit-modal__filename" id="edit-modal-filename"></p>
    <div class="admin-form__group">
      <label class="admin-form__label" for="edit-tab-select">Assign to Tab</label>
      <select id="edit-tab-select" class="admin-form__input admin-form__select">
        <option value="">— Unassigned —</option>
        <?php foreach ($tabs as $tab): ?>
        <option value="<?php echo htmlspecialchars($tab['id']); ?>"><?php echo htmlspecialchars($tab['name']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="admin-form__group">
      <label class="admin-form__label" for="edit-caption-input">Caption</label>
      <input type="text" id="edit-caption-input" class="admin-form__input"
             placeholder="Add a caption shown in the gallery…" maxlength="120"/>
    </div>
    <div class="admin-rename-modal__actions">
      <button class="admin-btn admin-btn--primary" id="btn-save-edit">Save Changes</button>
      <button class="admin-btn admin-btn--ghost"   id="btn-cancel-edit">Cancel</button>
    </div>
  </div>
</div>

<!-- Rename tab -->
<div class="admin-rename-modal" id="rename-modal" aria-hidden="true" role="dialog" aria-label="Rename tab">
  <div class="admin-rename-modal__backdrop" id="rename-backdrop"></div>
  <div class="admin-rename-modal__box">
    <h3 class="admin-rename-modal__title">Rename Tab</h3>
    <input type="text" id="rename-input" class="admin-form__input" maxlength="50" placeholder="Tab name"/>
    <div class="admin-rename-modal__actions">
      <button class="admin-btn admin-btn--primary" id="btn-confirm-rename">Save</button>
      <button class="admin-btn admin-btn--ghost"   id="btn-cancel-rename">Cancel</button>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════
     JAVASCRIPT
═══════════════════════════════════════ -->
<script>
const CSRF     = <?php echo json_encode($csrf); ?>;
const ALL_TABS = <?php echo json_encode($tabs); ?>;

/* ── Sidebar toggle ──────────────────────────────────────── */
document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
  document.getElementById('admin-sidebar').classList.toggle('is-open');
});
// Close sidebar on backdrop click (mobile)
document.addEventListener('click', e => {
  const sidebar = document.getElementById('admin-sidebar');
  const toggle  = document.getElementById('sidebar-toggle');
  if (sidebar.classList.contains('is-open') && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
    sidebar.classList.remove('is-open');
  }
});

/* ── Flash ───────────────────────────────────────────────── */
function showFlash(text, type = 'success') {
  const zone = document.getElementById('flash-zone');
  const el   = document.createElement('div');
  el.className = `admin-alert admin-alert--${type}`;
  el.setAttribute('role', 'alert');
  el.innerHTML = type === 'success'
    ? `<svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>${text}`
    : `<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>${text}`;
  zone.appendChild(el);
  setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }, 5000);
}

/* ── AJAX helper ─────────────────────────────────────────── */
async function postJSON(url, body) {
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    body: JSON.stringify({ csrf_token: CSRF, ...body })
  });
  return res.json();
}

/* ══════════════════════════════════════
   PAGE-LEVEL TAB SWITCHER
══════════════════════════════════════ */
const pageTabs   = document.querySelectorAll('.admin-page-tab');
const pagePanels = document.querySelectorAll('.admin-panel-content');
const TAB_KEY    = 'c1_admin_panel';

function switchPanel(panel) {
  pageTabs.forEach(btn => {
    const active = btn.dataset.panel === panel;
    btn.classList.toggle('is-active', active);
    btn.setAttribute('aria-selected', String(active));
  });
  pagePanels.forEach(p => {
    p.classList.toggle('is-active', p.id === `ppanel-${panel}`);
  });
  // Update URL without reload
  const url = new URL(window.location);
  url.searchParams.set('panel', panel);
  history.replaceState(null, '', url);
  localStorage.setItem(TAB_KEY, panel);
}

pageTabs.forEach(btn => {
  btn.addEventListener('click', () => switchPanel(btn.dataset.panel));
});

/* ── "View Images" from Tabs panel switches to images panel → filter ── */
document.querySelectorAll('.btn-view-tab-images').forEach(btn => {
  btn.addEventListener('click', () => {
    const tabId   = btn.dataset.tabId;
    const tabName = btn.dataset.tabName;
    // Update heading in images panel
    document.querySelector('#ppanel-images .admin-card__title').lastChild.textContent = ' ' + tabName;
    // Switch panel
    switchPanel('images');
    // Navigate to filtered view
    const url = new URL(window.location);
    url.searchParams.set('panel', 'images');
    url.searchParams.set('tab', tabId);
    window.location.href = url.toString();
  });
});

/* ══════════════════════════════════════
   GALLERY TABS — CRUD
══════════════════════════════════════ */
const btnAddTab    = document.getElementById('btn-add-tab');
const newTabForm   = document.getElementById('new-tab-form');
const newTabInput  = document.getElementById('new-tab-name');
const btnSaveTab   = document.getElementById('btn-save-tab');
const btnCancelTab = document.getElementById('btn-cancel-tab');

btnAddTab.addEventListener('click', () => {
  newTabForm.style.display = 'flex';
  newTabInput.focus();
  btnAddTab.style.display = 'none';
});
btnCancelTab.addEventListener('click', () => {
  newTabForm.style.display = 'none';
  btnAddTab.style.display  = '';
  newTabInput.value = '';
});
btnSaveTab.addEventListener('click', async () => {
  const name = newTabInput.value.trim();
  if (!name) { newTabInput.focus(); return; }
  btnSaveTab.textContent = 'Creating…';
  btnSaveTab.disabled = true;
  const res = await postJSON('tab-actions.php', { action: 'create', name });
  if (res.ok) {
    showFlash(`Tab "${res.tab.name}" created.`);
    setTimeout(() => location.reload(), 600);
  } else {
    showFlash(res.error || 'Failed.', 'error');
    btnSaveTab.textContent = 'Create Tab';
    btnSaveTab.disabled = false;
  }
});
newTabInput.addEventListener('keydown', e => { if (e.key === 'Enter') btnSaveTab.click(); });

/* ── Rename modal ────────────────────────────────────────── */
const renameModal   = document.getElementById('rename-modal');
const renameInput   = document.getElementById('rename-input');
const btnConfirmRen = document.getElementById('btn-confirm-rename');
const btnCancelRen  = document.getElementById('btn-cancel-rename');
let   renameTargetId = null;

document.querySelectorAll('.btn-rename-tab').forEach(btn => {
  btn.addEventListener('click', () => {
    renameTargetId = btn.dataset.tabId;
    renameInput.value = btn.dataset.tabName;
    renameModal.classList.add('is-open');
    renameModal.setAttribute('aria-hidden', 'false');
    renameInput.focus();
  });
});
function closeRenameModal() {
  renameModal.classList.remove('is-open');
  renameModal.setAttribute('aria-hidden', 'true');
}
btnCancelRen.addEventListener('click', closeRenameModal);
document.getElementById('rename-backdrop').addEventListener('click', closeRenameModal);
btnConfirmRen.addEventListener('click', async () => {
  const newName = renameInput.value.trim();
  if (!newName || !renameTargetId) return;
  btnConfirmRen.textContent = 'Saving…'; btnConfirmRen.disabled = true;
  const res = await postJSON('tab-actions.php', { action: 'rename', id: renameTargetId, name: newName });
  if (res.ok) { showFlash('Tab renamed.'); setTimeout(() => location.reload(), 500); }
  else { showFlash(res.error || 'Failed.', 'error'); btnConfirmRen.textContent = 'Save'; btnConfirmRen.disabled = false; }
});

/* ── Delete tab ──────────────────────────────────────────── */
document.querySelectorAll('.btn-delete-tab').forEach(btn => {
  btn.addEventListener('click', async () => {
    const name = btn.dataset.tabName;
    if (!confirm(`Delete tab "${name}"?\nImages will become unassigned.`)) return;
    const res = await postJSON('tab-actions.php', { action: 'delete', id: btn.dataset.tabId });
    if (res.ok) { showFlash(`Tab "${name}" deleted.`); setTimeout(() => location.href = 'index.php', 600); }
    else showFlash(res.error || 'Failed.', 'error');
  });
});

/* ══════════════════════════════════════
   VIEW TOGGLE (grid / list)
══════════════════════════════════════ */
const gridView = document.getElementById('gallery-grid');
const listView = document.getElementById('gallery-list');
const btnGrid  = document.getElementById('btn-grid-view');
const btnList  = document.getElementById('btn-list-view');
const VIEW_KEY = 'c1_admin_view';

function setView(mode) {
  if (!gridView || !listView) return;
  const isList = mode === 'list';
  gridView.style.display = isList ? 'none' : '';
  listView.style.display = isList ? ''     : 'none';
  btnGrid.classList.toggle('is-active', !isList); btnGrid.setAttribute('aria-pressed', String(!isList));
  btnList.classList.toggle('is-active',  isList); btnList.setAttribute('aria-pressed', String(isList));
  localStorage.setItem(VIEW_KEY, mode);
}
btnGrid?.addEventListener('click', () => setView('grid'));
btnList?.addEventListener('click', () => setView('list'));
setView(localStorage.getItem(VIEW_KEY) || 'grid');

/* ── Inline tab reassign (list view) ─────────────────────── */
document.querySelectorAll('.admin-list-tab-select').forEach(sel => {
  sel.addEventListener('change', async () => {
    const res = await postJSON('tab-actions.php', { action: 'move_image', image_id: sel.dataset.id, tab_id: sel.value });
    if (res.ok) showFlash('Tab updated.');
    else showFlash(res.error || 'Failed.', 'error');
  });
});

/* ── Inline caption (list view) ──────────────────────────── */
let capTimer = null;
document.querySelectorAll('.admin-list-caption-input').forEach(inp => {
  inp.addEventListener('input', () => {
    clearTimeout(capTimer);
    capTimer = setTimeout(async () => {
      const res = await postJSON('tab-actions.php', { action: 'update_caption', image_id: inp.dataset.id, caption: inp.value });
      if (!res.ok) showFlash('Caption save failed.', 'error');
    }, 700);
  });
});

/* ══════════════════════════════════════
   DELETE IMAGE
══════════════════════════════════════ */
document.querySelectorAll('.btn-delete-img').forEach(btn => {
  btn.addEventListener('click', async () => {
    if (!confirm(`Delete "${btn.dataset.name || 'this image'}"? Cannot be undone.`)) return;
    const res = await postJSON('tab-actions.php', { action: 'delete_image', image_id: btn.dataset.id });
    if (res.ok) {
      showFlash('Image deleted.');
      document.querySelectorAll(`[data-id="${btn.dataset.id}"]`).forEach(el => {
        el.style.transition = 'opacity 0.3s, transform 0.3s';
        el.style.opacity = '0'; el.style.transform = 'scale(0.9)';
        setTimeout(() => el.remove(), 300);
      });
    } else showFlash(res.error || 'Delete failed.', 'error');
  });
});

/* ══════════════════════════════════════
   EDIT IMAGE MODAL
══════════════════════════════════════ */
const editModal      = document.getElementById('edit-modal');
const editBackdrop   = document.getElementById('edit-backdrop');
const editModalClose = document.getElementById('edit-modal-close');
const editTabSel     = document.getElementById('edit-tab-select');
const editCaptionInp = document.getElementById('edit-caption-input');
const editFilename   = document.getElementById('edit-modal-filename');
const btnSaveEdit    = document.getElementById('btn-save-edit');
const btnCancelEdit  = document.getElementById('btn-cancel-edit');
let   editTargetId   = null;

function openEditModal(id, tab, caption, filename) {
  editTargetId = id;
  editFilename.textContent = filename;
  editTabSel.value = tab || '';
  editCaptionInp.value = caption || '';
  editModal.classList.add('is-open');
  editModal.setAttribute('aria-hidden', 'false');
  editCaptionInp.focus();
}
function closeEditModal() {
  editModal.classList.remove('is-open');
  editModal.setAttribute('aria-hidden', 'true');
  editTargetId = null;
}
document.addEventListener('click', e => {
  const btn = e.target.closest('.btn-edit-img');
  if (btn) openEditModal(btn.dataset.id, btn.dataset.tab, btn.dataset.caption, btn.dataset.filename);
});
editBackdrop.addEventListener('click', closeEditModal);
editModalClose.addEventListener('click', closeEditModal);
btnCancelEdit.addEventListener('click', closeEditModal);

btnSaveEdit.addEventListener('click', async () => {
  if (!editTargetId) return;
  btnSaveEdit.textContent = 'Saving…'; btnSaveEdit.disabled = true;
  const [tabRes, capRes] = await Promise.all([
    postJSON('tab-actions.php', { action: 'move_image',     image_id: editTargetId, tab_id:  editTabSel.value }),
    postJSON('tab-actions.php', { action: 'update_caption', image_id: editTargetId, caption: editCaptionInp.value }),
  ]);
  if (tabRes.ok && capRes.ok) {
    showFlash('Image updated.');
    const tabName = ALL_TABS.find(t => t.id === editTabSel.value)?.name || '—';
    document.querySelectorAll(`[data-id="${editTargetId}"] .admin-gallery-item__meta`).forEach(el => el.textContent = tabName);
    document.querySelectorAll(`.btn-edit-img[data-id="${editTargetId}"]`).forEach(btn => {
      btn.dataset.tab     = editTabSel.value;
      btn.dataset.caption = editCaptionInp.value;
    });
    closeEditModal();
  } else showFlash((tabRes.ok ? '' : tabRes.error) || capRes.error || 'Save failed.', 'error');
  btnSaveEdit.textContent = 'Save Changes'; btnSaveEdit.disabled = false;
});

/* ══════════════════════════════════════
   IMAGE PREVIEW MODAL
══════════════════════════════════════ */
const previewModal = document.getElementById('preview-modal');
const previewImg   = document.getElementById('preview-img');
const previewClose = document.getElementById('preview-close');
const previewBack  = document.getElementById('preview-backdrop');

function openPreview(src, alt) {
  previewImg.src = src; previewImg.alt = alt || '';
  previewModal.classList.add('is-open'); previewModal.setAttribute('aria-hidden','false');
  previewClose.focus();
}
function closePreview() {
  previewModal.classList.remove('is-open'); previewModal.setAttribute('aria-hidden','true');
  previewImg.src = '';
}
document.addEventListener('click', e => {
  const btn = e.target.closest('.btn-preview-img');
  if (btn) openPreview(btn.dataset.src, btn.dataset.alt);
});
previewClose.addEventListener('click', closePreview);
previewBack.addEventListener('click', closePreview);

/* ── Escape closes all modals ────────────────────────────── */
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') { closeEditModal(); closePreview(); closeRenameModal(); closeUploadPanel(); }
});

/* ══════════════════════════════════════
   UPLOAD PANEL
══════════════════════════════════════ */
const uploadPanel    = document.getElementById('upload-panel');
const uploadBackdrop = document.getElementById('upload-panel-backdrop');
const closeUploadBtn = document.getElementById('close-upload-panel');
const dropzone       = document.getElementById('upload-dropzone');
const fileInput      = document.getElementById('upload-file-input');
const uploadQueue    = document.getElementById('upload-queue');
const panelActions   = document.getElementById('upload-panel-actions');
const btnStartUpload = document.getElementById('btn-start-upload');
const btnClearQueue  = document.getElementById('btn-clear-queue');
const overallProg    = document.getElementById('overall-progress');
const overallFill    = document.getElementById('overall-progress-fill');
const overallLabel   = document.getElementById('overall-progress-label');
const tabSelect      = document.getElementById('upload-tab-select');
let fileQueue = [];

function openUploadPanel() {
  uploadPanel.classList.add('is-open');
  uploadPanel.setAttribute('aria-hidden','false');
  document.body.style.overflow = 'hidden';
}
function closeUploadPanel() {
  uploadPanel.classList.remove('is-open');
  uploadPanel.setAttribute('aria-hidden','true');
  document.body.style.overflow = '';
}
document.getElementById('btn-add-image').addEventListener('click', openUploadPanel);
closeUploadBtn.addEventListener('click', closeUploadPanel);
uploadBackdrop.addEventListener('click', closeUploadPanel);

dropzone.addEventListener('click', e => { if (e.target !== fileInput) fileInput.click(); });
dropzone.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); fileInput.click(); } });
['dragenter','dragover'].forEach(ev => dropzone.addEventListener(ev, e => { e.preventDefault(); dropzone.classList.add('is-over'); }));
['dragleave','drop'].forEach(ev => dropzone.addEventListener(ev, e => { e.preventDefault(); dropzone.classList.remove('is-over'); }));
dropzone.addEventListener('drop', e => { e.preventDefault(); addToQueue(e.dataTransfer.files); });
fileInput.addEventListener('change', () => { addToQueue(fileInput.files); fileInput.value = ''; });

function addToQueue(fileList) {
  Array.from(fileList).forEach(file => {
    if (!fileQueue.find(f => f.name === file.name && f.size === file.size)) fileQueue.push(file);
  });
  renderQueue();
}

function renderQueue() {
  uploadQueue.innerHTML = '';
  if (!fileQueue.length) { panelActions.style.display = 'none'; return; }
  panelActions.style.display = 'flex';
  const tabOptsHtml = ALL_TABS.map(t => `<option value="${t.id}">${t.name}</option>`).join('');

  fileQueue.forEach((file, i) => {
    const tooLarge = file.size > 8 * 1024 * 1024;
    const row = document.createElement('div');
    row.className = 'upload-queue-item' + (tooLarge ? ' upload-queue-item--error' : '');
    row.id = `queue-item-${i}`;
    const reader = new FileReader();
    reader.onload = ev => {
      row.innerHTML = `
        <img class="upload-queue-item__thumb" src="${ev.target.result}" alt="${file.name}"/>
        <div class="upload-queue-item__info">
          <p class="upload-queue-item__name" title="${file.name}">${file.name}</p>
          <p class="upload-queue-item__size ${tooLarge ? 'text-error' : ''}">${tooLarge ? '⚠ Too large — ' : ''}${(file.size/1024).toFixed(0)} KB</p>
          <div class="upload-queue-item__tab-row">
            <select class="upload-queue-tab-select" id="queue-tab-${i}">
              <option value="">— Unassigned —</option>${tabOptsHtml}
            </select>
          </div>
          <div class="upload-queue-item__progress" id="prog-${i}" style="display:none">
            <div class="upload-progress-bar"><div class="upload-progress-bar__fill" id="prog-fill-${i}"></div></div>
            <span class="upload-progress-label" id="prog-label-${i}">0%</span>
          </div>
          <p class="upload-queue-item__status" id="status-${i}"></p>
        </div>
        <button class="admin-icon-btn upload-queue-item__remove" data-index="${i}" aria-label="Remove">✕</button>
      `;
      const sel = row.querySelector(`#queue-tab-${i}`);
      if (sel && tabSelect.value) sel.value = tabSelect.value;
      row.querySelector('.upload-queue-item__remove').addEventListener('click', () => { fileQueue.splice(i, 1); renderQueue(); });
    };
    reader.readAsDataURL(file);
    uploadQueue.appendChild(row);
  });
}

tabSelect.addEventListener('change', () => {
  document.querySelectorAll('.upload-queue-tab-select').forEach(sel => { sel.value = tabSelect.value; });
});
btnClearQueue.addEventListener('click', () => { fileQueue = []; renderQueue(); });

btnStartUpload.addEventListener('click', async () => {
  const validFiles = fileQueue.filter(f => f.size <= 8 * 1024 * 1024);
  if (!validFiles.length) { showFlash('No valid files to upload.', 'error'); return; }
  btnStartUpload.disabled = true; btnClearQueue.disabled = true;
  overallProg.style.display = '';
  let done = 0; const total = validFiles.length;

  function updateOverall() {
    const pct = Math.round((done / total) * 100);
    overallFill.style.width = pct + '%';
    overallLabel.textContent = `Uploading ${done} of ${total}…`;
  }
  updateOverall();

  for (let i = 0; i < fileQueue.length; i++) {
    const file = fileQueue[i];
    if (file.size > 8 * 1024 * 1024) continue;
    const progWrap  = document.getElementById(`prog-${i}`);
    const progFill  = document.getElementById(`prog-fill-${i}`);
    const progLabel = document.getElementById(`prog-label-${i}`);
    const statusEl  = document.getElementById(`status-${i}`);
    if (progWrap) progWrap.style.display = 'flex';

    await new Promise(resolve => {
      const perFileSel = document.getElementById(`queue-tab-${i}`);
      const fd = new FormData();
      fd.append('csrf_token', CSRF);
      fd.append('tab_id', perFileSel ? perFileSel.value : tabSelect.value);
      fd.append('gallery_image', file, file.name);

      const xhr = new XMLHttpRequest();
      xhr.open('POST', 'upload.php');
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

      xhr.upload.addEventListener('progress', e => {
        if (!e.lengthComputable) return;
        const pct = Math.round((e.loaded / e.total) * 100);
        if (progFill)  progFill.style.width   = pct + '%';
        if (progLabel) progLabel.textContent   = pct + '%';
      });
      xhr.addEventListener('load', () => {
        let res = {};
        try { res = JSON.parse(xhr.responseText); } catch(e) {}
        if (res.ok) {
          if (progFill)  progFill.style.width  = '100%';
          if (progLabel) progLabel.textContent  = '✓';
          if (statusEl)  { statusEl.textContent = 'Uploaded'; statusEl.className = 'upload-queue-item__status text-success'; }
        } else {
          if (statusEl)  { statusEl.textContent = res.error || 'Failed'; statusEl.className = 'upload-queue-item__status text-error'; }
        }
        done++; updateOverall(); resolve();
      });
      xhr.addEventListener('error', () => {
        if (statusEl) { statusEl.textContent = 'Network error'; statusEl.className = 'upload-queue-item__status text-error'; }
        done++; updateOverall(); resolve();
      });
      xhr.send(fd);
    });
  }

  overallFill.style.width = '100%';
  overallLabel.textContent = `Done! ${done} of ${total} uploaded.`;
  btnStartUpload.textContent = '✓ Done — Click to Refresh';
  btnStartUpload.disabled = false;
  btnStartUpload.addEventListener('click', () => location.reload(), { once: true });
  showFlash(`${done} image(s) uploaded.`);
});
</script>
</body>
</html>
