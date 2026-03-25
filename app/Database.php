<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

// FILE: app/Database.php
// VERSION: 3.12.4
// START_MODULE_CONTRACT
//   PURPOSE: PDO SQLite: инициализация схемы, CRUD для products, categories, reviews, faqs, users, site_content, contact_messages, audit_log
//   SCOPE: init, categories, products, productById, createProduct, updateProduct, deleteProduct, faqs, authenticate, getMergedSiteContent, saveSiteContent, contactMessages, appendAuditLog, updatePassword
//   DEPENDS: M-SETTINGS (data_dir)
//   LINKS: M-DATABASE
// END_MODULE_CONTRACT
//
// START_MODULE_MAP
//   init                 — CREATE TABLE IF NOT EXISTS; вставка admin; вызов Seed
//   categories           — все категории
//   products             — товары с фильтрами
//   productById          — один товар по ID
//   createProduct        — INSERT INTO products
//   updateProduct        — UPDATE products
//   deleteProduct        — DELETE FROM products
//   faqs / createFaq / updateFaq / deleteFaq — CRUD FAQ
//   reviews              — все отзывы
//   authenticate         — password_verify для логина
//   getMergedSiteContent — слияние дефолтов + site_content
//   saveSiteContent      — UPSERT в site_content
//   contactMessages      — список заявок с фильтром
//   appendAuditLog       — INSERT INTO audit_log
//   updatePassword       — UPDATE users SET password_hash
// END_MODULE_MAP
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
        $this->pdo->exec('PRAGMA foreign_keys = ON');
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function init(): void
    {
        // START_BLOCK_INIT_SCHEMA
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

CREATE TABLE IF NOT EXISTS site_content (
    content_key TEXT PRIMARY KEY,
    value TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS audit_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    created_at TEXT NOT NULL,
    channel TEXT NOT NULL,
    method TEXT NOT NULL,
    path TEXT NOT NULL,
    query_string TEXT,
    status_code INTEGER NOT NULL,
    duration_ms INTEGER NOT NULL,
    ip TEXT NOT NULL,
    user_agent TEXT,
    admin_username TEXT,
    referer TEXT,
    context_json TEXT NOT NULL DEFAULT '{}'
);
CREATE INDEX IF NOT EXISTS idx_audit_log_created ON audit_log (created_at);
CREATE INDEX IF NOT EXISTS idx_audit_log_channel ON audit_log (channel);

CREATE TABLE IF NOT EXISTS contact_messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    name TEXT NOT NULL,
    email TEXT NOT NULL DEFAULT '',
    message TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'new',
    ip TEXT NOT NULL DEFAULT '',
    user_agent TEXT
);
CREATE INDEX IF NOT EXISTS idx_contact_messages_created ON contact_messages (created_at DESC);
CREATE INDEX IF NOT EXISTS idx_contact_messages_status ON contact_messages (status);

SQL;
        $this->pdo->exec($schema);
        // END_BLOCK_INIT_SCHEMA

        $row = $this->pdo->query("SELECT 1 FROM users WHERE username = 'admin'")->fetch();
        if (!$row) {
            $this->pdo->prepare(
                'INSERT INTO users (id, username, password_hash) VALUES (?, ?, ?)'
            )->execute(['1', 'admin', password_hash('admin123', PASSWORD_DEFAULT)]);
        }

        // START_BLOCK_SEED
        Seed::ifEmpty($this->pdo);
        // END_BLOCK_SEED
    }

    /** @return list<array<string, mixed>> */
    public function categories(): array
    {
        $st = $this->pdo->query('SELECT * FROM categories ORDER BY created_at DESC');
        return $st ? $st->fetchAll() : [];
    }

    /**
     * @param 'date_desc'|'price_asc'|'price_desc' $sort
     * @return list<array<string, mixed>>
     */
    public function products(?string $categoryId = null, ?string $search = null, string $sort = 'date_desc'): array
    {
        $where = [];
        $params = [];
        if ($categoryId !== null && $categoryId !== '') {
            $where[] = 'category = ?';
            $params[] = $categoryId;
        }
        if ($search !== null && $search !== '') {
            $where[] = 'instr(LOWER(name), LOWER(?)) > 0';
            $params[] = $search;
        }
        $whereSql = $where !== [] ? 'WHERE ' . implode(' AND ', $where) : '';
        $order = match ($sort) {
            'price_asc' => 'price ASC',
            'price_desc' => 'price DESC',
            default => 'created_at DESC',
        };
        $sql = 'SELECT * FROM products ' . $whereSql . ' ORDER BY ' . $order;
        if ($params !== []) {
            $st = $this->pdo->prepare($sql);
            $st->execute($params);
        } else {
            $st = $this->pdo->query($sql);
        }
        return $st ? array_map([$this, 'mapProduct'], $st->fetchAll()) : [];
    }

    public function updatePassword(string $username, string $newPlainPassword): void
    {
        $hash = password_hash($newPlainPassword, PASSWORD_DEFAULT);
        $this->pdo->prepare('UPDATE users SET password_hash = ? WHERE username = ?')->execute([$hash, $username]);
    }

    /**
     * @return list<string>
     */
    public function allProductIds(): array
    {
        $st = $this->pdo->query('SELECT id FROM products ORDER BY created_at DESC');
        if (!$st) {
            return [];
        }
        $out = [];
        foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $id) {
            if (is_string($id) && $id !== '') {
                $out[] = $id;
            }
        }

        return $out;
    }

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
        // START_BLOCK_AUTHENTICATE
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
        // END_BLOCK_AUTHENTICATE
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

    /**
     * Слияние: значения по умолчанию + .env + переопределения из БД.
     *
     * @param array<string, mixed> $settings
     *
     * @return array<string, string>
     */
    public function getMergedSiteContent(array $settings): array
    {
        // START_BLOCK_MERGE_CONTENT
        $defaults = SiteContentDefaults::defaults($settings);
        $st = $this->pdo->query('SELECT content_key, value FROM site_content');
        $over = [];
        if ($st) {
            foreach ($st->fetchAll() as $row) {
                $over[(string) $row['content_key']] = (string) $row['value'];
            }
        }

        $merged = array_merge($defaults, $over);
        $merged = SiteContentDefaults::inheritLegacyLayoutToggles($merged, $over);

        return SiteContentDefaults::applyMetaPlaceholders($merged, (string) ($settings['site_name'] ?? ''));
        // END_BLOCK_MERGE_CONTENT
    }

    /**
     * @param array<string, string> $pairs только известные ключи; пустая строка — сброс к значению по умолчанию (удаление из БД)
     */
    public function saveSiteContent(array $pairs): void
    {
        $allowed = array_flip(SiteContentDefaults::allKeys());
        $del = $this->pdo->prepare('DELETE FROM site_content WHERE content_key = ?');
        $up = $this->pdo->prepare(
            'INSERT INTO site_content (content_key, value) VALUES (?, ?)
             ON CONFLICT(content_key) DO UPDATE SET value = excluded.value'
        );
        foreach ($pairs as $k => $v) {
            if (!isset($allowed[$k])) {
                continue;
            }
            if ($v === '') {
                $del->execute([$k]);
            } else {
                $up->execute([$k, $v]);
            }
        }
    }

    public function faqById(string $id): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM faqs WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function createFaq(string $question, string $answer, string $category): string
    {
        $id = (string) (int) (microtime(true) * 1000);
        $this->pdo->prepare(
            'INSERT INTO faqs (id, question, answer, category) VALUES (?, ?, ?, ?)'
        )->execute([$id, $question, $answer, $category]);

        return $id;
    }

    public function updateFaq(string $id, ?string $question, ?string $answer, ?string $category): void
    {
        $fields = [];
        $vals = [];
        if ($question !== null) {
            $fields[] = 'question = ?';
            $vals[] = $question;
        }
        if ($answer !== null) {
            $fields[] = 'answer = ?';
            $vals[] = $answer;
        }
        if ($category !== null) {
            $fields[] = 'category = ?';
            $vals[] = $category;
        }
        if ($fields === []) {
            return;
        }
        $vals[] = $id;
        $this->pdo->prepare('UPDATE faqs SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($vals);
    }

    public function deleteFaq(string $id): void
    {
        $this->pdo->prepare('DELETE FROM faqs WHERE id = ?')->execute([$id]);
    }

    public function reviewById(string $id): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM reviews WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function createReview(string $author, int $rating, string $text, string $date, ?string $productId): string
    {
        $id = (string) (int) (microtime(true) * 1000);
        $this->pdo->prepare(
            'INSERT INTO reviews (id, author, rating, text, date, product_id) VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([$id, $author, max(1, min(5, $rating)), $text, $date, $productId]);

        return $id;
    }

    public function updateReview(string $id, string $author, int $rating, string $text, string $date, ?string $productId): void
    {
        $this->pdo->prepare(
            'UPDATE reviews SET author = ?, rating = ?, text = ?, date = ?, product_id = ? WHERE id = ?'
        )->execute([
            $author,
            max(1, min(5, $rating)),
            $text,
            $date,
            $productId,
            $id,
        ]);
    }

    public function deleteReview(string $id): void
    {
        $this->pdo->prepare('DELETE FROM reviews WHERE id = ?')->execute([$id]);
    }

    public function appendAuditLog(
        string $channel,
        string $method,
        string $path,
        ?string $queryString,
        int $statusCode,
        int $durationMs,
        string $ip,
        string $userAgent,
        ?string $adminUsername,
        ?string $referer,
        string $contextJson,
    ): void {
        $this->pdo->prepare(
            'INSERT INTO audit_log (
                created_at, channel, method, path, query_string, status_code, duration_ms,
                ip, user_agent, admin_username, referer, context_json
            ) VALUES (datetime(\'now\'), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $channel,
            $method,
            $path,
            $queryString,
            $statusCode,
            $durationMs,
            $ip,
            $userAgent,
            $adminUsername,
            $referer,
            $contextJson,
        ]);
        $this->pruneAuditLogIfNeeded();
    }

    private function pruneAuditLogIfNeeded(): void
    {
        $maxRows = 25000;
        $keep = 20000;
        $c = (int) $this->pdo->query('SELECT COUNT(*) FROM audit_log')->fetchColumn();
        if ($c <= $maxRows) {
            return;
        }
        $del = $c - $keep;
        $st = $this->pdo->prepare(
            'DELETE FROM audit_log WHERE rowid IN (
                SELECT rowid FROM audit_log ORDER BY id ASC LIMIT ?
            )'
        );
        $st->bindValue(1, $del, PDO::PARAM_INT);
        $st->execute();
    }

    /** @return list<array<string, mixed>> */
    public function auditLogs(?string $channel, int $limit, int $offset): array
    {
        $limit = max(1, min(200, $limit));
        $offset = max(0, $offset);
        if ($channel === 'admin' || $channel === 'public') {
            $st = $this->pdo->prepare(
                'SELECT * FROM audit_log WHERE channel = ? ORDER BY id DESC LIMIT ? OFFSET ?'
            );
            $st->bindValue(1, $channel, PDO::PARAM_STR);
            $st->bindValue(2, $limit, PDO::PARAM_INT);
            $st->bindValue(3, $offset, PDO::PARAM_INT);
            $st->execute();
        } else {
            $st = $this->pdo->prepare('SELECT * FROM audit_log ORDER BY id DESC LIMIT ? OFFSET ?');
            $st->bindValue(1, $limit, PDO::PARAM_INT);
            $st->bindValue(2, $offset, PDO::PARAM_INT);
            $st->execute();
        }
        $rows = $st->fetchAll();

        return $rows ?: [];
    }

    public function auditLogsCount(?string $channel): int
    {
        if ($channel === 'admin' || $channel === 'public') {
            $st = $this->pdo->prepare('SELECT COUNT(*) FROM audit_log WHERE channel = ?');
            $st->execute([$channel]);
        } else {
            $st = $this->pdo->query('SELECT COUNT(*) FROM audit_log');
        }

        return (int) $st->fetchColumn();
    }

    public function clearAuditLogs(): void
    {
        $this->pdo->exec('DELETE FROM audit_log');
    }

    /**
     * Одно значение CMS (дефолт из кода + переопределение в site_content).
     *
     * @param array<string, mixed> $settings
     */
    public function getSingleSiteContent(string $key, array $settings): string
    {
        $defaults = SiteContentDefaults::defaults($settings);
        $base = (string) ($defaults[$key] ?? '');
        $st = $this->pdo->prepare('SELECT value FROM site_content WHERE content_key = ?');
        $st->execute([$key]);
        $row = $st->fetchColumn();

        return $row !== false ? (string) $row : $base;
    }

    /** @param array<string, mixed> $settings */
    public function isAuditLogEnabled(array $settings): bool
    {
        $v = strtolower(trim($this->getSingleSiteContent('audit.log_enabled', $settings)));

        return $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on';
    }

    /** @param array<string, mixed> $settings */
    public function isAuditLogVerbose(array $settings): bool
    {
        $v = strtolower(trim($this->getSingleSiteContent('audit.log_verbose', $settings)));

        return $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on';
    }

    /**
     * Имена пользовательских таблиц (без служебных sqlite_*).
     *
     * @return list<string>
     */
    public function listSqliteTables(): array
    {
        $st = $this->pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
        if (!$st) {
            return [];
        }
        $out = [];
        foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $name) {
            if (is_string($name) && $name !== '') {
                $out[] = $name;
            }
        }

        return $out;
    }

    public function countTableRows(string $table): int
    {
        $t = $this->assertKnownTable($table);
        $st = $this->pdo->query('SELECT COUNT(*) FROM ' . self::quoteSqliteIdentifier($t));

        return (int) ($st?->fetchColumn() ?? 0);
    }

    /**
     * Имена колонок (в т.ч. для пустой таблицы).
     *
     * @return list<string>
     */
    public function tableColumnNames(string $table): array
    {
        $t = $this->assertKnownTable($table);
        $st = $this->pdo->query('PRAGMA table_info(' . self::quoteSqliteIdentifier($t) . ')');
        if (!$st) {
            return [];
        }
        $cols = [];
        foreach ($st->fetchAll() as $row) {
            if (isset($row['name'])) {
                $cols[] = (string) $row['name'];
            }
        }

        return $cols;
    }

    /**
     * Первые строки таблицы (для предпросмотра в админке).
     *
     * @return list<array<string, mixed>>
     */
    public function tableRowsPreview(string $table, int $limit = 30): array
    {
        $t = $this->assertKnownTable($table);
        $lim = max(1, min(100, $limit));
        $st = $this->pdo->query(
            'SELECT * FROM ' . self::quoteSqliteIdentifier($t) . ' LIMIT ' . $lim
        );
        $rows = $st ? $st->fetchAll() : [];

        return $this->sanitizeRowsForDisplay($t, $rows);
    }

    private function assertKnownTable(string $name): string
    {
        if (!in_array($name, $this->listSqliteTables(), true)) {
            throw new \InvalidArgumentException('Unknown table: ' . $name);
        }

        return $name;
    }

    /** @param list<array<string, mixed>> $rows */
    private function sanitizeRowsForDisplay(string $table, array $rows): array
    {
        if ($table !== 'users') {
            return $rows;
        }
        foreach ($rows as &$r) {
            if (isset($r['password_hash'])) {
                $r['password_hash'] = '***';
            }
        }
        unset($r);

        return $rows;
    }

    public static function quoteSqliteIdentifier(string $ident): string
    {
        return '"' . str_replace('"', '""', $ident) . '"';
    }

    /** @return list<string> */
    public static function contactMessageStatuses(): array
    {
        return ['new', 'done', 'archived'];
    }

    public function createContactMessage(string $name, string $email, string $message, string $ip, ?string $userAgent): int
    {
        $name = self::clipStr(trim($name), 500);
        $email = self::clipStr(trim($email), 320);
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = '';
        }
        $message = self::clipStr(trim($message), 20000);
        $ip = self::clipStr($ip, 80);
        $ua = $userAgent !== null ? self::clipStr($userAgent, 2000) : null;
        $this->pdo->prepare(
            'INSERT INTO contact_messages (name, email, message, ip, user_agent) VALUES (?, ?, ?, ?, ?)'
        )->execute([$name, $email, $message, $ip, $ua]);

        return (int) $this->pdo->lastInsertId();
    }

    public function contactMessagesCount(?string $status = null): int
    {
        if ($status !== null && in_array($status, self::contactMessageStatuses(), true)) {
            $st = $this->pdo->prepare('SELECT COUNT(*) FROM contact_messages WHERE status = ?');
            $st->execute([$status]);
        } else {
            $st = $this->pdo->query('SELECT COUNT(*) FROM contact_messages');
        }

        return (int) $st->fetchColumn();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function contactMessages(?string $status, int $limit, int $offset): array
    {
        $limit = max(1, min(200, $limit));
        $offset = max(0, $offset);
        if ($status !== null && in_array($status, self::contactMessageStatuses(), true)) {
            $st = $this->pdo->prepare(
                'SELECT id, created_at, name, email, substr(message, 1, 200) AS excerpt, status, ip
                 FROM contact_messages WHERE status = ? ORDER BY id DESC LIMIT ? OFFSET ?'
            );
            $st->bindValue(1, $status, PDO::PARAM_STR);
            $st->bindValue(2, $limit, PDO::PARAM_INT);
            $st->bindValue(3, $offset, PDO::PARAM_INT);
            $st->execute();
        } else {
            $st = $this->pdo->prepare(
                'SELECT id, created_at, name, email, substr(message, 1, 200) AS excerpt, status, ip
                 FROM contact_messages ORDER BY id DESC LIMIT ? OFFSET ?'
            );
            $st->bindValue(1, $limit, PDO::PARAM_INT);
            $st->bindValue(2, $offset, PDO::PARAM_INT);
            $st->execute();
        }

        return $st->fetchAll() ?: [];
    }

    /** @return ?array<string, mixed> */
    public function contactMessageById(int $id): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM contact_messages WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();

        return $row ?: null;
    }

    public function contactMessageSetStatus(int $id, string $status): bool
    {
        if (!in_array($status, self::contactMessageStatuses(), true)) {
            return false;
        }
        $st = $this->pdo->prepare('UPDATE contact_messages SET status = ? WHERE id = ?');
        $st->execute([$status, $id]);

        return $st->rowCount() > 0;
    }

    public function deleteContactMessage(int $id): void
    {
        $this->pdo->prepare('DELETE FROM contact_messages WHERE id = ?')->execute([$id]);
    }

    private static function clipStr(string $s, int $max): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($s, 0, $max);
        }

        return substr($s, 0, $max);
    }
}
