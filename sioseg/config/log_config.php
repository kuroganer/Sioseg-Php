<?php
/**
 * Configurações do sistema de logs
 */

return [
    // Arquivo principal de log
    'log_file' => dirname(__DIR__) . '/app_error.log',
    
    // Tamanho máximo do arquivo antes da rotação (em bytes)
    // 10MB = 10 * 1024 * 1024
    'max_file_size' => 10 * 1024 * 1024,
    
    // Número máximo de arquivos rotacionados a manter
    'max_files' => 5,
    
    // Número de linhas a manter quando fazer limpeza parcial
    'keep_lines' => 1000,
    
    // Dias para manter backups antes de deletar
    'backup_retention_days' => 7,
    
    // Ativar rotação automática
    'auto_rotation' => true,
    
    // Ativar limpeza automática de backups antigos
    'auto_cleanup_backups' => true,
    
    // Níveis de log
    'log_levels' => [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
        'CRITICAL' => 4
    ],
    
    // Nível mínimo para registrar (0 = todos, 4 = apenas críticos)
    'min_log_level' => 0
];