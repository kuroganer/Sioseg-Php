<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class Relatorio extends Model
{
    // Resumo geral de OS por período
    public function obterResumoOS($dataInicio = null, $dataFim = null): array
    {
        $whereClause = '';
        $params = [];
        
        if ($dataInicio && $dataFim) {
            $whereClause = 'WHERE (COALESCE(os.data_agendamento, os.data_abertura) BETWEEN ? AND ? OR (os.data_encerramento IS NOT NULL AND os.data_encerramento BETWEEN ? AND ?))';
            $params = [$dataInicio, $dataFim, $dataInicio, $dataFim];
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_os,
                    SUM(CASE WHEN os.status = 'aberta' THEN 1 ELSE 0 END) as abertas,
                    SUM(CASE WHEN os.status = 'em andamento' THEN 1 ELSE 0 END) as em_andamento,
                    SUM(CASE WHEN os.status = 'concluida' THEN 1 ELSE 0 END) as concluidas,
                    SUM(CASE WHEN os.status = 'encerrada' THEN 1 ELSE 0 END) as encerradas,
                    SUM(CASE WHEN (os.conclusao_tecnico = 'concluida' AND os.conclusao_cliente = 'pendente') OR (os.conclusao_cliente = 'concluida' AND os.conclusao_tecnico = 'pendente') THEN 1 ELSE 0 END) as pendente_confirmacao,
                    0 as canceladas,
                    AVG(CASE WHEN av.nota IS NOT NULL THEN av.nota ELSE NULL END) as media_avaliacao,
                    COUNT(av.id_ava) as total_avaliacoes
                FROM ordem_servico os
                LEFT JOIN avaliacao_tecnica av ON os.id_os = av.id_os_fk
                $whereClause";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    // Performance por técnico
    public function obterPerformanceTecnicos($dataInicio = null, $dataFim = null): array
    {
        $whereClause = '';
        $params = [];
        
        if ($dataInicio && $dataFim) {
            $whereClause = 'WHERE COALESCE(os.data_agendamento, os.data_abertura) BETWEEN ? AND ?';
            $params = [$dataInicio, $dataFim];
        }
        
        $sql = "SELECT 
                    t.nome_tec,
                    COUNT(CASE WHEN os.status != 'encerrada' THEN os.id_os ELSE NULL END) as total_os,
                    SUM(CASE WHEN os.status = 'concluida' THEN 1 ELSE 0 END) as os_concluidas,
                    AVG(CASE WHEN os.status = 'concluida' AND os.data_encerramento IS NOT NULL AND os.data_agendamento IS NOT NULL
                             THEN TIMESTAMPDIFF(MINUTE, os.data_agendamento, os.data_encerramento) 
                             WHEN os.status = 'concluida' AND os.data_encerramento IS NOT NULL
                             THEN TIMESTAMPDIFF(MINUTE, os.data_abertura, os.data_encerramento)
                             ELSE NULL END) as tempo_medio_minutos,
                    AVG(CASE WHEN av.nota IS NOT NULL THEN av.nota ELSE NULL END) as media_avaliacao,
                    COUNT(av.id_ava) as total_avaliacoes
                FROM tecnico t
                LEFT JOIN ordem_servico os ON t.id_tec = os.id_tec_fk
                LEFT JOIN avaliacao_tecnica av ON os.id_os = av.id_os_fk
                $whereClause
                GROUP BY t.id_tec, t.nome_tec
                HAVING COUNT(CASE WHEN os.status != 'encerrada' THEN os.id_os ELSE NULL END) > 0
                ORDER BY os_concluidas DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Consumo de materiais por OS específica
    public function obterConsumoMateriais($osId): array
    {
        $sql = "SELECT 
                    p.nome,
                    p.marca,
                    p.modelo,
                    mu.qtd_usada,
                    p.descricao
                FROM material_usado mu
                JOIN produto p ON mu.id_prod_fk = p.id_prod
                WHERE mu.id_os_fk = ?
                ORDER BY p.nome";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$osId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Relatório detalhado de OS para cliente
    public function obterRelatorioOS($osId): ?object
    {
        $sql = "SELECT 
                        os.*,
                        c.nome_cli,
                        c.razao_social,
                        c.tipo_pessoa,
                        c.endereco,
                        c.cidade,
                        c.bairro,
                        c.uf,
                        c.tel1_cli,
                        t.nome_tec,
                        t.tel_pessoal as tel_tecnico,
                        u.nome_usu as responsavel,
                        av.nota,
                        av.comentario
                    FROM ordem_servico os
                    JOIN cliente c ON os.id_cli_fk = c.id_cli
                    JOIN tecnico t ON os.id_tec_fk = t.id_tec
                    JOIN usuario u ON os.id_usu_fk = u.id_usu
                    LEFT JOIN avaliacao_tecnica av ON os.id_os = av.id_os_fk
                    WHERE os.id_os = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$osId]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // Top produtos mais utilizados
    public function obterTopProdutos($limite = 10): array
    {
        $sql = "SELECT 
                    p.nome,
                    p.marca,
                    p.modelo,
                    SUM(mu.qtd_usada) as total_usado,
                    COUNT(DISTINCT mu.id_os_fk) as os_utilizadas
                FROM produto p
                JOIN material_usado mu ON p.id_prod = mu.id_prod_fk
                GROUP BY p.id_prod
                ORDER BY total_usado DESC
                LIMIT " . (int)$limite;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OS por status nos últimos dias
    public function obterOSPorStatusDias($dias = 30): array
    {
        $sql = "SELECT 
                    DATE(os.data_abertura) as data,
                    os.status,
                    COUNT(*) as quantidade
                FROM ordem_servico os
                WHERE os.data_abertura >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(os.data_abertura), os.status
                ORDER BY data DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dias]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Estatísticas gerais de produtos
    public function obterEstatisticasProdutos($dataInicio = null, $dataFim = null): array
    {
        $whereClause = '';
        $params = [];
        
        if ($dataInicio && $dataFim) {
            $whereClause = 'WHERE COALESCE(os.data_agendamento, os.data_abertura) BETWEEN ? AND ?';
            $params = [$dataInicio, $dataFim];
        }
        
        $sql = "SELECT 
                    COUNT(DISTINCT p.id_prod) as total_produtos_cadastrados,
                    COUNT(DISTINCT mu.id_prod_fk) as produtos_utilizados,
                    SUM(mu.qtd_usada) as total_consumido,
                    COUNT(DISTINCT mu.id_os_fk) as os_com_materiais,
                    AVG(mu.qtd_usada) as media_consumo_por_uso
                FROM produto p
                LEFT JOIN material_usado mu ON p.id_prod = mu.id_prod_fk
                LEFT JOIN ordem_servico os ON mu.id_os_fk = os.id_os
                $whereClause";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    // Produtos mais consumidos por período
    public function obterProdutosConsumidos($dataInicio = null, $dataFim = null, $categoria = ''): array
    {
        $whereClause = 'WHERE 1=1';
        $params = [];
        
        if ($dataInicio && $dataFim) {
            $whereClause .= ' AND COALESCE(os.data_agendamento, os.data_abertura) BETWEEN ? AND ?';
            $params[] = $dataInicio;
            $params[] = $dataFim;
        }
        
        $sql = "SELECT 
                    p.id_prod,
                    p.nome,
                    p.marca,
                    p.modelo,
                    'Geral' as categoria,
                    p.qtde as qtd_estoque,
                    SUM(mu.qtd_usada) as total_consumido,
                    COUNT(DISTINCT mu.id_os_fk) as os_utilizadas,
                    AVG(mu.qtd_usada) as media_por_os,
                    MIN(os.data_abertura) as primeira_utilizacao,
                    MAX(os.data_abertura) as ultima_utilizacao
                FROM produto p
                INNER JOIN material_usado mu ON p.id_prod = mu.id_prod_fk
                INNER JOIN ordem_servico os ON mu.id_os_fk = os.id_os
                $whereClause
                GROUP BY p.id_prod
                ORDER BY total_consumido DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Estoque atual dos produtos
    public function obterEstoqueAtual($categoria = ''): array
    {
        $sql = "SELECT 
                    p.id_prod,
                    p.nome,
                    p.marca,
                    p.modelo,
                    'Geral' as categoria,
                    p.qtde as qtd_estoque,
                    10 as qtd_minima,
                    COALESCE(SUM(mu.qtd_usada), 0) as total_usado_historico,
                    CASE 
                        WHEN p.qtde <= 10 THEN 'critico'
                        WHEN p.qtde <= 20 THEN 'baixo'
                        ELSE 'normal'
                    END as status_estoque
                FROM produto p
                LEFT JOIN material_usado mu ON p.id_prod = mu.id_prod_fk
                WHERE p.status = 'ativo'
                GROUP BY p.id_prod
                ORDER BY 
                    CASE 
                        WHEN p.qtde <= 10 THEN 1
                        WHEN p.qtde <= 20 THEN 2
                        ELSE 3
                    END,
                    p.qtde ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Categorias de produtos (sistema não possui categorias)
    public function obterCategoriasProdutos(): array
    {
        return ['Geral']; // Sistema não possui categorias
    }

    // Buscar OS por status específico
    public function obterOSPorStatus($status, $dataInicio = null, $dataFim = null): array
    {
        $whereClause = 'WHERE 1=1';
        $params = [];
        
        if ($status === 'pendente_confirmacao') {
            $whereClause .= ' AND ((os.conclusao_tecnico = "concluida" AND os.conclusao_cliente = "pendente") OR (os.conclusao_cliente = "concluida" AND os.conclusao_tecnico = "pendente"))';
        } elseif ($status === 'cancelada') {
            // Status cancelada não existe no banco atual
            $whereClause .= ' AND 1=0'; // Retorna vazio
        } else {
            $whereClause .= ' AND os.status = ?';
            $params[] = $status;
        }
        
        if ($dataInicio && $dataFim) {
            $whereClause .= ' AND (COALESCE(os.data_agendamento, os.data_abertura) BETWEEN ? AND ? OR (os.data_encerramento IS NOT NULL AND os.data_encerramento BETWEEN ? AND ?))';
            $params[] = $dataInicio;
            $params[] = $dataFim;
            $params[] = $dataInicio;
            $params[] = $dataFim;
        }
        
        $sql = "SELECT os.id_os, os.servico_prestado, os.status, os.data_abertura, os.data_agendamento,
                       c.nome_cli, c.razao_social, c.tipo_pessoa, t.nome_tec
                FROM ordem_servico os
                JOIN cliente c ON os.id_cli_fk = c.id_cli
                JOIN tecnico t ON os.id_tec_fk = t.id_tec
                $whereClause
                ORDER BY COALESCE(os.data_agendamento, os.data_abertura) DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}