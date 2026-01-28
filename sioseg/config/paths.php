<?php
/**
 * Configuração de caminhos do sistema
 */

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('DOCS_PATH', ROOT_PATH . '/docs');
define('SCRIPTS_PATH', ROOT_PATH . '/scripts');
define('LOGS_PATH', ROOT_PATH . '/logs');

// Caminhos de armazenamento
define('DOCUMENTOS_GERADOS_PATH', STORAGE_PATH . '/documentos_gerados');
define('MODELOS_DOCUMENTOS_PATH', STORAGE_PATH . '/modelos_documentos');

// Caminhos de assets
define('ASSETS_PATH', PUBLIC_PATH . '/assets');
define('CSS_PATH', ASSETS_PATH . '/css');
define('JS_PATH', ASSETS_PATH . '/js');
define('IMG_PATH', ASSETS_PATH . '/img');

return [
    'root' => ROOT_PATH,
    'app' => APP_PATH,
    'public' => PUBLIC_PATH,
    'config' => CONFIG_PATH,
    'storage' => STORAGE_PATH,
    'docs' => DOCS_PATH,
    'scripts' => SCRIPTS_PATH,
    'logs' => LOGS_PATH,
    'documentos_gerados' => DOCUMENTOS_GERADOS_PATH,
    'modelos_documentos' => MODELOS_DOCUMENTOS_PATH,
    'assets' => ASSETS_PATH,
    'css' => CSS_PATH,
    'js' => JS_PATH,
    'img' => IMG_PATH
];