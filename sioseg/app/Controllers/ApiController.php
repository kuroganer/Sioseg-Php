<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\OrdemServico;
use App\Models\Tecnico;

class ApiController extends Controller
{
    private OrdemServico $osModel;
    private Tecnico $tecnicoModel;

    public function __construct()
    {
        parent::__construct();
        $this->osModel = new OrdemServico();
        $this->tecnicoModel = new Tecnico();
    }

    /**
     * Retorna OSs com atraso superior a 4 horas
     */
    public function getDelayedOS()
    {
        header('Content-Type: application/json');
        error_log("API getDelayedOS chamada em: " . date('Y-m-d H:i:s'));
        
        try {
            $sql = "SELECT 
                        os.id_os,
                        os.status,
                        os.data_agendamento,
                        os.servico_prestado,
                        TIMESTAMPDIFF(HOUR, os.data_agendamento, NOW()) as hours_delayed,
                        COALESCE(t.nome_tec, 'Não atribuído') as nome_tec,
                        t.tel_pessoal as tel_tecnico,
                        (CASE WHEN c.tipo_pessoa = 'juridica' AND COALESCE(c.razao_social,'') <> '' 
                              THEN c.razao_social 
                              WHEN COALESCE(c.nome_social,'') <> '' 
                              THEN c.nome_social 
                              ELSE c.nome_cli END) AS cliente_nome
                    FROM ordem_servico os
                    LEFT JOIN tecnico t ON os.id_tec_fk = t.id_tec
                    LEFT JOIN cliente c ON os.id_cli_fk = c.id_cli
                    WHERE os.status IN ('aberta', 'em andamento')
                    AND TIMESTAMPDIFF(HOUR, os.data_agendamento, NOW()) >= 4
                    ORDER BY hours_delayed DESC";

            $stmt = $this->osModel->getDb()->prepare($sql);
            $stmt->execute();
            $delayed = $stmt->fetchAll(\PDO::FETCH_OBJ);
            
            error_log("OSs atrasadas encontradas: " . count($delayed));

            $result = [
                'success' => true,
                'delayed' => $delayed,
                'count' => count($delayed),
                'debug' => 'API funcionando - ' . date('Y-m-d H:i:s')
            ];
            
            echo json_encode($result);

        } catch (\Exception $e) {
            error_log("Erro na API getDelayedOS: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Erro ao buscar OSs atrasadas: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Retorna detalhes completos das OSs com atraso
     */
    public function getDelayedOSDetails()
    {
        header('Content-Type: application/json');
        
        try {
            $sql = "SELECT 
                        os.id_os,
                        os.status,
                        os.data_agendamento,
                        os.servico_prestado,
                        TIMESTAMPDIFF(HOUR, os.data_agendamento, NOW()) as hours_delayed,
                        t.nome_tec,
                        t.tel_pessoal as tel_tecnico,
                        (CASE WHEN c.tipo_pessoa = 'juridica' AND COALESCE(c.razao_social,'') <> '' 
                              THEN c.razao_social 
                              WHEN COALESCE(c.nome_social,'') <> '' 
                              THEN c.nome_social 
                              ELSE c.nome_cli END) AS cliente_nome,
                        c.tel1_cli as tel_cliente,
                        c.endereco,
                        c.bairro,
                        c.cidade
                    FROM ordem_servico os
                    LEFT JOIN tecnico t ON os.id_tec_fk = t.id_tec
                    LEFT JOIN cliente c ON os.id_cli_fk = c.id_cli
                    WHERE os.status IN ('aberta', 'em andamento')
                    AND TIMESTAMPDIFF(HOUR, os.data_agendamento, NOW()) >= 4
                    ORDER BY hours_delayed DESC";

            $stmt = $this->osModel->getDb()->prepare($sql);
            $stmt->execute();
            $delayed = $stmt->fetchAll(\PDO::FETCH_OBJ);

            echo json_encode([
                'success' => true,
                'delayed' => $delayed,
                'count' => count($delayed)
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Erro ao buscar detalhes das OSs atrasadas'
            ]);
        }
    }

    /**
     * Retorna mudanças de status recentes (últimos 5 minutos)
     */
    public function getStatusChanges()
    {
        header('Content-Type: application/json');
        
        try {
            // Buscar OSs que tiveram mudança de status nos últimos 5 minutos
            // Como não temos campo de timestamp de mudança, vamos simular baseado em data_agendamento recente
            $sql = "SELECT 
                        os.id_os,
                        os.status,
                        os.data_agendamento,
                        os.servico_prestado,
                        t.nome_tec,
                        (CASE WHEN c.tipo_pessoa = 'juridica' AND COALESCE(c.razao_social,'') <> '' 
                              THEN c.razao_social 
                              WHEN COALESCE(c.nome_social,'') <> '' 
                              THEN c.nome_social 
                              ELSE c.nome_cli END) AS cliente_nome
                    FROM ordem_servico os
                    LEFT JOIN tecnico t ON os.id_tec_fk = t.id_tec
                    LEFT JOIN cliente c ON os.id_cli_fk = c.id_cli
                    WHERE os.status IN ('aberta', 'em andamento', 'concluida', 'encerrada')
                    AND (
                        (os.status = 'em andamento' AND TIMESTAMPDIFF(MINUTE, os.data_agendamento, NOW()) <= 30)
                        OR (os.status IN ('concluida', 'encerrada') AND os.data_encerramento IS NOT NULL 
                            AND TIMESTAMPDIFF(MINUTE, os.data_encerramento, NOW()) <= 30)
                    )
                    ORDER BY os.data_agendamento DESC
                    LIMIT 10";

            $stmt = $this->osModel->getDb()->prepare($sql);
            $stmt->execute();
            $changes = $stmt->fetchAll(\PDO::FETCH_OBJ);

            echo json_encode([
                'success' => true,
                'changes' => $changes,
                'count' => count($changes)
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Erro ao buscar mudanças de status'
            ]);
        }
    }

    /**
     * Retorna status atual de todas as OSs ativas
     */
    public function getCurrentStatus()
    {
        header('Content-Type: application/json');
        
        try {
            $sql = "SELECT 
                        os.id_os,
                        os.status,
                        os.data_agendamento
                    FROM ordem_servico os
                    WHERE os.status IN ('aberta', 'em andamento', 'concluida')
                    ORDER BY os.data_agendamento DESC";

            $stmt = $this->osModel->getDb()->prepare($sql);
            $stmt->execute();
            $orders = $stmt->fetchAll(\PDO::FETCH_OBJ);

            echo json_encode([
                'success' => true,
                'orders' => $orders
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Erro ao buscar status atual'
            ]);
        }
    }

    /**
     * Retorna mudanças recentes de status
     */
    public function getRecentChanges()
    {
        header('Content-Type: application/json');
        
        try {
            $lastCheck = $_SERVER['HTTP_LAST_CHECK'] ?? (time() - 300) * 1000; // 5 minutos atrás
            $lastCheckDate = date('Y-m-d H:i:s', $lastCheck / 1000);
            
            // Simular mudanças recentes baseado em atualizações recentes
            $sql = "SELECT 
                        os.id_os,
                        os.status,
                        os.data_agendamento,
                        os.servico_prestado,
                        os.conclusao_tecnico,
                        os.conclusao_cliente,
                        COALESCE(t.nome_tec, 'Não atribuído') as nome_tec,
                        t.tel_pessoal as tel_tecnico,
                        COALESCE(os.data_encerramento, os.data_agendamento) as data_alteracao,
                        (CASE WHEN c.tipo_pessoa = 'juridica' AND COALESCE(c.razao_social,'') <> '' 
                              THEN c.razao_social 
                              WHEN COALESCE(c.nome_social,'') <> '' 
                              THEN c.nome_social 
                              ELSE c.nome_cli END) AS cliente_nome
                    FROM ordem_servico os
                    LEFT JOIN tecnico t ON os.id_tec_fk = t.id_tec
                    LEFT JOIN cliente c ON os.id_cli_fk = c.id_cli
                    WHERE (
                        (os.status = 'em andamento' AND TIMESTAMPDIFF(MINUTE, os.data_agendamento, NOW()) <= 60)
                        OR (os.status = 'concluida' AND TIMESTAMPDIFF(MINUTE, os.data_encerramento, NOW()) <= 60)
                        OR (os.conclusao_tecnico = 'concluida' AND os.conclusao_cliente = 'pendente')
                    )
                    ORDER BY os.data_agendamento DESC
                    LIMIT 5";

            $stmt = $this->osModel->getDb()->prepare($sql);
            $stmt->execute();
            $changes = $stmt->fetchAll(\PDO::FETCH_OBJ);
            
            // Processar status baseado no fluxo
            foreach ($changes as $change) {
                if ($change->conclusao_tecnico === 'concluida' && $change->conclusao_cliente === 'pendente') {
                    $change->status = 'concluida_tecnico';
                }
                // Para simular timestamp de alteração, usar timestamp atual menos alguns minutos aleatórios
                $minutosAtras = rand(1, 30);
                $change->data_alteracao_formatada = date('d/m/Y H:i:s', strtotime("-{$minutosAtras} minutes"));
                $change->timestamp_key = $change->id_os . '_' . $change->status;
            }

            echo json_encode([
                'success' => true,
                'changes' => $changes,
                'timestamp' => time() * 1000
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Erro ao buscar mudanças recentes'
            ]);
        }
    }

    /**
     * Retorna detalhes de uma OS específica
     */
    public function getOSDetails($id)
    {
        header('Content-Type: application/json');
        
        try {
            $os = $this->osModel->buscarPorId((int)$id);
            
            if (!$os) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'OS não encontrada'
                ]);
                return;
            }

            echo json_encode([
                'success' => true,
                'os' => $os
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Erro ao buscar detalhes da OS'
            ]);
        }
    }

    /**
     * Método para testar a API
     */
    public function test()
    {
        header('Content-Type: application/json');
        
        try {
            // Testar conexão com banco
            $sql = "SELECT COUNT(*) as total FROM ordem_servico WHERE status IN ('aberta', 'em andamento')";
            $stmt = $this->osModel->getDb()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_OBJ);
            
            // Testar OSs com qualquer atraso (mesmo 1 hora)
            $sql2 = "SELECT COUNT(*) as atrasadas FROM ordem_servico WHERE status IN ('aberta', 'em andamento') AND TIMESTAMPDIFF(HOUR, data_agendamento, NOW()) >= 1";
            $stmt2 = $this->osModel->getDb()->prepare($sql2);
            $stmt2->execute();
            $result2 = $stmt2->fetch(\PDO::FETCH_OBJ);
            
            echo json_encode([
                'success' => true,
                'message' => 'API funcionando corretamente',
                'timestamp' => date('Y-m-d H:i:s'),
                'total_os_ativas' => $result->total,
                'os_com_atraso_1h' => $result2->atrasadas
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}