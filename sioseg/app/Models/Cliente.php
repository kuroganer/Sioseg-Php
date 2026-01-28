<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class Cliente extends Model
{
    protected string $table = 'cliente';
    protected string $primaryKey = 'id_cli';

    public function __construct()
    {
        parent::__construct();
    }

    public function criar(array $dados): bool
    {
        // Defensive normalization: ensure rg_cli empty/whitespace becomes NULL
        if (array_key_exists('rg_cli', $dados)) {
            $dados['rg_cli'] = is_null($dados['rg_cli']) ? null : trim((string)$dados['rg_cli']);
            if ($dados['rg_cli'] === '') {
                $dados['rg_cli'] = null;
            }
        }

        $sql = "INSERT INTO {$this->table} (
                    nome_cli, nome_social, cnpj, cpf_cli, rg_cli, rg_emissor_cli,
                    data_expedicao_rg_cli, data_nascimento_cli, data_cadastro_cli,
                    tipo_pessoa, tel1_cli, tel2_cli, razao_social, email_cli,
                    senha_hash_cli, endereco, tipo_moradia, logradouro, cidade, bairro,
                    uf, cep, ponto_referencia, complemento, num_end, status
                ) VALUES (
                    :nome_cli, :nome_social, :cnpj, :cpf_cli, :rg_cli, :rg_emissor_cli,
                    :data_expedicao_rg_cli, :data_nascimento_cli, :data_cadastro_cli,
                    :tipo_pessoa, :tel1_cli, :tel2_cli, :razao_social, :email_cli,
                    :senha_hash_cli, :endereco, :tipo_moradia, :logradouro, :cidade, :bairro,
                    :uf, :cep, :ponto_referencia, :complemento, :num_end, :status
                )";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nome_cli'              => $dados['nome_cli'],
            ':nome_social'           => $dados['nome_social'] ?? null,
            ':cnpj'                  => $dados['cnpj'] ?? null,
            ':cpf_cli'               => $dados['cpf_cli'] ?? null,
            ':rg_cli'                => $dados['rg_cli'] ?? null,
            ':rg_emissor_cli'        => $dados['rg_emissor_cli'] ?? null,
            ':data_expedicao_rg_cli' => $dados['data_expedicao_rg_cli'] ?? null,
            ':data_nascimento_cli'   => $dados['data_nascimento_cli'] ?? null,
            ':data_cadastro_cli'     => $dados['data_cadastro_cli'] ?? date('Y-m-d H:i:s'),
            ':tipo_pessoa'           => $dados['tipo_pessoa'],
            ':tel1_cli'              => $dados['tel1_cli'],
            ':tel2_cli'              => $dados['tel2_cli'] ?? null,
            ':razao_social'          => $dados['razao_social'] ?? null,
            ':email_cli'             => $dados['email_cli'],
            ':senha_hash_cli'        => $dados['senha_hash_cli'],
            ':endereco'              => $dados['endereco'],
            ':tipo_moradia'          => $dados['tipo_moradia'] ?? null,
            ':logradouro'            => $dados['logradouro'] ?? null,
            ':cidade'                => $dados['cidade'] ?? null,
            ':bairro'                => $dados['bairro'] ?? null,
            ':uf'                    => $dados['uf'],
            ':cep'                   => $dados['cep'] ?? null,
            ':ponto_referencia'      => $dados['ponto_referencia'] ?? null,
            ':complemento'           => $dados['complemento'] ?? null,
            ':num_end'               => $dados['num_end'] ?? null,
            ':status'                => $dados['status'] ?? 'ativo'
        ]);
    }

    public function buscarPorEmail(string $email): object|false
    {
        $sql = "SELECT id_cli, nome_cli, nome_social, cnpj, cpf_cli, rg_cli, rg_emissor_cli,
                       data_expedicao_rg_cli, data_nascimento_cli, data_cadastro_cli, nome_social,
                       tipo_pessoa, tel1_cli, tel2_cli, razao_social, email_cli, senha_hash_cli,
                       endereco, tipo_moradia, logradouro, cidade, bairro, uf, cep, ponto_referencia,
                       complemento, num_end, status
                FROM {$this->table} WHERE email_cli = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function obterTodos(): array
    {
        try {
            $stmt = $this->db->query("SELECT * FROM {$this->table}");
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro ao obter todos os clientes: " . $e->getMessage());
            return [];
        }
    }

    public function obterTodosComPaginacao(int $offset, int $limit): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY id_cli DESC LIMIT :limit OFFSET :offset");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro ao obter clientes com paginação: " . $e->getMessage());
            return [];
        }
    }

    public function obterAtivosComPaginacao(int $offset, int $limit): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE status = 'ativo' ORDER BY id_cli DESC LIMIT :limit OFFSET :offset");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro ao obter clientes ativos com paginação: " . $e->getMessage());
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
            error_log("Erro ao contar clientes: " . $e->getMessage());
            return 0;
        }
    }

    public function contarAtivos(): int
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM {$this->table} WHERE status = 'ativo'");
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            return (int)$result->total;
        } catch (PDOException $e) {
            error_log("Erro ao contar clientes ativos: " . $e->getMessage());
            return 0;
        }
    }

    public function buscarPorId(int $id): object|false
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id_cli = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function atualizarCliente(int $id, array $dados): bool
    {
        $allowedFields = [
            'nome_cli', 'nome_social', 'cnpj', 'cpf_cli', 'rg_cli', 'rg_emissor_cli',
            'data_expedicao_rg_cli', 'data_nascimento_cli', 'data_cadastro_cli',
            'tipo_pessoa', 'tel1_cli', 'tel2_cli', 'razao_social', 'email_cli', 'senha_hash_cli', 'nome_social',
            'endereco', 'tipo_moradia', 'logradouro', 'cidade', 'bairro', 'uf', 'cep',
            'ponto_referencia', 'complemento', 'num_end', 'status'
        ];

        $fields = [];
        $values = [];

        // Defensive normalization: if rg_cli provided as empty string, convert to NULL
        if (array_key_exists('rg_cli', $dados)) {
            $dados['rg_cli'] = is_null($dados['rg_cli']) ? null : trim((string)$dados['rg_cli']);
            if ($dados['rg_cli'] === '') {
                $dados['rg_cli'] = null;
            }
        }

        foreach ($dados as $chave => $valor) {
            if (in_array($chave, $allowedFields)) {
                $fields[] = "{$chave} = :{$chave}";
                $values[":{$chave}"] = $valor;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE {$this->primaryKey} = :id";
        $values[':id'] = $id;

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar cliente: " . $e->getMessage());
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
            error_log("Erro ao alterar o status do cliente: " . $e->getMessage());
            return false;
        }
    }

    public function buscarPorNome(string $nome): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE (nome_cli LIKE :nome OR nome_social LIKE :nome OR razao_social LIKE :nome)
              AND status = 'ativo'
            LIMIT 20
        ");
        $stmt->execute([':nome' => "%$nome%"]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function buscarPorNomeTodosStatus(string $nome): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE (nome_cli LIKE :nome OR nome_social LIKE :nome OR razao_social LIKE :nome)
            ORDER BY status DESC, nome_cli ASC, razao_social ASC
            LIMIT 50
        ");
        $stmt->execute([':nome' => "%$nome%"]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function obterTodosAtivos(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE status = 'ativo'");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro ao obter clientes ativos: " . $e->getMessage());
            return [];
        }
    }

    public function buscarPorNomeAtivo(string $nome): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE (nome_cli LIKE :nome OR nome_social LIKE :nome OR razao_social LIKE :nome)
              AND status = 'ativo'
        ");
        $stmt->execute([':nome' => "%$nome%"]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function buscarPorCpfCnpj(string $documento): object|false
    {
        // Normalizar documento: remover apenas pontuação e espaços, preservando letras
        // quando houver identificadores alfanuméricos.
        $documento = preg_replace('/[.\-\/\s]/', '', $documento);
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE (REPLACE(REPLACE(REPLACE(cpf_cli, '.', ''), '-', ''), '/', '') = :documento 
                   OR REPLACE(REPLACE(REPLACE(cnpj, '.', ''), '-', ''), '/', '') = :documento)
              AND status = 'ativo'
            LIMIT 1
        ");
        $stmt->execute([':documento' => $documento]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function buscarPorCpfCnpjTodosStatus(string $documento): array
    {
        // Normalizar documento: remover apenas pontuação e espaços, preservando letras
        $documento = preg_replace('/[.\-\/\s]/', '', $documento);
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE (REPLACE(REPLACE(REPLACE(cpf_cli, '.', ''), '-', ''), '/', '') = :documento 
                   OR REPLACE(REPLACE(REPLACE(cnpj, '.', ''), '-', ''), '/', '') = :documento)
            ORDER BY status DESC
        ");
        $stmt->execute([':documento' => $documento]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function verificarCpfDuplicado(string $cpf, int $excludeId = null): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE REPLACE(REPLACE(REPLACE(cpf_cli, '.', ''), '-', ''), '/', '') = :cpf";
        
        if ($excludeId) {
            $sql .= " AND id_cli != :excludeId";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cpf', $cpf);
        if ($excludeId) {
            $stmt->bindParam(':excludeId', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ)->count > 0;
    }

    public function verificarRgDuplicado(string $rg, int $excludeId = null): bool
    {
        $rg = trim($rg);
        if ($rg === '') {
            return false;
        }

        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE rg_cli = :rg";
        if ($excludeId) {
            $sql .= " AND id_cli != :excludeId";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':rg', $rg);
        if ($excludeId) {
            $stmt->bindParam(':excludeId', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ)->count > 0;
    }

    public function verificarCnpjDuplicado(string $cnpj, int $excludeId = null): bool
    {
        // Remover apenas pontuação/espacos para permitir CNPJs alfanuméricos
        $cnpj = preg_replace('/[.\-\/\s]/', '', $cnpj);
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE REPLACE(REPLACE(REPLACE(cnpj, '.', ''), '-', ''), '/', '') = :cnpj";
        
        if ($excludeId) {
            $sql .= " AND id_cli != :excludeId";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cnpj', $cnpj);
        if ($excludeId) {
            $stmt->bindParam(':excludeId', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ)->count > 0;
    }

    public function verificarEmailDuplicado(string $email, int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email_cli = :email";
        
        if ($excludeId) {
            $sql .= " AND id_cli != :excludeId";
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
