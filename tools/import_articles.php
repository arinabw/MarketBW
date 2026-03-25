<?php

/**
 * Однократный импорт статей из temp/_extracted/*.txt в БД.
 * Запуск из корня проекта: php tools/import_articles.php
 */

declare(strict_types=1);

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

$settings = require $root . '/config/settings.php';

$db = new App\Database($settings['data_dir']);
$db->init();

$dir = $root . '/temp/_extracted';
$n = App\ArticleImport::importFromTxtDirectory($db, $dir);

echo "imported: {$n}\n";
