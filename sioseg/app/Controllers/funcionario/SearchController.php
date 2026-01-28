<?php

namespace App\Controllers\Funcionario;

use App\Core\Controller;
use App\Models\Cliente;
use App\Models\Produto;

class SearchController extends Controller
{
    public function buscarClientes()
    {
        header('Content-Type: application/json');
        
        $term = $_GET['term'] ?? '';
        if (strlen($term) < 2) {
            echo json_encode([]);
            return;
        }
        
        $clienteModel = new Cliente();
        $clientes = $clienteModel->buscarPorNome($term);
        
        $results = [];
        foreach ($clientes as $cliente) {
            $nome = $cliente->nome_cli;
            if ($cliente->tipo_pessoa === 'juridica' && !empty($cliente->razao_social)) {
                $nome = $cliente->razao_social;
            }
            
            $results[] = [
                'id' => $cliente->id_cli,
                'text' => $nome
            ];
        }
        
        echo json_encode($results);
    }
    
    public function buscarProdutos()
    {
        header('Content-Type: application/json');
        
        $term = $_GET['term'] ?? '';
        if (strlen($term) < 2) {
            echo json_encode([]);
            return;
        }
        
        $produtoModel = new Produto();
        $produtos = $produtoModel->buscarPorNome($term);
        
        $results = [];
        foreach ($produtos as $produto) {
            $results[] = [
                'id' => $produto->id_prod,
                'text' => $produto->nome . ' - ' . $produto->marca . ' - ' . $produto->modelo . ' (Estoque: ' . $produto->qtde . ')',
                'estoque' => $produto->qtde
            ];
        }
        
        echo json_encode($results);
    }
}