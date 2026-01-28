<?php
// ProjetoGES_MVC/app/Config/Database.php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        $host = 'localhost';
        $dbname = 'sioseg'; // <-- Nome do seu banco
        $user = 'root';     // <-- Usuário
        $password = '';     // <-- Senha

        try {
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

            $this->connection = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            die("Um erro técnico ocorreu. Por favor, tente novamente mais tarde.");
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }
}
