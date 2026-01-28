<?php

namespace App\Core;

/**
 * Factory para escolher a implementação de relatório baseada na versão do PHP
 */
class RelatorioFactory
{
    /**
     * Retorna a classe de relatório apropriada
     */
    public static function getRelatorioClass($useTemplate = false)
    {
        // Se TCPDF estiver disponível, sempre usar template (mais moderno)
        if (class_exists('TCPDF')) {
            return RelatorioTemplate::class;
        }
        
        // Fallback para implementação compatível
        return RelatorioCompativel::class;
    }
    
    /**
     * Gera PDF de OS usando a implementação apropriada
     */
    public static function gerarPDFOS($os, $materiais, $useTemplate = false)
    {
        $class = self::getRelatorioClass($useTemplate);
        return $class::gerarPDFOS($os, $materiais);
    }
    
    /**
     * Gera PDF de OS usando template
     */
    public static function gerarPDFOSTemplate($os, $materiais)
    {
        return self::gerarPDFOS($os, $materiais, true);
    }
    
    /**
     * Gera Excel de OS usando a implementação apropriada
     */
    public static function gerarExcelOS($os, $materiais)
    {
        $class = self::getRelatorioClass();
        return $class::gerarExcelOS($os, $materiais);
    }
    
    /**
     * Gera PDF de resumo usando a implementação apropriada
     */
    public static function gerarPDFResumo($resumo, $performanceTecnicos, $topProdutos, $dataInicio, $dataFim)
    {
        $class = self::getRelatorioClass();
        return $class::gerarPDFResumo($resumo, $performanceTecnicos, $topProdutos, $dataInicio, $dataFim);
    }
    
    /**
     * Gera Excel de resumo usando a implementação apropriada
     */
    public static function gerarExcelResumo($resumo, $performanceTecnicos, $topProdutos, $dataInicio, $dataFim)
    {
        $class = self::getRelatorioClass();
        return $class::gerarExcelResumo($resumo, $performanceTecnicos, $topProdutos, $dataInicio, $dataFim);
    }
    
    /**
     * Gera PDF de produtos
     */
    public static function gerarPDFProdutos($estatisticas, $produtosConsumidos, $estoqueAtual, $dataInicio, $dataFim)
    {
        $class = self::getRelatorioClass();
        return $class::gerarPDFProdutos($estatisticas, $produtosConsumidos, $estoqueAtual, $dataInicio, $dataFim);
    }
    
    /**
     * Gera Excel de produtos
     */
    public static function gerarExcelProdutos($estatisticas, $produtosConsumidos, $estoqueAtual, $dataInicio, $dataFim)
    {
        $class = self::getRelatorioClass();
        return $class::gerarExcelProdutos($estatisticas, $produtosConsumidos, $estoqueAtual, $dataInicio, $dataFim);
    }
}