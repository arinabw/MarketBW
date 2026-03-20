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
    Database::class => static fn () => new Database($settings['data_dir']),
    'twig' => static function () use ($settings, $twigCache) {
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
        $env->addFunction(new \Twig\TwigFunction('csrf_token', static function (): string {
            return $_SESSION['csrf'] ?? '';
        }));
        $env->addFilter(new \Twig\TwigFilter('price_rub', static function ($v): string {
            return number_format((float) $v, 0, ',', ' ') . ' ₽';
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
