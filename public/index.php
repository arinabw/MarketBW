<?php

/**
 * Единственная точка входа HTTP. Подключает Slim-приложение: `app/bootstrap.php`.
 *
 * @package MarketBW
 */

declare(strict_types=1);

(require dirname(__DIR__) . '/app/bootstrap.php')->run();
