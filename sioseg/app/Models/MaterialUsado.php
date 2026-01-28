<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class MaterialUsado extends Model
{
    protected string $table = 'material_usado';
    protected string $primaryKey = 'id_prod_fk';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Adiciona material a uma OS
     */
    public function adicionarMaterial(int $id_os, int $id_prod, int $qtd_usada): bool
    {
        try {
            $sql = "INSERT INTO {$this->table} (id_prod_fk, id_os_fk, qtd_usada)
                    VALUES (:id_prod_fk, :id_os_fk, :qtd_usada)
                    ON DUPLICATE KEY UPDATE qtd_usada = qtd_usada + :qtd_usada";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id_prod_fk' => $id_prod,
                ':id_os_fk' => $id_os,
                ':qtd_usada' => $qtd_usada
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao adicionar material: " . $e->getMessage());
            throw new \Exception("Erro ao adicionar material à OS");
        }
    }

    /**
     * Busca material específico de uma OS
     */
     public function obterMaterialPorOSEProduto(int $id_os, int $id_prod)
    {
        $sql = "SELECT mu.*, p.qtde AS estoque_atual_produto 
                FROM {$this->table} mu 
                JOIN produto p ON mu.id_prod_fk = p.id_prod 
                WHERE mu.id_os_fk = :id_os AND mu.id_prod_fk = :id_prod";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_os' => $id_os, ':id_prod' => $id_prod]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    public function atualizarQuantidadeMaterial(int $id_os, int $id_prod, int $nova_qtd): bool
    {
        $sql = "UPDATE {$this->table} SET qtd_usada = :qtd WHERE id_os_fk = :id_os AND id_prod_fk = :id_prod";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':qtd' => $nova_qtd, ':id_os' => $id_os, ':id_prod' => $id_prod]);
        } catch (\PDOException $e) {
            error_log("Erro ao atualizar quantidade do material: " . $e->getMessage());
            return false;
        }
    }

    public function removerMaterial(int $id_os, int $id_prod): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id_os_fk = :id_os AND id_prod_fk = :id_prod";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id_os' => $id_os, ':id_prod' => $id_prod]);
        } catch (\PDOException $e) {
            error_log("Erro ao remover material: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Busca todos os materiais de uma OS
     */
    public function obterMateriaisPorOS(int $id_os): array
{
    try {
        $sql = "SELECT mu.*, p.nome, p.marca, p.modelo, p.descricao, p.qtde AS estoque_atual_produto, p.id_prod
                FROM {$this->table} mu
                INNER JOIN produto p ON mu.id_prod_fk = p.id_prod
                WHERE mu.id_os_fk = :id_os_fk";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_os_fk', $id_os, \PDO::PARAM_INT);
        $stmt->execute();
        
        // Retorne o resultado
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    } catch (PDOException $e) {
        error_log("Erro ao buscar materiais da OS: " . $e->getMessage());
        return [];
    }
}

    /**
     * Remove todos os materiais de uma OS
     */
    public function removerTodosMateriaisDaOS(int $id_os): bool
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id_os_fk = :id_os_fk";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id_os_fk' => $id_os]);
        } catch (PDOException $e) {
            error_log("Erro ao remover materiais da OS: " . $e->getMessage());
            throw new \Exception("Erro ao remover materiais da OS");
        }
    }
}
