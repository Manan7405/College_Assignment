<?php
/**
 * Product picture upload helper (used by admin/product_form.php).
 *
 * Steps performed for every upload:
 *   1. check there was no upload error
 *   2. check the file size (max 2 MB)
 *   3. check it is REALLY an image (getimagesize) of type JPEG / PNG / WebP
 *   4. create a unique file name
 *   5. resize with GD (keeps aspect ratio, max 800 px) and save
 */
require_once __DIR__ . '/../config/config.php';

/** Is the GD extension enabled? (php.ini -> extension=gd) */
function gd_available(): bool
{
    return extension_loaded('gd') && function_exists('imagecreatetruecolor');
}

function upload_error_message(int $code): string
{
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'The picture is larger than the allowed size.';
        case UPLOAD_ERR_PARTIAL:
            return 'The picture was only partly uploaded. Please try again.';
        case UPLOAD_ERR_NO_TMP_DIR:
        case UPLOAD_ERR_CANT_WRITE:
            return 'The server could not save the picture (temporary folder problem).';
        default:
            return 'The picture could not be uploaded.';
    }
}

/**
 * Handles one uploaded picture.
 *
 * Return value:
 *   ''      -> no file was chosen (not an error)
 *   string  -> the new unique file name (success)
 *   null    -> failure; the reason is placed in $error
 */
function save_product_image(array $file, ?string &$error)
{
    $error = null;

    // Nothing chosen
    if (!isset($file['error']) || is_array($file['error'])) {
        $error = 'Invalid upload.';
        return null;
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = upload_error_message($file['error']);
        return null;
    }

    // Size check
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        $error = 'The picture is too large. Maximum allowed size is ' . (MAX_UPLOAD_SIZE / 1024 / 1024) . ' MB.';
        return null;
    }

    // Type check: getimagesize() reads the real file content, not just the extension
    $info = @getimagesize($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if ($info === false || !isset($allowed[$info['mime']])) {
        $error = 'Invalid picture. Only JPEG, PNG and WebP images are allowed.';
        return null;
    }
    $mime = $info['mime'];
    $ext  = $allowed[$mime];

    // Make sure the uploads folder exists
    if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0755, true)) {
        $error = 'The uploads folder does not exist and could not be created.';
        return null;
    }

    // Unique file name: product_20260101_101530_a1b2c3d4.jpg  (never overwrites)
    $filename    = 'product_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destination = UPLOAD_DIR . $filename;

    if (gd_available()) {
        // Resize + re-encode. Re-encoding also removes any hidden non-image data.
        if (!resize_and_save($file['tmp_name'], $destination, $mime, MAX_IMAGE_DIMENSION)) {
            $error = 'The picture could not be processed. It may be damaged or WebP may not be supported by your GD.';
            return null;
        }
    } else {
        // GD is missing: save the checked file without resizing.
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $error = 'The picture could not be saved.';
            return null;
        }
    }
    return $filename;
}

/**
 * Reads an image with GD, shrinks it so neither side is longer than $max
 * (aspect ratio is kept) and writes it to $destination.
 */
function resize_and_save(string $source, string $destination, string $mime, int $max): bool
{
    switch ($mime) {
        case 'image/jpeg':
            $image = @imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = @imagecreatefrompng($source);
            break;
        case 'image/webp':
            if (!function_exists('imagecreatefromwebp')) {
                return false;
            }
            $image = @imagecreatefromwebp($source);
            break;
        default:
            return false;
    }
    if (!$image) {
        return false;
    }

    $width  = imagesx($image);
    $height = imagesy($image);

    // Only shrink - never enlarge small pictures
    if ($width > $max || $height > $max) {
        $ratio     = min($max / $width, $max / $height);
        $newWidth  = max(1, (int)round($width * $ratio));
        $newHeight = max(1, (int)round($height * $ratio));

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        // keep transparent background for PNG and WebP
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        $image = $resized;
    }

    switch ($mime) {
        case 'image/jpeg':
            return imagejpeg($image, $destination, 85);
        case 'image/png':
            return imagepng($image, $destination, 6);
        case 'image/webp':
            return function_exists('imagewebp') ? imagewebp($image, $destination, 85) : false;
    }
    return false;
}

/** Deletes a picture file from the uploads folder (ignores sample-safe errors). */
function delete_product_image(?string $filename): void
{
    if ($filename === null || $filename === '') {
        return;
    }
    $path = UPLOAD_DIR . basename($filename);   // basename() blocks "../" tricks
    if (is_file($path)) {
        @unlink($path);
    }
}
