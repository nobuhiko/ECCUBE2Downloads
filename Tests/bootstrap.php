<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Polyfill\Php84\Php84;

require __DIR__.'/../../../../vendor/autoload.php';

// 本体の tests/bootstrap.php と同様に、bcround() の遅延 autoload 失敗を防ぐため事前にロードする.
if (class_exists(Php84::class)) {
    class_exists(Php84::class);
}

$envFile = __DIR__.'/../../../../.env';
if (file_exists($envFile)) {
    (new Dotenv())->bootEnv($envFile);
}
