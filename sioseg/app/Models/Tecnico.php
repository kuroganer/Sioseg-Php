<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class Tecnico extends Model
{
    protected string $table = 'tecnico';
    protected string $primaryKey = 'id_tec';

    public function __construct()
    {
        parent::__construct();
    }

    public function criar(array $dados): bool
    {
        $sql = "INSERT INTO {$this->table} (
                    nome_tec, cpf_tec, rg_tec, rg_emissor_tec,
                    data_expedicao_rg_tec, data_nascimento_tec, data_cadastro_tec,
                    tel_pessoal, tel_empresa,
                    email_tec, senha_hash_tec, status
                ) VALUES (
                    :nome_tec, :cpf_tec, :rg_tec, :rg_emissor_tec,
                    :data_expedicao_rg_tec, :data_nascimento_tec, :data_cadastro_tec,
                    :tel_pessoal, :tel_empresa,
                    :email_tec, :senha_hash_tec, :status
                )";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nome_tec'               => $dados['nome_tec'],
            ':cpf_tec'                => $dados['cpf_tec'],
            ':rg_tec'                 => $dados['rg_tec'],
            ':rg_emissor_tec'         => $dados['rg_emissor_tec'] ?? null,
            ':data_expedicao_rg_tec'  => $dados['data_expedicao_rg_tec'] ?? null,
            ':data_nascimento_tec'    => $dados['data_nascimento_tec'] ?? null,
            ':data_cadastro_tec'      => $dados['data_cadastro_tec'],
            ':tel_pessoal'            => $dados['tel_pessoal'],
            ':tel_empresa'            => $dados['tel_empresa'] ?? null,
            ':email_tec'              => $dados['email_tec'],
            ':senha_hash_tec'         => $dados['senha_hash_tec'],
            ':status'                 => $dados['status'] ?? 'ativo'
        ]);
    }

    public function buscarPorEmail(string $email): object|false
    {
        $sql = "SELECT id_tec, nome_tec, cpf_tec, email_tec, senha_hash_tec, status
                FROM {$this->table} WHERE email_tec = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function obterTodos(): array
    {
        try {
            $stmt = $this->db->query("SELECT 
                id_tec, nome_tec, cpf_tec, rg_tec, rg_emissor_tec,
                data_expedicao_rg_tec, data_nascimento_tec, data_cadastro_tec,
                tel_pessoal, tel_empresa,
                email_tec, status
                FROM {$this->table}");
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro ao obter todos os técnicos: " . $e->getMessage());
            return [];
        }
    }

    public function obterTodosComPaginacao(int $offset, int $limit): array
    {
        try {
            $stmt = $this->db->prepare("SELECT 
                id_tec, nome_tec, cpf_tec, rg_tec, rg_emissor_tec,
                data_expedicao_rg_tec, data_nascimento_tec, data_cadastro_tec,
                tel_pessoal, tel_empresa,
                email_tec, status
                FROM {$this->table}
                ORDER BY id_tec DESC
                LIMIT :limit OFFSET :offset");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro ao obter técnicos com paginação: " . $e->getMessage());
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
            error_log("Erro ao contar técnicos: " . $e->getMessage());
            return 0;
        }
    }

    public function buscarPorId(int $id): object|false
    {
        $stmt = $this->db->prepare("
            SELECT 
                id_tec, nome_tec, cpf_tec, rg_tec, rg_emissor_tec,
                data_expedicao_rg_tec, data_nascimento_tec, data_cadastro_tec,
                tel_pessoal, tel_empresa,
                email_tec, status
            FROM {$this->table} 
            WHERE id_tec = :id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function atualizarTecnico(int $id, array $dados): bool
    {
        $allowedFields = [
            'nome_tec', 'cpf_tec', 'rg_tec', 'rg_emissor_tec',
            'data_expedicao_rg_tec', 'data_nascimento_tec', 'data_cadastro_tec',
            'tel_pessoal', 'tel_empresa',
            'email_tec', 'senha_hash_tec', 'status'
        ];

        $fields = [];
        $values = [];

        foreach ($dados as $chave => $valor) {
            if (in_array($chave, $allowedFields)) {
                $fields[] = "{$chave} = :{$chave}";
                $values[":{$chave}"] = $valor;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE {$this->table} 
                SET " . implode(', ', $fields) . " 
                WHERE {$this->primaryKey} = :id";
        $values[':id'] = $id;

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar técnico: " . $e->getMessage());
            throw $e;
        }
    }

    public function alterarStatus(int $id, string $status): bool
    {
        $sql = "UPDATE {$this->table} SET status = :status WHERE {$this->primaryKey} = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao alterar o status do técnico: " . $e->getMessage());
            return false;
        }
    }

    public function buscarPorNome(string $nome): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                id_tec, nome_tec, cpf_tec, rg_tec, rg_emissor_tec,
                data_expedicao_rg_tec, data_nascimento_tec, data_cadastro_tec,
                tel_pessoal, tel_empresa,
                email_tec, status
            FROM {$this->table}
            WHERE nome_tec LIKE :nome
        ");
        $stmt->execute([':nome' => "%$nome%"]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function verificarCpfDuplicado(string $cpf, int $excludeId = null): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE REPLACE(REPLACE(REPLACE(cpf_tec, '.', ''), '-', ''), '/', '') = :cpf";
        
        if ($excludeId) {
            $sql .= " AND id_tec != :excludeId";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cpf', $cpf);
        if ($excludeId) {
            $stmt->bindParam(':excludeId', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ)->count > 0;
    }

    public function verificarEmailDuplicado(string $email, int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email_tec = :email";
        
        if ($excludeId) {
            $sql .= " AND id_tec != :excludeId";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email);
        if ($excludeId) {
            $stmt->bindParam(':excludeId', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ)->count > 0;
    }


}
