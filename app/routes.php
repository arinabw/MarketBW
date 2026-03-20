<?php

declare(strict_types=1);

use App\Database;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Psr7\Response as SlimResponse;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app, ContainerInterface $container): void {
    $db = static fn (): Database => $container->get(Database::class);
    $view = static fn () => $container->get('twig');
    $settings = static fn (): array => $container->get('settings');

    $app->get('/', static function (Request $request, Response $response) use ($db, $view): Response {
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

    $app->get('/catalog', static function (Request $request, Response $response) use ($db, $view): Response {
        $q = $request->getQueryParams();
        $cat = isset($q['category']) ? (string) $q['category'] : null;
        $database = $db();
        return $view()->render($response, 'catalog.twig', [
            'products' => $database->products($cat),
            'categories' => $database->categories(),
            'active_category' => $cat,
        ]);
    });

    $app->get('/product/{id}', static function (Request $request, Response $response, array $args) use ($db, $view): Response {
        $database = $db();
        $product = $database->productById((string) $args['id']);
        if (!$product) {
            return $view()->render($response->withStatus(404), '404.twig', []);
        }
        return $view()->render($response, 'product.twig', [
            'product' => $product,
            'categories' => $database->categories(),
            'reviews' => $database->reviews((string) $args['id']),
        ]);
    });

    $app->get('/contact', static function (Request $request, Response $response) use ($view): Response {
        return $view()->render($response, 'contact.twig', []);
    });

    $app->get('/faq', static function (Request $request, Response $response) use ($db, $view): Response {
        return $view()->render($response, 'faq.twig', [
            'faqs' => $db()->faqs(),
        ]);
    });

    $adminGuard = static function (Request $request, $handler): Response {
        $path = $request->getUri()->getPath();
        if (str_starts_with($path, '/admin/login')) {
            return $handler->handle($request);
        }
        if (empty($_SESSION['admin'])) {
            return (new SlimResponse(302))->withHeader('Location', '/admin/login');
        }
        return $handler->handle($request);
    };

    $app->get('/admin/login', static function (Request $request, Response $response) use ($view): Response {
        if (!empty($_SESSION['admin'])) {
            return $response->withHeader('Location', '/admin')->withStatus(302);
        }
        return $view()->render($response, 'admin/login.twig', []);
    });

    $app->post('/admin/login', static function (Request $request, Response $response) use ($db, $view): Response {
        $data = (array) $request->getParsedBody();
        $user = isset($data['username']) ? (string) $data['username'] : '';
        $pass = isset($data['password']) ? (string) $data['password'] : '';
        if ($db()->authenticate($user, $pass)) {
            $_SESSION['admin'] = true;
            return $response->withHeader('Location', '/admin')->withStatus(302);
        }
        return $view()->render($response, 'admin/login.twig', ['error' => 'Неверный логин или пароль']);
    });

    $app->post('/admin/logout', static function (Request $request, Response $response): Response {
        $_SESSION['admin'] = false;
        unset($_SESSION['admin']);
        return $response->withHeader('Location', '/admin/login')->withStatus(302);
    })->add($adminGuard);

    $app->group('/admin', function (RouteCollectorProxy $group) use ($db, $view, $settings): void {
        $group->get('', static function (Request $request, Response $response) use ($db, $view): Response {
            $database = $db();
            $pc = (int) $database->pdo()->query('SELECT COUNT(*) FROM products')->fetchColumn();
            $cc = (int) $database->pdo()->query('SELECT COUNT(*) FROM categories')->fetchColumn();
            return $view()->render($response, 'admin/dashboard.twig', [
                'product_count' => $pc,
                'category_count' => $cc,
                'admin_section' => 'dash',
            ]);
        });

        $group->get('/products', static function (Request $request, Response $response) use ($db, $view): Response {
            return $view()->render($response, 'admin/products.twig', [
                'products' => $db()->products(),
                'categories' => $db()->categories(),
                'admin_section' => 'products',
            ]);
        });

        $group->get('/products/new', static function (Request $request, Response $response) use ($db, $view): Response {
            return $view()->render($response, 'admin/product_form.twig', [
                'product' => null,
                'categories' => $db()->categories(),
                'admin_section' => 'products',
            ]);
        });

        $group->post('/products/new', static function (Request $request, Response $response) use ($db, $view, $settings): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $imagesDir = rtrim($settings()['images_dir'], '/');
            if (!is_dir($imagesDir . '/products')) {
                mkdir($imagesDir . '/products', 0755, true);
            }
            $paths = parse_image_paths($_POST['image_paths'] ?? '');
            $paths = array_merge($paths, handle_image_uploads($_FILES['images'] ?? null, $imagesDir));
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

        $group->get('/products/{id}/edit', static function (Request $request, Response $response, array $args) use ($db, $view): Response {
            $p = $db()->productById((string) $args['id']);
            if (!$p) {
                return $response->withStatus(404);
            }
            return $view()->render($response, 'admin/product_form.twig', [
                'product' => $p,
                'categories' => $db()->categories(),
                'admin_section' => 'products',
            ]);
        });

        $group->post('/products/{id}/edit', static function (Request $request, Response $response, array $args) use ($db, $view, $settings): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $id = (string) $args['id'];
            $imagesDir = rtrim($settings()['images_dir'], '/');
            if (!is_dir($imagesDir . '/products')) {
                mkdir($imagesDir . '/products', 0755, true);
            }
            $paths = parse_image_paths($_POST['image_paths'] ?? '');
            $paths = array_merge($paths, handle_image_uploads($_FILES['images'] ?? null, $imagesDir));
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

        $group->post('/products/{id}/delete', static function (Request $request, Response $response, array $args) use ($db): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $db()->deleteProduct((string) $args['id']);
            return $response->withHeader('Location', '/admin/products')->withStatus(302);
        });

        $group->get('/categories', static function (Request $request, Response $response) use ($db, $view): Response {
            return $view()->render($response, 'admin/categories.twig', [
                'categories' => $db()->categories(),
                'admin_section' => 'cats',
            ]);
        });

        $group->get('/categories/new', static function (Request $request, Response $response) use ($view): Response {
            return $view()->render($response, 'admin/category_form.twig', [
                'category' => null,
                'admin_section' => 'cats',
            ]);
        });

        $group->post('/categories/new', static function (Request $request, Response $response) use ($db, $view, $settings): Response {
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

        $group->get('/categories/{id}/edit', static function (Request $request, Response $response, array $args) use ($db, $view): Response {
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

        $group->post('/categories/{id}/edit', static function (Request $request, Response $response, array $args) use ($db, $view, $settings): Response {
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

        $group->post('/categories/{id}/delete', static function (Request $request, Response $response, array $args) use ($db): Response {
            if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
                $response->getBody()->write('CSRF');
                return $response->withStatus(403);
            }
            $db()->deleteCategory((string) $args['id']);
            return $response->withHeader('Location', '/admin/categories')->withStatus(302);
        });
    })->add($adminGuard);
};

/**
 * @return list<string>
 */
function parse_image_paths(string $raw): array
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
