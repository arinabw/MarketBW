<?php

// FILE: app/ProductImages.php
// VERSION: 3.12.2
// START_MODULE_CONTRACT
//   PURPOSE: Нормализация путей к фото товаров; конвертация data-URL в файлы на диске
//   SCOPE: normalizeFromForm, migrateStoredDataUrls, saveDataUrlToFile
//   DEPENDS: M-SETTINGS (images_dir), M-DATABASE (updateProduct при миграции)
//   LINKS: M-PRODUCT-IMAGES
// END_MODULE_CONTRACT
//
// START_MODULE_MAP
//   normalizeFromForm      — разбор поля формы: пути /images/…, data-URL → файлы
//   migrateStoredDataUrls  — миграция data-URL из БД в файлы при открытии формы
//   saveDataUrlToFile      — base64 data-URL → файл products/xxx.ext
// END_MODULE_MAP

declare(strict_types=1);

namespace App;

final class ProductImages
{
    private const MAX_BYTES = 8 * 1024 * 1024;

    /**
     * Разбор поля формы: пути `/images/...`, плюс конвертация data-URL в файлы.
     *
     * @return array{paths: list<string>, error: ?string}
     */
    public static function normalizeFromForm(string $raw, string $imagesDir): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $paths = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $resolved = self::resolveLine($line, $imagesDir);
            if ($resolved !== null) {
                $paths[] = $resolved;

                continue;
            }
            if (str_starts_with($line, 'data:')) {
                return [
                    'paths' => [],
                    'error' => 'Не удалось сохранить встроенное изображение (data URL). Загрузите файл через поле «Загрузить файлы» или укажите путь вида /images/products/…',
                ];
            }
            if (preg_match('#^https?://#i', $line)) {
                return [
                    'paths' => [],
                    'error' => 'Внешние ссылки не поддерживаются. Загрузите файл или укажите путь на сайте: /images/products/…',
                ];
            }

            return [
                'paths' => [],
                'error' => 'Недопустимая строка в путях к изображениям. Укажите только пути вида /images/… (по одному в строке).',
            ];
        }

        return ['paths' => $paths, 'error' => null];
    }

    /**
     * Убирает из БД огромные data-URL, сохраняя их как файлы (при открытии формы редактирования).
     *
     * @param array<string, mixed> $product
     *
     * @return array{0: array<string, mixed>, 1: bool}
     */
    public static function migrateStoredDataUrls(array $product, Database $db, string $imagesDir): array
    {
        $images = $product['images'] ?? [];
        if (!is_array($images)) {
            return [$product, false];
        }
        $new = [];
        $changed = false;
        foreach ($images as $img) {
            if (!is_string($img)) {
                continue;
            }
            if (str_starts_with($img, 'data:')) {
                $path = self::saveDataUrlToFile($img, $imagesDir);
                if ($path !== null) {
                    $new[] = $path;
                    $changed = true;
                } else {
                    $new[] = $img;
                }

                continue;
            }
            $new[] = $img;
        }
        if ($changed) {
            $db->updateProduct((string) $product['id'], images: $new);
            $product['images'] = $new;
        }

        return [$product, $changed];
    }

    private static function resolveLine(string $line, string $imagesDir): ?string
    {
        if (str_starts_with($line, 'data:')) {
            return self::saveDataUrlToFile($line, $imagesDir);
        }
        if (str_contains($line, '..')) {
            return null;
        }
        if (!str_starts_with($line, '/')) {
            $line = '/' . ltrim($line, '/');
        }
        if (!str_starts_with($line, '/images/')) {
            return null;
        }

        return $line;
    }

    public static function saveDataUrlToFile(string $line, string $imagesDir): ?string
    {
        if (!str_starts_with($line, 'data:')) {
            return null;
        }
        $rest = substr($line, strlen('data:'));
        $sep = strpos($rest, ';base64,');
        if ($sep === false) {
            return null;
        }
        $mime = strtolower(substr($rest, 0, $sep));
        if (!preg_match('#^(image/[\w.+-]+|application/octet-stream)$#', $mime)) {
            return null;
        }
        $b64 = substr($rest, $sep + strlen(';base64,'));
        $b64 = str_replace(["\r", "\n", ' '], '', $b64);
        if (strlen($b64) > (int) (self::MAX_BYTES * 4 / 3) + 1024) {
            return null;
        }
        $binary = base64_decode($b64, true);
        if ($binary === false || $binary === '') {
            return null;
        }
        if (strlen($binary) > self::MAX_BYTES) {
            return null;
        }
        $ext = self::extensionFromMime($mime);
        if ($ext === null) {
            $ext = self::extensionFromBinary($binary);
        }
        if ($ext === null) {
            return null;
        }
        if (!is_dir($imagesDir . '/products')) {
            mkdir($imagesDir . '/products', 0755, true);
        }
        $name = bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $imagesDir . '/products/' . $name;
        if (file_put_contents($dest, $binary) === false) {
            return null;
        }

        return '/images/products/' . $name;
    }

    private static function extensionFromMime(string $mime): ?string
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            default => null,
        };
    }

    private static function extensionFromBinary(string $binary): ?string
    {
        $len = strlen($binary);
        if ($len < 12) {
            return null;
        }
        if ($binary[0] === "\xFF" && $binary[1] === "\xD8") {
            return 'jpg';
        }
        if (substr($binary, 0, 8) === "\x89PNG\r\n\x1a\n") {
            return 'png';
        }
        if (substr($binary, 0, 6) === 'GIF87a' || substr($binary, 0, 6) === 'GIF89a') {
            return 'gif';
        }
        if ($len >= 12 && substr($binary, 0, 4) === 'RIFF' && substr($binary, 8, 4) === 'WEBP') {
            return 'webp';
        }
        if (str_contains(substr($binary, 0, min(500, $len)), '<svg')) {
            return 'svg';
        }

        return null;
    }
}
