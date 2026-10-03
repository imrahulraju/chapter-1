<?php
/* ============================================================
   Chapter 1 — AJAX Upload Handler v2
   Responds with JSON. Saves metadata to gallery.json.
   ============================================================ */
session_start();
header('Content-Type: application/json; charset=utf-8');

function jsonOut(array $data): never {
    echo json_encode($data);
    exit;
}

// Auth guard
if (empty($_SESSION['c1_admin_logged_in'])) {
    jsonOut(['ok' => false, 'error' => 'Not authenticated.']);
}

// AJAX only
if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
    jsonOut(['ok' => false, 'error' => 'Invalid request.']);
}

// CSRF
$csrfPost = $_POST['csrf_token'] ?? '';
if (!$csrfPost || $csrfPost !== ($_SESSION['csrf_token'] ?? '')) {
    jsonOut(['ok' => false, 'error' => 'CSRF validation failed.']);
}

require_once __DIR__ . '/gallery-data.php';

define('MAX_UPLOAD_SIZE', 8 * 1024 * 1024);
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp', 'gif']);

// Ensure upload dir
if (!is_dir(GALLERY_UPLOAD_DIR)) {
    mkdir(GALLERY_UPLOAD_DIR, 0755, true);
}

$tabId = trim($_POST['tab_id'] ?? '');

// Validate tab exists (allow empty = unassigned)
if ($tabId !== '') {
    $tabs     = galleryGetTabs();
    $validIds = array_column($tabs, 'id');
    if (!in_array($tabId, $validIds)) {
        jsonOut(['ok' => false, 'error' => 'Invalid tab.']);
    }
}

// Single file upload (called once per file from JS XHR loop)
$file = $_FILES['gallery_image'] ?? null;
if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    jsonOut(['ok' => false, 'error' => 'No file received.']);
}

$originalName = $file['name'];
$tmpPath      = $file['tmp_name'];
$uploadError  = $file['error'];
$size         = $file['size'];

// PHP upload error
if ($uploadError !== UPLOAD_ERR_OK) {
    $msg = match($uploadError) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File too large (max 8 MB).',
        UPLOAD_ERR_PARTIAL => 'File only partially uploaded.',
        default            => "Upload error (code {$uploadError}).",
    };
    jsonOut(['ok' => false, 'error' => $msg]);
}

// Size check
if ($size > MAX_UPLOAD_SIZE) {
    jsonOut(['ok' => false, 'error' => 'File exceeds 8 MB limit.']);
}

// Extension
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
if (!in_array($ext, ALLOWED_EXTENSIONS)) {
    jsonOut(['ok' => false, 'error' => "Unsupported file type: .{$ext}"]);
}

// Real MIME
$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $tmpPath);
finfo_close($finfo);
if (!in_array($mimeType, ALLOWED_MIME_TYPES)) {
    jsonOut(['ok' => false, 'error' => "Invalid MIME type: {$mimeType}"]);
}

// Safe filename + unique ID
$baseName  = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
$baseName  = substr($baseName, 0, 60);
$uid       = substr(uniqid('', true), -8);
$destFile  = $baseName . '_' . $uid . '.' . $ext;
$destPath  = GALLERY_UPLOAD_DIR . $destFile;

// Path traversal guard
$realDir = realpath(GALLERY_UPLOAD_DIR);
if (strpos(realpath(dirname($destPath . 'x')) . DIRECTORY_SEPARATOR, $realDir . DIRECTORY_SEPARATOR) !== 0) {
    jsonOut(['ok' => false, 'error' => 'Invalid destination path.']);
}

if (!move_uploaded_file($tmpPath, $destPath)) {
    jsonOut(['ok' => false, 'error' => 'Failed to save file. Check folder permissions.']);
}
chmod($destPath, 0644);

// Build clean alt text from filename
$altText = ucwords(str_replace(['_', '-'], ' ', $baseName));

// Save to gallery.json
$imageRecord = [
    'id'         => 'img_' . $uid . '_' . time(),
    'filename'   => $destFile,
    'path'       => GALLERY_UPLOAD_URL . $destFile,
    'tab'        => $tabId,
    'caption'    => '',
    'alt'        => 'Chapter 1 — ' . $altText,
    'uploaded'   => time(),
    'is_default' => false,
];

if (!galleryAddImage($imageRecord)) {
    // File was saved but metadata failed — clean up
    @unlink($destPath);
    jsonOut(['ok' => false, 'error' => 'Saved file but failed to update metadata.']);
}

jsonOut(['ok' => true, 'image' => $imageRecord]);
