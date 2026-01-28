<?php

namespace App\Core;

/**
 * Gerenciador de logs com rotação automática
 */
class LogManager
{
    private $logFile;
    private $maxFileSize;
    private $maxFiles;
    private $keepLines;
    
    public function __construct($logFile = null, $maxFileSize = 10485760, $maxFiles = 5, $keepLines = 1000)
    {
        $this->logFile = $logFile ?: dirname(__DIR__, 2) . '/app_error.log';
        $this->maxFileSize = $maxFileSize; // 10MB por padrão
        $this->maxFiles = $maxFiles; // Manter 5 arquivos rotacionados
        $this->keepLines = $keepLines; // Manter 1000 linhas quando limpar
    }
    
    /**
     * Escreve uma mensagem no log e verifica se precisa rotacionar
     */
    public function log($message, $level = 'INFO')
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] $level: $message" . PHP_EOL;
        
        // Escreve no arquivo
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Verifica se precisa rotacionar
        $this->checkAndRotate();
    }
    
    /**
     * Verifica se o arquivo precisa ser rotacionado
     */
    private function checkAndRotate()
    {
        if (!file_exists($this->logFile)) {
            return;
        }
        
        $fileSize = filesize($this->logFile);
        
        if ($fileSize > $this->maxFileSize) {
            $this->rotateLog();
        }
    }
    
    /**
     * Rotaciona o arquivo de log
     */
    private function rotateLog()
    {
        $logDir = dirname($this->logFile);
        $logName = basename($this->logFile, '.log');
        
        // Move arquivos existentes
        for ($i = $this->maxFiles - 1; $i >= 1; $i--) {
            $oldFile = "$logDir/{$logName}.{$i}.log";
            $newFile = "$logDir/{$logName}." . ($i + 1) . ".log";
            
            if (file_exists($oldFile)) {
                if ($i == $this->maxFiles - 1) {
                    // Remove o arquivo mais antigo
                    unlink($oldFile);
                } else {
                    rename($oldFile, $newFile);
                }
            }
        }
        
        // Move o arquivo atual para .1
        $rotatedFile = "$logDir/{$logName}.1.log";
        rename($this->logFile, $rotatedFile);
        
        // Cria novo arquivo vazio
        touch($this->logFile);
        
        // Log da rotação
        $this->log("Log rotacionado - arquivo anterior salvo como " . basename($rotatedFile), 'SYSTEM');
    }
    
    /**
     * Limpa o arquivo atual mantendo apenas as últimas linhas
     */
    public function cleanCurrentLog()
    {
        if (!file_exists($this->logFile)) {
            return false;
        }
        
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES);
        $totalLines = count($lines);
        
        if ($totalLines > $this->keepLines) {
            // Cria backup
            $backupFile = $this->logFile . '.backup.' . date('Y-m-d_H-i-s');
            copy($this->logFile, $backupFile);
            
            // Mantém apenas as últimas linhas
            $linesToKeep = array_slice($lines, -$this->keepLines);
            file_put_contents($this->logFile, implode("\n", $linesToKeep) . "\n");
            
            $this->log("Log limpo - mantidas {$this->keepLines} linhas de $totalLines", 'SYSTEM');
            return true;
        }
        
        return false;
    }
    
    /**
     * Limpa completamente o arquivo de log
     */
    public function clearLog()
    {
        if (!file_exists($this->logFile)) {
            return false;
        }
        
        // Cria backup
        $backupFile = $this->logFile . '.backup.' . date('Y-m-d_H-i-s');
        copy($this->logFile, $backupFile);
        
        // Limpa o arquivo
        file_put_contents($this->logFile, '');
        
        $this->log("Log completamente limpo", 'SYSTEM');
        return true;
    }
    
    /**
     * Remove backups antigos
     */
    public function cleanOldBackups($daysOld = 7)
    {
        $logDir = dirname($this->logFile);
        $backupPattern = $logDir . '/*.backup.*';
        $backupFiles = glob($backupPattern);
        $cutoffTime = time() - ($daysOld * 24 * 60 * 60);
        $removed = 0;
        
        foreach ($backupFiles as $backupFile) {
            if (filemtime($backupFile) < $cutoffTime) {
                unlink($backupFile);
                $removed++;
            }
        }
        
        return $removed;
    }
    
    /**
     * Obtém informações sobre o arquivo de log
     */
    public function getLogInfo()
    {
        if (!file_exists($this->logFile)) {
            return [
                'exists' => false,
                'size' => 0,
                'lines' => 0,
                'last_modified' => null
            ];
        }
        
        $size = filesize($this->logFile);
        $lines = count(file($this->logFile));
        $lastModified = filemtime($this->logFile);
        
        return [
            'exists' => true,
            'size' => $size,
            'size_formatted' => $this->formatBytes($size),
            'lines' => $lines,
            'last_modified' => $lastModified,
            'last_modified_formatted' => date('Y-m-d H:i:s', $lastModified),
            'needs_rotation' => $size > $this->maxFileSize
        ];
    }
    
    /**
     * Formata bytes em formato legível
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
}