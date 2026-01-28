<?php

namespace App\Core;

/**
 * Classe de relatórios compatível com máquinas antigas
 * Usa métodos simples que funcionam em PHP 7.4+
 */
class RelatorioCompativel
{
    /**
     * Gera PDF simples usando HTML
     */
    public static function gerarPDFOS($os, $materiais)
    {
        $html = self::gerarHTMLOS($os, $materiais);
        
        header('Content-Type: text/html');
        header('Content-Disposition: inline; filename="OS_' . $os->id_os . '.html"');
        
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>OS #' . $os->id_os . '</title>';
        echo '<style>body{font-family:Arial;margin:20px;}table{border-collapse:collapse;width:100%;}th,td{border:1px solid #ddd;padding:8px;text-align:left;}th{background:#f2f2f2;}</style>';
        echo '</head><body>';
        echo $html;
        echo '<script>window.print();</script></body></html>';
    }

    /**
     * Gera Excel usando template
     */
    public static function gerarExcelOS($os, $materiais)
    {
        try {
            $template = \App\Core\TemplateManager::carregarTemplate('template_excel_os');
        } catch (\Exception $e) {
            // Fallback para método simples
            self::gerarExcelOSSimples($os, $materiais);
            return;
        }
        $html = self::processarTemplateOS($template, $os, $materiais);
        
        $filename = "OS_{$os->id_os}.xls";
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo $html;
    }
    
    /**
     * Fallback Excel simples
     */
    private static function gerarExcelOSSimples($os, $materiais)
    {
        $filename = "OS_{$os->id_os}.xls";
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo self::gerarHTMLOS($os, $materiais);
        echo '</body></html>';
    }

    /**
     * Gera HTML base para OS
     */
    private static function gerarHTMLOS($os, $materiais)
    {
        $nomeEmpresa = self::getNomeEmpresa();
        $html = '<h2>' . $nomeEmpresa . ' - ORDEM DE SERVIÇO #' . $os->id_os . '</h2>';
        
        $html .= '<table>';
        $html .= '<tr><th>Campo</th><th>Valor</th></tr>';
        $html .= '<tr><td>Status</td><td>' . ucfirst($os->status) . '</td></tr>';
        $html .= '<tr><td>Tipo</td><td>' . ucfirst($os->tipo_servico) . '</td></tr>';
        $html .= '<tr><td>Serviço</td><td>' . htmlspecialchars($os->servico_prestado) . '</td></tr>';
        $html .= '<tr><td>Data Abertura</td><td>' . date('d/m/Y H:i', strtotime($os->data_abertura)) . '</td></tr>';
        
        if ($os->data_encerramento) {
            $html .= '<tr><td>Data Encerramento</td><td>' . date('d/m/Y H:i', strtotime($os->data_encerramento)) . '</td></tr>';
        }
        
        $nomeCliente = $os->tipo_pessoa === 'juridica' ? $os->razao_social : $os->nome_cli;
        $html .= '<tr><td>Cliente</td><td>' . htmlspecialchars($nomeCliente) . '</td></tr>';
        $html .= '<tr><td>Telefone</td><td>' . htmlspecialchars($os->tel1_cli) . '</td></tr>';
        $html .= '<tr><td>Técnico</td><td>' . htmlspecialchars($os->nome_tec) . '</td></tr>';
        $html .= '</table>';
        
        if (!empty($materiais)) {
            $html .= '<h3>MATERIAIS UTILIZADOS</h3>';
            $html .= '<table>';
            $html .= '<tr><th>Produto</th><th>Quantidade</th></tr>';
            foreach ($materiais as $material) {
                $html .= '<tr><td>' . htmlspecialchars($material['nome']) . '</td><td>' . $material['qtd_usada'] . '</td></tr>';
            }
            $html .= '</table>';
        }
        
        if ($os->nota) {
            $html .= '<h3>AVALIAÇÃO</h3>';
            $html .= '<p>Nota: ' . $os->nota . ' estrelas</p>';
            if ($os->comentario) {
                $html .= '<p>Comentário: ' . htmlspecialchars($os->comentario) . '</p>';
            }
        }
        
        return $html;
    }

    /**
     * Gera resumo geral em Excel
     */
    public static function gerarExcelResumo($resumo, $performanceTecnicos, $topProdutos, $dataInicio, $dataFim)
    {
        $filename = "Resumo_Geral.xls";
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo '<html><head><meta charset="UTF-8"></head><body>';
        $nomeEmpresa = self::getNomeEmpresa();
        echo '<h2>' . $nomeEmpresa . ' - RESUMO GERAL - ORDENS DE SERVIÇO</h2>';
        echo '<p>Período: ' . date('d/m/Y', strtotime($dataInicio)) . ' a ' . date('d/m/Y', strtotime($dataFim)) . '</p>';
        
        echo '<h3>ESTATÍSTICAS</h3>';
        echo '<table border="1">';
        echo '<tr><th>Métrica</th><th>Valor</th></tr>';
        echo '<tr><td>Total OS</td><td>' . ($resumo['total_os'] ?? 0) . '</td></tr>';
        echo '<tr><td>Concluídas</td><td>' . ($resumo['concluidas'] ?? 0) . '</td></tr>';
        echo '<tr><td>Em Andamento</td><td>' . ($resumo['em_andamento'] ?? 0) . '</td></tr>';
        echo '<tr><td>Abertas</td><td>' . ($resumo['abertas'] ?? 0) . '</td></tr>';
        echo '</table>';
        
        if (!empty($performanceTecnicos)) {
            echo '<h3>PERFORMANCE TÉCNICOS</h3>';
            echo '<table border="1">';
            echo '<tr><th>Técnico</th><th>Total OS</th><th>Concluídas</th><th>Taxa %</th></tr>';
            foreach ($performanceTecnicos as $tecnico) {
                $taxa = $tecnico['total_os'] > 0 ? ($tecnico['os_concluidas'] / $tecnico['total_os']) * 100 : 0;
                echo '<tr>';
                echo '<td>' . htmlspecialchars($tecnico['nome_tec']) . '</td>';
                echo '<td>' . $tecnico['total_os'] . '</td>';
                echo '<td>' . $tecnico['os_concluidas'] . '</td>';
                echo '<td>' . number_format($taxa, 1) . '%</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
        
        echo '</body></html>';
    }

    /**
     * Gera PDF do resumo (HTML para impressão)
     */
    public static function gerarPDFResumo($resumo, $performanceTecnicos, $topProdutos, $dataInicio, $dataFim)
    {
        header('Content-Type: text/html');
        header('Content-Disposition: inline; filename="Resumo_Geral.html"');
        
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Resumo Geral</title>';
        echo '<style>body{font-family:Arial;margin:20px;}table{border-collapse:collapse;width:100%;}th,td{border:1px solid #ddd;padding:8px;}th{background:#f2f2f2;}</style>';
        echo '</head><body>';
        
        $nomeEmpresa = self::getNomeEmpresa();
        echo '<h2>' . $nomeEmpresa . ' - RESUMO GERAL - ORDENS DE SERVIÇO</h2>';
        echo '<p>Período: ' . date('d/m/Y', strtotime($dataInicio)) . ' a ' . date('d/m/Y', strtotime($dataFim)) . '</p>';
        
        echo '<h3>ESTATÍSTICAS</h3>';
        echo '<table>';
        echo '<tr><th>Métrica</th><th>Valor</th></tr>';
        echo '<tr><td>Total OS</td><td>' . ($resumo['total_os'] ?? 0) . '</td></tr>';
        echo '<tr><td>Concluídas</td><td>' . ($resumo['concluidas'] ?? 0) . '</td></tr>';
        echo '<tr><td>Em Andamento</td><td>' . ($resumo['em_andamento'] ?? 0) . '</td></tr>';
        echo '<tr><td>Abertas</td><td>' . ($resumo['abertas'] ?? 0) . '</td></tr>';
        echo '</table>';
        
        if (!empty($performanceTecnicos)) {
            echo '<h3>PERFORMANCE TÉCNICOS</h3>';
            echo '<table>';
            echo '<tr><th>Técnico</th><th>Total OS</th><th>Concluídas</th><th>Taxa %</th></tr>';
            foreach ($performanceTecnicos as $tecnico) {
                $taxa = $tecnico['total_os'] > 0 ? ($tecnico['os_concluidas'] / $tecnico['total_os']) * 100 : 0;
                echo '<tr>';
                echo '<td>' . htmlspecialchars($tecnico['nome_tec']) . '</td>';
                echo '<td>' . $tecnico['total_os'] . '</td>';
                echo '<td>' . $tecnico['os_concluidas'] . '</td>';
                echo '<td>' . number_format($taxa, 1) . '%</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
        
        echo '<script>window.print();</script></body></html>';
    }

    /**
     * Gera Excel de produtos
     */
    public static function gerarExcelProdutos($estatisticas, $produtosConsumidos, $estoqueAtual, $dataInicio, $dataFim)
    {
        $filename = "Relatorio_Produtos.xls";
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo '<html><head><meta charset="UTF-8"></head><body>';
        $nomeEmpresa = self::getNomeEmpresa();
        echo '<h2>' . $nomeEmpresa . ' - RELATÓRIO DE PRODUTOS</h2>';
        echo '<p>Período: ' . date('d/m/Y', strtotime($dataInicio)) . ' a ' . date('d/m/Y', strtotime($dataFim)) . '</p>';
        
        echo '<h3>ESTATÍSTICAS</h3>';
        echo '<table border="1">';
        echo '<tr><th>Métrica</th><th>Valor</th></tr>';
        echo '<tr><td>Total Produtos</td><td>' . ($estatisticas['total_produtos_cadastrados'] ?? 0) . '</td></tr>';
        echo '<tr><td>Produtos Utilizados</td><td>' . ($estatisticas['produtos_utilizados'] ?? 0) . '</td></tr>';
        echo '<tr><td>Total Consumido</td><td>' . ($estatisticas['total_consumido'] ?? 0) . '</td></tr>';
        echo '</table>';
        
        if (!empty($produtosConsumidos)) {
            echo '<h3>PRODUTOS MAIS CONSUMIDOS</h3>';
            echo '<table border="1">';
            echo '<tr><th>Produto</th><th>Marca</th><th>Consumido</th><th>OS</th></tr>';
            foreach ($produtosConsumidos as $produto) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($produto['nome']) . '</td>';
                echo '<td>' . htmlspecialchars($produto['marca']) . '</td>';
                echo '<td>' . $produto['total_consumido'] . '</td>';
                echo '<td>' . $produto['os_utilizadas'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
        
        echo '</body></html>';
    }

    /**
     * Gera PDF de produtos
     */
    public static function gerarPDFProdutos($estatisticas, $produtosConsumidos, $estoqueAtual, $dataInicio, $dataFim)
    {
        header('Content-Type: text/html');
        header('Content-Disposition: inline; filename="Relatorio_Produtos.html"');
        
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Relatório Produtos</title>';
        echo '<style>body{font-family:Arial;margin:20px;}table{border-collapse:collapse;width:100%;}th,td{border:1px solid #ddd;padding:8px;}th{background:#f2f2f2;}</style>';
        echo '</head><body>';
        
        $nomeEmpresa = self::getNomeEmpresa();
        echo '<h2>' . $nomeEmpresa . ' - RELATÓRIO DE PRODUTOS</h2>';
        echo '<p>Período: ' . date('d/m/Y', strtotime($dataInicio)) . ' a ' . date('d/m/Y', strtotime($dataFim)) . '</p>';
        
        echo '<h3>ESTATÍSTICAS</h3>';
        echo '<table>';
        echo '<tr><th>Métrica</th><th>Valor</th></tr>';
        echo '<tr><td>Total Produtos</td><td>' . ($estatisticas['total_produtos_cadastrados'] ?? 0) . '</td></tr>';
        echo '<tr><td>Produtos Utilizados</td><td>' . ($estatisticas['produtos_utilizados'] ?? 0) . '</td></tr>';
        echo '<tr><td>Total Consumido</td><td>' . ($estatisticas['total_consumido'] ?? 0) . '</td></tr>';
        echo '</table>';
        
        if (!empty($produtosConsumidos)) {
            echo '<h3>PRODUTOS MAIS CONSUMIDOS</h3>';
            echo '<table>';
            echo '<tr><th>Produto</th><th>Marca</th><th>Consumido</th><th>OS</th></tr>';
            foreach ($produtosConsumidos as $produto) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($produto['nome']) . '</td>';
                echo '<td>' . htmlspecialchars($produto['marca']) . '</td>';
                echo '<td>' . $produto['total_consumido'] . '</td>';
                echo '<td>' . $produto['os_utilizadas'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
        
        echo '<script>window.print();</script></body></html>';
    }
    
    /**
     * Processa template de OS
     */
    private static function processarTemplateOS($template, $os, $materiais)
    {
        // Substituições básicas
        $replacements = [
            '{{ID_OS}}' => $os->id_os,
            '{{STATUS}}' => strtoupper($os->status),
            '{{STATUS_COLOR}}' => self::getStatusColorHex($os->status),
            '{{TIPO_SERVICO}}' => ucfirst($os->tipo_servico ?? ''),
            '{{SERVICO_PRESTADO}}' => htmlspecialchars($os->servico_prestado ?? ''),
            '{{DATA_ABERTURA}}' => date('d/m/Y H:i', strtotime($os->data_abertura)),
            '{{NOME_CLIENTE}}' => htmlspecialchars($os->tipo_pessoa === 'juridica' ? ($os->razao_social ?? '') : ($os->nome_cli ?? '')),
            '{{TELEFONE}}' => htmlspecialchars($os->tel1_cli ?? ''),
            '{{NOME_TECNICO}}' => htmlspecialchars($os->nome_tec ?? ''),
            '{{DATA_GERACAO}}' => date('d/m/Y H:i:s'),
            '{{NOME_EMPRESA}}' => self::getNomeEmpresa()
        ];
        
        // Data encerramento
        if ($os->data_encerramento) {
            $replacements['{{DATA_ENCERRAMENTO_ROW}}'] = '<tr><td style="width: 25%; background: #f5f5f5; font-weight: bold;">Data Encerramento:</td><td>' . date('d/m/Y H:i', strtotime($os->data_encerramento)) . '</td></tr>';
        } else {
            $replacements['{{DATA_ENCERRAMENTO_ROW}}'] = '';
        }
        
        // Documento
        if ($os->tipo_pessoa === 'juridica' && isset($os->cnpj)) {
            $replacements['{{DOCUMENTO_ROW}}'] = '<tr><td style="width: 25%; background: #f5f5f5; font-weight: bold;">CNPJ:</td><td>' . htmlspecialchars($os->cnpj) . '</td></tr>';
        } elseif (isset($os->cpf_cli)) {
            $replacements['{{DOCUMENTO_ROW}}'] = '<tr><td style="width: 25%; background: #f5f5f5; font-weight: bold;">CPF:</td><td>' . htmlspecialchars($os->cpf_cli) . '</td></tr>';
        } else {
            $replacements['{{DOCUMENTO_ROW}}'] = '';
        }
        
        // Email
        if (isset($os->email_cli)) {
            $replacements['{{EMAIL_ROW}}'] = '<tr><td style="width: 25%; background: #f5f5f5; font-weight: bold;">E-mail:</td><td>' . htmlspecialchars($os->email_cli) . '</td></tr>';
        } else {
            $replacements['{{EMAIL_ROW}}'] = '';
        }
        
        // Endereço
        if (isset($os->endereco)) {
            $replacements['{{ENDERECO_ROW}}'] = '<tr><td style="width: 25%; background: #f5f5f5; font-weight: bold;">Endereço:</td><td>' . htmlspecialchars($os->endereco) . '</td></tr>';
        } else {
            $replacements['{{ENDERECO_ROW}}'] = '';
        }
        
        // Telefone técnico
        if (isset($os->tel_tecnico)) {
            $replacements['{{TELEFONE_TECNICO_ROW}}'] = '<tr><td style="width: 25%; background: #f5f5f5; font-weight: bold;">Telefone:</td><td>' . htmlspecialchars($os->tel_tecnico) . '</td></tr>';
        } else {
            $replacements['{{TELEFONE_TECNICO_ROW}}'] = '';
        }
        
        // Materiais
        if (!empty($materiais)) {
            $materiaisHtml = '<table><tr><td colspan="2" style="background: #f0f0f0; padding: 5px; font-weight: bold;">MATERIAIS UTILIZADOS</td></tr>';
            $materiaisHtml .= '<tr><th style="background: #e0e0e0; font-weight: bold;">Material</th><th style="background: #e0e0e0; font-weight: bold;">Quantidade</th></tr>';
            foreach ($materiais as $material) {
                $materiaisHtml .= '<tr>';
                $materiaisHtml .= '<td>' . htmlspecialchars($material['nome'] ?? '') . '</td>';
                $materiaisHtml .= '<td style="text-align: center;">' . ($material['qtd_usada'] ?? 0) . '</td>';
                $materiaisHtml .= '</tr>';
            }
            $materiaisHtml .= '</table>';
            $replacements['{{MATERIAIS_TABLE}}'] = $materiaisHtml;
        } else {
            $replacements['{{MATERIAIS_TABLE}}'] = '';
        }
        
        // Avaliação
        if (isset($os->nota) && $os->nota) {
            $avaliacaoHtml = '<table><tr><td colspan="2" style="background: #f0f0f0; padding: 5px; font-weight: bold;">AVALIAÇÃO DO CLIENTE</td></tr>';
            $estrelas = str_repeat('★', $os->nota) . str_repeat('☆', 5 - $os->nota);
            $avaliacaoHtml .= '<tr><td style="width: 25%; background: #f5f5f5; font-weight: bold;">Avaliação:</td><td>' . $estrelas . ' (' . $os->nota . '/5)</td></tr>';
            if (isset($os->comentario) && $os->comentario) {
                $avaliacaoHtml .= '<tr><td style="width: 25%; background: #f5f5f5; font-weight: bold;">Comentário:</td><td>' . htmlspecialchars($os->comentario) . '</td></tr>';
            }
            $avaliacaoHtml .= '</table>';
            $replacements['{{AVALIACAO_TABLE}}'] = $avaliacaoHtml;
        } else {
            $replacements['{{AVALIACAO_TABLE}}'] = '';
        }
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
    
    /**
     * Retorna cor hexadecimal para status
     */
    private static function getStatusColorHex($status)
    {
        switch (strtolower($status)) {
            case 'aberta': return '#e74c3c';
            case 'em andamento': return '#f39c12';
            case 'concluida': return '#27ae60';
            case 'encerrada': return '#34495e';
            default: return '#95a5a6';
        }
    }
    
    /**
     * Obtém nome da empresa
     */
    private static function getNomeEmpresa()
    {
        $config = include __DIR__ . '/../../config/empresa.php';
        return $config['nome'] ?? 'SIOSEG';
    }
}