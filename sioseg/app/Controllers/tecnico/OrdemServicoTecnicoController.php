<?php

namespace App\Controllers\Tecnico;

use App\Core\Controller;
use App\Core\Session;
use App\Models\OrdemServico;

class OrdemServicoTecnicoController extends Controller
{
    private OrdemServico $osModel;

    public function __construct()
    {
        parent::__construct();
        $this->osModel = new OrdemServico();
        // Garante que apenas usuários com perfil 'tecnico' acessem esta área
        Session::exigirPermissao(['tecnico']);
    }

    /**
     * Prepara os dados base para as views do técnico.
     */
    private function getBaseData(): array
    {
        return [
            'userEmail'   => Session::obter('email'),
            'userProfile' => Session::obter('perfil'),
        ];
    }

    /**
     * Exibe a lista de Ordens de Serviço do dia para o técnico logado.
     */
    public function index()
    {
        // A chave correta da sessão para o ID do técnico é 'id_tec'
        $tecnicoId = Session::obter('id_tec');
        if (!$tecnicoId) {
            Session::definir('erro', 'Sessão de técnico inválida. Por favor, faça login novamente.');
            header('Location: ' . BASE_URL . 'login');
            exit();
        }

        // Busca as OS do técnico para a data de hoje
        $today = date('Y-m-d');
        $osList = $this->osModel->buscarPorTecnicoEData($tecnicoId, $today);

        $data = array_merge($this->getBaseData(), [
            'osList'  => $osList,
            'sucesso' => Session::obter('sucesso'),
            'erro'    => Session::obter('erro')
        ]);

        // Limpa as mensagens da sessão
        Session::remover('sucesso');
        Session::remover('erro');

        // A view correspondente precisa ser criada
        $this->visualizacao('tecnico/os/index', $data);
    }

    // Wrapper PT
    public function indexar()
    {
        return $this->index();
    }

    /**
     * Exibe o histórico de Ordens de Serviço do técnico.
     */
    public function historico()
    {
        $tecnicoId = Session::obter('id_tec');
        if (!$tecnicoId) {
            Session::definir('erro', 'Sessão de técnico inválida. Por favor, faça login novamente.');
            header('Location: ' . BASE_URL . 'login');
            exit();
        }

        $osList = $this->osModel->buscarHistoricoPorTecnico($tecnicoId);

        $data = array_merge($this->getBaseData(), [
            'osList'  => $osList,
            'sucesso' => Session::obter('sucesso'),
            'erro'    => Session::obter('erro')
        ]);

        Session::remover('sucesso');
        Session::remover('erro');

        $this->visualizacao('tecnico/os/historico', $data);
    }

    /**
     * Exibe os detalhes de uma Ordem de Serviço.
     * Este método foi descontinuado e pode ser removido.
     */
    public function editar(int $id)
    {
        // Redireciona para a página principal, pois esta funcionalidade foi removida.
        header('Location: ' . BASE_URL . 'tecnico/os');
        exit();
    }

    /**
     * Altera o status de uma Ordem de Serviço.
     */
    public function alterarStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'tecnico/os');
            exit();
        }

        $id = $_POST['id'] ?? 0;
        $novoStatus = $_POST['status'] ?? '';
        $tecnicoId = Session::obter('id_tec');

        // Validação básica
        if (!$id || !$tecnicoId || !in_array($novoStatus, ['em andamento', 'concluida'])) {
            Session::definir('erro', 'Dados inválidos para alterar o status.');
            header('Location: ' . BASE_URL . 'tecnico/os');
            exit();
        }

        $id = (int)$id;
        $success = false;

        // Se o técnico está marcando como 'concluida', usamos a nova lógica
        if ($novoStatus === 'concluida') {
            // O ideal seria verificar se a OS pertence ao técnico antes de alterar
            $success = $this->osModel->atualizarConclusao($id, 'tecnico');
            Session::definir($success ? 'sucesso' : 'erro', $success ? 'Serviço marcado como concluído!' : 'Erro ao marcar serviço como concluído.');
        } else {
            // Para outros status como 'em andamento', a lógica antiga se mantém
            $success = $this->osModel->alterarStatus($id, $novoStatus);
            Session::definir($success ? 'sucesso' : 'erro', $success ? 'Status alterado com sucesso!' : 'Erro ao alterar status.');
        }

        header('Location: ' . BASE_URL . 'tecnico/os');
        exit();
    }
}