<?php
/* ============================================================
   Chapter 1 — Tab & Image AJAX Actions
   All actions respond with JSON.
   ============================================================ */
session_start();
header('Content-Type: application/json; charset=utf-8');

function jsonOut(array $data): never {
    echo json_encode($data);
    exit;
}

// Auth
if (empty($_SESSION['c1_admin_logged_in'])) {
    jsonOut(['ok' => false, 'error' => 'Not authenticated.']);
}

// AJAX only
if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
    jsonOut(['ok' => false, 'error' => 'Invalid request.']);
}

require_once __DIR__ . '/gallery-data.php';

// Parse JSON body
$raw  = file_get_contents('php://input');
$body = json_decode($raw, true) ?? [];

// CSRF
$csrf = $body['csrf_token'] ?? '';
if (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
    jsonOut(['ok' => false, 'error' => 'CSRF validation failed.']);
}

$action = $body['action'] ?? '';

switch ($action) {

    /* ── Create tab ────────────────────────────────────── */
    case 'create': {
        $name = trim($body['name'] ?? '');
        if (!$name || mb_strlen($name) > 60) {
            jsonOut(['ok' => false, 'error' => 'Invalid tab name.']);
        }
        $tab = galleryAddTab($name);
        jsonOut(['ok' => true, 'tab' => $tab]);
    }

    /* ── Rename tab ────────────────────────────────────── */
    case 'rename': {
        $id   = trim($body['id']   ?? '');
        $name = trim($body['name'] ?? '');
        if (!$id || !$name || mb_strlen($name) > 60) {
            jsonOut(['ok' => false, 'error' => 'Invalid parameters.']);
        }
        $ok = galleryRenameTab($id, $name);
        jsonOut(['ok' => $ok, 'error' => $ok ? null : 'Tab not found.']);
    }

    /* ── Delete tab ────────────────────────────────────── */
    case 'delete': {
        $id = trim($body['id'] ?? '');
        if (!$id) jsonOut(['ok' => false, 'error' => 'Missing tab ID.']);
        $ok = galleryDeleteTab($id);
        jsonOut(['ok' => $ok, 'error' => $ok ? null : 'Tab not found.']);
    }

    /* ── Move image to tab ─────────────────────────────── */
    case 'move_image': {
        $imgId = trim($body['image_id'] ?? '');
        $tabId = trim($body['tab_id']   ?? '');
        if (!$imgId) jsonOut(['ok' => false, 'error' => 'Missing image ID.']);
        // Validate tab (allow empty = unassign)
        if ($tabId !== '') {
            $tabs  = galleryGetTabs();
            $valid = array_column($tabs, 'id');
            if (!in_array($tabId, $valid)) jsonOut(['ok' => false, 'error' => 'Invalid tab.']);
        }
        $ok = galleryMoveImage($imgId, $tabId);
        jsonOut(['ok' => $ok, 'error' => $ok ? null : 'Image not found.']);
    }

    /* ── Update caption ────────────────────────────────── */
    case 'update_caption': {
        $imgId   = trim($body['image_id'] ?? '');
        $caption = trim($body['caption']  ?? '');
        if (!$imgId) jsonOut(['ok' => false, 'error' => 'Missing image ID.']);
        $ok = galleryUpdateCaption($imgId, $caption);
        jsonOut(['ok' => $ok, 'error' => $ok ? null : 'Image not found.']);
    }

    /* ── Delete image ──────────────────────────────────── */
    case 'delete_image': {
        $imgId = trim($body['image_id'] ?? '');
        if (!$imgId) jsonOut(['ok' => false, 'error' => 'Missing image ID.']);
        $ok = galleryDeleteImage($imgId);
        jsonOut(['ok' => $ok, 'error' => $ok ? null : 'Image not found.']);
    }

    default:
        jsonOut(['ok' => false, 'error' => "Unknown action: {$action}"]);
}
