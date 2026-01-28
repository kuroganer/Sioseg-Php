<?php

namespace App\Services;

use App\Models\OrdemServico;

class OrdemServicoService
{
    private OrdemServico $osModel;

    public function __construct()
    {
        $this->osModel = new OrdemServico();
    }

    public function findByStatusWithDetails(string $status, ?int $limit = null): array
    {
        return $this->osModel->buscarPorStatusComDetalhes($status, $limit);
    }

    public function findHistoricoByTecnico(int $id_tec): array
    {
        return $this->osModel->buscarHistoricoPorTecnico($id_tec);
    }

    public function findHistoricoByCliente(int $id_cli): array
    {
        return $this->osModel->buscarHistoricoPorCliente($id_cli);
    }

    public function findByTecnicoAndDate(int $id_tec, string $date): array
    {
        return $this->osModel->buscarPorTecnicoEData($id_tec, $date);
    }

    public function searchOS($termo): array
    {
        return $this->osModel->buscarOS($termo);
    }

    public function findOSTecnicoDia(int $id_tec): array
    {
        return $this->osModel->buscarOSTecnicoDia($id_tec);
    }

    public function getOSStatsByMonth(int $year, int $month): array
    {
        return $this->osModel->obterEstatisticasOSPorMes($year, $month);
    }

    public function findConclusoesPendentes(): array
    {
        return $this->osModel->buscarConclusoesPendentes();
    }

    public function verificarHorarioDisponivel(string $dataAgendamento, int $idTecnico, ?int $idOsExcluir = null): bool
    {
        return $this->osModel->verificarHorarioDisponivel($dataAgendamento, $idTecnico, $idOsExcluir);
    }

    public function obterConflitosHorario(string $dataAgendamento, int $idTecnico, ?int $idOsExcluir = null): array
    {
        return $this->osModel->verificarConflitosHorario($dataAgendamento, $idTecnico, $idOsExcluir);
    }

    public function obterHorariosBloqueados(int $idTecnico, string $dataInicio, string $dataFim): array
    {
        return $this->osModel->obterHorariosBloqueados($idTecnico, $dataInicio, $dataFim);
    }
}