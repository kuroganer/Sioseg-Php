<?php

namespace App\Services;

use App\Models\Produto;

class ProdutoService
{
    private Produto $produtoModel;

    public function __construct()
    {
        $this->produtoModel = new Produto();
    }

    public function searchByName(string $nome): array
    {
        return $this->produtoModel->buscarPorNome($nome);
    }

    public function searchByNome(string $nome): array
    {
        return $this->produtoModel->buscarPorNome($nome);
    }
}