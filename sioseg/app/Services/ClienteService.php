<?php

namespace App\Services;

use App\Models\Cliente;

class ClienteService
{
    private Cliente $clienteModel;

    public function __construct()
    {
        $this->clienteModel = new Cliente();
    }

    public function searchByName(string $nome): array
    {
        return $this->clienteModel->buscarPorNome($nome);
    }
}