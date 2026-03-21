<?php

/**
 * Сборка Slim 4: PHP-DI, Twig, middleware (сессия, CSRF, глобалы контента), маршруты из routes.php.
 *
 * @package MarketBW
 */

declare(strict_types=1);

use App\AuditLogMiddleware;
use App\Database;
use App\HttpsDetector;
use App\SiteContentDefaults;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require dirname(__DIR__) . '/vendor/autoload.php';

$settings = require dirname(__DIR__) . '/config/settings.php';

$twigCache = ($settings['displayErrorDetails'] ?? false) ? false : sys_get_temp_dir() . '/marketbw-twig';
if ($twigCache !== false && !is_dir($twigCache)) {
    @mkdir($twigCache, 0755, true);
}

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    'settings' => $settings,
    Database::class => fn () => new Database($settings['data_dir']),
    'twig' => function () use ($settings, $twigCache) {
        $twig = Twig::create(dirname(__DIR__) . '/templates', [
            'cache' => $twigCache,
            'auto_reload' => (bool) ($settings['displayErrorDetails'] ?? false),
        ]);
        $env = $twig->getEnvironment();
        $env->addGlobal('site_name', $settings['site_name']);
        $env->addGlobal('app_version', $settings['app_version'] ?? '0.0.0');
        $env->addGlobal('contact_email', $settings['contact_email']);
        $env->addGlobal('contact_phone', $settings['contact_phone']);
        $env->addGlobal('social_instagram', $settings['social_instagram']);
        $env->addGlobal('social_telegram', $settings['social_telegram']);
        $env->addGlobal('social_vk', $settings['social_vk']);
        $env->addGlobal('contact_whatsapp', $settings['contact_whatsapp']);
        $env->addGlobal('master_name', $settings['master_name']);
        $env->addGlobal('master_tagline', $settings['master_tagline']);
        $env->addFunction(new \Twig\TwigFunction('csrf_token', function (): string {
            return $_SESSION['csrf'] ?? '';
        }));
        $env->addFilter(new \Twig\TwigFilter('price_rub', function ($v): string {
            return number_format((float) $v, 0, ',', ' ') . ' ₽';
        }));
        $env->addFunction(new \Twig\TwigFunction('nav_is_active', function (string $path): bool {
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            $cur = parse_url($uri, PHP_URL_PATH) ?: '/';
            if ($path === '/') {
                return $cur === '/' || $cur === '';
            }
            return str_starts_with($cur, $path);
        }));
        $env->addFunction(new \Twig\TwigFunction('phone_digits', function (string $phone): string {
            return preg_replace('/\D+/', '', $phone) ?: '';
        }));
        $env->addFunction(new \Twig\TwigFunction('t', function (string $key) use ($env): string {
            $content = $env->getGlobals()['content'] ?? [];

            return (string) ($content[$key] ?? '');
        }));
        $env->addFunction(new \Twig\TwigFunction('content_label', function (string $key): string {
            return SiteContentDefaults::fieldLabel($key);
        }));
        $env->addFunction(new \Twig\TwigFunction('whatsapp_url', function () use ($env): string {
            $g = $env->getGlobals();
            $d = preg_replace('/\D+/', '', (string) ($g['contact_whatsapp'] ?? ''));

            return $d !== '' ? 'https://wa.me/' . $d : '#';
        }));
        $env->addFilter(new \Twig\TwigFilter('stars', function ($rating): string {
            $r = max(0, min(5, (int) $rating));
            return str_repeat('★', $r) . str_repeat('☆', 5 - $r);
        }));
        $env->addFunction(new \Twig\TwigFunction('catalog_url', function (?string $category = null, ?string $search = null, ?string $sort = null): string {
            $params = [];
            if ($category !== null && $category !== '') {
                $params['category'] = $category;
            }
            if ($search !== null && $search !== '') {
                $params['q'] = $search;
            }
            if ($sort !== null && $sort !== '' && $sort !== 'date_desc') {
                $params['sort'] = $sort;
            }
            $qs = http_build_query($params);
            return $qs !== '' ? '/catalog?' . $qs : '/catalog';
        }));
        return $twig;
    },
]);

$container = $containerBuilder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$basePath = trim((string) ($settings['base_path'] ?? ''), '/');
if ($basePath !== '') {
    $app->setBasePath('/' . $basePath);
}

$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();

$twig = $container->get('twig');
$app->add(TwigMiddleware::create($app, $twig));

// Не static: Slim привязывает замыкание к контейнеру; static ломает MiddlewareDispatcher.
$app->add(function ($request, $handler) use ($settings) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        $https = HttpsDetector::fromServer($_SERVER)
            || (bool) ($settings['session_force_secure'] ?? false);
        $basePath = trim((string) ($settings['base_path'] ?? ''), '/');
        $cookiePath = $basePath !== '' ? '/' . $basePath : '/';
        $sessionDir = rtrim((string) ($settings['data_dir']), '/') . '/sessions';
        if (!is_dir($sessionDir)) {
            @mkdir($sessionDir, 0700, true);
        }
        if (is_dir($sessionDir) && is_writable($sessionDir)) {
            session_save_path($sessionDir);
        }
        $sessionOpts = [
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'cookie_secure' => $https,
            'cookie_path' => $cookiePath,
        ];
        $cookieDomain = trim((string) ($settings['session_cookie_domain'] ?? ''));
        if ($cookieDomain !== '') {
            $sessionOpts['cookie_domain'] = $cookieDomain;
        }
        session_start($sessionOpts);
    }
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $handler->handle($request);
});

$app->addErrorMiddleware(
    (bool) ($settings['displayErrorDetails'] ?? false),
    true,
    true
);

$container->get(Database::class)->init();

(require __DIR__ . '/routes.php')($app, $container);

$app->add(function ($request, $handler) use ($container) {
    $settings = $container->get('settings');
    $merged = $container->get(Database::class)->getMergedSiteContent($settings);
    $env = $container->get('twig')->getEnvironment();
    $env->addGlobal('content', $merged);
    $env->addGlobal('master_name', $merged['brand.master_name'] ?? $settings['master_name']);
    $env->addGlobal('master_tagline', $merged['brand.tagline'] ?? $settings['master_tagline']);
    $env->addGlobal('contact_email', $merged['contact.email'] ?? $settings['contact_email']);
    $env->addGlobal('contact_phone', $merged['contact.phone'] ?? $settings['contact_phone']);
    $env->addGlobal('contact_whatsapp', $merged['contact.whatsapp'] ?? $settings['contact_whatsapp']);
    $env->addGlobal('social_instagram', $merged['social.instagram'] ?? $settings['social_instagram']);
    $env->addGlobal('social_telegram', $merged['social.telegram'] ?? $settings['social_telegram']);
    $env->addGlobal('social_vk', $merged['social.vk'] ?? $settings['social_vk']);

    return $handler->handle($request);
});

$app->add(new AuditLogMiddleware($container->get(Database::class), $settings));

return $app;
