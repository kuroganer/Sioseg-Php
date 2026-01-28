<?php

namespace App\Controllers\Cliente;

use App\Core\Controller;
use App\Core\Session;
use App\Models\AvaliacaoTecnica;
use App\Models\OrdemServico;

class AvaliacaoTecnicaController extends Controller
{
    private AvaliacaoTecnica $avaliacaoModel;
    private OrdemServico $osModel;

    public function __construct()
    {
        parent::__construct();
        Session::exigirPermissao(['cliente']); // Garante que apenas clientes acessem
        $this->avaliacaoModel = new AvaliacaoTecnica();
        $this->osModel = new OrdemServico();
    }

    /**
     * Prepara os dados base para as views do cliente.
     */
    private function getBaseData(): array
    {
        return [
            'userEmail'   => Session::obter('email'),
            'userProfile' => Session::obter('perfil'),
            // Adicione aqui itens de menu se a área do cliente tiver um
        ];
    }

    /**
     * Exibe o formulário de avaliação para uma OS específica.
     *
     * @param int $osId ID da Ordem de Serviço
     */
    public function showRegisterForm(int $osId)
    {
        $os = $this->osModel->buscarPorId($osId);
        $clienteId = Session::obter('id_cli');

        // Validações de segurança e regra de negócio
        if (!$os || $os->id_cli_fk != $clienteId) {
            Session::definir('erro', 'Ordem de Serviço não encontrada ou não pertence a você.');
            header('Location: ' . BASE_URL . 'cliente/portal');
            exit();
        }

        if (!in_array($os->status, ['concluida', 'encerrada'])) {
            Session::definir('erro', 'Esta Ordem de Serviço ainda não pode ser avaliada.');
            header('Location: ' . BASE_URL . 'cliente/portal');
            exit();
        }

        if ($this->avaliacaoModel->buscarPorIdOS($osId)) {
            Session::definir('erro', 'Esta Ordem de Serviço já foi avaliada.');
            header('Location: ' . BASE_URL . 'cliente/portal');
            exit();
        }

        $data = array_merge($this->getBaseData(), [
            'os' => $os,
            'cadastro_erro' => Session::obter('cadastro_erro')
        ]);
        Session::remover('cadastro_erro');

        $this->visualizacao('cliente/avaliacoes/register', $data);
    }

    /**
     * Processa o registro da avaliação.
     *
     * @param int $osId ID da Ordem de Serviço
     */
    public function processRegister(int $osId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'cliente/portal');
            exit();
        }

        // Re-valida a permissão para garantir segurança
        $os = $this->osModel->buscarPorId($osId);
        $clienteId = Session::obter('id_cli');
        if (!$os || $os->id_cli_fk != $clienteId) {
            Session::definir('erro', 'Acesso negado.');
            header('Location: ' . BASE_URL . 'cliente/portal');
            exit();
        }

        $data = [
            'id_os_fk'   => $osId,
            'nota'       => filter_input(INPUT_POST, 'nota', FILTER_VALIDATE_INT),
            'comentario' => strip_tags($_POST['comentario'] ?? ''),
            // Garante que a data da avaliação seja sempre registrada
        ];

        // Validação simples da nota
        if ($data['nota'] === false || $data['nota'] < 1 || $data['nota'] > 5) {
            Session::definir('cadastro_erro', 'Por favor, selecione uma nota de 1 a 5.');
            header('Location: ' . BASE_URL . 'cliente/portal');
            exit();
        }

        $result = $this->avaliacaoModel->criar($data);

        if ($result === true) {
            Session::definir('sucesso', 'Avaliação registrada com sucesso! Obrigado pelo seu feedback.');
            header('Location: ' . BASE_URL . 'cliente/portal');
        } else {
            // A mensagem de erro vem diretamente do Model
            Session::definir('cadastro_erro', $result);
            header('Location: ' . BASE_URL . 'cliente/portal');
        }
        exit();
    }
}