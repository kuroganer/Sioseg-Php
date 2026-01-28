<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class AvaliacaoTecnica extends Model
{
    protected string $table = 'avaliacao_tecnica';
    protected string $primaryKey = 'id_ava';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Cria uma nova avaliação técnica, mas somente se a Ordem de Serviço estiver 'CONCLUÍDA'.
     *
     * @param array $data Dados da avaliação. Deve conter 'id_os_fk', 'nota', e opcionalmente 'comentario'.
     * @return bool|string Retorna true em sucesso, ou uma string com a mensagem de erro em caso de falha.
     */
    public function criar(array $data)
{
    // 1. Verificar se a Ordem de Serviço está concluída
    $osModel = new OrdemServico();
    $os = $osModel->buscarPorId((int)$data['id_os_fk']);

    if (!$os) {
        return "Ordem de Serviço não encontrada.";
    }

    if (!in_array($os->status, ['concluida', 'encerrada'])) {
        return "A avaliação só pode ser registrada para Ordens de Serviço encerradas.";
    }

    // 2. Verificar se já existe uma avaliação para esta OS
    if ($this->buscarPorIdOS((int)$data['id_os_fk'])) {
        return "Esta Ordem de Serviço já foi avaliada.";
    }

    // 3. Inserir a avaliação no banco de dados
    $sql = "INSERT INTO {$this->table} (nota, comentario, id_os_fk) 
            VALUES (:nota, :comentario, :id_os_fk)";  // Remover o campo data_avaliacao

    try {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nota'       => $data['nota'],
            ':comentario' => $data['comentario'] ?? null,
            ':id_os_fk'   => $data['id_os_fk']
        ]);
    } catch (PDOException $e) {
        error_log("Erro ao criar avaliação técnica: " . $e->getMessage());
        return "Ocorreu um erro ao salvar a avaliação.";
    }
}
    /**
     * Busca uma avaliação pelo ID da Ordem de Serviço.
     *
     * @param int $osId
     * @return object|false
     */
   public function buscarPorIdOS(int $osId): object|false
{
    $sql = "SELECT * FROM {$this->table} WHERE id_os_fk = :id_os_fk LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':id_os_fk' => $osId]);
    return $stmt->fetch(PDO::FETCH_OBJ);
}


    /**
     * Busca uma avaliação pelo seu ID primário.
     *
     * @param int $id
     * @return object|false
     */
    public function buscarPorId(int $id): object|false
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Busca avaliações por cliente
     *
     * @param int $clienteId
     * @return array
     */
    public function buscarPorCliente(int $clienteId): array
    {
        $sql = "SELECT av.*, os.id_os, os.servico_prestado, os.data_encerramento as data_avaliacao
                FROM {$this->table} av
                JOIN ordem_servico os ON av.id_os_fk = os.id_os
                WHERE os.id_cli_fk = :cliente_id
                ORDER BY av.id_ava DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cliente_id' => $clienteId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Calcula a média de avaliações dos últimos N dias.
     * @param int $days O número de dias a serem considerados.
     * @return array Um array associativo com 'data' => 'media'.
     */
    public function obterMediaAvaliacoesUltimosDias(int $days = 7): array
    {
        $dates = [];
        $results = [];

        // Gera as labels dos últimos N dias (ex: '25/10', '26/10', ...)
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $label = date('d/m', strtotime($date));
            $dates[$date] = $label;
            $results[$label] = 0; // Inicializa a média do dia com 0
        }

        // A nota na sua tabela é um único campo 'nota'. A query foi ajustada.
        // A query agora busca a data da tabela 'ordem_servico' pois 'avaliacao_tecnica' não possui data.
        $sql = "SELECT 
                    DATE(os.data_encerramento) as dia, 
                    AVG(av.nota) as media_diaria
                FROM {$this->table} av
                JOIN ordem_servico os ON av.id_os_fk = os.id_os
                WHERE os.data_encerramento IS NOT NULL 
                  AND os.data_encerramento >= :startDate
                GROUP BY DATE(os.data_encerramento)
                ORDER BY dia ASC";

        $startDate = date('Y-m-d', strtotime("-" . ($days - 1) . " days"));
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':startDate' => $startDate]);
        $dbResults = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Preenche os resultados com os dados do banco
        foreach ($dbResults as $row) {
            $label = date('d/m', strtotime($row['dia']));
            if (isset($results[$label])) {
                // Formata o número para ter no máximo 2 casas decimais
                $results[$label] = (float) number_format($row['media_diaria'], 2);
            }
        }

        return $results;
    }
}