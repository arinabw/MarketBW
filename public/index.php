<?php

// FILE: public/index.php
// VERSION: 3.10.0
// START_MODULE_CONTRACT
//   PURPOSE: Единственная точка входа HTTP — подключает bootstrap и запускает Slim
//   SCOPE: require bootstrap.php → $app->run()
//   DEPENDS: M-BOOTSTRAP
//   LINKS: M-ENTRY
// END_MODULE_CONTRACT

declare(strict_types=1);

(require dirname(__DIR__) . '/app/bootstrap.php')->run();
