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

    $app->get('/product/{id}', function (Request $request, Response $response, array $args) use ($db, $view): Response {
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
        return $view()->render($response, 'product.twig', [
            'product' => $product,
            'category_name' => $categoryName,
            'categories' => $database->categories(),
            'reviews' => $database->reviews((string) $args['id']),
        ]);
    });

    $app->get('/contact', function (Request $request, Response $response) use ($view): Response {
        $qp = $request->getQueryParams();
        $sent = isset($qp['sent']) && $qp['sent'] === '1';
        $formError = isset($qp['error']) && $qp['error'] === '1';
        return $view()->render($response, 'contact.twig', [
            'sent' => $sent,
            'form_error' => $formError,
        ]);
    });

    $app->post('/contact', function (Request $request, Response $response): Response {
        $data = (array) $request->getParsedBody();
        if (!isset($data['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $data['csrf'])) {
            return $response->withHeader('Location', '/contact?error=1')->withStatus(302);
        }
        $name = trim((string) ($data['name'] ?? ''));
        $message = trim((string) ($data['message'] ?? ''));
        if ($name === '' || $message === '') {
            return $response->withHeader('Location', '/contact?error=1')->withStatus(302);
        }
        return $response->withHeader('Location', '/contact?sent=1')->withStatus(302);
    });

    $app->get('/faq', function (Request $request, Response $response) use ($db, $view): Response {
        return $view()->render($response, 'faq.twig', [
            'faqs' => $db()->faqs(),
        ]);
    });

    $adminGuard = function (Request $request, $handler): Response {
        $path = $request->getUri()->getPath();
        if (str_starts_with($path, '/admin/login')) {
            return $handler->handle($request);
        }
        if (empty($_SESSION['admin'])) {
            return (new SlimResponse(302))->withHeader('Location', '/admin/login');
        }
        return $handler->handle($request);
    };

    $app->get('/admin/login', function (Request $request, Response $response) use ($view): Response {
        if (!empty($_SESSION['admin'])) {
            return $response->withHeader('Location', '/admin')->withStatus(302);
        }
        return $view()->render($response, 'admin/login.twig', []);
    });

    $app->post('/admin/login', function (Request $request, Response $response) use ($db, $view): Response {
        $data = (array) $request->getParsedBody();
        $user = trim((string) ($data['username'] ?? ''));
        $pass = trim((string) ($data['password'] ?? ''));
        if ($db()->authenticate($user, $pass)) {
            $_SESSION['admin'] = true;
            $_SESSION['admin_username'] = $user;
            return $response->withHeader('Location', '/admin')->withStatus(302);
        }
        return $view()->render($response, 'admin/login.twig', ['error' => 'Неверный логин или пароль']);
    });

    $app->post('/admin/logout', function (Request $request, Response $response): Response {
        $_SESSION['admin'] = false;
        unset($_SESSION['admin'], $_SESSION['admin_username']);
        return $response->withHeader('Location', '/admin/login')->withStatus(302);
    })->add($adminGuard);

    $app->group('/admin', function (RouteCollectorProxy $group) use ($db, $view, $settings): void {
        $group->get('', function (Request $request, Response $response) use ($db, $view): Response {
            $database = $db();
            $pc = (int) $database->pdo()->query('SELECT COUNT(*) FROM products')->fetchColumn();
            $cc = (int) $database->pdo()->query('SELECT COUNT(*) FROM categories')->fetchColumn();
            return $view()->render($response, 'admin/dashboard.twig', [
                'product_count' => $pc,
                'category_count' => $cc,
                'admin_section' => 'dash',
            ]);
        });

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

        $group->get('/products/new', function (Request $request, Response $response) use ($db, $view): Response {
            return $view()->render($response, 'admin/product_form.twig', [
                'product' => null,
                'categories' => $db()->categories(),
                'admin_section' => 'products',
            ]);
        });

        $group->post('/products/new', function (Request $request, Response $response) use ($db, $view, $settings): Response {
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
            return $response->withHeader('Location', '/admin/products')->withStatus(302);
        });

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

        $group->post('/products/{id}/edit', function (Request $request, Response $response, array $args) use ($db, $view, $settings): Response {
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
            return $response->withHeader('Location', '/admin/products')->withStatus(302);
        });

        $group->post('/products/{id}/delete', function (Request $request, Response $response, array $args) use ($db): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $db()->deleteProduct((string) $args['id']);
            return $response->withHeader('Location', '/admin/products')->withStatus(302);
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

        $group->post('/categories/new', function (Request $request, Response $response) use ($db, $view, $settings): Response {
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
            return $response->withHeader('Location', '/admin/categories')->withStatus(302);
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

        $group->post('/categories/{id}/edit', function (Request $request, Response $response, array $args) use ($db, $view, $settings): Response {
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
            return $response->withHeader('Location', '/admin/categories')->withStatus(302);
        });

        $group->post('/categories/{id}/delete', function (Request $request, Response $response, array $args) use ($db): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $db()->deleteCategory((string) $args['id']);
            return $response->withHeader('Location', '/admin/categories')->withStatus(302);
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

        $group->post('/content', function (Request $request, Response $response) use ($db, $settings): Response {
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

            return $response->withHeader('Location', '/admin/content?saved=1')->withStatus(302);
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

        $group->post('/faqs/new', function (Request $request, Response $response) use ($db, $view): Response {
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

            return $response->withHeader('Location', '/admin/faqs')->withStatus(302);
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

        $group->post('/faqs/{id}/edit', function (Request $request, Response $response, array $args) use ($db, $view): Response {
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

            return $response->withHeader('Location', '/admin/faqs')->withStatus(302);
        });

        $group->post('/faqs/{id}/delete', function (Request $request, Response $response, array $args) use ($db): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $db()->deleteFaq((string) $args['id']);

            return $response->withHeader('Location', '/admin/faqs')->withStatus(302);
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

        $group->post('/reviews/new', function (Request $request, Response $response) use ($db, $view): Response {
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

            return $response->withHeader('Location', '/admin/reviews')->withStatus(302);
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

        $group->post('/reviews/{id}/edit', function (Request $request, Response $response, array $args) use ($db, $view): Response {
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

            return $response->withHeader('Location', '/admin/reviews')->withStatus(302);
        });

        $group->post('/reviews/{id}/delete', function (Request $request, Response $response, array $args) use ($db): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $db()->deleteReview((string) $args['id']);

            return $response->withHeader('Location', '/admin/reviews')->withStatus(302);
        });
    })->add($adminGuard);
};

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
