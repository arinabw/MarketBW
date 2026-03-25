<?php

// FILE: config/settings.php
// VERSION: 3.12.4
// START_MODULE_CONTRACT
//   PURPOSE: Конфигурация приложения из env-переменных и дефолтов
//   SCOPE: site_name, contacts, social, paths, base_path, public_site_url, session, debug
//   DEPENDS: none
//   LINKS: M-SETTINGS
// END_MODULE_CONTRACT
//
// START_MODULE_MAP
//   return array — массив настроек приложения
// END_MODULE_MAP

declare(strict_types=1);

$root = dirname(__DIR__);

$versionFile = $root . DIRECTORY_SEPARATOR . 'VERSION';
$appVersion = '0.0.0';
if (is_readable($versionFile)) {
    $v = trim((string) file_get_contents($versionFile));
    if ($v !== '') {
        $appVersion = $v;
    }
}

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
    'app_version' => $appVersion,
    'displayErrorDetails' => filter_var($envStr('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN),
    /** Полный публичный URL сайта без завершающего слэша (https://site.ru или https://site.ru/shop). Для SEO: canonical, Open Graph, sitemap. Пусто — берётся из запроса (Host + X-Forwarded-*). */
    'public_site_url' => $envStr('PUBLIC_SITE_URL', 'https://marketbw.ru'),
    /** Префикс URL, если сайт не в корне домена (например `shop` для https://ex.com/shop/). Без слэшей по краям. */
    'base_path' => $envStr('BASE_PATH', ''),
    /** Необязательно: домен cookie сессии, например `.example.com` если и www, и apex ведут в одну админку. Пусто = по умолчанию PHP (только текущий хост). */
    'session_cookie_domain' => $envStr('SESSION_COOKIE_DOMAIN', ''),
    /** Прод за TLS-терминатором: всегда выставлять Secure на cookie сессии (если заголовки прокси не доходят до PHP). */
    'session_force_secure' => filter_var($envStr('SESSION_FORCE_SECURE', 'false'), FILTER_VALIDATE_BOOLEAN),
    'site_name' => $envStr('SITE_NAME', 'Bead Wonder'),
    'data_dir' => $envStr('DATA_DIR', $root . '/data'),
    'images_dir' => $envStr('IMAGES_DIR', $root . '/public/images'),
    'contact_email' => $envStr('CONTACT_EMAIL', 'your-email@example.com'),
    'contact_phone' => $envStr('CONTACT_PHONE', '+7 (999) 123-45-67'),
    'social_instagram' => $envStr('SOCIAL_INSTAGRAM', '#'),
    'social_telegram' => $envStr('SOCIAL_TELEGRAM', '#'),
    'social_vk' => $envStr('SOCIAL_VK', '#'),
    /** Только цифры, например 79991234567 — для wa.me */
    'contact_whatsapp' => $envStr('CONTACT_WHATSAPP', ''),
    'master_name' => $envStr('MASTER_NAME', 'Мастер'),
    'master_tagline' => $envStr('MASTER_TAGLINE', 'Украшения из бисера ручной работы'),
];
