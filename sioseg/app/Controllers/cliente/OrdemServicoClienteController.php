<?php

namespace App\Controllers\Cliente;

use App\Core\Controller;
use App\Core\Session;
use App\Models\OrdemServico;

class OrdemServicoClienteController extends Controller
{
    private OrdemServico $osModel;

    public function __construct()
    {
        parent::__construct();
        $this->osModel = new OrdemServico();
        // Permite apenas clientes
        if (Session::obter('perfil') !== 'cliente') {
            header('Location: ' . BASE_URL . 'dashboard');
            exit();
        }
    }

    // Lista OS do cliente logado
    public function index()
    {
        $clienteId = Session::obter('id_cli');
        $osList = $this->osModel->buscarPorCliente($clienteId);

        $data = [
            'userEmail' => Session::obter('email'),
            'osList'    => $osList
        ];

        $this->visualizacao('cliente/os/index', $data);
    }

    // Histórico de OS do cliente
    public function historico()
    {
        $clienteId = Session::obter('id_cli');
        $osList = $this->osModel->buscarHistoricoPorCliente($clienteId);

        $data = [
            'userEmail' => Session::obter('email'),
            'osList'    => $osList
        ];

        $this->visualizacao('cliente/os/historico', $data);
    }

    // Cliente confirma conclusão do serviço
    public function confirmarConclusao(int $id)
    {
        $clienteId = Session::obter('id_cli');
        $os = $this->osModel->buscarPorId($id);

        // Verifica se a OS pertence ao cliente
        if (!$os || $os->id_cli_fk != $clienteId) {
            header('Location: ' . BASE_URL . 'cliente/portal');
            exit();
        }

        try {
            // Atualiza a conclusão do cliente e verifica se deve mudar o status da OS
            $success = $this->osModel->atualizarConclusao($id, 'cliente');
            if ($success) {
                // Define uma flag na sessão para o JS saber que a OS foi confirmada
                Session::definir('os_confirmada_id', $id);
                Session::definir('sucesso', 'Serviço confirmado com sucesso!');
            } else {
                Session::definir('erro', 'Erro ao confirmar a conclusão do serviço.');
            }
        } catch (\Exception $e) {
            Session::definir('erro', 'Erro ao confirmar conclusão.');
        }
        // Volta para o portal em ambos os casos (sucesso ou falha)
        header('Location: ' . BASE_URL . 'cliente/portal');
        exit();
    }
}