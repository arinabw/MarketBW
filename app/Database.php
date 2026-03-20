<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

final class Database
{
    private PDO $pdo;

    public function __construct(private string $dataDir)
    {
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }
        $path = $this->dataDir . '/marketbw.db';
        $this->pdo = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function init(): void
    {
        $schema = <<<'SQL'
CREATE TABLE IF NOT EXISTS categories (
    id TEXT PRIMARY KEY,
    name TEXT NOT NULL,
    description TEXT,
    image TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id TEXT PRIMARY KEY,
    name TEXT NOT NULL,
    description TEXT NOT NULL,
    price REAL NOT NULL,
    category TEXT NOT NULL,
    images TEXT NOT NULL,
    materials TEXT NOT NULL,
    size TEXT,
    technique TEXT NOT NULL,
    in_stock INTEGER DEFAULT 1,
    featured INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id TEXT PRIMARY KEY,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS reviews (
    id TEXT PRIMARY KEY,
    author TEXT NOT NULL,
    rating INTEGER NOT NULL,
    text TEXT NOT NULL,
    date TEXT NOT NULL,
    product_id TEXT
);

CREATE TABLE IF NOT EXISTS faqs (
    id TEXT PRIMARY KEY,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    category TEXT NOT NULL
);
SQL;
        $this->pdo->exec($schema);

        $row = $this->pdo->query("SELECT 1 FROM users WHERE username = 'admin'")->fetch();
        if (!$row) {
            $this->pdo->prepare(
                'INSERT INTO users (id, username, password_hash) VALUES (?, ?, ?)'
            )->execute(['1', 'admin', password_hash('admin123', PASSWORD_DEFAULT)]);
        }

        Seed::ifEmpty($this->pdo);
    }

    /** @return list<array<string, mixed>> */
    public function categories(): array
    {
        $st = $this->pdo->query('SELECT * FROM categories ORDER BY created_at DESC');
        return $st ? $st->fetchAll() : [];
    }

    /** @return list<array<string, mixed>> */
    public function products(?string $categoryId = null): array
    {
        if ($categoryId !== null && $categoryId !== '') {
            $st = $this->pdo->prepare('SELECT * FROM products WHERE category = ? ORDER BY created_at DESC');
            $st->execute([$categoryId]);
        } else {
            $st = $this->pdo->query('SELECT * FROM products ORDER BY created_at DESC');
        }
        return $st ? array_map([$this, 'mapProduct'], $st->fetchAll()) : [];
    }

    /** @return list<array<string, mixed>> */
    public function featuredProducts(int $limit = 8): array
    {
        $st = $this->pdo->prepare(
            'SELECT * FROM products WHERE featured = 1 AND in_stock = 1 ORDER BY created_at DESC LIMIT ?'
        );
        $st->bindValue(1, $limit, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll();
        return array_map([$this, 'mapProduct'], $rows);
    }

    public function productById(string $id): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM products WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ? $this->mapProduct($row) : null;
    }

    /** @return list<array<string, mixed>> */
    public function reviews(?string $productId = null): array
    {
        if ($productId !== null && $productId !== '') {
            $st = $this->pdo->prepare('SELECT * FROM reviews WHERE product_id = ? ORDER BY date DESC');
            $st->execute([$productId]);
        } else {
            $st = $this->pdo->query('SELECT * FROM reviews ORDER BY date DESC');
        }
        return $st ? $st->fetchAll() : [];
    }

    /** @return list<array<string, mixed>> */
    public function faqs(): array
    {
        $st = $this->pdo->query('SELECT * FROM faqs ORDER BY id');
        return $st ? $st->fetchAll() : [];
    }

    public function authenticate(string $username, string $password): bool
    {
        $username = trim($username);
        $password = trim($password);

        $st = $this->pdo->prepare('SELECT password_hash, username FROM users WHERE LOWER(username) = LOWER(?)');
        $st->execute([$username]);
        $row = $st->fetch();
        if (!$row) {
            return false;
        }
        $hash = (string) $row['password_hash'];
        $dbUsername = (string) $row['username'];

        // Сначала современные хэши (bcrypt/argon и т.д.)
        if (password_verify($password, $hash)) {
            return true;
        }

        // Легаси: в колонке лежал открытый пароль (старый Python)
        if (hash_equals($hash, $password)) {
            try {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $up = $this->pdo->prepare('UPDATE users SET password_hash = ? WHERE username = ?');
                $up->execute([$newHash, $dbUsername]);
            } catch (PDOException) {
                // БД только для чтения — вход всё равно разрешаем
            }
            return true;
        }

        return false;
    }

    public function createCategory(string $name, ?string $description, string $image): string
    {
        $id = (string) (int) (microtime(true) * 1000);
        $this->pdo->prepare(
            'INSERT INTO categories (id, name, description, image) VALUES (?, ?, ?, ?)'
        )->execute([$id, $name, $description ?? '', $image]);
        return $id;
    }

    public function updateCategory(string $id, ?string $name, ?string $description, ?string $image): void
    {
        $fields = [];
        $vals = [];
        if ($name !== null) {
            $fields[] = 'name = ?';
            $vals[] = $name;
        }
        if ($description !== null) {
            $fields[] = 'description = ?';
            $vals[] = $description;
        }
        if ($image !== null) {
            $fields[] = 'image = ?';
            $vals[] = $image;
        }
        if ($fields === []) {
            return;
        }
        $vals[] = $id;
        $sql = 'UPDATE categories SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $this->pdo->prepare($sql)->execute($vals);
    }

    public function deleteCategory(string $id): void
    {
        $this->pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
    }

    /**
     * @param list<string> $images
     * @param list<string> $materials
     */
    public function createProduct(
        string $name,
        string $description,
        float $price,
        string $category,
        array $images,
        array $materials,
        ?string $size,
        string $technique,
        bool $inStock,
        bool $featured,
    ): string {
        $id = (string) (int) (microtime(true) * 1000);
        $this->pdo->prepare(
            'INSERT INTO products (id, name, description, price, category, images, materials, size, technique, in_stock, featured)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $id,
            $name,
            $description,
            $price,
            $category,
            json_encode($images, JSON_UNESCAPED_UNICODE),
            json_encode($materials, JSON_UNESCAPED_UNICODE),
            $size,
            $technique,
            $inStock ? 1 : 0,
            $featured ? 1 : 0,
        ]);
        return $id;
    }

    /**
     * @param list<string>|null $images
     * @param list<string>|null $materials
     */
    public function updateProduct(
        string $pid,
        ?string $name = null,
        ?string $description = null,
        ?float $price = null,
        ?string $category = null,
        ?array $images = null,
        ?array $materials = null,
        ?string $size = null,
        ?string $technique = null,
        ?bool $inStock = null,
        ?bool $featured = null,
    ): void {
        $fields = [];
        $vals = [];
        if ($name !== null) {
            $fields[] = 'name = ?';
            $vals[] = $name;
        }
        if ($description !== null) {
            $fields[] = 'description = ?';
            $vals[] = $description;
        }
        if ($price !== null) {
            $fields[] = 'price = ?';
            $vals[] = $price;
        }
        if ($category !== null) {
            $fields[] = 'category = ?';
            $vals[] = $category;
        }
        if ($images !== null) {
            $fields[] = 'images = ?';
            $vals[] = json_encode($images, JSON_UNESCAPED_UNICODE);
        }
        if ($materials !== null) {
            $fields[] = 'materials = ?';
            $vals[] = json_encode($materials, JSON_UNESCAPED_UNICODE);
        }
        if ($size !== null) {
            $fields[] = 'size = ?';
            $vals[] = $size;
        }
        if ($technique !== null) {
            $fields[] = 'technique = ?';
            $vals[] = $technique;
        }
        if ($inStock !== null) {
            $fields[] = 'in_stock = ?';
            $vals[] = $inStock ? 1 : 0;
        }
        if ($featured !== null) {
            $fields[] = 'featured = ?';
            $vals[] = $featured ? 1 : 0;
        }
        if ($fields === []) {
            return;
        }
        $vals[] = $pid;
        $sql = 'UPDATE products SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $this->pdo->prepare($sql)->execute($vals);
    }

    public function deleteProduct(string $id): void
    {
        $this->pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
    }

    /** @param array<string, mixed> $row */
    private function mapProduct(array $row): array
    {
        $row['images'] = json_decode($row['images'] ?: '[]', true) ?: [];
        $row['materials'] = json_decode($row['materials'] ?: '[]', true) ?: [];
        $row['in_stock'] = (bool) $row['in_stock'];
        $row['featured'] = (bool) $row['featured'];
        $row['price'] = (float) $row['price'];
        return $row;
    }
}
