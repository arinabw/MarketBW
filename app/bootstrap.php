<?php

declare(strict_types=1);

use App\Database;
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
        $env->addFunction(new \Twig\TwigFunction('whatsapp_url', function () use ($settings): string {
            $d = preg_replace('/\D+/', '', $settings['contact_whatsapp'] ?? '');
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

$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();

$twig = $container->get('twig');
$app->add(TwigMiddleware::create($app, $twig));

// Не static: Slim привязывает замыкание к контейнеру; static ломает MiddlewareDispatcher.
$app->add(function ($request, $handler) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
        ]);
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

return $app;
