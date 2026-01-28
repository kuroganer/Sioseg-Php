<?php

namespace App\Services;

use App\Models\Tecnico;

class TecnicoService
{
    private Tecnico $tecnicoModel;

    public function __construct()
    {
        $this->tecnicoModel = new Tecnico();
    }

    public function searchByName(string $nome): array
    {
        return $this->tecnicoModel->buscarPorNome($nome);
    }

    public function findById(int $id): object|false
    {
        return $this->tecnicoModel->buscarPorId($id);
    }

    public function getAll(): array
    {
        return $this->tecnicoModel->obterTodos();
    }

    public function updateTecnico(int $id, array $dados): bool
    {
        return $this->tecnicoModel->atualizarTecnico($id, $dados);
    }

    public function changeStatus(int $id, string $status): bool
    {
        return $this->tecnicoModel->alterarStatus($id, $status);
    }
}