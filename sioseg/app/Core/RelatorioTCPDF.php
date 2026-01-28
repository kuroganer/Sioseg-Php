<?php

namespace App\Core;

use TCPDF;

/**
 * Classe de relatórios usando TCPDF real
 */
class RelatorioTCPDF
{
    /**
     * Gera PDF usando TCPDF
     */
    public static function gerarPDFOS($os, $materiais)
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
        $nomeEmpresa = self::getNomeEmpresa();
        $pdf->SetCreator($nomeEmpresa);
        $pdf->SetAuthor('Sistema ' . $nomeEmpresa);
        $pdf->SetTitle("Ordem de Serviço #{$os->id_os}");
        $pdf->SetSubject('Ordem de Serviço');
        $pdf->SetMargins(15, 20, 15);
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AddPage();
        
        // Cabeçalho
        $pdf->SetFillColor(41, 128, 185);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 8, $nomeEmpresa . ' - SISTEMA INTEGRADO DE ORDENS DE SERVIÇO', 0, 1, 'C', true);
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 15, 'ORDEM DE SERVIÇO', 0, 1, 'C', true);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, "Nº {$os->id_os}", 0, 1, 'C', true);
        $pdf->Ln(5);
        
        // Reset cores
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(240, 240, 240);
        
        // Status com cor
        $statusColor = self::getStatusColor($os->status);
        $pdf->SetFillColor($statusColor[0], $statusColor[1], $statusColor[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'STATUS: ' . strtoupper($os->status), 0, 1, 'C', true);
        $pdf->Ln(3);
        
        // Reset cores
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(245, 245, 245);
        
        // Informações da OS
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'INFORMAÇÕES DA ORDEM DE SERVIÇO', 0, 1, 'L', true);
        $pdf->SetFont('helvetica', '', 10);
        
        $pdf->Cell(50, 6, 'Tipo de Serviço:', 1, 0, 'L');
        $pdf->Cell(0, 6, ucfirst($os->tipo_servico ?? ''), 1, 1, 'L');
        
        $pdf->Cell(50, 6, 'Serviço Prestado:', 1, 0, 'L');
        $pdf->Cell(0, 6, $os->servico_prestado ?? '', 1, 1, 'L');
        
        $pdf->Cell(50, 6, 'Data Abertura:', 1, 0, 'L');
        $pdf->Cell(0, 6, date('d/m/Y H:i', strtotime($os->data_abertura)), 1, 1, 'L');
        
        if ($os->data_encerramento) {
            $pdf->Cell(50, 6, 'Data Encerramento:', 1, 0, 'L');
            $pdf->Cell(0, 6, date('d/m/Y H:i', strtotime($os->data_encerramento)), 1, 1, 'L');
        }
        
        if (isset($os->prioridade)) {
            $pdf->Cell(50, 6, 'Prioridade:', 1, 0, 'L');
            $pdf->Cell(0, 6, ucfirst($os->prioridade), 1, 1, 'L');
        }
        
        $pdf->Ln(5);
        
        // Dados do Cliente
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'DADOS DO CLIENTE', 0, 1, 'L', true);
        $pdf->SetFont('helvetica', '', 10);
        
        $nomeCliente = $os->tipo_pessoa === 'juridica' ? ($os->razao_social ?? '') : ($os->nome_cli ?? '');
        $pdf->Cell(50, 6, 'Nome/Razão Social:', 1, 0, 'L');
        $pdf->Cell(0, 6, $nomeCliente, 1, 1, 'L');
        
        if ($os->tipo_pessoa === 'juridica' && isset($os->cnpj_cli)) {
            $pdf->Cell(50, 6, 'CNPJ:', 1, 0, 'L');
            $pdf->Cell(0, 6, $os->cnpj_cli, 1, 1, 'L');
        } elseif (isset($os->cpf_cli)) {
            $pdf->Cell(50, 6, 'CPF:', 1, 0, 'L');
            $pdf->Cell(0, 6, $os->cpf_cli, 1, 1, 'L');
        }
        
        $pdf->Cell(50, 6, 'Telefone:', 1, 0, 'L');
        $pdf->Cell(0, 6, ($os->tel1_cli ?? '') . (isset($os->tel2_cli) ? ' / ' . $os->tel2_cli : ''), 1, 1, 'L');
        
        if (isset($os->email_cli)) {
            $pdf->Cell(50, 6, 'E-mail:', 1, 0, 'L');
            $pdf->Cell(0, 6, $os->email_cli, 1, 1, 'L');
        }
        
        if (isset($os->endereco_cli)) {
            $pdf->Cell(50, 6, 'Endereço:', 1, 0, 'L');
            $pdf->Cell(0, 6, $os->endereco_cli, 1, 1, 'L');
        }
        
        $pdf->Ln(5);
        
        // Dados do Técnico
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'TÉCNICO RESPONSÁVEL', 0, 1, 'L', true);
        $pdf->SetFont('helvetica', '', 10);
        
        $pdf->Cell(50, 6, 'Nome:', 1, 0, 'L');
        $pdf->Cell(0, 6, $os->nome_tec ?? '', 1, 1, 'L');
        
        if (isset($os->tel_tec)) {
            $pdf->Cell(50, 6, 'Telefone:', 1, 0, 'L');
            $pdf->Cell(0, 6, $os->tel_tec, 1, 1, 'L');
        }
        
        $pdf->Ln(5);
        
        // Descrição do Problema
        if (isset($os->descricao_problema)) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 8, 'DESCRIÇÃO DO PROBLEMA', 0, 1, 'L', true);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->MultiCell(0, 6, $os->descricao_problema, 1, 'L');
            $pdf->Ln(3);
        }
        
        // Solução Aplicada
        if (isset($os->solucao_aplicada)) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 8, 'SOLUÇÃO APLICADA', 0, 1, 'L', true);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->MultiCell(0, 6, $os->solucao_aplicada, 1, 'L');
            $pdf->Ln(3);
        }
        
        // Materiais Utilizados
        if (!empty($materiais)) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 8, 'MATERIAIS UTILIZADOS', 0, 1, 'L', true);
            $pdf->SetFont('helvetica', 'B', 9);
            
            // Cabeçalho da tabela
            $pdf->Cell(80, 6, 'Material', 1, 0, 'C', true);
            $pdf->Cell(30, 6, 'Quantidade', 1, 0, 'C', true);
            $pdf->Cell(30, 6, 'Valor Unit.', 1, 0, 'C', true);
            $pdf->Cell(30, 6, 'Total', 1, 1, 'C', true);
            
            $pdf->SetFont('helvetica', '', 9);
            $totalGeral = 0;
            foreach ($materiais as $material) {
                $valorUnit = isset($material['valor_unitario']) ? $material['valor_unitario'] : 0;
                $total = $valorUnit * $material['qtd_usada'];
                $totalGeral += $total;
                
                $pdf->Cell(80, 6, $material['nome'] ?? '', 1, 0, 'L');
                $pdf->Cell(30, 6, $material['qtd_usada'], 1, 0, 'C');
                $pdf->Cell(30, 6, 'R$ ' . number_format($valorUnit, 2, ',', '.'), 1, 0, 'R');
                $pdf->Cell(30, 6, 'R$ ' . number_format($total, 2, ',', '.'), 1, 1, 'R');
            }
            
            // Total
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(140, 6, 'TOTAL MATERIAIS:', 1, 0, 'R', true);
            $pdf->Cell(30, 6, 'R$ ' . number_format($totalGeral, 2, ',', '.'), 1, 1, 'R', true);
            $pdf->Ln(3);
        }
        
        // Avaliação
        if (isset($os->nota) && $os->nota) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 8, 'AVALIAÇÃO DO CLIENTE', 0, 1, 'L', true);
            $pdf->SetFont('helvetica', '', 10);
            
            $estrelas = str_repeat('★', $os->nota) . str_repeat('☆', 5 - $os->nota);
            $pdf->Cell(50, 6, 'Avaliação:', 1, 0, 'L');
            $pdf->Cell(0, 6, $estrelas . " ({$os->nota}/5)", 1, 1, 'L');
            
            if (isset($os->comentario) && $os->comentario) {
                $pdf->Cell(50, 6, 'Comentário:', 1, 0, 'L');
                $pdf->MultiCell(0, 6, $os->comentario, 1, 'L');
            }
        }
        
        // Rodapé
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'I', 8);
        $nomeEmpresa = self::getNomeEmpresa();
        $pdf->Cell(0, 5, 'Relatório gerado em ' . date('d/m/Y H:i:s') . ' pelo Sistema ' . $nomeEmpresa, 0, 1, 'C');
        
        $pdf->Output("OS_{$os->id_os}.pdf", 'D');
    }
    
    /**
     * Retorna cores para status
     */
    private static function getStatusColor($status)
    {
        switch (strtolower($status)) {
            case 'aberta': return [231, 76, 60];   // Vermelho
            case 'em_andamento': return [241, 196, 15]; // Amarelo
            case 'concluida': return [46, 204, 113]; // Verde
            case 'cancelada': return [149, 165, 166]; // Cinza
            default: return [52, 73, 94]; // Azul escuro
        }
    }
    
    /**
     * Gera Excel usando HTML
     */
    public static function gerarExcelOS($os, $materiais)
    {
        // Usa método HTML para Excel (mais compatível)
        RelatorioCompativel::gerarExcelOS($os, $materiais);
    }
    
    /**
     * Gera PDF de resumo
     */
    public static function gerarPDFResumo($resumo, $performanceTecnicos, $topProdutos, $dataInicio, $dataFim)
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
        $nomeEmpresa = self::getNomeEmpresa();
        $pdf->SetCreator($nomeEmpresa);
        $pdf->SetAuthor('Sistema ' . $nomeEmpresa);
        $pdf->SetTitle('Resumo Geral - Ordens de Serviço');
        $pdf->SetMargins(15, 20, 15);
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AddPage();
        
        // Cabeçalho
        $pdf->SetFillColor(41, 128, 185);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 8, $nomeEmpresa . ' - SISTEMA INTEGRADO DE ORDENS DE SERVIÇO', 0, 1, 'C', true);
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 15, 'RESUMO GERAL', 0, 1, 'C', true);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'ORDENS DE SERVIÇO', 0, 1, 'C', true);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 8, 'Período: ' . date('d/m/Y', strtotime($dataInicio)) . ' a ' . date('d/m/Y', strtotime($dataFim)), 0, 1, 'C', true);
        $pdf->Ln(8);
        
        // Reset cores
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(245, 245, 245);
        
        // Estatísticas Gerais
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'ESTATÍSTICAS GERAIS', 0, 1, 'L', true);
        $pdf->Ln(2);
        
        // Cards de estatísticas
        $totalOS = $resumo['total_os'] ?? 0;
        $concluidas = $resumo['concluidas'] ?? 0;
        $emAndamento = $resumo['em_andamento'] ?? 0;
        $abertas = $resumo['abertas'] ?? 0;
        $canceladas = $resumo['canceladas'] ?? 0;
        
        $pdf->SetFont('helvetica', 'B', 12);
        
        // Linha 1 - Total e Concluídas
        $pdf->SetFillColor(52, 152, 219);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(90, 20, 'TOTAL DE OS', 1, 0, 'C', true);
        $pdf->SetFillColor(46, 204, 113);
        $pdf->Cell(90, 20, 'CONCLUÍDAS', 1, 1, 'C', true);
        
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(52, 152, 219);
        $pdf->Cell(90, 15, $totalOS, 1, 0, 'C');
        $pdf->SetTextColor(46, 204, 113);
        $pdf->Cell(90, 15, $concluidas, 1, 1, 'C');
        
        // Linha 2 - Em Andamento e Abertas
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(241, 196, 15);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(90, 20, 'EM ANDAMENTO', 1, 0, 'C', true);
        $pdf->SetFillColor(231, 76, 60);
        $pdf->Cell(90, 20, 'ABERTAS', 1, 1, 'C', true);
        
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(241, 196, 15);
        $pdf->Cell(90, 15, $emAndamento, 1, 0, 'C');
        $pdf->SetTextColor(231, 76, 60);
        $pdf->Cell(90, 15, $abertas, 1, 1, 'C');
        
        if ($canceladas > 0) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetFillColor(149, 165, 166);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(180, 20, 'CANCELADAS', 1, 1, 'C', true);
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->SetTextColor(149, 165, 166);
            $pdf->Cell(180, 15, $canceladas, 1, 1, 'C');
        }
        
        $pdf->Ln(8);
        
        // Taxa de Conclusão
        if ($totalOS > 0) {
            $taxaConclusao = ($concluidas / $totalOS) * 100;
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 8, 'TAXA DE CONCLUSÃO GERAL', 0, 1, 'L', true);
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->SetTextColor(46, 204, 113);
            $pdf->Cell(0, 10, number_format($taxaConclusao, 1) . '%', 0, 1, 'C');
            $pdf->Ln(5);
        }
        
        // Performance dos Técnicos
        if (!empty($performanceTecnicos)) {
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 10, 'PERFORMANCE DOS TÉCNICOS', 0, 1, 'L', true);
            $pdf->Ln(2);
            
            // Cabeçalho da tabela
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(52, 73, 94);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(60, 8, 'Técnico', 1, 0, 'C', true);
            $pdf->Cell(30, 8, 'Total OS', 1, 0, 'C', true);
            $pdf->Cell(30, 8, 'Concluídas', 1, 0, 'C', true);
            $pdf->Cell(30, 8, 'Em And.', 1, 0, 'C', true);
            $pdf->Cell(30, 8, 'Taxa %', 1, 1, 'C', true);
            
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(0, 0, 0);
            $contador = 0;
            foreach ($performanceTecnicos as $tecnico) {
                if ($contador >= 10) break; // Limita a 10 técnicos
                
                $taxa = $tecnico['total_os'] > 0 ? ($tecnico['os_concluidas'] / $tecnico['total_os']) * 100 : 0;
                $fillColor = $contador % 2 == 0 ? [248, 249, 250] : [255, 255, 255];
                $pdf->SetFillColor($fillColor[0], $fillColor[1], $fillColor[2]);
                
                $pdf->Cell(60, 6, $tecnico['nome_tec'] ?? '', 1, 0, 'L', true);
                $pdf->Cell(30, 6, $tecnico['total_os'], 1, 0, 'C', true);
                $pdf->Cell(30, 6, $tecnico['os_concluidas'], 1, 0, 'C', true);
                $pdf->Cell(30, 6, ($tecnico['total_os'] - $tecnico['os_concluidas']), 1, 0, 'C', true);
                
                // Cor da taxa baseada na performance
                if ($taxa >= 80) $pdf->SetTextColor(46, 204, 113); // Verde
                elseif ($taxa >= 60) $pdf->SetTextColor(241, 196, 15); // Amarelo
                else $pdf->SetTextColor(231, 76, 60); // Vermelho
                
                $pdf->Cell(30, 6, number_format($taxa, 1) . '%', 1, 1, 'C', true);
                $pdf->SetTextColor(0, 0, 0);
                $contador++;
            }
        }
        
        // Produtos mais utilizados
        if (!empty($topProdutos)) {
            $pdf->Ln(8);
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 10, 'PRODUTOS MAIS UTILIZADOS', 0, 1, 'L', true);
            $pdf->Ln(2);
            
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(52, 73, 94);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(120, 8, 'Produto', 1, 0, 'C', true);
            $pdf->Cell(30, 8, 'Quantidade', 1, 0, 'C', true);
            $pdf->Cell(30, 8, 'Freq. Uso', 1, 1, 'C', true);
            
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(0, 0, 0);
            $contador = 0;
            foreach (array_slice($topProdutos, 0, 8) as $produto) {
                $fillColor = $contador % 2 == 0 ? [248, 249, 250] : [255, 255, 255];
                $pdf->SetFillColor($fillColor[0], $fillColor[1], $fillColor[2]);
                
                $pdf->Cell(120, 6, $produto['nome'] ?? '', 1, 0, 'L', true);
                $pdf->Cell(30, 6, $produto['total_usado'] ?? 0, 1, 0, 'C', true);
                $pdf->Cell(30, 6, ($produto['os_utilizadas'] ?? 0) . 'x', 1, 1, 'C', true);
                $contador++;
            }
        }
        
        // Rodapé
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(128, 128, 128);
        $nomeEmpresa = self::getNomeEmpresa();
        $pdf->Cell(0, 5, 'Relatório gerado em ' . date('d/m/Y H:i:s') . ' pelo Sistema ' . $nomeEmpresa, 0, 1, 'C');
        
        $pdf->Output('Resumo_Geral.pdf', 'D');
    }
    
    /**
     * Gera Excel de resumo
     */
    public static function gerarExcelResumo($resumo, $performanceTecnicos, $topProdutos, $dataInicio, $dataFim)
    {
        // Usa método HTML para Excel (mais compatível)
        RelatorioCompativel::gerarExcelResumo($resumo, $performanceTecnicos, $topProdutos, $dataInicio, $dataFim);
    }
    
    /**
     * Gera PDF de produtos
     */
    public static function gerarPDFProdutos($estatisticas, $produtosConsumidos, $estoqueAtual, $dataInicio, $dataFim)
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
        $nomeEmpresa = self::getNomeEmpresa();
        $pdf->SetCreator($nomeEmpresa);
        $pdf->SetAuthor('Sistema ' . $nomeEmpresa);
        $pdf->SetTitle('Relatório de Produtos');
        $pdf->SetMargins(15, 20, 15);
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AddPage();
        
        // Cabeçalho
        $pdf->SetFillColor(155, 89, 182);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 8, $nomeEmpresa . ' - SISTEMA INTEGRADO DE ORDENS DE SERVIÇO', 0, 1, 'C', true);
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 15, 'RELATÓRIO DE PRODUTOS', 0, 1, 'C', true);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 8, 'Período: ' . date('d/m/Y', strtotime($dataInicio)) . ' a ' . date('d/m/Y', strtotime($dataFim)), 0, 1, 'C', true);
        $pdf->Ln(8);
        
        // Reset cores
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(245, 245, 245);
        
        // Estatísticas Gerais
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'ESTATÍSTICAS GERAIS', 0, 1, 'L', true);
        $pdf->Ln(2);
        
        // Cards de estatísticas
        $totalProdutos = $estatisticas['total_produtos_cadastrados'] ?? 0;
        $produtosUtilizados = $estatisticas['produtos_utilizados'] ?? 0;
        $totalConsumido = $estatisticas['total_consumido'] ?? 0;
        
        $pdf->SetFont('helvetica', 'B', 12);
        
        // Linha 1
        $pdf->SetFillColor(52, 152, 219);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(90, 20, 'PRODUTOS CADASTRADOS', 1, 0, 'C', true);
        $pdf->SetFillColor(46, 204, 113);
        $pdf->Cell(90, 20, 'PRODUTOS UTILIZADOS', 1, 1, 'C', true);
        
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(52, 152, 219);
        $pdf->Cell(90, 15, $totalProdutos, 1, 0, 'C');
        $pdf->SetTextColor(46, 204, 113);
        $pdf->Cell(90, 15, $produtosUtilizados, 1, 1, 'C');
        
        // Linha 2
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(241, 196, 15);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(180, 20, 'TOTAL CONSUMIDO', 1, 1, 'C', true);
        
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(241, 196, 15);
        $pdf->Cell(180, 15, number_format($totalConsumido, 0, ',', '.'), 1, 1, 'C');
        
        $pdf->Ln(8);
        
        // Taxa de Utilização
        if ($totalProdutos > 0) {
            $taxaUtilizacao = ($produtosUtilizados / $totalProdutos) * 100;
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 8, 'TAXA DE UTILIZAÇÃO DE PRODUTOS', 0, 1, 'L', true);
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->SetTextColor(155, 89, 182);
            $pdf->Cell(0, 10, number_format($taxaUtilizacao, 1) . '%', 0, 1, 'C');
            $pdf->Ln(5);
        }
        
        // Produtos Mais Consumidos
        if (!empty($produtosConsumidos)) {
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 10, 'PRODUTOS MAIS CONSUMIDOS', 0, 1, 'L', true);
            $pdf->Ln(2);
            
            // Cabeçalho da tabela
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(52, 73, 94);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(80, 8, 'Produto', 1, 0, 'C', true);
            $pdf->Cell(25, 8, 'Consumido', 1, 0, 'C', true);
            $pdf->Cell(25, 8, 'Freq. Uso', 1, 0, 'C', true);
            $pdf->Cell(25, 8, 'Estoque', 1, 0, 'C', true);
            $pdf->Cell(25, 8, 'Valor Unit.', 1, 1, 'C', true);
            
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(0, 0, 0);
            $contador = 0;
            foreach (array_slice($produtosConsumidos, 0, 15) as $produto) {
                $fillColor = $contador % 2 == 0 ? [248, 249, 250] : [255, 255, 255];
                $pdf->SetFillColor($fillColor[0], $fillColor[1], $fillColor[2]);
                
                $estoque = isset($produto['qtd_estoque']) ? $produto['qtd_estoque'] : 'N/A';
                $valorUnit = isset($produto['valor_unitario']) ? 'R$ ' . number_format($produto['valor_unitario'], 2, ',', '.') : 'N/A';
                $frequencia = isset($produto['os_utilizadas']) ? $produto['os_utilizadas'] . 'x' : 'N/A';
                
                $pdf->Cell(80, 6, $produto['nome'] ?? '', 1, 0, 'L', true);
                $pdf->Cell(25, 6, $produto['total_consumido'], 1, 0, 'C', true);
                $pdf->Cell(25, 6, $frequencia, 1, 0, 'C', true);
                
                // Cor do estoque baseada na quantidade
                if (is_numeric($estoque)) {
                    if ($estoque <= 5) $pdf->SetTextColor(231, 76, 60); // Vermelho - estoque baixo
                    elseif ($estoque <= 20) $pdf->SetTextColor(241, 196, 15); // Amarelo - estoque médio
                    else $pdf->SetTextColor(46, 204, 113); // Verde - estoque bom
                }
                
                $pdf->Cell(25, 6, $estoque, 1, 0, 'C', true);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->Cell(25, 6, $valorUnit, 1, 1, 'C', true);
                $contador++;
            }
        }
        
        // Produtos com Estoque Baixo
        if (!empty($estoqueAtual)) {
            $estoqueBaixo = array_filter($estoqueAtual, function($produto) {
                return isset($produto['qtd_estoque']) && $produto['qtd_estoque'] <= 10;
            });
            
            if (!empty($estoqueBaixo)) {
                $pdf->Ln(8);
                $pdf->SetFont('helvetica', 'B', 14);
                $pdf->SetTextColor(231, 76, 60);
                $pdf->Cell(0, 10, 'ALERTA: PRODUTOS COM ESTOQUE BAIXO', 0, 1, 'L', true);
                $pdf->Ln(2);
                
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->SetFillColor(231, 76, 60);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->Cell(120, 8, 'Produto', 1, 0, 'C', true);
                $pdf->Cell(30, 8, 'Estoque Atual', 1, 0, 'C', true);
                $pdf->Cell(30, 8, 'Status', 1, 1, 'C', true);
                
                $pdf->SetFont('helvetica', '', 9);
                $pdf->SetTextColor(0, 0, 0);
                $contador = 0;
                foreach (array_slice($estoqueBaixo, 0, 10) as $produto) {
                    $fillColor = $contador % 2 == 0 ? [254, 242, 242] : [255, 255, 255];
                    $pdf->SetFillColor($fillColor[0], $fillColor[1], $fillColor[2]);
                    
                    $status = $produto['qtd_estoque'] <= 5 ? 'CRÍTICO' : 'BAIXO';
                    
                    $pdf->Cell(120, 6, $produto['nome'] ?? '', 1, 0, 'L', true);
                    $pdf->Cell(30, 6, $produto['qtd_estoque'], 1, 0, 'C', true);
                    $pdf->SetTextColor(231, 76, 60);
                    $pdf->Cell(30, 6, $status, 1, 1, 'C', true);
                    $pdf->SetTextColor(0, 0, 0);
                    $contador++;
                }
            }
        }
        
        // Rodapé
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(128, 128, 128);
        $nomeEmpresa = self::getNomeEmpresa();
        $pdf->Cell(0, 5, 'Relatório gerado em ' . date('d/m/Y H:i:s') . ' pelo Sistema ' . $nomeEmpresa, 0, 1, 'C');
        
        $pdf->Output('Relatorio_Produtos.pdf', 'D');
    }
    
    /**
     * Gera Excel de produtos
     */
    public static function gerarExcelProdutos($estatisticas, $produtosConsumidos, $estoqueAtual, $dataInicio, $dataFim)
    {
        // Usa método HTML para Excel (mais compatível)
        RelatorioCompativel::gerarExcelProdutos($estatisticas, $produtosConsumidos, $estoqueAtual, $dataInicio, $dataFim);
    }
    
    /**
     * Obtém nome da empresa
     */
    private static function getNomeEmpresa()
    {
        $configPath = __DIR__ . '/../../config/empresa.php';
        if (file_exists($configPath)) {
            $config = include $configPath;
            return $config['nome'] ?? 'SIOSEG';
        }
        return 'SIOSEG';
    }
}