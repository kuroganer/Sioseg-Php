<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class OrdemServico extends Model
{
    protected string $table = 'ordem_servico';
    protected string $primaryKey = 'id_os';

    public function __construct()
    {
        parent::__construct();
    }
    
    public function getDb()
    {
        return $this->db;
    }

    // ---------------------------
    // Operações básicas (PT)
    // ---------------------------
    public function criar(array $dados): int|false
    {
        // Verificar conflitos de horário se técnico e data de agendamento foram fornecidos
        if (isset($dados['id_tec_fk']) && isset($dados['data_agendamento'])) {
            $conflitos = $this->verificarConflitosHorario($dados['data_agendamento'], $dados['id_tec_fk']);
            if (!empty($conflitos)) {
                $conflito = $conflitos[0];
                $dataFormatada = date('d/m/Y \à\s H:i', strtotime($conflito->data_agendamento));
                $fimBloqueio = date('H:i', strtotime($conflito->fim_bloqueio));
                throw new \Exception("Horário indisponível! Já existe a OS #{$conflito->id_os} agendada para {$dataFormatada} até {$fimBloqueio}. Escolha outro horário.");
            }
        }

        $sql = "INSERT INTO {$this->table}
                (servico_prestado, tipo_servico, status, data_abertura, data_agendamento, data_encerramento, conclusao_cliente, conclusao_tecnico, id_tec_fk, id_usu_fk, id_cli_fk)
                VALUES
                (:servico_prestado, :tipo_servico, :status, :data_abertura, :data_agendamento, :data_encerramento, :conclusao_cliente, :conclusao_tecnico, :id_tec_fk, :id_usu_fk, :id_cli_fk)";

        $stmt = $this->db->prepare($sql);
        $sucesso = $stmt->execute([
            ':servico_prestado'   => $dados['servico_prestado'] ?? null,
            ':tipo_servico'       => $dados['tipo_servico'] ?? null,
            ':status'             => $dados['status'] ?? 'aberta',
            ':data_abertura'      => $dados['data_abertura'] ?? date('Y-m-d H:i:s'),
            ':data_agendamento'   => $dados['data_agendamento'] ?? date('Y-m-d H:i:s'),
            ':data_encerramento'  => $dados['data_encerramento'] ?? null,
            ':conclusao_cliente'  => $dados['conclusao_cliente'] ?? 'pendente',
            ':conclusao_tecnico'  => $dados['conclusao_tecnico'] ?? 'pendente',
            ':id_tec_fk'          => $dados['id_tec_fk'] ?? null,
            ':id_usu_fk'          => $dados['id_usu_fk'] ?? null,
            ':id_cli_fk'          => $dados['id_cli_fk'] ?? null
        ]);

        if ($sucesso) {
            return (int)$this->db->lastInsertId();
        }

        return false;
    }

    public function buscarPorId(int $id): object|false
    {
        $sql = "SELECT os.*, 
                 (CASE WHEN c.tipo_pessoa = 'juridica' AND COALESCE(c.razao_social,'') <> '' THEN c.razao_social 
                     WHEN COALESCE(c.nome_social,'') <> '' THEN c.nome_social 
                     ELSE c.nome_cli END) AS cliente_nome,
                 c.nome_cli, c.razao_social, t.nome_tec, u.nome_usu
              FROM {$this->table} os
              LEFT JOIN cliente c ON os.id_cli_fk = c.id_cli
              LEFT JOIN tecnico t ON os.id_tec_fk = t.id_tec
              LEFT JOIN usuario u ON os.id_usu_fk = u.id_usu
              WHERE os.{$this->primaryKey} = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function obterTodos(): array
    {
        try {
            $sql = "SELECT os.*, 
                     (CASE WHEN c.tipo_pessoa = 'juridica' AND COALESCE(c.razao_social,'') <> '' THEN c.razao_social 
                         WHEN COALESCE(c.nome_social,'') <> '' THEN c.nome_social 
                         ELSE c.nome_cli END) AS cliente_nome,
                     c.nome_cli, c.nome_social, c.razao_social, c.tipo_pessoa, t.nome_tec, u.nome_usu
              FROM {$this->table} os
              LEFT JOIN cliente c ON os.id_cli_fk = c.id_cli
              LEFT JOIN tecnico t ON os.id_tec_fk = t.id_tec
              LEFT JOIN usuario u ON os.id_usu_fk = u.id_usu
              ORDER BY os.data_agendamento ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro ao obter todas as OS: " . $e->getMessage());
            return [];
        }
    }

    public function obterTodosComPaginacao(int $offset, int $limit): array
    {
        try {
            $sql = "SELECT os.*, 
                     (CASE WHEN c.tipo_pessoa = 'juridica' AND COALESCE(c.razao_social,'') <> '' THEN c.razao_social 
                         WHEN COALESCE(c.nome_social,'') <> '' THEN c.nome_social 
                         ELSE c.nome_cli END) AS cliente_nome,
                     c.nome_cli, c.nome_social, c.razao_social, c.tipo_pessoa, t.nome_tec, u.nome_usu
              FROM {$this->table} os
              LEFT JOIN cliente c ON os.id_cli_fk = c.id_cli
              LEFT JOIN tecnico t ON os.id_tec_fk = t.id_tec
              LEFT JOIN usuario u ON os.id_usu_fk = u.id_usu
              ORDER BY os.id_os DESC
              LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro ao obter OS com paginação: " . $e->getMessage());
            return [];
        }
    }

    public function contarTodos(): int
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            return (int)$result->total;
        } catch (PDOException $e) {
            error_log("Erro ao contar OS: " . $e->getMessage());
            return 0;
        }
    }

    public function atualizarOS(int $id, array $dados): bool
    {
        // Verificar conflitos de horário se está alterando data_agendamento ou id_tec_fk
        if (isset($dados['data_agendamento']) || isset($dados['id_tec_fk'])) {
            $osAtual = $this->buscarPorId($id);
            if (!$osAtual) {
                throw new \Exception('OS não encontrada.');
            }
            
            $dataAgendamento = $dados['data_agendamento'] ?? $osAtual->data_agendamento;
            $idTecnico = $dados['id_tec_fk'] ?? $osAtual->id_tec_fk;
            
            $conflitos = $this->verificarConflitosHorario($dataAgendamento, $idTecnico, $id);
            if (!empty($conflitos)) {
                $conflito = $conflitos[0];
                $dataFormatada = date('d/m/Y \à\s H:i', strtotime($conflito->data_agendamento));
                $fimBloqueio = date('H:i', strtotime($conflito->fim_bloqueio));
                throw new \Exception("Horário indisponível! Já existe a OS #{$conflito->id_os} agendada para {$dataFormatada} até {$fimBloqueio}. Escolha outro horário.");
            }
        }

        $allowedFields = [
            'servico_prestado', 'tipo_servico', 'status', 'data_abertura', 'data_agendamento',
            'data_encerramento', 'conclusao_cliente', 'conclusao_tecnico', 'id_tec_fk', 'id_usu_fk', 'id_cli_fk'
        ];

        $fields = [];
        $values = [];

        foreach ($dados as $chave => $valor) {
            if (!in_array($chave, $allowedFields)) continue;

            if ($chave === 'data_abertura' && (is_null($valor) || $valor === '')) {
                continue;
            }

            $fields[] = "{$chave} = :{$chave}";
            $values[":{$chave}"] = $valor;
        }

        if (empty($fields)) return false;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE {$this->primaryKey} = :id";
        $values[':id'] = $id;

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar OS: " . $e->getMessage());
            throw $e;
        }
    }

    public function alterarStatus(int $id, string $status): bool
    {
        $setClauses = ['status = :status'];
        $values = [':status' => $status, ':id' => $id];

        if (in_array($status, ['encerrada', 'cancelada'])) {
            $setClauses[] = 'data_encerramento = NOW()';
        }

        // Se o status for 'encerrada', volta conclusões confirmadas para 'pendente'
        if ($status === 'encerrada') {
            $setClauses[] = 'conclusao_tecnico = CASE WHEN conclusao_tecnico = "concluida" THEN "pendente" ELSE conclusao_tecnico END';
            $setClauses[] = 'conclusao_cliente = CASE WHEN conclusao_cliente = "concluida" THEN "pendente" ELSE conclusao_cliente END';
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . " WHERE {$this->primaryKey} = :id";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Erro ao alterar status da OS: " . $e->getMessage());
            return false;
        }
    }

    public function atualizarConclusao(int $id_os, string $perfil): bool
    {
        $campo = ($perfil === 'tecnico') ? 'conclusao_tecnico' : 'conclusao_cliente';

        try {
            $this->db->beginTransaction();

            $sql = "UPDATE {$this->table} SET {$campo} = 'concluida' WHERE {$this->primaryKey} = :id_os";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_os' => $id_os]);

            $os = $this->buscarPorId($id_os);
            if ($os && $os->conclusao_tecnico === 'concluida' && $os->conclusao_cliente === 'concluida') {
                $sqlUpdateOS = "UPDATE {$this->table} SET status = 'concluida', data_encerramento = NOW() WHERE {$this->primaryKey} = :id_os";
                $stmtUpdate = $this->db->prepare($sqlUpdateOS);
                $stmtUpdate->execute([':id_os' => $id_os]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Erro ao atualizar conclusão da OS #{$id_os} para o perfil {$perfil}: " . $e->getMessage());
            return false;
        }
    }

    // ---------------------------
    // Consultas e utilitários
    // ---------------------------
    public function buscarPorCliente(int $id_cli): array
    {
        $sql = "SELECT os.*, 
                 (CASE WHEN c.tipo_pessoa = 'juridica' AND COALESCE(c.razao_social,'') <> '' THEN c.razao_social 
                     WHEN COALESCE(c.nome_social,'') <> '' THEN c.nome_social 
                     ELSE c.nome_cli END) AS cliente_nome,
                 c.nome_cli, c.razao_social, t.nome_tec, u.nome_usu
              FROM {$this->table} os
              INNER JOIN cliente c ON os.id_cli_fk = c.id_cli
              INNER JOIN tecnico t ON os.id_tec_fk = t.id_tec
              INNER JOIN usuario u ON os.id_usu_fk = u.id_usu
              WHERE os.id_cli_fk = :id_cli
              ORDER BY os.data_agendamento ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_cli', $id_cli, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function buscarPorData(string $data): array
    {
        $sql = "SELECT os.*, 
                 (CASE WHEN c.tipo_pessoa = 'juridica' AND COALESCE(c.razao_social,'') <> '' THEN c.razao_social 
                     WHEN COALESCE(c.nome_social,'') <> '' THEN c.nome_social 
                     ELSE c.nome_cli END) AS cliente_nome,
                 c.nome_cli, c.razao_social, c.tipo_pessoa, t.nome_tec
              FROM {$this->table} os
              LEFT JOIN cliente c ON os.id_cli_fk = c.id_cli
              LEFT JOIN tecnico t ON os.id_tec_fk = t.id_tec
              WHERE DATE(os.data_agendamento) = :date
              ORDER BY os.data_agendamento ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':date', $data, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function obterEstatisticasOSPorMes(int $year, int $month): array
    {
        $sql = "SELECT DAY(data_agendamento) as dia, status, COUNT(id_os) as total
                    FROM {$this->table}
                    WHERE YEAR(data_agendamento) = :year AND MONTH(data_agendamento) = :month AND status IN ('aberta', 'em andamento', 'concluida', 'encerrada')
                        GROUP BY DAY(data_agendamento), status";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':year', $year, PDO::PARAM_INT);
        $stmt->bindValue(':month', $month, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);

        $stats = [];
        foreach ($results as $row) {
            $dia = (int)$row->dia;
            if (!isset($stats[$dia])) $stats[$dia] = [];
            $stats[$dia][$row->status] = $row->total;
        }
        return $stats;
    }

    public function encerrarOS(int $id_os, bool $removerMateriais = false): bool
    {
        try {
            if ($removerMateriais) {
                $this->db->beginTransaction();
                $materialModel = new MaterialUsado();
                $materialModel->removerTodosMateriaisDaOS($id_os);
            }

            $sql = "UPDATE {$this->table} SET status = 'encerrada', data_encerramento = NOW()
                    WHERE {$this->primaryKey} = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([':id' => $id_os]);

            if ($removerMateriais) {
                if (!$result) {
                    throw new \Exception("Erro ao alterar status da OS");
                }
                $this->db->commit();
            }

            return $result;
        } catch (\Exception $e) {
            if ($removerMateriais) {
                $this->db->rollBack();
            }
            error_log("Erro ao encerrar OS {$id_os}: " . $e->getMessage());
            throw $e;
        }
    }

    public function cancelarOS(int $id_os): bool
    {
        return $this->encerrarOS($id_os, true);
    }

    public function buscarPorStatusComDetalhes(string $status, ?int $limit = null): array
    {
        $sql = "SELECT os.id_os, os.servico_prestado, cli.nome_cli, cli.razao_social, cli.tipo_pessoa, tec.nome_tec
                FROM {$this->table} os
                JOIN cliente cli ON os.id_cli_fk = cli.id_cli
                LEFT JOIN tecnico tec ON os.id_tec_fk = tec.id_tec
                WHERE os.status = :status
                ORDER BY os.data_agendamento DESC";

        if ($limit) {
            $sql .= " LIMIT :limit";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $status, \PDO::PARAM_STR);
        if ($limit) {
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    public function buscarPorNomeCliente(string $nome): array
    {
        $sql = "SELECT os.*, c.nome_cli, c.razao_social, t.nome_tec, u.nome_usu
                FROM {$this->table} os
                INNER JOIN cliente c ON os.id_cli_fk = c.id_cli
                INNER JOIN tecnico t ON os.id_tec_fk = t.id_tec
                INNER JOIN usuario u ON os.id_usu_fk = u.id_usu
                WHERE c.nome_cli LIKE :nome OR c.razao_social LIKE :nome
                ORDER BY os.data_agendamento ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nome' => "%$nome%"]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function buscarOSTecnicoDia(int $id_tec): array
    {
        $today = date('Y-m-d');
        $sql = "SELECT
            os.id_os,
            os.servico_prestado as desc_servico,
            os.status,
            os.conclusao_tecnico,
            os.data_agendamento,
            (CASE WHEN c.tipo_pessoa = 'juridica' AND COALESCE(c.razao_social,'') <> '' THEN c.razao_social WHEN COALESCE(c.nome_social,'') <> '' THEN c.nome_social ELSE c.nome_cli END) as cliente_nome,
            c.tel1_cli as cliente_telefone,
            c.tel2_cli,
            c.nome_social as cliente_contato,
            c.endereco,
            c.tipo_moradia,
            c.logradouro,
            c.cidade,
            c.bairro,
            c.uf,
            c.cep,
            c.ponto_referencia,
            c.complemento,
            c.num_end,
            CONCAT(c.endereco, CASE WHEN c.num_end IS NOT NULL THEN CONCAT(', ', c.num_end) ELSE '' END,
                   CASE WHEN c.complemento IS NOT NULL THEN CONCAT(' - ', c.complemento) ELSE '' END,
                   ' - ', c.bairro, ', ', c.cidade, ' - ', c.uf,
                   CASE WHEN c.cep IS NOT NULL THEN CONCAT(' - CEP: ', c.cep) ELSE '' END) as endereco_completo
            FROM {$this->table} os
            JOIN cliente c ON os.id_cli_fk = c.id_cli
            WHERE os.id_tec_fk = :id_tec
            AND (
                (DATE(os.data_agendamento) = :today AND os.status = 'aberta')
                OR os.status = 'em andamento'
            )
            ORDER BY os.data_agendamento ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_tec' => $id_tec, ':today' => $today]);
        $osList = $stmt->fetchAll(PDO::FETCH_OBJ);

        if (!empty($osList)) {
            $sqlProdutosUsados = "SELECT p.id_prod, p.nome, p.descricao, mu.qtd_usada
                                  FROM material_usado mu
                                  JOIN produto p ON mu.id_prod_fk = p.id_prod
                                  WHERE mu.id_os_fk = :id_os";
            $stmtUsados = $this->db->prepare($sqlProdutosUsados);

            $sqlProdutosDisponiveis = "SELECT id_prod, nome, descricao, qtde FROM produto WHERE status = 'ativo' AND qtde > 0";
            $stmtDisponiveis = $this->db->query($sqlProdutosDisponiveis);
            $produtosDisponiveis = $stmtDisponiveis->fetchAll(PDO::FETCH_OBJ);

            foreach ($osList as $os) {
                $stmtUsados->execute([':id_os' => $os->id_os]);
                $produtosUsados = $stmtUsados->fetchAll(PDO::FETCH_OBJ);

                $usadosIds = array_column($produtosUsados, 'id_prod');
                $disponiveisFiltrados = array_filter($produtosDisponiveis, function($p) use ($usadosIds) {
                    return !in_array($p->id_prod, $usadosIds);
                });

                $os->produtos_usados = $produtosUsados;
                $os->produtos_disponiveis = array_values($disponiveisFiltrados);
            }
        }

        return $osList;
    }

    public function buscarOSAtrasadas(int $id_tec): array
    {
        $today = date('Y-m-d');
        $sql = "SELECT
            os.id_os,
            os.servico_prestado as desc_servico,
            os.status,
            os.conclusao_tecnico,
            os.data_agendamento,
            DATEDIFF(:today, DATE(os.data_agendamento)) as dias_atraso,
            (CASE WHEN c.tipo_pessoa = 'juridica' AND COALESCE(c.razao_social,'') <> '' THEN c.razao_social WHEN COALESCE(c.nome_social,'') <> '' THEN c.nome_social ELSE c.nome_cli END) as cliente_nome,
            c.tel1_cli as cliente_telefone,
            c.tel2_cli,
            c.nome_social as cliente_contato,
            c.endereco,
            c.tipo_moradia,
            c.logradouro,
            c.cidade,
            c.bairro,
            c.uf,
            c.cep,
            c.ponto_referencia,
            c.complemento,
            c.num_end,
            CONCAT(c.endereco, CASE WHEN c.num_end IS NOT NULL THEN CONCAT(', ', c.num_end) ELSE '' END,
                   CASE WHEN c.complemento IS NOT NULL THEN CONCAT(' - ', c.complemento) ELSE '' END,
                   ' - ', c.bairro, ', ', c.cidade, ' - ', c.uf,
                   CASE WHEN c.cep IS NOT NULL THEN CONCAT(' - CEP: ', c.cep) ELSE '' END) as endereco_completo
            FROM {$this->table} os
            JOIN cliente c ON os.id_cli_fk = c.id_cli
            WHERE os.id_tec_fk = :id_tec
            AND DATE(os.data_agendamento) < :today
            AND os.status IN ('aberta', 'em andamento')
            AND os.conclusao_tecnico = 'pendente'
            ORDER BY os.data_agendamento ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_tec' => $id_tec, ':today' => $today]);
        $osAtrasadas = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Adiciona produtos usados e disponíveis para cada OS atrasada
        if (!empty($osAtrasadas)) {
            $sqlProdutosUsados = "SELECT p.id_prod, p.nome, p.descricao, mu.qtd_usada
                                  FROM material_usado mu
                                  JOIN produto p ON mu.id_prod_fk = p.id_prod
                                  WHERE mu.id_os_fk = :id_os";
            $stmtUsados = $this->db->prepare($sqlProdutosUsados);

            $sqlProdutosDisponiveis = "SELECT id_prod, nome, descricao, qtde FROM produto WHERE status = 'ativo' AND qtde > 0";
            $stmtDisponiveis = $this->db->query($sqlProdutosDisponiveis);
            $produtosDisponiveis = $stmtDisponiveis->fetchAll(PDO::FETCH_OBJ);

            foreach ($osAtrasadas as $os) {
                $stmtUsados->execute([':id_os' => $os->id_os]);
                $produtosUsados = $stmtUsados->fetchAll(PDO::FETCH_OBJ);

                $usadosIds = array_column($produtosUsados, 'id_prod');
                $disponiveisFiltrados = array_filter($produtosDisponiveis, function($p) use ($usadosIds) {
                    return !in_array($p->id_prod, $usadosIds);
                });

                $os->produtos_usados = $produtosUsados;
                $os->produtos_disponiveis = array_values($disponiveisFiltrados);
            }
        }

        return $osAtrasadas;
    }

    public function buscarHistoricoPorTecnico(int $id_tec): array
    {
        $sql = "SELECT os.*, (CASE WHEN c.tipo_pessoa = 'juridica' AND COALESCE(c.razao_social,'') <> '' THEN c.razao_social WHEN COALESCE(c.nome_social,'') <> '' THEN c.nome_social ELSE c.nome_cli END) AS cliente_nome FROM {$this->table} os
                JOIN cliente c ON os.id_cli_fk = c.id_cli
                WHERE os.id_tec_fk = :id_tec AND os.status IN ('concluida', 'encerrada', 'cancelada')
                ORDER BY os.data_encerramento DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_tec' => $id_tec]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function buscarHistoricoPorCliente(int $id_cli): array
    {
        $sql = "SELECT os.*, c.nome_cli, t.nome_tec, u.nome_usu
                FROM {$this->table} os
                INNER JOIN cliente c ON os.id_cli_fk = c.id_cli
                INNER JOIN tecnico t ON os.id_tec_fk = t.id_tec
                INNER JOIN usuario u ON os.id_usu_fk = u.id_usu
                WHERE os.id_cli_fk = :id_cli AND os.status = 'encerrada'
                ORDER BY os.data_encerramento DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_cli' => $id_cli]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function buscarPorTecnicoEData(int $id_tec, string $date): array
    {
        $sql = "SELECT os.*, c.nome_cli, c.tel1_cli, c.endereco, c.bairro, c.cidade
                FROM {$this->table} os
                JOIN cliente c ON os.id_cli_fk = c.id_cli
                WHERE os.id_tec_fk = :id_tec
                    AND DATE(os.data_agendamento) = :date
                AND os.status IN ('aberta', 'em andamento')
                ORDER BY os.data_agendamento ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_tec' => $id_tec, ':date' => $date]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function buscarOS($termo): array
    {
        $sql = "SELECT os.*, c.nome_cli, c.razao_social, c.tipo_pessoa, t.nome_tec 
                FROM {$this->table} os 
                JOIN cliente c ON os.id_cli_fk = c.id_cli 
                JOIN tecnico t ON os.id_tec_fk = t.id_tec 
                WHERE os.id_os LIKE ? 
                   OR c.nome_cli LIKE ? 
                   OR c.razao_social LIKE ? 
                   OR t.nome_tec LIKE ? 
                ORDER BY os.data_abertura DESC";

        $searchTerm = '%' . $termo . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function buscarConclusoesPendentes(): array
    {
        $sql = "SELECT os.id_os, os.servico_prestado as descricao_problema,
                       os.conclusao_tecnico, os.conclusao_cliente,
                       c.nome_cli, c.razao_social, c.tel1_cli, 
                       t.nome_tec, t.tel_pessoal as tel_tec
                FROM {$this->table} os
                JOIN cliente c ON os.id_cli_fk = c.id_cli
                JOIN tecnico t ON os.id_tec_fk = t.id_tec
                WHERE (os.conclusao_tecnico = 'concluida' AND os.conclusao_cliente = 'pendente')
                   OR (os.conclusao_cliente = 'concluida' AND os.conclusao_tecnico = 'pendente')
                ORDER BY os.data_agendamento DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // ---------------------------
    // Counters (English names kept as aliases)
    // ---------------------------
    public function countByStatus(string $status): int
    {
        // Se for status final (encerrada, cancelada, concluida), conta normalmente
        if (in_array($status, ['concluida', 'encerrada', 'cancelada'])) {
            $sql = "SELECT COUNT(id_os) FROM {$this->table} WHERE status = :status";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':status' => $status]);
        } else {
            // Para status pendentes, só exclui se o técnico concluiu E o status ainda não é final
            $sql = "SELECT COUNT(id_os) FROM {$this->table} WHERE status = :status AND (conclusao_tecnico != 'concluida' OR status IN ('encerrada', 'cancelada'))";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':status' => $status]);
        }
        return (int) $stmt->fetchColumn();
    }

    public function countByStatusAndDate(string $status, string $date): int
    {
        $dateColumn = ($status === 'aberta') ? 'data_abertura' : 'data_agendamento';
        
        if (in_array($status, ['concluida', 'encerrada', 'cancelada'])) {
            $sql = "SELECT COUNT(id_os) FROM {$this->table} WHERE status = :status AND DATE({$dateColumn}) = :date";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':status' => $status, ':date' => $date]);
        } else {
            $sql = "SELECT COUNT(id_os) FROM {$this->table} WHERE status = :status AND (conclusao_tecnico != 'concluida' OR status IN ('encerrada', 'cancelada')) AND DATE({$dateColumn}) = :date";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':status' => $status, ':date' => $date]);
        }
        return (int) $stmt->fetchColumn();
    }

    public function countAvaliacoesPendentes(): int
    {
        $sql = "SELECT COUNT(os.id_os) 
                FROM {$this->table} os
                LEFT JOIN avaliacao_tecnica av ON os.id_os = av.id_os_fk
                WHERE os.status = 'encerrada' AND av.id_ava IS NULL";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetchColumn();
    }

    public function countConclusoesPendentes(): int
    {
        $sql = "SELECT COUNT(id_os) FROM {$this->table} WHERE (conclusao_tecnico = 'concluida' AND conclusao_cliente = 'pendente') OR (conclusao_cliente = 'concluida' AND conclusao_tecnico = 'pendente')";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetchColumn();
    }

    // PT wrappers for counts
    public function contarPorStatus(string $status): int { return $this->countByStatus($status); }
    public function contarPorStatusEData(string $status, string $date): int { return $this->countByStatusAndDate($status, $date); }
    public function contarAvaliacoesPendentes(): int { return $this->countAvaliacoesPendentes(); }
    public function contarConclusoesPendentes(): int { return $this->countConclusoesPendentes(); }

    // ---------------------------
    // Controle de horários e bloqueios
    // ---------------------------
    public function verificarConflitosHorario(string $dataAgendamento, int $idTecnico, ?int $idOsExcluir = null): array
    {
        $sql = "SELECT id_os, data_agendamento, servico_prestado,
                       DATE_ADD(data_agendamento, INTERVAL 3 HOUR) as fim_bloqueio
                FROM {$this->table}
                WHERE id_tec_fk = :id_tec
                AND status IN ('aberta', 'em andamento')
                AND (
                    (:data_agendamento BETWEEN data_agendamento AND DATE_ADD(data_agendamento, INTERVAL 3 HOUR))
                    OR (data_agendamento BETWEEN :data_agendamento AND DATE_ADD(:data_agendamento, INTERVAL 3 HOUR))
                )";
        
        if ($idOsExcluir) {
            $sql .= " AND id_os != :id_os_excluir";
        }
        
        $stmt = $this->db->prepare($sql);
        $params = [
            ':id_tec' => $idTecnico,
            ':data_agendamento' => $dataAgendamento
        ];
        
        if ($idOsExcluir) {
            $params[':id_os_excluir'] = $idOsExcluir;
        }
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function verificarHorarioDisponivel(string $dataAgendamento, int $idTecnico, ?int $idOsExcluir = null): bool
    {
        $conflitos = $this->verificarConflitosHorario($dataAgendamento, $idTecnico, $idOsExcluir);
        return empty($conflitos);
    }

    public function obterHorariosBloqueados(int $idTecnico, string $dataInicio, string $dataFim): array
    {
        $sql = "SELECT id_os, data_agendamento,
                       DATE_ADD(data_agendamento, INTERVAL 3 HOUR) as fim_bloqueio,
                       servico_prestado, status
                FROM {$this->table}
                WHERE id_tec_fk = :id_tec
                AND status IN ('aberta', 'em andamento')
                AND data_agendamento BETWEEN :data_inicio AND :data_fim
                ORDER BY data_agendamento ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_tec' => $idTecnico,
            ':data_inicio' => $dataInicio,
            ':data_fim' => $dataFim
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }


}
