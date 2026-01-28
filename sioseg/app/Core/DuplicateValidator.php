<?php

namespace App\Core;

use PDO;
use App\Config\Database;

class DuplicateValidator
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function verificarCpfGlobal(string $cpf, string $excludeTable = null, int $excludeId = null): array
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        $duplicatas = [];
        
        $tabelas = [
            'cliente' => ['campo' => 'cpf_cli', 'id' => 'id_cli', 'nome' => 'nome_cli', 'tipo' => 'Cliente'],
            'tecnico' => ['campo' => 'cpf_tec', 'id' => 'id_tec', 'nome' => 'nome_tec', 'tipo' => 'Técnico'],
            'usuario' => ['campo' => 'cpf_usu', 'id' => 'id_usu', 'nome' => 'nome_usu', 'tipo' => 'Usuário']
        ];
        
        foreach ($tabelas as $tabela => $config) {
            if ($excludeTable === $tabela) {
                $sql = "SELECT COUNT(*) as count, {$config['nome']} as nome FROM {$tabela} 
                        WHERE REPLACE(REPLACE(REPLACE({$config['campo']}, '.', ''), '-', ''), '/', '') = :cpf 
                        AND {$config['id']} != :excludeId";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':cpf', $cpf);
                $stmt->bindParam(':excludeId', $excludeId, PDO::PARAM_INT);
            } else {
                $sql = "SELECT COUNT(*) as count, {$config['nome']} as nome FROM {$tabela} 
                        WHERE REPLACE(REPLACE(REPLACE({$config['campo']}, '.', ''), '-', ''), '/', '') = :cpf";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':cpf', $cpf);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            
            if ($result->count > 0) {
                $duplicatas[] = [
                    'tabela' => $tabela,
                    'tipo' => $config['tipo'],
                    'nome' => $result->nome
                ];
            }
        }
        
        return $duplicatas;
    }
    
    public function verificarEmailGlobal(string $email, string $excludeTable = null, int $excludeId = null): array
    {
        $duplicatas = [];
        
        $tabelas = [
            'cliente' => ['campo' => 'email_cli', 'id' => 'id_cli', 'nome' => 'nome_cli', 'tipo' => 'Cliente'],
            'tecnico' => ['campo' => 'email_tec', 'id' => 'id_tec', 'nome' => 'nome_tec', 'tipo' => 'Técnico'],
            'usuario' => ['campo' => 'email_usu', 'id' => 'id_usu', 'nome' => 'nome_usu', 'tipo' => 'Usuário']
        ];
        
        foreach ($tabelas as $tabela => $config) {
            if ($excludeTable === $tabela) {
                $sql = "SELECT COUNT(*) as count, {$config['nome']} as nome FROM {$tabela} 
                        WHERE {$config['campo']} = :email AND {$config['id']} != :excludeId";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':excludeId', $excludeId, PDO::PARAM_INT);
            } else {
                $sql = "SELECT COUNT(*) as count, {$config['nome']} as nome FROM {$tabela} 
                        WHERE {$config['campo']} = :email";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':email', $email);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            
            if ($result->count > 0) {
                $duplicatas[] = [
                    'tabela' => $tabela,
                    'tipo' => $config['tipo'],
                    'nome' => $result->nome
                ];
            }
        }
        
        return $duplicatas;
    }
}