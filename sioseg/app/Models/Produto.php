<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class Produto extends Model
{
    protected string $table = 'produto';
    protected string $primaryKey = 'id_prod';

    public function __construct()
    {
        parent::__construct();
    }

    public function criar(array $dados): bool
    {
        $sql = "INSERT INTO {$this->table} (
                    marca, modelo, descricao, qtde, nome, status
                ) VALUES (
                    :marca, :modelo, :descricao, :qtde, :nome, :status
                )";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':marca'     => $dados['marca'] ?? null,
            ':modelo'    => $dados['modelo'] ?? null,
            ':descricao' => $dados['descricao'] ?? null,
            ':qtde'      => $dados['qtde'],
            ':nome'      => $dados['nome'],
            ':status'    => $dados['status'] ?? 'ativo'
        ]);
    }

    public function buscarPorId(int $id): object|false
    {
        $stmt = $this->db->prepare("
            SELECT
                id_prod, marca, modelo, descricao, qtde, nome, status
            FROM {$this->table}
            WHERE id_prod = :id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function obterTodos(bool $somenteAtivos = true): array
    {
        try {
            $sql = "
                SELECT
                    id_prod, marca, modelo, descricao, qtde, nome, status
                FROM {$this->table}
                " . ($somenteAtivos ? "WHERE status = 'ativo'" : "") . "
                ORDER BY nome ASC
            ";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro ao obter todos os produtos: " . $e->getMessage());
            return [];
        }
    }

    public function obterTodosComPaginacao(int $offset, int $limit): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id_prod, marca, modelo, descricao, qtde, nome, status
                FROM {$this->table}
                ORDER BY id_prod DESC
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Erro ao obter produtos com paginação: " . $e->getMessage());
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
            error_log("Erro ao contar produtos: " . $e->getMessage());
            return 0;
        }
    }

    public function atualizarProduto(int $id, array $dados): bool
    {
        // Campos permitidos para atualizar
        $allowedFields = [
            'marca', 'modelo', 'descricao', 'qtde', 'nome', 'status'
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
            error_log("Erro ao atualizar produto: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Altera o status de um produto.
     * @param int $id O ID do produto.
     * @param string $status O novo status ('ATIVO', 'INATIVO').
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
            error_log("Erro ao alterar o status do produto: " . $e->getMessage());
            return false;
        }
    }



    public function atualizarEstoque(int $id_prod, int $nova_qtde): bool
    {
        $sql = "UPDATE {$this->table} SET qtde = :qtde WHERE id_prod = :id_prod";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':qtde' => $nova_qtde, ':id_prod' => $id_prod]);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar estoque do produto: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Decrementa o estoque de um produto de forma atômica somente se houver quantidade suficiente.
     * Retorna true se o decremento foi aplicado, false se não havia estoque suficiente.
     */
    public function decrementarEstoqueSeDisponivel(int $id_prod, int $qtd): bool
    {
        $sql = "UPDATE {$this->table} SET qtde = qtde - :qty WHERE id_prod = :id_prod AND qtde >= :qty";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':qty' => $qtd, ':id_prod' => $id_prod]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao decrementar estoque do produto: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Incrementa o estoque de um produto (estorno) de forma atômica.
     */
    public function incrementarEstoque(int $id_prod, int $qtd): bool
    {
        $sql = "UPDATE {$this->table} SET qtde = qtde + :qty WHERE id_prod = :id_prod";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':qty' => $qtd, ':id_prod' => $id_prod]);
        } catch (PDOException $e) {
            error_log("Erro ao incrementar estoque do produto: " . $e->getMessage());
            throw $e;
        }
    }
    public function buscarPorNome(string $nome): array
    {
        $stmt = $this->db->prepare("
            SELECT
                id_prod, marca, modelo, descricao, qtde, nome, status
            FROM {$this->table}
            WHERE (nome LIKE :nome OR marca LIKE :nome OR modelo LIKE :nome)
              AND status = 'ativo'
            LIMIT 20
        ");
        $stmt->execute([':nome' => "%$nome%"]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function buscarPorNomeTodosStatus(string $nome): array
    {
        $stmt = $this->db->prepare("
            SELECT
                id_prod, marca, modelo, descricao, qtde, nome, status
            FROM {$this->table}
            WHERE (nome LIKE :nome OR marca LIKE :nome OR modelo LIKE :nome)
            ORDER BY status DESC, nome ASC
            LIMIT 50
        ");
        $stmt->execute([':nome' => "%$nome%"]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }


}
