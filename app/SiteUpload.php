<?php

// FILE: app/SiteUpload.php
// VERSION: 3.12.2
// START_MODULE_CONTRACT
//   PURPOSE: Загрузка картинок CMS-контента в public/images/site/
//   SCOPE: saveImage — валидация расширения, перемещение файла
//   DEPENDS: M-SETTINGS (images_dir)
//   LINKS: M-SITE-UPLOAD
// END_MODULE_CONTRACT
//
// START_MODULE_MAP
//   saveImage — обработка загрузки файла для CMS-ключа → /images/site/xxx.ext
// END_MODULE_MAP

declare(strict_types=1);

namespace App;

final class SiteUpload
{
    /**
     * @param array<string, mixed>|null $file $_FILES['name']
     */
    public static function saveImage(?array $file, string $imagesDir): ?string
    {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            return null;
        }
        $dir = rtrim($imagesDir, '/') . '/site';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $dir . '/' . $name;
        if (!move_uploaded_file((string) $file['tmp_name'], $dest)) {
            return null;
        }

        return '/images/site/' . $name;
    }
}
