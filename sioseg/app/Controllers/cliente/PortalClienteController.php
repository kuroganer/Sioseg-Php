<?php

namespace App\Controllers\Cliente;

use App\Core\Controller;
use App\Core\Session;
use App\Models\OrdemServico;
use App\Models\AvaliacaoTecnica;

class PortalClienteController extends Controller
{
    private OrdemServico $osModel;
    private AvaliacaoTecnica $avaliacaoModel;

    public function __construct()
    {
        parent::__construct();
        $this->osModel = new OrdemServico();
        $this->avaliacaoModel = new AvaliacaoTecnica();
        
        // Permite apenas clientes
        if (Session::obter('perfil') !== 'cliente') {
            header('Location: ' . BASE_URL . 'dashboard');
            exit();
        }
    }

    // Portal principal do cliente
    public function index()
    {
        $clienteId = Session::obter('id_cli');
        
        // Busca todas as OS do cliente
        $todasOS = $this->osModel->buscarPorCliente($clienteId);
        
        // Separa OS ativas e histórico
        $osAtivas = array_filter($todasOS, function($os) {
            return in_array($os->status, ['aberta', 'em andamento']) || ($os->status === 'concluida' && $os->conclusao_cliente === 'pendente');
        });
        
        $historico = array_filter($todasOS, function($os) {
            return in_array($os->status, ['concluida', 'encerrada']) && $os->conclusao_cliente === 'concluida';
        });

        // Busca avaliações
        $avaliacoes = $this->avaliacaoModel->buscarPorCliente($clienteId);

        $data = [
            'userEmail' => Session::obter('email'),
            'osAtivas' => $osAtivas,
            'historico' => $historico,
            'avaliacoes' => $avaliacoes
        ];

        $this->visualizacao('cliente/portal', $data);
    }

    // Confirma conclusão do serviço
    public function confirmarConclusao()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'cliente/portal');
            exit();
        }

        $osId = $_POST['os_id'] ?? null;
        $clienteId = Session::obter('id_cli');

        if (!$osId) {
            Session::definir('erro', 'ID da OS não informado.');
            header('Location: ' . BASE_URL . 'cliente/portal');
            exit();
        }

        $os = $this->osModel->buscarPorId($osId);

        // Verifica se a OS pertence ao cliente
        if (!$os || $os->id_cli_fk != $clienteId) {
            Session::definir('erro', 'OS não encontrada ou não pertence ao cliente.');
            header('Location: ' . BASE_URL . 'cliente/portal');
            exit();
        }

        try {
            // Usa a nova lógica de conclusão, que é mais segura e correta
            $success = $this->osModel->atualizarConclusao((int)$osId, 'cliente');
            if ($success) {
                Session::definir('os_confirmada_id', (int)$osId);
                Session::definir('sucesso', 'Serviço confirmado com sucesso!');
            } else {
                Session::definir('erro', 'Erro ao confirmar a conclusão do serviço.');
            }
        } catch (\Exception $e) {
            Session::definir('erro', 'Erro ao confirmar conclusão: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . 'cliente/portal');
        exit();
    }

    // Salva avaliação
    public function salvarAvaliacao()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'cliente/portal');
            exit();
        }

        $osId = $_POST['os_id'] ?? null;
        $nota = $_POST['nota'] ?? null;
        $comentario = $_POST['comentario'] ?? '';
        $clienteId = Session::obter('id_cli');

        if (!$osId || !$nota) {
            Session::definir('erro', 'Dados da avaliação incompletos.');
            header('Location: ' . BASE_URL . 'cliente/portal');
            exit();
        }

        $os = $this->osModel->buscarPorId($osId);

        // Verifica se a OS pertence ao cliente e está encerrada
        if (!$os || $os->id_cli_fk != $clienteId || !in_array($os->status, ['concluida', 'encerrada'])) {
            Session::definir('erro', 'Não é possível avaliar esta OS.');
            header('Location: ' . BASE_URL . 'cliente/portal');
            exit();
        }

        try {
            $avaliacaoData = [
                'id_os_fk' => $osId,
                'nota' => (string)$nota,
                'comentario' => $comentario
            ];

            $result = $this->avaliacaoModel->criar($avaliacaoData);
            
            if ($result === true) {
                Session::definir('sucesso', 'Avaliação salva com sucesso!');
            } else {
                Session::definir('erro', is_string($result) ? $result : 'Erro ao salvar avaliação.');
            }
        } catch (\Exception $e) {
            Session::definir('erro', 'Erro ao salvar avaliação: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . 'cliente/portal');
        exit();
    }
}