<?php

declare(strict_types=1);

namespace App\Actions;

use App\Core\Database;
use App\Core\Session;

/**
 * Action class for handling file uploads with MIME validation.
 *
 * Validates MIME types, enforces size limits, and stores metadata.
 */
class HandleMediaUploadAction
{
    /** @var list<string> Allowed MIME types */
    private array $allowedMimes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    public function __construct()
    {
        // Database is accessed via singleton
    }

    /**
     * Execute the media upload action.
     *
     * @param array<string, mixed>|null $file $_FILES entry
     * @return string|null Stored file path or null on failure
     */
    public function execute(?array $file): ?string
    {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        $mimeType = $this->detectMimeType($file['tmp_name']);

        if (!in_array($mimeType, $this->allowedMimes, true)) {
            return null;
        }

        $maxSize = (int) ($_ENV['MAX_UPLOAD_SIZE'] ?? 10485760);
        if ($file['size'] > $maxSize) {
            return null;
        }

        $extension = $this->getExtension($mimeType);
        $filename = uniqid('upload_', true) . '.' . $extension;
        $uploadPath = $_ENV['UPLOAD_PATH'] ?? 'public/uploads';
        $destination = $uploadPath . '/' . $filename;

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return null;
        }

        // Store metadata
        $db = Database::getInstance();
        $db->insert('cms_media', [
            'filename' => $filename,
            'original_name' => $file['name'],
            'mime_type' => $mimeType,
            'size' => $file['size'],
            'path' => $destination,
            'uploaded_by' => Session::get('user_id'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $destination;
    }

    /**
     * Detect the actual MIME type of a file.
     *
     * @param string $path File path
     * @return string Detected MIME type
     */
    private function detectMimeType(string $path): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = (string) finfo_file($finfo, $path);
        finfo_close($finfo);
        return $mimeType;
    }

    /**
     * Get file extension from MIME type.
     *
     * @param string $mimeType
     * @return string
     */
    private function getExtension(string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => 'bin',
        };
    }
}
