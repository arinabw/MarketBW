<?php

/**
 * Регистрация всех маршрутов и вспомогательных функций загрузки файлов (товары, категории).
 *
 * Подключается из app/bootstrap.php. Админка: группа `/admin` с проверкой сессии.
 *
 * @package MarketBW
 */

declare(strict_types=1);

use App\Database;
use App\DatabaseExcelExport;
use App\SeoHelper;
use App\ProductImages;
use App\SiteContentDefaults;
use App\SiteUpload;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Psr7\Response as SlimResponse;
use Slim\Routing\RouteCollectorProxy;

return function (App $app, ContainerInterface $container): void {
    $db = fn (): Database => $container->get(Database::class);
    $view = fn () => $container->get('twig');
    $settings = fn (): array => $container->get('settings');

    $withBase = static function (string $path) use ($app): string {
        $path = '/' . ltrim($path, '/');
        $bp = $app->getBasePath();

        return ($bp !== '' ? rtrim($bp, '/') : '') . $path;
    };

    $app->get('/favicon.ico', function (Request $request, Response $response) use ($withBase): Response {
        return $response->withHeader('Location', $withBase('/favicon.svg'))->withStatus(308);
    });

    $app->get('/robots.txt', function (Request $request, Response $response) use ($app, $settings, $withBase): Response {
        $base = SeoHelper::resolvePublicBase($request, $settings(), $app->getBasePath());
        $bp = trim($app->getBasePath(), '/');
        $adminPrefix = ($bp !== '' ? '/' . $bp : '') . '/admin';
        $sitemapUrl = $base !== '' ? (rtrim($base, '/') . $withBase('/sitemap.xml')) : $withBase('/sitemap.xml');
        $lines = [
            'User-agent: *',
            'Allow: /',
            '',
            'Disallow: ' . $adminPrefix,
            '',
            'Sitemap: ' . $sitemapUrl,
        ];
        $response->getBody()->write(implode("\n", $lines));

        return $response->withHeader('Content-Type', 'text/plain; charset=UTF-8');
    });

    $app->get('/sitemap.xml', function (Request $request, Response $response) use ($app, $db, $settings, $withBase): Response {
        $base = SeoHelper::resolvePublicBase($request, $settings(), $app->getBasePath());
        if ($base === '') {
            $response->getBody()->write('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');

            return $response->withHeader('Content-Type', 'application/xml; charset=UTF-8');
        }
        $urls = [];
        $add = static function (string $path, string $changefreq = 'weekly', string $priority = '0.8') use (&$urls, $base, $withBase): void {
            $loc = htmlspecialchars(rtrim($base, '/') . $withBase($path), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $urls[] = '<url><loc>' . $loc . '</loc><changefreq>' . $changefreq . '</changefreq><priority>' . $priority . '</priority></url>';
        };
        $add('/', 'daily', '1.0');
        $add('/catalog', 'daily', '0.9');
        $add('/about', 'monthly', '0.7');
        $add('/contact', 'monthly', '0.8');
        $add('/faq', 'weekly', '0.7');
        foreach ($db()->categories() as $c) {
            if (!isset($c['id'])) {
                continue;
            }
            $add('/catalog?' . http_build_query(['category' => (string) $c['id']]), 'weekly', '0.8');
        }
        foreach ($db()->allProductIds() as $pid) {
            $add('/product/' . rawurlencode($pid), 'weekly', '0.9');
        }
        $body = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
            . implode("\n", $urls) . "\n</urlset>";
        $response->getBody()->write($body);

        return $response->withHeader('Content-Type', 'application/xml; charset=UTF-8');
    });

    $app->get('/', function (Request $request, Response $response) use ($db, $view): Response {
        $database = $db();
        $featured = $database->featuredProducts(8);
        $categories = $database->categories();
        $reviews = array_slice($database->reviews(), 0, 3);
        return $view()->render($response, 'home.twig', [
            'featured' => $featured,
            'categories' => $categories,
            'reviews' => $reviews,
        ]);
    });

    $app->get('/catalog', function (Request $request, Response $response) use ($db, $view): Response {
        $qp = $request->getQueryParams();
        $cat = isset($qp['category']) ? (string) $qp['category'] : null;
        $search = isset($qp['q']) ? trim((string) $qp['q']) : null;
        $search = ($search !== null && $search !== '') ? $search : null;
        $sort = isset($qp['sort']) ? (string) $qp['sort'] : 'date_desc';
        if (!in_array($sort, ['date_desc', 'price_asc', 'price_desc'], true)) {
            $sort = 'date_desc';
        }
        $database = $db();
        return $view()->render($response, 'catalog.twig', [
            'products' => $database->products($cat, $search, $sort),
            'categories' => $database->categories(),
            'active_category' => $cat,
            'search_q' => $search ?? '',
            'sort' => $sort,
        ]);
    });

    $app->get('/about', function (Request $request, Response $response) use ($view): Response {
        return $view()->render($response, 'about.twig', []);
    });

    $app->get('/product/{id}', function (Request $request, Response $response, array $args) use ($db, $view, $settings, $app, $withBase): Response {
        $database = $db();
        $product = $database->productById((string) $args['id']);
        if (!$product) {
            return $view()->render($response->withStatus(404), '404.twig', []);
        }
        $categoryName = null;
        foreach ($database->categories() as $c) {
            if ($c['id'] === $product['category']) {
                $categoryName = $c['name'];
                break;
            }
        }
        $pub = SeoHelper::resolvePublicBase($request, $settings(), $app->getBasePath());
        $pageUrl = $pub !== '' ? rtrim($pub, '/') . $withBase('/product/' . $args['id']) : '';
        $productJsonLd = ($pageUrl !== '' && $pub !== '')
            ? SeoHelper::buildProductJsonLd($product, $pageUrl, $pub)
            : '';

        return $view()->render($response, 'product.twig', [
            'product' => $product,
            'category_name' => $categoryName,
            'categories' => $database->categories(),
            'reviews' => $database->reviews((string) $args['id']),
            'product_json_ld' => $productJsonLd,
        ]);
    });

    $app->get('/contact', function (Request $request, Response $response) use ($db, $view): Response {
        $qp = $request->getQueryParams();
        $sent = isset($qp['sent']) && $qp['sent'] === '1';
        $formError = isset($qp['error']) && $qp['error'] === '1';
        $orderProductId = '';
        $orderMessagePrefill = '';
        $pid = isset($qp['product']) ? trim((string) $qp['product']) : '';
        if ($pid !== '') {
            $database = $db();
            $p = $database->productById($pid);
            if ($p !== null) {
                $orderProductId = $pid;
                $catName = null;
                foreach ($database->categories() as $c) {
                    if ($c['id'] === $p['category']) {
                        $catName = (string) $c['name'];
                        break;
                    }
                }
                $orderMessagePrefill = marketbw_contact_order_draft($p, $catName);
            }
        }

        return $view()->render($response, 'contact.twig', [
            'sent' => $sent,
            'form_error' => $formError,
            'order_product_id' => $orderProductId,
            'order_message_prefill' => $orderMessagePrefill,
        ]);
    });

    $app->post('/contact', function (Request $request, Response $response) use ($db, $withBase): Response {
        $data = (array) $request->getParsedBody();
        $productRef = trim((string) ($data['product_ref'] ?? ''));
        $errLoc = function () use ($withBase, $db, $productRef): string {
            $q = '?error=1';
            if ($productRef !== '' && $db()->productById($productRef) !== null) {
                $q .= '&product=' . rawurlencode($productRef);
            }

            return $withBase('/contact') . $q;
        };
        if (!isset($data['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $data['csrf'])) {
            return $response->withHeader('Location', $errLoc())->withStatus(302);
        }
        $name = trim((string) ($data['name'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $message = trim((string) ($data['message'] ?? ''));
        if ($name === '' || $message === '') {
            return $response->withHeader('Location', $errLoc())->withStatus(302);
        }
        $ua = $request->getServerParams()['HTTP_USER_AGENT'] ?? null;
        $db()->createContactMessage(
            $name,
            $email,
            $message,
            marketbw_client_ip($request),
            is_string($ua) ? $ua : null
        );

        return $response->withHeader('Location', $withBase('/contact') . '?sent=1')->withStatus(302);
    });

    $app->get('/faq', function (Request $request, Response $response) use ($db, $view): Response {
        return $view()->render($response, 'faq.twig', [
            'faqs' => $db()->faqs(),
        ]);
    });

    $adminGuard = function (Request $request, $handler) use ($withBase): Response {
        $path = $request->getUri()->getPath();
        $loginPath = $withBase('/admin/login');
        if (str_starts_with($path, $loginPath) || str_starts_with($path, '/admin/login')) {
            return $handler->handle($request);
        }
        if (empty($_SESSION['admin'])) {
            return (new SlimResponse(302))
                ->withHeader('Location', $loginPath)
                ->withStatus(302);
        }
        return $handler->handle($request);
    };

    $app->get('/admin/login', function (Request $request, Response $response) use ($view, $withBase): Response {
        if (!empty($_SESSION['admin'])) {
            return $response->withHeader('Location', $withBase('/admin'))->withStatus(302);
        }
        return $view()->render($response, 'admin/login.twig', []);
    });

    $app->post('/admin/login', function (Request $request, Response $response) use ($db, $view, $withBase): Response {
        $data = (array) $request->getParsedBody();
        $user = trim((string) ($data['username'] ?? ''));
        $pass = trim((string) ($data['password'] ?? ''));
        if ($db()->authenticate($user, $pass)) {
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            $_SESSION['admin_username'] = $user;
            return $response->withHeader('Location', $withBase('/admin'))->withStatus(302);
        }
        return $view()->render($response, 'admin/login.twig', ['error' => 'Неверный логин или пароль']);
    });

    $app->post('/admin/logout', function (Request $request, Response $response) use ($withBase): Response {
        $_SESSION['admin'] = false;
        unset($_SESSION['admin'], $_SESSION['admin_username']);
        return $response->withHeader('Location', $withBase('/admin/login'))->withStatus(302);
    })->add($adminGuard);

    $adminDashboard = function (Request $request, Response $response) use ($db, $view): Response {
        $database = $db();
        $pc = (int) $database->pdo()->query('SELECT COUNT(*) FROM products')->fetchColumn();
        $cc = (int) $database->pdo()->query('SELECT COUNT(*) FROM categories')->fetchColumn();

        return $view()->render($response, 'admin/dashboard.twig', [
            'product_count' => $pc,
            'category_count' => $cc,
            'contact_new_count' => $database->contactMessagesCount('new'),
            'admin_section' => 'dash',
        ]);
    };
    $app->get('/admin', $adminDashboard)->add($adminGuard);
    $app->get('/admin/', $adminDashboard)->add($adminGuard);

    $app->group('/admin', function (RouteCollectorProxy $group) use ($db, $view, $settings, $withBase): void {
        $group->get('/password', function (Request $request, Response $response) use ($view): Response {
            return $view()->render($response, 'admin/password.twig', [
                'admin_section' => 'password',
            ]);
        });

        $group->post('/password', function (Request $request, Response $response) use ($db, $view): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $user = (string) ($_SESSION['admin_username'] ?? 'admin');
            $old = (string) ($_POST['old_password'] ?? '');
            $new = (string) ($_POST['new_password'] ?? '');
            $again = (string) ($_POST['new_password_confirm'] ?? '');
            if (!$db()->authenticate($user, $old)) {
                return $view()->render($response, 'admin/password.twig', [
                    'error' => 'Неверный текущий пароль',
                    'admin_section' => 'password',
                ]);
            }
            if (strlen($new) < 8) {
                return $view()->render($response, 'admin/password.twig', [
                    'error' => 'Новый пароль — не короче 8 символов',
                    'admin_section' => 'password',
                ]);
            }
            if ($new !== $again) {
                return $view()->render($response, 'admin/password.twig', [
                    'error' => 'Пароли не совпадают',
                    'admin_section' => 'password',
                ]);
            }
            $db()->updatePassword($user, $new);
            return $view()->render($response, 'admin/password.twig', [
                'success' => 'Пароль обновлён',
                'admin_section' => 'password',
            ]);
        });

        $group->get('/products', function (Request $request, Response $response) use ($db, $view): Response {
            return $view()->render($response, 'admin/products.twig', [
                'products' => $db()->products(),
                'categories' => $db()->categories(),
                'admin_section' => 'products',
            ]);
        });

        $adminProductNewGet = function (Request $request, Response $response) use ($db, $view): Response {
            return $view()->render($response, 'admin/product_form.twig', [
                'product' => null,
                'categories' => $db()->categories(),
                'admin_section' => 'products',
            ]);
        };
        $group->get('/products/new', $adminProductNewGet);
        $group->get('/products/create', $adminProductNewGet);

        $adminProductNewPost = function (Request $request, Response $response) use ($db, $view, $settings, $withBase): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $imagesDir = rtrim($settings()['images_dir'], '/');
            if (!is_dir($imagesDir . '/products')) {
                mkdir($imagesDir . '/products', 0755, true);
            }
            $norm = ProductImages::normalizeFromForm((string) ($_POST['image_paths'] ?? ''), $imagesDir);
            if ($norm['error'] !== null) {
                return $view()->render($response, 'admin/product_form.twig', [
                    'error' => $norm['error'],
                    'product' => null,
                    'categories' => $db()->categories(),
                    'form' => $_POST,
                    'admin_section' => 'products',
                ]);
            }
            $paths = array_merge($norm['paths'], handle_image_uploads($_FILES['images'] ?? null, $imagesDir));
            $materials = parse_lines($_POST['materials'] ?? '');
            $name = trim((string) ($_POST['name'] ?? ''));
            $desc = trim((string) ($_POST['description'] ?? ''));
            $price = (float) str_replace(',', '.', (string) ($_POST['price'] ?? '0'));
            $cat = trim((string) ($_POST['category'] ?? ''));
            $size = trim((string) ($_POST['size'] ?? '')) ?: null;
            $tech = trim((string) ($_POST['technique'] ?? ''));
            $inStock = isset($_POST['in_stock']);
            $featured = isset($_POST['featured']);
            if ($name === '' || $cat === '') {
                return $view()->render($response, 'admin/product_form.twig', [
                    'error' => 'Заполните название и категорию',
                    'product' => null,
                    'categories' => $db()->categories(),
                    'form' => $_POST,
                    'admin_section' => 'products',
                ]);
            }
            $db()->createProduct($name, $desc, $price, $cat, $paths, $materials, $size, $tech, $inStock, $featured);
            return $response->withHeader('Location', $withBase('/admin/products'))->withStatus(302);
        };
        $group->post('/products/new', $adminProductNewPost);
        $group->post('/products/create', $adminProductNewPost);

        $group->get('/products/{id}/edit', function (Request $request, Response $response, array $args) use ($db, $view, $settings): Response {
            $p = $db()->productById((string) $args['id']);
            if (!$p) {
                return $response->withStatus(404);
            }
            $imagesDir = rtrim($settings()['images_dir'], '/');
            [$p, ] = ProductImages::migrateStoredDataUrls($p, $db(), $imagesDir);

            return $view()->render($response, 'admin/product_form.twig', [
                'product' => $p,
                'categories' => $db()->categories(),
                'admin_section' => 'products',
            ]);
        });

        $group->post('/products/{id}/edit', function (Request $request, Response $response, array $args) use ($db, $view, $settings, $withBase): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $id = (string) $args['id'];
            $imagesDir = rtrim($settings()['images_dir'], '/');
            if (!is_dir($imagesDir . '/products')) {
                mkdir($imagesDir . '/products', 0755, true);
            }
            $norm = ProductImages::normalizeFromForm((string) ($_POST['image_paths'] ?? ''), $imagesDir);
            if ($norm['error'] !== null) {
                $p = $db()->productById($id);
                if (!$p) {
                    return $response->withStatus(404);
                }

                return $view()->render($response, 'admin/product_form.twig', [
                    'error' => $norm['error'],
                    'product' => $p,
                    'categories' => $db()->categories(),
                    'form' => $_POST,
                    'admin_section' => 'products',
                ]);
            }
            $paths = array_merge($norm['paths'], handle_image_uploads($_FILES['images'] ?? null, $imagesDir));
            $materials = parse_lines($_POST['materials'] ?? '');
            $name = trim((string) ($_POST['name'] ?? ''));
            $desc = trim((string) ($_POST['description'] ?? ''));
            $price = (float) str_replace(',', '.', (string) ($_POST['price'] ?? '0'));
            $cat = trim((string) ($_POST['category'] ?? ''));
            $size = trim((string) ($_POST['size'] ?? '')) ?: null;
            $tech = trim((string) ($_POST['technique'] ?? ''));
            $inStock = isset($_POST['in_stock']);
            $featured = isset($_POST['featured']);
            $db()->updateProduct(
                $id,
                $name,
                $desc,
                $price,
                $cat,
                $paths,
                $materials,
                $size,
                $tech,
                $inStock,
                $featured
            );
            return $response->withHeader('Location', $withBase('/admin/products'))->withStatus(302);
        });

        $group->post('/products/{id}/delete', function (Request $request, Response $response, array $args) use ($db, $withBase): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $db()->deleteProduct((string) $args['id']);
            return $response->withHeader('Location', $withBase('/admin/products'))->withStatus(302);
        });

        $group->get('/categories', function (Request $request, Response $response) use ($db, $view): Response {
            return $view()->render($response, 'admin/categories.twig', [
                'categories' => $db()->categories(),
                'admin_section' => 'cats',
            ]);
        });

        $group->get('/categories/new', function (Request $request, Response $response) use ($view): Response {
            return $view()->render($response, 'admin/category_form.twig', [
                'category' => null,
                'admin_section' => 'cats',
            ]);
        });

        $group->post('/categories/new', function (Request $request, Response $response) use ($db, $view, $settings, $withBase): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $name = trim((string) ($_POST['name'] ?? ''));
            $desc = trim((string) ($_POST['description'] ?? ''));
            $image = trim((string) ($_POST['image'] ?? ''));
            $uploaded = handle_category_upload($_FILES['image_file'] ?? null, rtrim($settings()['images_dir'], '/'));
            if ($uploaded) {
                $image = $uploaded;
            }
            if ($name === '' || $image === '') {
                return $view()->render($response, 'admin/category_form.twig', [
                    'error' => 'Название и картинка обязательны',
                    'category' => null,
                    'form' => $_POST,
                    'admin_section' => 'cats',
                ]);
            }
            $db()->createCategory($name, $desc, $image);
            return $response->withHeader('Location', $withBase('/admin/categories'))->withStatus(302);
        });

        $group->get('/categories/{id}/edit', function (Request $request, Response $response, array $args) use ($db, $view): Response {
            $id = (string) $args['id'];
            foreach ($db()->categories() as $c) {
                if ($c['id'] === $id) {
                    return $view()->render($response, 'admin/category_form.twig', [
                        'category' => $c,
                        'admin_section' => 'cats',
                    ]);
                }
            }
            return $response->withStatus(404);
        });

        $group->post('/categories/{id}/edit', function (Request $request, Response $response, array $args) use ($db, $view, $settings, $withBase): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $id = (string) $args['id'];
            $name = trim((string) ($_POST['name'] ?? ''));
            $desc = trim((string) ($_POST['description'] ?? ''));
            $image = trim((string) ($_POST['image'] ?? ''));
            $uploaded = handle_category_upload($_FILES['image_file'] ?? null, rtrim($settings()['images_dir'], '/'));
            if ($uploaded) {
                $image = $uploaded;
            }
            $db()->updateCategory($id, $name, $desc, $image !== '' ? $image : null);
            return $response->withHeader('Location', $withBase('/admin/categories'))->withStatus(302);
        });

        $group->post('/categories/{id}/delete', function (Request $request, Response $response, array $args) use ($db, $withBase): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $db()->deleteCategory((string) $args['id']);
            return $response->withHeader('Location', $withBase('/admin/categories'))->withStatus(302);
        });

        $group->get('/content', function (Request $request, Response $response) use ($db, $view, $settings): Response {
            $qp = $request->getQueryParams();
            $saved = isset($qp['saved']) && $qp['saved'] === '1';

            return $view()->render($response, 'admin/content.twig', [
                'content' => $db()->getMergedSiteContent($settings()),
                'content_groups' => SiteContentDefaults::adminGroups(),
                'image_keys' => SiteContentDefaults::imageKeys(),
                'saved' => $saved,
                'admin_section' => 'content',
            ]);
        });

        $group->post('/content', function (Request $request, Response $response) use ($db, $settings, $withBase): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $input = $_POST['content'] ?? [];
            if (!is_array($input)) {
                $input = [];
            }
            $pairs = [];
            foreach (SiteContentDefaults::allKeys() as $k) {
                if (!array_key_exists($k, $input)) {
                    continue;
                }
                $pairs[$k] = (string) $input[$k];
            }
            $imgDir = rtrim($settings()['images_dir'], '/');
            foreach (SiteContentDefaults::imageKeys() as $k) {
                $fname = 'img_' . str_replace('.', '_', $k);
                $path = SiteUpload::saveImage($_FILES[$fname] ?? null, $imgDir);
                if ($path !== null) {
                    $pairs[$k] = $path;
                }
            }
            $db()->saveSiteContent($pairs);

            return $response->withHeader('Location', $withBase('/admin/content') . '?saved=1')->withStatus(302);
        });

        $group->get('/faqs', function (Request $request, Response $response) use ($db, $view): Response {
            return $view()->render($response, 'admin/faqs.twig', [
                'faqs' => $db()->faqs(),
                'admin_section' => 'faqs',
            ]);
        });

        $group->get('/faqs/new', function (Request $request, Response $response) use ($view): Response {
            return $view()->render($response, 'admin/faq_form.twig', [
                'faq' => null,
                'admin_section' => 'faqs',
            ]);
        });

        $group->post('/faqs/new', function (Request $request, Response $response) use ($db, $view, $withBase): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $q = trim((string) ($_POST['question'] ?? ''));
            $a = trim((string) ($_POST['answer'] ?? ''));
            $cat = trim((string) ($_POST['category'] ?? 'general'));
            if ($q === '' || $a === '') {
                return $view()->render($response, 'admin/faq_form.twig', [
                    'error' => 'Заполните вопрос и ответ',
                    'faq' => null,
                    'form' => $_POST,
                    'admin_section' => 'faqs',
                ]);
            }
            $db()->createFaq($q, $a, $cat !== '' ? $cat : 'general');

            return $response->withHeader('Location', $withBase('/admin/faqs'))->withStatus(302);
        });

        $group->get('/faqs/{id}/edit', function (Request $request, Response $response, array $args) use ($db, $view): Response {
            $f = $db()->faqById((string) $args['id']);
            if (!$f) {
                return $response->withStatus(404);
            }

            return $view()->render($response, 'admin/faq_form.twig', [
                'faq' => $f,
                'admin_section' => 'faqs',
            ]);
        });

        $group->post('/faqs/{id}/edit', function (Request $request, Response $response, array $args) use ($db, $view, $withBase): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $id = (string) $args['id'];
            $q = trim((string) ($_POST['question'] ?? ''));
            $a = trim((string) ($_POST['answer'] ?? ''));
            $cat = trim((string) ($_POST['category'] ?? 'general'));
            if ($q === '' || $a === '') {
                $f = $db()->faqById($id);
                if (!$f) {
                    return $response->withStatus(404);
                }

                return $view()->render($response, 'admin/faq_form.twig', [
                    'error' => 'Заполните вопрос и ответ',
                    'faq' => $f,
                    'form' => $_POST,
                    'admin_section' => 'faqs',
                ]);
            }
            $db()->updateFaq($id, $q, $a, $cat !== '' ? $cat : 'general');

            return $response->withHeader('Location', $withBase('/admin/faqs'))->withStatus(302);
        });

        $group->post('/faqs/{id}/delete', function (Request $request, Response $response, array $args) use ($db, $withBase): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $db()->deleteFaq((string) $args['id']);

            return $response->withHeader('Location', $withBase('/admin/faqs'))->withStatus(302);
        });

        $group->get('/reviews', function (Request $request, Response $response) use ($db, $view): Response {
            return $view()->render($response, 'admin/reviews.twig', [
                'reviews' => $db()->reviews(),
                'admin_section' => 'reviews',
            ]);
        });

        $group->get('/reviews/new', function (Request $request, Response $response) use ($db, $view): Response {
            return $view()->render($response, 'admin/review_form.twig', [
                'review' => null,
                'products' => $db()->products(),
                'admin_section' => 'reviews',
            ]);
        });

        $group->post('/reviews/new', function (Request $request, Response $response) use ($db, $view, $withBase): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $author = trim((string) ($_POST['author'] ?? ''));
            $text = trim((string) ($_POST['text'] ?? ''));
            $rating = (int) ($_POST['rating'] ?? 5);
            $date = trim((string) ($_POST['date'] ?? '')) ?: date('Y-m-d H:i:s');
            $pid = trim((string) ($_POST['product_id'] ?? ''));
            $productId = $pid !== '' ? $pid : null;
            if ($author === '' || $text === '') {
                return $view()->render($response, 'admin/review_form.twig', [
                    'error' => 'Заполните автора и текст',
                    'review' => null,
                    'products' => $db()->products(),
                    'form' => $_POST,
                    'admin_section' => 'reviews',
                ]);
            }
            $db()->createReview($author, $rating, $text, $date, $productId);

            return $response->withHeader('Location', $withBase('/admin/reviews'))->withStatus(302);
        });

        $group->get('/reviews/{id}/edit', function (Request $request, Response $response, array $args) use ($db, $view): Response {
            $r = $db()->reviewById((string) $args['id']);
            if (!$r) {
                return $response->withStatus(404);
            }

            return $view()->render($response, 'admin/review_form.twig', [
                'review' => $r,
                'products' => $db()->products(),
                'admin_section' => 'reviews',
            ]);
        });

        $group->post('/reviews/{id}/edit', function (Request $request, Response $response, array $args) use ($db, $view, $withBase): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $id = (string) $args['id'];
            $author = trim((string) ($_POST['author'] ?? ''));
            $text = trim((string) ($_POST['text'] ?? ''));
            $rating = (int) ($_POST['rating'] ?? 5);
            $date = trim((string) ($_POST['date'] ?? '')) ?: date('Y-m-d H:i:s');
            $pid = trim((string) ($_POST['product_id'] ?? ''));
            $productId = $pid !== '' ? $pid : null;
            if ($author === '' || $text === '') {
                $r = $db()->reviewById($id);
                if (!$r) {
                    return $response->withStatus(404);
                }

                return $view()->render($response, 'admin/review_form.twig', [
                    'error' => 'Заполните автора и текст',
                    'review' => $r,
                    'products' => $db()->products(),
                    'form' => $_POST,
                    'admin_section' => 'reviews',
                ]);
            }
            $db()->updateReview($id, $author, $rating, $text, $date, $productId);

            return $response->withHeader('Location', $withBase('/admin/reviews'))->withStatus(302);
        });

        $group->post('/reviews/{id}/delete', function (Request $request, Response $response, array $args) use ($db, $withBase): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $db()->deleteReview((string) $args['id']);

            return $response->withHeader('Location', $withBase('/admin/reviews'))->withStatus(302);
        });

        $group->get('/contact-messages', function (Request $request, Response $response) use ($db, $view): Response {
            $qp = $request->getQueryParams();
            $page = max(1, (int) ($qp['page'] ?? 1));
            $st = isset($qp['status']) ? trim((string) $qp['status']) : '';
            $status = in_array($st, Database::contactMessageStatuses(), true) ? $st : null;
            $perPage = 50;
            $database = $db();
            $total = $database->contactMessagesCount($status);
            $pages = max(1, (int) ceil($total / $perPage));
            if ($page > $pages) {
                $page = $pages;
            }
            $offset = ($page - 1) * $perPage;

            return $view()->render($response, 'admin/contact_messages.twig', [
                'admin_section' => 'contact_messages',
                'cmessages' => $database->contactMessages($status, $perPage, $offset),
                'cm_status' => $status,
                'cm_page' => $page,
                'cm_pages' => $pages,
                'cm_total' => $total,
                'cm_counts' => [
                    'all' => $database->contactMessagesCount(null),
                    'new' => $database->contactMessagesCount('new'),
                    'done' => $database->contactMessagesCount('done'),
                    'archived' => $database->contactMessagesCount('archived'),
                ],
                'deleted' => isset($qp['deleted']) && $qp['deleted'] === '1',
            ]);
        });

        $group->get('/contact-messages/{id}', function (Request $request, Response $response, array $args) use ($db, $view): Response {
            $id = (int) $args['id'];
            if ($id < 1) {
                return $view()->render($response->withStatus(404), '404.twig', []);
            }
            $row = $db()->contactMessageById($id);
            if (!$row) {
                return $view()->render($response->withStatus(404), '404.twig', []);
            }
            $qp = $request->getQueryParams();

            return $view()->render($response, 'admin/contact_message.twig', [
                'admin_section' => 'contact_messages',
                'msg' => $row,
                'saved' => isset($qp['saved']) && $qp['saved'] === '1',
            ]);
        });

        $group->post('/contact-messages/{id}/status', function (Request $request, Response $response, array $args) use ($db, $withBase): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $id = (int) $args['id'];
            $newStatus = trim((string) ($_POST['status'] ?? ''));
            if ($id < 1 || !in_array($newStatus, Database::contactMessageStatuses(), true)) {
                return $response->withStatus(400);
            }
            $db()->contactMessageSetStatus($id, $newStatus);

            return $response->withHeader('Location', $withBase('/admin/contact-messages/' . $id) . '?saved=1')->withStatus(302);
        });

        $group->post('/contact-messages/{id}/delete', function (Request $request, Response $response, array $args) use ($db, $withBase): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $id = (int) $args['id'];
            if ($id < 1) {
                return $response->withStatus(400);
            }
            $db()->deleteContactMessage($id);

            return $response->withHeader('Location', $withBase('/admin/contact-messages') . '?deleted=1')->withStatus(302);
        });

        $group->get('/logs', function (Request $request, Response $response) use ($db, $view, $settings): Response {
            $qp = $request->getQueryParams();
            $page = max(1, (int) ($qp['page'] ?? 1));
            $ch = isset($qp['channel']) ? trim((string) $qp['channel']) : '';
            $channel = ($ch === 'admin' || $ch === 'public') ? $ch : null;
            $perPage = 80;
            $total = $db()->auditLogsCount($channel);
            $pages = max(1, (int) ceil($total / $perPage));
            if ($page > $pages) {
                $page = $pages;
            }
            $offset = ($page - 1) * $perPage;
            return $view()->render($response, 'admin/logs.twig', [
                'admin_section' => 'logs',
                'logs' => $db()->auditLogs($channel, $perPage, $offset),
                'log_total' => $total,
                'log_page' => $page,
                'log_pages' => $pages,
                'log_channel' => $channel,
                'audit_enabled' => $db()->isAuditLogEnabled($settings()),
                'audit_verbose' => $db()->isAuditLogVerbose($settings()),
                'saved' => isset($qp['saved']) && $qp['saved'] === '1',
                'cleared' => isset($qp['cleared']) && $qp['cleared'] === '1',
            ]);
        });

        $group->post('/logs/toggle', function (Request $request, Response $response) use ($db, $withBase): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $on = isset($_POST['enabled']) && (string) $_POST['enabled'] === '1';
            $verbose = isset($_POST['verbose']) && (string) $_POST['verbose'] === '1';
            $db()->saveSiteContent([
                'audit.log_enabled' => $on ? '1' : '0',
                'audit.log_verbose' => $verbose ? '1' : '0',
            ]);

            return $response->withHeader('Location', $withBase('/admin/logs') . '?saved=1')->withStatus(302);
        });

        $group->post('/logs/clear', function (Request $request, Response $response) use ($db, $withBase): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $db()->clearAuditLogs();

            return $response->withHeader('Location', $withBase('/admin/logs') . '?cleared=1')->withStatus(302);
        });

        $group->get('/database', function (Request $request, Response $response) use ($db, $view): Response {
            $database = $db();
            $dbTables = [];
            foreach ($database->listSqliteTables() as $name) {
                $preview = $database->tableRowsPreview($name, 20);
                $columns = $preview !== [] ? array_keys($preview[0]) : $database->tableColumnNames($name);
                $dbTables[] = [
                    'name' => $name,
                    'count' => $database->countTableRows($name),
                    'preview_columns' => $columns,
                    'preview_rows' => $preview,
                ];
            }

            return $view()->render($response, 'admin/database.twig', [
                'admin_section' => 'database',
                'db_tables' => $dbTables,
            ]);
        });

        $group->post('/database/export', function (Request $request, Response $response) use ($db): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $h = DatabaseExcelExport::openSpreadsheetXmlStream($db());
            $body = stream_get_contents($h);
            fclose($h);
            $filename = 'marketbw-db-' . gmdate('Y-m-d-His') . '.xls';
            $response->getBody()->write($body === false ? '' : $body);

            return $response
                ->withHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
                ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
                ->withStatus(200);
        });
    })->add($adminGuard);
};

/**
 * Черновик сообщения в контакты при заказе с карточки товара.
 *
 * @param array<string, mixed> $product результат {@see Database::productById()}
 */
function marketbw_contact_order_draft(array $product, ?string $categoryName): string
{
    $name = (string) ($product['name'] ?? '');
    $price = number_format((float) ($product['price'] ?? 0), 0, ',', ' ') . ' ₽';
    $id = (string) ($product['id'] ?? '');
    $technique = (string) ($product['technique'] ?? '');
    $lines = [
        'Здравствуйте! Хочу заказать изделие с сайта:',
        '',
        'Название: ' . $name,
        'Цена: ' . $price,
        'ID товара на сайте: ' . $id,
    ];
    if ($categoryName !== null && $categoryName !== '') {
        $lines[] = 'Категория: ' . $categoryName;
    }
    if ($technique !== '') {
        $lines[] = 'Техника: ' . $technique;
    }
    $size = trim((string) ($product['size'] ?? ''));
    if ($size !== '') {
        $lines[] = 'Размер: ' . $size;
    }
    $materials = $product['materials'] ?? [];
    if (is_array($materials) && $materials !== []) {
        $matStrs = [];
        foreach ($materials as $m) {
            $matStrs[] = (string) $m;
        }
        $lines[] = 'Материалы: ' . implode(', ', $matStrs);
    }
    $stock = !empty($product['in_stock']);
    $lines[] = 'Наличие на момент заказа: ' . ($stock ? 'в наличии' : 'нет в наличии');
    $lines[] = '';
    $lines[] = 'Комментарий к заказу (уточнения, доставка, сроки):';
    $lines[] = '';

    return implode("\n", $lines);
}

function marketbw_client_ip(Request $request): string
{
    $s = $request->getServerParams();
    $ip = (string) ($s['HTTP_CF_CONNECTING_IP'] ?? '');
    if ($ip === '' && !empty($s['HTTP_X_FORWARDED_FOR'])) {
        $parts = explode(',', (string) $s['HTTP_X_FORWARDED_FOR']);
        $ip = trim($parts[0]);
    }
    if ($ip === '') {
        $ip = (string) ($s['REMOTE_ADDR'] ?? '');
    }

    return function_exists('mb_substr') ? mb_substr($ip, 0, 80) : substr($ip, 0, 80);
}

/**
 * @return list<string>
 */
function parse_lines(string $raw): array
{
    $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
    $out = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line !== '') {
            $out[] = $line;
        }
    }
    return $out;
}

/**
 * @param array<string, mixed>|null $files
 * @return list<string>
 */
function handle_image_uploads(?array $files, string $imagesDir): array
{
    if ($files === null || !isset($files['name']) || !is_array($files['name'])) {
        return [];
    }
    $out = [];
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    $n = count($files['name']);
    for ($i = 0; $i < $n; $i++) {
        if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            continue;
        }
        $tmp = (string) $files['tmp_name'][$i];
        $orig = (string) $files['name'][$i];
        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            continue;
        }
        $name = bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $imagesDir . '/products/' . $name;
        if (move_uploaded_file($tmp, $dest)) {
            $out[] = '/images/products/' . $name;
        }
    }
    return $out;
}

/**
 * @param array<string, mixed>|null $file
 */
function handle_category_upload(?array $file, string $imagesDir): ?string
{
    if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        return null;
    }
    if (!is_dir($imagesDir . '/categories')) {
        mkdir($imagesDir . '/categories', 0755, true);
    }
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = $imagesDir . '/categories/' . $name;
    if (move_uploaded_file((string) $file['tmp_name'], $dest)) {
        return '/images/categories/' . $name;
    }
    return null;
}
