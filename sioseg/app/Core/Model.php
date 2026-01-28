<?php

namespace App\Core;

use PDO;
use App\Config\Database;

abstract class Model
{
    /**
     * @var PDO A instância da conexão com o banco de dados.
     * É protected para ser acessível pelas classes filhas (Models).
     */
    protected ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Inicia uma transação no banco de dados.
     */
    public function iniciarTransacao(): bool
    {
        return $this->db->beginTransaction();
    }

    /**
     * Confirma (commita) a transação atual.
     */
    public function confirmar(): bool
    {
        return $this->db->commit();
    }

    /**
     * Reverte (rollback) a transação atual.
     */
    public function reverter(): bool
    {
        return $this->db->rollBack();
    }
}