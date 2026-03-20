<?php

declare(strict_types=1);

$root = dirname(__DIR__);

/** @return non-empty-string */
$envStr = static function (string $key, string $default): string {
    $try = [getenv($key), $_SERVER[$key] ?? null, $_ENV[$key] ?? null];
    foreach ($try as $v) {
        if ($v !== false && $v !== null && $v !== '') {
            return (string) $v;
        }
    }
    return $default;
};

return [
    'displayErrorDetails' => filter_var($envStr('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN),
    'site_name' => $envStr('SITE_NAME', 'Bead Wonder'),
    'data_dir' => $envStr('DATA_DIR', $root . '/data'),
    'images_dir' => $envStr('IMAGES_DIR', $root . '/public/images'),
    'contact_email' => $envStr('CONTACT_EMAIL', 'your-email@example.com'),
    'contact_phone' => $envStr('CONTACT_PHONE', '+7 (999) 123-45-67'),
    'social_instagram' => $envStr('SOCIAL_INSTAGRAM', '#'),
    'social_telegram' => $envStr('SOCIAL_TELEGRAM', '#'),
    'social_vk' => $envStr('SOCIAL_VK', '#'),
];
