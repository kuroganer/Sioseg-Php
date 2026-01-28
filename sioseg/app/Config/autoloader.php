<?php
// app/Config/autoloader.php

// Carrega configurações de caminhos
require_once dirname(__DIR__, 2) . '/config/paths.php';

// Carrega vendor se existir
if (file_exists(dirname(__DIR__, 2) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
}

spl_autoload_register(function ($className) {
    // Mapeia o namespace base 'App\' para o diretório 'app/'
    $prefix = 'App\\';
    $baseDir = defined('APP_ROOT') ? APP_ROOT . '/app/' : __DIR__ . '/../';

    // Verifica se a classe usa o prefixo do namespace
    $len = strlen($prefix);
    if (strncmp($prefix, $className, $len) !== 0) {
        // Não, passa para o próximo autoloader registrado (se houver)
        return;
    }

    // Pega o nome relativo da classe (ex: Core\Router)
    $relativeClass = substr($className, $len);

    // Substitui os separadores de namespace por separadores de diretório
    // e adiciona a extensão .php
    // App\Core\Router -> app/Core/Router.php
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // Se o arquivo existir, carrega-o
    if (file_exists($file)) {
        require $file;
    }
});
