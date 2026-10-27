<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

require __DIR__.'/vendor/autoload.php';
chdir(__DIR__);
umask(0077);

try {
    $path = __DIR__.'/.env';
    $configuration = file_exists($path)
        ? file_get_contents($path)
        : file_get_contents(__DIR__.'/.env.example');

    if (preg_match('/^APP_KEY=$/m', $configuration)) {
        $configuration = preg_replace('/^APP_KEY=$/m', 'APP_KEY=base64:'.base64_encode(random_bytes(32)), $configuration);
    }
    if (preg_match('/^API_TOKEN_HASH=$/m', $configuration)) {
        $tokenPath = __DIR__.'/storage/api-token.txt';
        if (file_exists($tokenPath)) {
            throw new RuntimeException('Ya existe un token local. Revisa la configuración antes de continuar.');
        }
        $token = bin2hex(random_bytes(32));
        file_put_contents($tokenPath, $token.PHP_EOL, LOCK_EX);
        chmod($tokenPath, 0600);
        $configuration = preg_replace('/^API_TOKEN_HASH=$/m', 'API_TOKEN_HASH='.hash('sha256', $token), $configuration);
    }
    if (! preg_match('/^API_TOKEN_HASH=[a-f0-9]{64}$/m', $configuration)
        || ! preg_match('/^APP_KEY=base64:[A-Za-z0-9+\/]{43}=$/m', $configuration)) {
        throw new RuntimeException('APP_KEY o API_TOKEN_HASH no tienen un formato válido.');
    }
    file_put_contents($path, $configuration, LOCK_EX);
    chmod($path, 0600);

    $database = __DIR__.'/database/database.sqlite';
    if (! file_exists($database)) {
        touch($database);
        chmod($database, 0600);
    }
    foreach ([['migrate', '--force'], ['db:seed', '--force']] as $command) {
        $process = new Process([PHP_BINARY, 'artisan', ...$command], __DIR__);
        $process->mustRun(function (string $type, string $output): void {
            echo $output;
        });
    }
    echo 'Configuración completada. El token local está en storage/api-token.txt.'.PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, 'No se pudo completar la configuración: '.$error->getMessage().PHP_EOL);
    exit(1);
}
