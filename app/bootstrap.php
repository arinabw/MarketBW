<?php

// FILE: app/bootstrap.php
// VERSION: 3.12.4
// START_MODULE_CONTRACT
//   PURPOSE: DI-контейнер, Twig, middleware (сессия, CSRF, CMS-контент), Database::init(), подключение routes.php
//   SCOPE: создание Slim App, настройка контейнера, Twig-глобалы, CSRF, content middleware, SEO middleware
//   DEPENDS: M-SETTINGS, M-DATABASE, M-CONTENT-DEFAULTS, M-SEO
//   LINKS: M-BOOTSTRAP
// END_MODULE_CONTRACT
//
// START_MODULE_MAP
//   $container    — DI-контейнер с settings, Database, Twig
//   $app          — настроенный Slim\App (возвращается для index.php)
//   contentMiddleware — middleware: слияние CMS-ключей + Twig-глобалы
//   csrfHelper        — генерация и проверка CSRF-токена
// END_MODULE_MAP

declare(strict_types=1);

use App\AuditLogMiddleware;
use App\Database;
use App\HttpsDetector;
use App\SeoHelper;
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

// START_BLOCK_DI_CONTAINER
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
        $env->addFunction(new \Twig\TwigFunction('path', function (string $p) use ($settings): string {
            $p = '/' . ltrim($p, '/');
            $bp = trim((string) ($settings['base_path'] ?? ''), '/');

            return ($bp !== '' ? '/' . $bp : '') . $p;
        }));
        $env->addFunction(new \Twig\TwigFunction('nav_is_active', function (string $path) use ($settings): bool {
            $bp = trim((string) ($settings['base_path'] ?? ''), '/');
            $prefix = $bp !== '' ? '/' . $bp : '';
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            $cur = parse_url($uri, PHP_URL_PATH) ?: '/';
            if ($prefix !== '' && str_starts_with($cur, $prefix)) {
                $cur = substr($cur, strlen($prefix));
                if ($cur === '' || $cur === false) {
                    $cur = '/';
                } elseif (!str_starts_with($cur, '/')) {
                    $cur = '/' . $cur;
                }
            }
            $path = '/' . ltrim($path, '/');
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
        $env->addFilter(new \Twig\TwigFilter('stars', function ($rating): string {
            $r = max(0, min(5, (int) $rating));
            return str_repeat('★', $r) . str_repeat('☆', 5 - $r);
        }));
        $env->addFunction(new \Twig\TwigFunction('catalog_url', function (?string $category = null, ?string $search = null, ?string $sort = null) use ($settings): string {
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
            $bp = trim((string) ($settings['base_path'] ?? ''), '/');
            $pref = $bp !== '' ? '/' . $bp : '';
            $base = $pref . '/catalog';
            $qs = http_build_query($params);

            return $qs !== '' ? $base . '?' . $qs : $base;
        }));
        $env->addFunction(new \Twig\TwigFunction('layout_visible', function (\Twig\Environment $twigEnv, string $key): bool {
            $c = (string) (($twigEnv->getGlobals()['content'] ?? [])[$key] ?? '1');
            $v = strtolower(trim($c));

            return $v === '' || $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on';
        }, ['needs_environment' => true]));
        $env->addFunction(new \Twig\TwigFunction('layout_any_visible', function (\Twig\Environment $twigEnv, iterable $keys): bool {
            $content = (array) (($twigEnv->getGlobals()['content'] ?? []));
            $isOn = static function (string $key) use ($content): bool {
                $c = (string) ($content[$key] ?? '1');
                $v = strtolower(trim($c));

                return $v === '' || $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on';
            };
            foreach ($keys as $key) {
                if ($isOn((string) $key)) {
                    return true;
                }
            }

            return false;
        }, ['needs_environment' => true]));
        $env->addFunction(new \Twig\TwigFunction('absolute_url', function (\Twig\Environment $twigEnv, string $path): string {
            $base = (string) ($twigEnv->getGlobals()['seo_absolute_base'] ?? '');
            $path = '/' . ltrim($path, '/');

            return $base !== '' ? rtrim($base, '/') . $path : $path;
        }, ['needs_environment' => true]));
        return $twig;
    },
]);

$container = $containerBuilder->build();
// END_BLOCK_DI_CONTAINER

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

// START_BLOCK_DB_INIT
$container->get(Database::class)->init();
// END_BLOCK_DB_INIT

(require __DIR__ . '/routes.php')($app, $container);

// START_BLOCK_CONTENT_MIDDLEWARE
$app->add(function ($request, $handler) use ($container, $app) {
    $settings = $container->get('settings');
    $merged = $container->get(Database::class)->getMergedSiteContent($settings);
    $env = $container->get('twig')->getEnvironment();
    $env->addGlobal('content', $merged);
    $env->addGlobal('master_name', $merged['brand.master_name'] ?? $settings['master_name']);
    $env->addGlobal('master_tagline', $merged['brand.tagline'] ?? $settings['master_tagline']);
    $env->addGlobal('contact_email', $merged['contact.email'] ?? $settings['contact_email']);

    $base = SeoHelper::resolvePublicBase($request, $settings, $app->getBasePath());
    $path = $request->getUri()->getPath();
    $canonical = $base !== '' ? rtrim($base, '/') . $path : $path;
    $env->addGlobal('seo_absolute_base', $base);
    $env->addGlobal('seo_canonical_url', $canonical);
    $hero = (string) ($merged['home.hero_image'] ?? '');
    $ogDefault = ($hero !== '' && str_starts_with($hero, '/') && $base !== '')
        ? rtrim($base, '/') . $hero
        : '';
    $env->addGlobal('seo_default_og_image', $ogDefault);
    $homeUrl = $base !== '' ? rtrim($base, '/') . '/' : '';
    $orgName = (string) ($merged['brand.master_name'] ?? $settings['site_name']);
    $orgDesc = (string) ($merged['meta.description'] ?? '');
    $kwMeta = trim((string) ($merged['meta.keywords'] ?? ''));
    $env->addGlobal(
        'seo_organization_json_ld',
        $homeUrl !== ''
            ? SeoHelper::buildOrganizationJsonLd(
                $orgName,
                $orgDesc,
                $homeUrl,
                '',
                (string) ($merged['contact.email'] ?? $settings['contact_email']),
                [],
                SeoHelper::thematicKnowsAbout(),
                $kwMeta !== '' ? $kwMeta : null,
                $base !== '' ? rtrim($base, '/') . '/favicon.svg' : '',
            )
            : ''
    );
    $bp = trim($app->getBasePath(), '/');
    $searchTpl = $base !== '' ? rtrim($base, '/') . ($bp !== '' ? '/' . $bp : '') . '/catalog?q={search_term_string}' : '';
    $env->addGlobal(
        'seo_website_json_ld',
        $homeUrl !== ''
            ? SeoHelper::buildWebSiteJsonLd(
                (string) ($settings['site_name'] ?? ''),
                $homeUrl,
                $orgDesc,
                $searchTpl !== '' ? $searchTpl : null,
            )
            : ''
    );

    return $handler->handle($request);
});
// END_BLOCK_CONTENT_MIDDLEWARE

$app->add(new AuditLogMiddleware($container->get(Database::class), $settings));

return $app;
