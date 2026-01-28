<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class Usuario extends Model
{
    protected string $table = 'usuario';
    protected string $primaryKey = 'id_usu';

    public function __construct()
    {
        parent::__construct();
    }

    public function criar(array $dados): bool
    {
        $sql = "INSERT INTO {$this->table} (
                    nome_usu, cpf_usu, rg_usu, rg_emissor_usu,
                    data_expedicao_rg_usu, data_nascimento_usu, data_cadastro_usu,
                    tel1_usu, tel2_usu, tel3_usu,
                    email_usu, senha_hash_usu, perfil, status
                ) VALUES (
                    :nome_usu, :cpf_usu, :rg_usu, :rg_emissor_usu,
                    :data_expedicao_rg_usu, :data_nascimento_usu, :data_cadastro_usu,
                    :tel1_usu, :tel2_usu, :tel3_usu,
                    :email_usu, :senha_hash_usu, :perfil, :status
                )";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nome_usu'             => $dados['nome_usu'],
            ':cpf_usu'              => $dados['cpf_usu'],
            ':rg_usu'               => $dados['rg_usu'],
            ':rg_emissor_usu'       => $dados['rg_emissor_usu'] ?? null,
            ':data_expedicao_rg_usu'=> $dados['data_expedicao_rg_usu'] ?? null,
            ':data_nascimento_usu'  => $dados['data_nascimento_usu'] ?? null,
            ':data_cadastro_usu'    => $dados['data_cadastro_usu'],
            ':tel1_usu'             => $dados['tel1_usu'],
            ':tel2_usu'             => $dados['tel2_usu'] ?? null,
            ':tel3_usu'             => $dados['tel3_usu'] ?? null,
            ':email_usu'            => $dados['email_usu'],
            ':senha_hash_usu'       => $dados['senha_hash_usu'],
            ':perfil'               => $dados['perfil'] ?? 'funcionario',
            ':status'               => $dados['status'] ?? 'ativo'
        ]);
    }

    public function buscarPorEmail(string $email): object|false
    {
        $sql = "SELECT id_usu, nome_usu, cpf_usu, email_usu, senha_hash_usu, perfil, status
                FROM {$this->table} WHERE email_usu = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

   public function obterTodos(): array
{
    try {
        $stmt = $this->db->query("SELECT 
            id_usu, nome_usu, cpf_usu, rg_usu, rg_emissor_usu,
            data_expedicao_rg_usu, data_nascimento_usu,
            tel1_usu, tel2_usu, tel3_usu,
            email_usu, perfil, status
            FROM {$this->table}");
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
        error_log("Erro ao obter todos os usuários: " . $e->getMessage());
        return [];
    }
}


   public function buscarPorId(int $id): object|false
{
    $stmt = $this->db->prepare("
        SELECT 
            id_usu, nome_usu, cpf_usu, rg_usu, rg_emissor_usu,
            data_expedicao_rg_usu, data_nascimento_usu,
            tel1_usu, tel2_usu, tel3_usu,
            email_usu, perfil, status
        FROM {$this->table} 
        WHERE id_usu = :id
    ");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_OBJ);
}


   public function atualizarUsuario(int $id, array $dados): bool
{
    // Lista de campos permitidos para atualizar (todos os campos da tabela exceto PK)
    $allowedFields = [
        'nome_usu', 'cpf_usu', 'rg_usu', 'rg_emissor_usu',
        'data_expedicao_rg_usu', 'data_nascimento_usu', 'data_cadastro_usu',
        'tel1_usu', 'tel2_usu', 'tel3_usu',
        'email_usu', 'senha_hash_usu', 'perfil', 'status'
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
        return false; // Nenhum campo válido enviado
    }

    $sql = "UPDATE {$this->table} 
            SET " . implode(', ', $fields) . " 
            WHERE {$this->primaryKey} = :id";
    $values[':id'] = $id;

    try {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    } catch (PDOException $e) {
        error_log("Erro ao atualizar usuário: " . $e->getMessage());
        throw $e;
    }
}
    /**
     * Altera o status de um usuário.
     * @param int $id O ID do usuário.
     * @param string $status O novo status ('ativo', 'inativo', etc.).
     * @return bool
     */
    public function alterarStatus(int $id, string $status): bool
    {
        $sql = "UPDATE {$this->table} SET status = :status WHERE {$this->primaryKey} = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao alterar o status do usuário: " . $e->getMessage());
            return false;
        }
    }

   public function buscarPorNome(string $nome)
{
    $stmt = $this->db->prepare("
        SELECT 
            id_usu, nome_usu, cpf_usu, rg_usu, rg_emissor_usu, 
            data_expedicao_rg_usu, data_nascimento_usu, 
            tel1_usu, tel2_usu, tel3_usu, email_usu, perfil, status
        FROM usuario
        WHERE nome_usu LIKE :nome
    ");
    $stmt->execute([':nome' => "%$nome%"]);
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}

    // English alias for compatibility
    public function searchByName(string $nome): array
    {
        return $this->buscarPorNome($nome);
    }

    public function verificarCpfDuplicado(string $cpf, int $excludeId = null): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE REPLACE(REPLACE(REPLACE(cpf_usu, '.', ''), '-', ''), '/', '') = :cpf";
        
        if ($excludeId) {
            $sql .= " AND id_usu != :excludeId";
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
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email_usu = :email";
        
        if ($excludeId) {
            $sql .= " AND id_usu != :excludeId";
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