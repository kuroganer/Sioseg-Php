<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Cliente;
use App\Models\Produto;

class SearchController extends Controller
{
    public function searchClientes()
    {
        header('Content-Type: application/json');
        
        $term = $_GET['term'] ?? '';
        if (strlen($term) < 2) {
            echo json_encode([]);
            return;
        }
        
        $clienteModel = new Cliente();
        $clientes = $clienteModel->buscarPorNomeTodosStatus($term);
        
        $results = [];
        foreach ($clientes as $cliente) {
            $nome = $cliente->nome_cli;
            if ($cliente->tipo_pessoa === 'juridica' && !empty($cliente->razao_social)) {
                $nome = $cliente->razao_social;
            }
            
            $statusIndicator = ($cliente->status === 'inativo') ? ' (INATIVO)' : '';
            
            $results[] = [
                'id' => $cliente->id_cli,
                'text' => $nome . $statusIndicator
            ];
        }
        
        echo json_encode($results);
    }
    
    public function searchProdutos()
    {
        header('Content-Type: application/json');
        
        $term = $_GET['term'] ?? '';
        if (strlen($term) < 2) {
            echo json_encode([]);
            return;
        }
        
        $produtoModel = new Produto();
        $produtos = $produtoModel->buscarPorNomeTodosStatus($term);
        
        $results = [];
        foreach ($produtos as $produto) {
            $statusIndicator = ($produto->status === 'inativo') ? ' (INATIVO)' : '';
            
            $results[] = [
                'id' => $produto->id_prod,
                'text' => $produto->nome . ' - ' . $produto->marca . ' - ' . $produto->modelo . ' (Estoque: ' . $produto->qtde . ')' . $statusIndicator,
                'estoque' => $produto->qtde
            ];
        }
        
        echo json_encode($results);
    }

    // Portuguese canonical methods (wrappers) to migrate to PT names while keeping English compatibility
    public function pesquisarClientes()
    {
        return $this->searchClientes();
    }

    public function pesquisarProdutos()
    {
        return $this->searchProdutos();
    }
}