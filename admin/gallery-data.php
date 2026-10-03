<?php
/* ============================================================
   Chapter 1 — Gallery Data Helper
   Single source of truth for reading/writing gallery.json
   ============================================================ */

define('GALLERY_JSON',   dirname(__DIR__) . '/assets/data/gallery.json');
define('GALLERY_UPLOAD_DIR', dirname(__DIR__) . '/assets/images/gallery/');
define('GALLERY_UPLOAD_URL', 'assets/images/gallery/');

/* ── Read ─────────────────────────────────────────────────── */
function galleryRead(): array {
    if (!file_exists(GALLERY_JSON)) {
        return ['tabs' => [], 'images' => []];
    }
    $raw  = file_get_contents(GALLERY_JSON);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : ['tabs' => [], 'images' => []];
}

/* ── Write (atomic) ───────────────────────────────────────── */
function galleryWrite(array $data): bool {
    $dir = dirname(GALLERY_JSON);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $tmp = GALLERY_JSON . '.tmp.' . uniqid();
    $ok  = file_put_contents($tmp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if ($ok === false) return false;
    return rename($tmp, GALLERY_JSON);
}

/* ── Get all tabs ─────────────────────────────────────────── */
function galleryGetTabs(): array {
    return galleryRead()['tabs'] ?? [];
}

/* ── Get images, optionally by tab ───────────────────────── */
function galleryGetImages(?string $tabId = null): array {
    $data   = galleryRead();
    $images = $data['images'] ?? [];
    if ($tabId !== null) {
        $images = array_values(array_filter($images, fn($img) => ($img['tab'] ?? '') === $tabId));
    }
    // Sort: newest first
    usort($images, fn($a, $b) => ($b['uploaded'] ?? 0) - ($a['uploaded'] ?? 0));
    return $images;
}

/* ── Get public URL for an image ──────────────────────────── */
function galleryImageUrl(array $img, string $prefix = ''): string {
    // Default images use assets/images/gallery-X.jpg
    if (!empty($img['is_default'])) {
        return $prefix . $img['path'];
    }
    return $prefix . GALLERY_UPLOAD_URL . rawurlencode($img['filename']);
}

/* ── Add tab ──────────────────────────────────────────────── */
function galleryAddTab(string $name): array {
    $data = galleryRead();
    $id   = preg_replace('/[^a-z0-9\-]/', '-', strtolower(trim($name)));
    $id   = preg_replace('/-+/', '-', $id);
    $id   = trim($id, '-');
    if (!$id) $id = 'tab-' . time();
    // Ensure unique
    $existing = array_column($data['tabs'], 'id');
    if (in_array($id, $existing)) $id .= '-' . substr(uniqid(), -4);
    $tab = ['id' => $id, 'name' => trim($name), 'created' => time()];
    $data['tabs'][] = $tab;
    galleryWrite($data);
    return $tab;
}

/* ── Rename tab ───────────────────────────────────────────── */
function galleryRenameTab(string $tabId, string $newName): bool {
    $data = galleryRead();
    foreach ($data['tabs'] as &$tab) {
        if ($tab['id'] === $tabId) {
            $tab['name'] = trim($newName);
            return galleryWrite($data);
        }
    }
    return false;
}

/* ── Delete tab (moves images to "uncategorised") ─────────── */
function galleryDeleteTab(string $tabId): bool {
    $data = galleryRead();
    $data['tabs'] = array_values(array_filter($data['tabs'], fn($t) => $t['id'] !== $tabId));
    foreach ($data['images'] as &$img) {
        if (($img['tab'] ?? '') === $tabId) $img['tab'] = '';
    }
    return galleryWrite($data);
}

/* ── Add image record ─────────────────────────────────────── */
function galleryAddImage(array $img): bool {
    $data = galleryRead();
    $data['images'][] = $img;
    return galleryWrite($data);
}

/* ── Delete image record + file ───────────────────────────── */
function galleryDeleteImage(string $imageId): bool {
    $data  = galleryRead();
    $found = null;
    $data['images'] = array_values(array_filter($data['images'], function($img) use ($imageId, &$found) {
        if ($img['id'] === $imageId) { $found = $img; return false; }
        return true;
    }));
    if (!$found) return false;
    // Delete the physical file (but NOT default images)
    if (empty($found['is_default'])) {
        $path = GALLERY_UPLOAD_DIR . $found['filename'];
        if (file_exists($path)) @unlink($path);
    }
    return galleryWrite($data);
}

/* ── Reassign image to a different tab ────────────────────── */
function galleryMoveImage(string $imageId, string $tabId): bool {
    $data = galleryRead();
    foreach ($data['images'] as &$img) {
        if ($img['id'] === $imageId) {
            $img['tab'] = $tabId;
            return galleryWrite($data);
        }
    }
    return false;
}

/* ── Update image caption ─────────────────────────────────── */
function galleryUpdateCaption(string $imageId, string $caption): bool {
    $data = galleryRead();
    foreach ($data['images'] as &$img) {
        if ($img['id'] === $imageId) {
            $img['caption'] = trim($caption);
            return galleryWrite($data);
        }
    }
    return false;
}

/* ── Format bytes ─────────────────────────────────────────── */
function fmtBytes(int $bytes): string {
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}
