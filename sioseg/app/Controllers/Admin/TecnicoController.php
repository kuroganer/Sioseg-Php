<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Tecnico;

class TecnicoController extends Controller
{
    private Tecnico $tecnicoModel;

    public function __construct()
    {
        parent::__construct();
        $this->tecnicoModel = new Tecnico();
        Session::exigirPermissao(['admin']);
    }

    private function getAdminMenu(): array
    {
        return [
            [
                'text' => 'Dashboard',
                'route' => BASE_URL . 'dashboard',
                'sub_items' => []
            ],
            [
                'text' => 'Técnicos',
                'route' => '#',
                'sub_items' => [
                    ['text' => 'Gerenciar Técnico', 'route' => BASE_URL . 'admin/tecnicos'],
                    ['text' => 'Cadastrar Técnico', 'route' => BASE_URL . 'admin/tecnicos/register'],
                    ['text' => 'Pesquisar Técnico', 'route' => BASE_URL . 'admin/tecnicos/search'],
                ]
            ],
            [
                'text' => 'Configurações do Sistema',
                'route' => BASE_URL . 'admin/settings',
                'sub_items' => []
            ],
            [
                'text' => 'Relatórios',
                'route' => BASE_URL . 'admin/reports',
                'sub_items' => []
            ]
        ];
    }

    private function getBaseData(): array
    {
        return [
            'userEmail'   => Session::obter('email'),
            'userProfile' => Session::obter('perfil'),
            'menuItems'   => $this->getAdminMenu()
        ];
    }

    // Listagem de técnicos
    public function index()
    {
        $currentPage = (int)($_GET['page'] ?? 1);
        $recordsPerPage = 50;
        
        $totalRecords = $this->tecnicoModel->contarTodos();
        $pagination = new \App\Core\Pagination($currentPage, $totalRecords, $recordsPerPage, BASE_URL . 'admin/tecnicos');
        
        $tecnicos = $this->tecnicoModel->obterTodosComPaginacao($pagination->getOffset(), $pagination->getLimit());
        
        $data = array_merge($this->getBaseData(), [
            'tecnicos' => $tecnicos,
            'pagination' => $pagination,
            'sucesso'  => Session::obter('sucesso'),
            'erro'     => Session::obter('erro')
        ]);

        Session::remover('sucesso');
        Session::remover('erro');

        $this->visualizacao('admin/tecnicos/index', $data);
    }

    // Formulário de cadastro
    public function showRegisterForm()
    {
        $data = array_merge($this->getBaseData(), [
            'cadastro_erro'    => Session::obter('cadastro_erro'),
            'cadastro_sucesso' => Session::obter('cadastro_sucesso')
        ]);

        Session::remover('cadastro_erro');
        Session::remover('cadastro_sucesso');

        $this->visualizacao('admin/tecnicos/tecnico_register', $data);
    }

    // Wrappers PT
    public function mostrarFormularioCadastro()
    {
        return $this->showRegisterForm();
    }

    public function processarCadastro()
    {
        return $this->processRegister();
    }

    // Processa cadastro
    public function processRegister()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'dashboard');
            exit();
        }

        $data = [
   'nome_tec' => strip_tags($_POST['nome_tec'] ?? ''),
    'cpf_tec'               => $_POST['cpf_tec'] ?? null,
    'rg_tec'                => $_POST['rg_tec'] ?? null,
    'rg_emissor_tec'        => $_POST['rg_emissor_tec'] ?? null,
    'data_expedicao_rg_tec' => $_POST['data_expedicao_rg_tec'] ?? null,
    'data_nascimento_tec'   => $_POST['data_nascimento_tec'] ?? null,
    'tel_pessoal'           => $_POST['tel_pessoal'] ?? null,
    'tel_empresa'           => $_POST['tel_empresa'] ?? null,
    'email_tec'             => filter_var($_POST['email_tec'] ?? '', FILTER_VALIDATE_EMAIL),
    'status'                => $_POST['status'] ?? 'ativo',
    'data_cadastro_tec'     => date('Y-m-d H:i:s')
];


        $senha = $_POST['senha'] ?? '';
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';

        if (empty($data['nome_tec']) || empty($data['email_tec']) || empty($senha) || empty($confirmar_senha)) {
            Session::definir('cadastro_erro', 'Todos os campos obrigatórios devem ser preenchidos.');
            header('Location: ' . BASE_URL . 'admin/tecnicos/register');
            exit();
        }

        if (strlen($senha) < 6 || $senha !== $confirmar_senha) {
            Session::definir('cadastro_erro', 'Senha inválida ou não coincide.');
            header('Location: ' . BASE_URL . 'admin/tecnicos/register');
            exit();
        }

        $data['senha_hash_tec'] = password_hash($senha, PASSWORD_DEFAULT);
        
        // Limpar formatação dos telefones e CPF
        if ($data['tel_pessoal']) {
            $data['tel_pessoal'] = preg_replace('/[^0-9]/', '', $data['tel_pessoal']);
        }
        if ($data['tel_empresa']) {
            $data['tel_empresa'] = preg_replace('/[^0-9]/', '', $data['tel_empresa']);
        }
        if ($data['cpf_tec']) {
            $data['cpf_tec'] = preg_replace('/[^0-9]/', '', $data['cpf_tec']);
        }

        try {
            $validator = new \App\Core\DuplicateValidator();
            
            // Verificar email global
            $emailDuplicatas = $validator->verificarEmailGlobal($data['email_tec']);
            if (!empty($emailDuplicatas)) {
                $tipo = $emailDuplicatas[0]['tipo'];
                Session::definir('cadastro_erro', "E-mail já cadastrado como {$tipo}.");
                header('Location: ' . BASE_URL . 'admin/tecnicos/register');
                exit();
            }
            
            // Verificar CPF global
            if ($data['cpf_tec']) {
                $cpfDuplicatas = $validator->verificarCpfGlobal($data['cpf_tec']);
                if (!empty($cpfDuplicatas)) {
                    $tipo = $cpfDuplicatas[0]['tipo'];
                    Session::definir('cadastro_erro', "CPF já cadastrado como {$tipo}.");
                    header('Location: ' . BASE_URL . 'admin/tecnicos/register');
                    exit();
                }
            }

            $this->tecnicoModel->criar($data);

            Session::definir('cadastro_sucesso', 'Técnico criado com sucesso!');
            header('Location: ' . BASE_URL . 'admin/tecnicos/register');
            exit();
        } catch (\PDOException $e) {
            error_log('Erro ao criar técnico: ' . $e->getMessage());
            Session::definir('cadastro_erro', 'Erro ao cadastrar técnico.');
            header('Location: ' . BASE_URL . 'admin/tecnicos/register');
            exit();
        }
    }

    // Formulário de edição
    public function showEditForm(int $id)
    {
        $tecnico = $this->tecnicoModel->buscarPorId($id);
        if (!$tecnico) {
            header("HTTP/1.0 404 Not Found");
            $this->visualizacao('errors/404');
            exit();
        }

        $data = array_merge($this->getBaseData(), [
            'tecnico'         => $tecnico,
            'edicao_erro'    => Session::obter('edicao_erro'),
            'edicao_sucesso' => Session::obter('edicao_sucesso')
        ]);

        Session::remover('edicao_erro');
        Session::remover('edicao_sucesso');

        $this->visualizacao('admin/tecnicos/edit', $data);
    }

    public function mostrarFormularioEdicao(int $id)
    {
        return $this->showEditForm($id);
    }

    // Processa atualização
    public function atualizar(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'dashboard');
            exit();
        }

        $data = [
            'nome_tec' => strip_tags($_POST['nome_tec'] ?? ''),
    'cpf_tec'               => $_POST['cpf_tec'] ?? null,
    'rg_tec'                => $_POST['rg_tec'] ?? null,
    'rg_emissor_tec'        => $_POST['rg_emissor_tec'] ?? null,
    'data_expedicao_rg_tec' => $_POST['data_expedicao_rg_tec'] ?? null,
    'data_nascimento_tec'   => $_POST['data_nascimento_tec'] ?? null,
    'tel_pessoal'           => $_POST['tel_pessoal'] ?? null,
    'tel_empresa'           => $_POST['tel_empresa'] ?? null,
    'email_tec'             => filter_var($_POST['email_tec'] ?? '', FILTER_VALIDATE_EMAIL),
    'status'                => $_POST['status'] ?? 'ativo',   
];


        $senha = $_POST['senha'] ?? '';
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';
        if (!empty($senha)) {
            if (strlen($senha) < 6 || $senha !== $confirmar_senha) {
                Session::definir('edicao_erro', 'Nova senha inválida ou não coincide.');
                header('Location: ' . BASE_URL . 'admin/tecnicos/edit/' . $id);
                exit();
            }
            $data['senha_hash_tec'] = password_hash($senha, PASSWORD_DEFAULT);
        }

        // Limpar formatação dos telefones e CPF
        if (isset($data['tel_pessoal']) && $data['tel_pessoal']) {
            $data['tel_pessoal'] = preg_replace('/[^0-9]/', '', $data['tel_pessoal']);
        }
        if (isset($data['tel_empresa']) && $data['tel_empresa']) {
            $data['tel_empresa'] = preg_replace('/[^0-9]/', '', $data['tel_empresa']);
        }
        if (isset($data['cpf_tec']) && $data['cpf_tec']) {
            $data['cpf_tec'] = preg_replace('/[^0-9]/', '', $data['cpf_tec']);
        }
        
        $updateData = array_filter($data, function($v) { return $v !== null; });

        if (empty($updateData['nome_tec']) || empty($updateData['email_tec'])) {
            Session::definir('edicao_erro', 'Nome e e-mail são obrigatórios.');
            header('Location: ' . BASE_URL . 'admin/tecnicos/edit/' . $id);
            exit();
        }

        try {
            $validator = new \App\Core\DuplicateValidator();
            
            // Verificar email global
            $emailDuplicatas = $validator->verificarEmailGlobal($updateData['email_tec'], 'tecnico', $id);
            if (!empty($emailDuplicatas)) {
                $tipo = $emailDuplicatas[0]['tipo'];
                Session::definir('edicao_erro', "E-mail já cadastrado como {$tipo}.");
                header('Location: ' . BASE_URL . 'admin/tecnicos/edit/' . $id);
                exit();
            }
            
            // Verificar CPF global
            if (isset($updateData['cpf_tec']) && $updateData['cpf_tec']) {
                $cpfDuplicatas = $validator->verificarCpfGlobal($updateData['cpf_tec'], 'tecnico', $id);
                if (!empty($cpfDuplicatas)) {
                    $tipo = $cpfDuplicatas[0]['tipo'];
                    Session::definir('edicao_erro', "CPF já cadastrado como {$tipo}.");
                    header('Location: ' . BASE_URL . 'admin/tecnicos/edit/' . $id);
                    exit();
                }
            }

            $this->tecnicoModel->atualizarTecnico($id, $updateData);

            Session::definir('edicao_sucesso', 'Técnico atualizado com sucesso!');
            header('Location: ' . BASE_URL . 'admin/tecnicos/edit/' . $id);
            exit();
        } catch (\PDOException $e) {
            error_log('Erro ao atualizar técnico: ' . $e->getMessage());
            Session::definir('edicao_erro', 'Erro ao atualizar técnico.');
            header('Location: ' . BASE_URL . 'admin/tecnicos/edit/' . $id);
            exit();
        }
    }

    // Alteração de status
    public function alterarStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/tecnicos');
            exit();
        }

        $id = $_POST['id'] ?? 0;
        $novoStatus = $_POST['status'] ?? '';

        if (!$id || !in_array($novoStatus, ['ativo', 'inativo'])) {
            Session::definir('erro', 'Status inválido.');
            header('Location: ' . BASE_URL . 'admin/tecnicos');
            exit();
        }

        $success = $this->tecnicoModel->alterarStatus((int)$id, $novoStatus);

        Session::definir($success ? 'sucesso' : 'erro', $success ? 'Status alterado com sucesso!' : 'Erro ao alterar status.');
        header('Location: ' . BASE_URL . 'admin/tecnicos');
        exit();
    }

    // Busca de técnicos
    public function pesquisar()
    {
        $searchTerm = $_GET['nome'] ?? '';

        $tecnicos = $searchTerm ? $this->tecnicoModel->buscarPorNome($searchTerm) : [];

        $data = array_merge($this->getBaseData(), [
            'tecnicos'   => $tecnicos,
            'searchTerm' => $searchTerm
        ]);

        $this->visualizacao('admin/tecnicos/tecnico_search', $data);
    }

    // Verificar email duplicado (AJAX)
    public function verificarEmail()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['erro' => 'Método não permitido']);
            exit();
        }
        
        $email = $_POST['email'] ?? '';
        $excludeId = (int)($_POST['exclude_id'] ?? 0);
        
        if (empty($email)) {
            echo json_encode(['duplicado' => false]);
            exit();
        }
        
        // Verificar na própria tabela primeiro
        if ($this->tecnicoModel->verificarEmailDuplicado($email, $excludeId ?: null)) {
            echo json_encode([
                'duplicado' => true,
                'tipo' => 'Técnico',
                'nome' => ''
            ]);
            exit();
        }
        
        // Verificar em outras tabelas
        $validator = new \App\Core\DuplicateValidator();
        $duplicatas = $validator->verificarEmailGlobal($email, 'tecnico', $excludeId ?: null);
        
        if (!empty($duplicatas)) {
            echo json_encode([
                'duplicado' => true,
                'tipo' => $duplicatas[0]['tipo'],
                'nome' => $duplicatas[0]['nome']
            ]);
        } else {
            echo json_encode(['duplicado' => false]);
        }
        exit();
    }

    // Verificar CPF duplicado (AJAX)
    public function verificarCpf()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['erro' => 'Método não permitido']);
            exit();
        }
        
        $cpf = $_POST['cpf'] ?? '';
        $excludeId = (int)($_POST['exclude_id'] ?? 0);
        
        if (empty($cpf)) {
            echo json_encode(['duplicado' => false]);
            exit();
        }
        
        // Limpar formatação
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        // Verificar na própria tabela primeiro
        if ($this->tecnicoModel->verificarCpfDuplicado($cpf, $excludeId ?: null)) {
            echo json_encode([
                'duplicado' => true,
                'tipo' => 'Técnico',
                'nome' => ''
            ]);
            exit();
        }
        
        // Verificar em outras tabelas
        $validator = new \App\Core\DuplicateValidator();
        $duplicatas = $validator->verificarCpfGlobal($cpf, 'tecnico', $excludeId ?: null);
        
        if (!empty($duplicatas)) {
            echo json_encode([
                'duplicado' => true,
                'tipo' => $duplicatas[0]['tipo'],
                'nome' => $duplicatas[0]['nome']
            ]);
        } else {
            echo json_encode(['duplicado' => false]);
        }
        exit();
    }
}
