<?php

namespace App\Core;

final class Uploader
{
    public static function store(string $field, string $directory, array $allowedMimes, ?int $maxMb = null): ?string
    {
        $file = $_FILES[$field] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload failed. Please try again.');
        }

        $maxBytes = (($maxMb ?? (int) config('app.upload_max_mb')) * 1024 * 1024);
        if (($file['size'] ?? 0) > $maxBytes) {
            throw new \RuntimeException('File is larger than the allowed upload size.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $allowedMimes, true)) {
            throw new \RuntimeException('Unsupported file type.');
        }

        $targetDir = public_path('uploads/' . trim($directory, '/'));
        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            throw new \RuntimeException('Unable to prepare upload directory.');
        }

        $extension = self::extensionForMime($mime, pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $filename = bin2hex(random_bytes(18)) . '.' . $extension;
        $target = $targetDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new \RuntimeException('Could not save uploaded file.');
        }

        return 'uploads/' . trim($directory, '/') . '/' . $filename;
    }

    private static function extensionForMime(string $mime, string $fallback): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            default => strtolower($fallback ?: 'bin'),
        };
    }
}
