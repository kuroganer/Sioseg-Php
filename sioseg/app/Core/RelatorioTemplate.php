<?php

namespace App\Core;

use TCPDF;

class RelatorioTemplate
{
    private static $templatePath = 'storage/modelos_documentos/';
    
    public static function gerarPDFOS($os, $materiais, $templateName = 'template_os')
    {
        try {
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            $html = TemplateManager::carregarTemplate($templateName);
            $html = self::processarTemplate($html, $os, $materiais);
            $html = self::inlineStyles($html);
            $html = self::aplicarCorrecoesTCPDF($html);
            
            if (!class_exists('TCPDF')) {
                throw new \Exception("Classe TCPDF não encontrada");
            }
            
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
            $pdf->SetCreator(self::getNomeEmpresa());
            $pdf->SetAuthor('Sistema ' . self::getNomeEmpresa());
            $pdf->SetTitle("Ordem de Serviço #{$os->id_os}");
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(12, 8, 12);
            $pdf->SetAutoPageBreak(true, 8);
            $pdf->SetFont('helvetica', '', 10);
            
            if (method_exists($pdf, 'setCellPaddings')) {
                $pdf->setCellPaddings(0, 0, 0, 0);
            }
            if (method_exists($pdf, 'setCellMargins')) {
                $pdf->setCellMargins(0, 0, 0, 0);
            }

            $pdf->AddPage();
            $pdf->writeHTML($html, true, false, true, false, '');
            
            if (!headers_sent()) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="OS_' . $os->id_os . '.pdf"');
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
                $pdf->Output("OS_{$os->id_os}.pdf", 'D');
            } else {
                $outPath = defined('APP_ROOT') ? APP_ROOT . '/storage/documentos_gerados/OS_' . $os->id_os . '.pdf' : 'OS_' . $os->id_os . '.pdf';
                if (!is_dir(dirname($outPath))) {
                    mkdir(dirname($outPath), 0755, true);
                }
                $pdf->Output($outPath, 'F');
                echo "PDF salvo em: $outPath\n";
            }
            
        } catch (\Exception $e) {
            error_log("ERRO na geração do PDF: " . $e->getMessage());
            return false;
        }
    }

    public static function gerarPDFResumo($resumo, $performanceTecnicos, $topProdutos, $dataInicio, $dataFim)
    {
        return RelatorioTCPDF::gerarPDFResumo($resumo, $performanceTecnicos, $topProdutos, $dataInicio, $dataFim);
    }
    
    public static function gerarExcelResumo($resumo, $performanceTecnicos, $topProdutos, $dataInicio, $dataFim)
    {
        return RelatorioCompativel::gerarExcelResumo($resumo, $performanceTecnicos, $topProdutos, $dataInicio, $dataFim);
    }
    
    public static function gerarPDFProdutos($estatisticas, $produtosConsumidos, $estoqueAtual, $dataInicio, $dataFim)
    {
        return RelatorioTCPDF::gerarPDFProdutos($estatisticas, $produtosConsumidos, $estoqueAtual, $dataInicio, $dataFim);
    }
    
    public static function gerarExcelProdutos($estatisticas, $produtosConsumidos, $estoqueAtual, $dataInicio, $dataFim)
    {
        return RelatorioCompativel::gerarExcelProdutos($estatisticas, $produtosConsumidos, $estoqueAtual, $dataInicio, $dataFim);
    }
    
    public static function gerarExcelOS($os, $materiais)
    {
        return RelatorioCompativel::gerarExcelOS($os, $materiais);
    }

    private static function inlineStyles($html)
    {
        if (!preg_match('/<style[^>]*>(.*?)<\/style>/is', $html, $m)) {
            return $html;
        }

        $css = $m[1];
        preg_match_all('/([^\{]+)\{([^\}]+)\}/s', $css, $rules, PREG_SET_ORDER);
        
        if (empty($rules)) {
            return preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        foreach ($rules as $rule) {
            $selector = trim($rule[1]);
            $decls = trim($rule[2]);
            $decls = self::normalizeCSSForTCPDF($decls);

            $parts = preg_split('/\s+/', $selector);

            if (count($parts) === 1) {
                if (strpos($parts[0], '.') === 0) {
                    $class = substr($parts[0], 1);
                    $nodes = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " ' . $class . ' ")]');
                } else {
                    $nodes = $dom->getElementsByTagName($parts[0]);
                }
            } else {
                if (strpos($parts[0], '.') === 0) {
                    $class = substr($parts[0], 1);
                    $tag = $parts[1];
                    $candidateNodes = $dom->getElementsByTagName($tag);
                    $nodes = [];
                    foreach ($candidateNodes as $n) {
                        $p = $n->parentNode;
                        while ($p && $p->nodeType === XML_ELEMENT_NODE) {
                            if ($p instanceof \DOMElement) {
                                $cls = $p->getAttribute('class');
                                if ($cls && preg_match('/\b' . preg_quote($class, '/') . '\b/', $cls)) {
                                    $nodes[] = $n;
                                    break;
                                }
                            }
                            $p = $p->parentNode;
                        }
                    }
                } else {
                    $nodes = [];
                }
            }

            $count = 0;
            if ($nodes instanceof \DOMNodeList) {
                $count = $nodes->length;
            } elseif (is_array($nodes)) {
                $count = count($nodes);
            }

            if ($count > 0) {
                if ($nodes instanceof \DOMNodeList) {
                    $iter = iterator_to_array($nodes);
                } elseif (is_array($nodes)) {
                    $iter = $nodes;
                } else {
                    $iter = [];
                }

                foreach ($iter as $node) {
                    if (!($node instanceof \DOMElement)) continue;
                    $existing = $node->getAttribute('style');
                    $declsNormalized = trim(preg_replace('/\s+/', ' ', $decls));
                    if ($existing) {
                        $new = rtrim($existing, ';') . '; ' . $declsNormalized;
                    } else {
                        $new = $declsNormalized;
                    }
                    $node->setAttribute('style', $new);
                }
            }
        }

        $htmlOut = $dom->saveHTML();
        $htmlOut = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $htmlOut);
        $htmlOut = preg_replace('/^<!DOCTYPE.+?>/i', '', $htmlOut);
        $htmlOut = preg_replace('/^<\?xml.+?\?>/i', '', $htmlOut);

        return trim($htmlOut);
    }
    
    private static function normalizeCSSForTCPDF($css)
    {
        $css = preg_replace('/background:\s*([#\w]+);/', 'background-color: $1;', $css);
        $css = preg_replace('/color:\s*#([0-9a-f]{3})([^0-9a-f]|$)/i', 'color: #$1$1$2', $css);
        return $css;
    }
    
    private static function aplicarCorrecoesTCPDF($html)
    {
        $html = preg_replace('/<!--.*?-->/s', '', $html);
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return $html;
    }
    
    private static function processarTemplate($html, $os, $materiais)
    {
        $replacements = [
            '{{ID_OS}}' => $os->id_os,
            '{{STATUS}}' => strtoupper($os->status),
            '{{TIPO_SERVICO}}' => ucfirst($os->tipo_servico ?? ''),
            '{{SERVICO_PRESTADO}}' => htmlspecialchars($os->servico_prestado ?? 'Não informado'),
            '{{DATA_ABERTURA}}' => date('d/m/Y H:i', strtotime($os->data_abertura)),
            '{{NOME_CLIENTE}}' => htmlspecialchars($os->tipo_pessoa === 'juridica' ? ($os->razao_social ?? '') : ($os->nome_cli ?? '')),
            '{{TELEFONE}}' => htmlspecialchars($os->tel1_cli ?? ''),
            '{{NOME_TECNICO}}' => htmlspecialchars($os->nome_tec ?? ''),
            '{{DATA_GERACAO}}' => date('d/m/Y H:i:s'),
            '{{NOME_EMPRESA}}' => self::getNomeEmpresa(),
            '{{RESPONSAVEL}}' => htmlspecialchars($os->responsavel ?? $os->nome_tec ?? ''),
            '{{PRIORIDADE_FIELD}}' => isset($os->prioridade) ? 'Prioridade: ' . ucfirst($os->prioridade) : '',
            '{{DATA_ENCERRAMENTO_FIELD}}' => $os->data_encerramento ? 'Encerramento: ' . date('d/m/Y H:i', strtotime($os->data_encerramento)) : '',
            '{{DOCUMENTO_FIELD}}' => self::getDocumentoField($os),
            '{{EMAIL_FIELD}}' => isset($os->email_cli) ? 'E-mail: ' . htmlspecialchars($os->email_cli) : '',
            '{{ENDERECO_FIELD}}' => self::getEnderecoField($os),
            '{{TELEFONE_TECNICO_FIELD}}' => isset($os->tel_tecnico) ? 'Telefone: ' . htmlspecialchars($os->tel_tecnico) : '',
            '{{DESCRICAO_PROBLEMA_SECTION}}' => self::getDescricaoProblemaSection($os),
            '{{SOLUCAO_APLICADA_SECTION}}' => self::getSolucaoAplicadaSection($os),
            '{{MATERIAIS_SECTION}}' => self::getMateriaisSection($materiais),
            '{{AVALIACAO_SECTION}}' => self::getAvaliacaoSection($os)
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $html);
    }
    
    private static function getDocumentoField($os)
    {
        if ($os->tipo_pessoa === 'juridica' && isset($os->cnpj_cli)) {
            return 'CNPJ: ' . htmlspecialchars($os->cnpj_cli);
        } elseif (isset($os->cpf_cli)) {
            return 'CPF: ' . htmlspecialchars($os->cpf_cli);
        }
        return '';
    }
    
    private static function getEnderecoField($os)
    {
        if (isset($os->endereco)) {
            $endereco = htmlspecialchars($os->endereco);
            if (isset($os->bairro)) $endereco .= ', ' . htmlspecialchars($os->bairro);
            if (isset($os->cidade)) $endereco .= ' - ' . htmlspecialchars($os->cidade);
            if (isset($os->uf)) $endereco .= '/' . htmlspecialchars($os->uf);
            return 'Endereço: ' . $endereco;
        }
        return '';
    }
    
    private static function getDescricaoProblemaSection($os)
    {
        if (isset($os->descricao_problema) && $os->descricao_problema) {
            return '<div class="block"><table style="width:100%; border-collapse:collapse;"><tr><td style="background-color:#0b4a59; color:#ffffff; padding:8px 10px; font-weight:700; font-size:12px;">DESCRIÇÃO DO PROBLEMA</td></tr></table><table style="width:100%; border-collapse:collapse;"><tr><td style="background-color:#ffffff; border:1px solid #dcdcdc; padding:8px 10px; font-size:11px; color:#0b1f23;">' . nl2br(htmlspecialchars($os->descricao_problema)) . '</td></tr></table></div>';
        }
        return '';
    }
    
    private static function getSolucaoAplicadaSection($os)
    {
        if (isset($os->solucao_aplicada) && $os->solucao_aplicada) {
            return '<div class="block"><table style="width:100%; border-collapse:collapse;"><tr><td style="background-color:#0b4a59; color:#ffffff; padding:8px 10px; font-weight:700; font-size:12px;">SOLUÇÃO APLICADA</td></tr></table><table style="width:100%; border-collapse:collapse;"><tr><td style="background-color:#ffffff; border:1px solid #dcdcdc; padding:8px 10px; font-size:11px; color:#0b1f23;">' . nl2br(htmlspecialchars($os->solucao_aplicada)) . '</td></tr></table></div>';
        }
        return '';
    }
    
    private static function getMateriaisSection($materiais)
    {
        if (!empty($materiais)) {
            $html = '<div class="block"><table style="width:100%; border-collapse:collapse;"><tr><td style="background-color:#0b4a59; color:#ffffff; padding:8px 10px; font-weight:700; font-size:12px;">MATERIAIS UTILIZADOS</td></tr></table><table style="width:100%; border-collapse:collapse;"><tr><td style="background-color:#ffffff; border:1px solid #dcdcdc; padding:8px 10px; font-size:11px; color:#0b1f23;"><table class="materials-table" style="width:100%; border-collapse:collapse; margin-top:6px;"><tr style="background-color:#f0f0f0; font-weight:bold;"><td style="border:1px solid #dcdcdc; padding:6px; font-size:10px;">Material</td><td style="border:1px solid #dcdcdc; padding:6px; font-size:10px;">Marca</td><td style="border:1px solid #dcdcdc; padding:6px; font-size:10px;">Modelo</td><td style="border:1px solid #dcdcdc; padding:6px; font-size:10px;">Quantidade</td></tr>';
            foreach ($materiais as $material) {
                $html .= '<tr><td style="border:1px solid #dcdcdc; padding:6px; font-size:10px;">' . htmlspecialchars($material['nome'] ?? '') . '</td><td style="border:1px solid #dcdcdc; padding:6px; font-size:10px;">' . htmlspecialchars($material['marca'] ?? '') . '</td><td style="border:1px solid #dcdcdc; padding:6px; font-size:10px;">' . htmlspecialchars($material['modelo'] ?? '') . '</td><td style="border:1px solid #dcdcdc; padding:6px; font-size:10px; text-align:center;">' . ($material['qtd_usada'] ?? 0) . '</td></tr>';
            }
            $html .= '</table></td></tr></table></div>';
            return $html;
        }
        return '';
    }
    
    private static function getAvaliacaoSection($os)
    {
        $temNota = isset($os->nota) && $os->nota !== null && $os->nota !== '';
        $temComentario = isset($os->comentario) && !empty(trim($os->comentario));
        
        if ($temNota || $temComentario) {
            $html = '<div class="block"><table style="width:100%; border-collapse:collapse;"><tr><td style="background-color:#0b4a59; color:#ffffff; padding:8px 10px; font-weight:700; font-size:12px;">AVALIAÇÃO DO CLIENTE</td></tr></table><table style="width:100%; border-collapse:collapse;"><tr><td style="background-color:#ffffff; border:1px solid #dcdcdc; padding:8px 10px; font-size:11px; color:#0b1f23;">';
            
            if ($temNota) {
                $html .= '<div style="margin-bottom:10px;"><strong>Nota:</strong> ' . htmlspecialchars($os->nota) . '/5</div>';
            }
            
            if ($temComentario) {
                $html .= '<div><strong>Comentário:</strong><br>' . nl2br(htmlspecialchars($os->comentario)) . '</div>';
            }
            
            $html .= '</td></tr></table></div>';
            return $html;
        }
        
        return '';
    }
    
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