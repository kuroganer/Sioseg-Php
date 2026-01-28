<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Usuario;

class UserController extends Controller
{
    private Usuario $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new Usuario();
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
                'text' => 'Usuários',
                'route' => '#',
                'sub_items' => [
                    ['text' => 'Gerenciar Usuário', 'route' => BASE_URL . 'admin/users'],
                    ['text' => 'Cadastrar Usuário', 'route' => BASE_URL . 'admin/users/register'],
                    ['text' => 'Pesquisar Usuário', 'route' => BASE_URL . 'admin/users/search'],
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

    // Listagem de usuários
    public function index()
    {
        $users = $this->userModel->obterTodos();
        $data = array_merge($this->getBaseData(), [
            'users'   => $users,
            'sucesso' => Session::obter('sucesso'),
            'erro'    => Session::obter('erro')
        ]);

        Session::remover('sucesso');
        Session::remover('erro');

        $this->visualizacao('admin/users/index', $data);
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

        $this->visualizacao('admin/users/user_register', $data);
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
            'nome_usu' => strip_tags($_POST['nome_usu'] ?? ''),
            'cpf_usu'                => $_POST['cpf_usu'] ?? null,
            'rg_usu'                 => $_POST['rg_usu'] ?? null,
            'rg_emissor_usu'         => $_POST['rg_emissor_usu'] ?? null,
            'data_expedicao_rg_usu'  => $_POST['data_expedicao_rg_usu'] ?? null,
            'data_nascimento_usu'    => $_POST['data_nascimento_usu'] ?? null,
            'tel1_usu'               => $_POST['tel1_usu'] ?? null,
            'tel2_usu'               => $_POST['tel2_usu'] ?? null,
            'tel3_usu'               => $_POST['tel3_usu'] ?? null,
            'email_usu'              => filter_var($_POST['email_usu'] ?? '', FILTER_VALIDATE_EMAIL),
            'perfil'                 => filter_var($_POST['perfil'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'status'                 => $_POST['status'] ?? 'ativo',
            'data_cadastro_usu'      => date('Y-m-d H:i:s')
        ];
        

        $senha = $_POST['senha'] ?? '';
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';

        // Validações básicas
        if (empty($data['nome_usu']) || empty($data['email_usu']) || empty($data['perfil']) || empty($senha) || empty($confirmar_senha)) {
            Session::definir('cadastro_erro', 'Todos os campos obrigatórios devem ser preenchidos.');
            header('Location: ' . BASE_URL . 'admin/users/register');
            exit();
        }

        if (strlen($senha) < 6 || $senha !== $confirmar_senha) {
            Session::definir('cadastro_erro', 'Senha inválida ou não coincide.');
            header('Location: ' . BASE_URL . 'admin/users/register');
            exit();
        }

        $data['senha_hash_usu'] = password_hash($senha, PASSWORD_DEFAULT);
        
        // Limpar formatação dos telefones e CPF
        if ($data['tel1_usu']) {
            $data['tel1_usu'] = preg_replace('/[^0-9]/', '', $data['tel1_usu']);
        }
        if ($data['tel2_usu']) {
            $data['tel2_usu'] = preg_replace('/[^0-9]/', '', $data['tel2_usu']);
        }
        if ($data['tel3_usu']) {
            $data['tel3_usu'] = preg_replace('/[^0-9]/', '', $data['tel3_usu']);
        }
        if ($data['cpf_usu']) {
            $data['cpf_usu'] = preg_replace('/[^0-9]/', '', $data['cpf_usu']);
        }

        try {
            $validator = new \App\Core\DuplicateValidator();
            
            // Verificar email global
            $emailDuplicatas = $validator->verificarEmailGlobal($data['email_usu']);
            if (!empty($emailDuplicatas)) {
                $tipo = $emailDuplicatas[0]['tipo'];
                Session::definir('cadastro_erro', "E-mail já cadastrado como {$tipo}.");
                header('Location: ' . BASE_URL . 'admin/users/register');
                exit();
            }
            
            // Verificar CPF global
            if ($data['cpf_usu']) {
                $cpfDuplicatas = $validator->verificarCpfGlobal($data['cpf_usu']);
                if (!empty($cpfDuplicatas)) {
                    $tipo = $cpfDuplicatas[0]['tipo'];
                    Session::definir('cadastro_erro', "CPF já cadastrado como {$tipo}.");
                    header('Location: ' . BASE_URL . 'admin/users/register');
                    exit();
                }
            }

            $this->userModel->criar($data);

            Session::definir('cadastro_sucesso', 'Usuário criado com sucesso!');
            header('Location: ' . BASE_URL . 'admin/users/register');
            exit();
        } catch (\PDOException $e) {
            error_log('Erro ao criar usuário: ' . $e->getMessage());
            Session::definir('cadastro_erro', 'Erro ao cadastrar usuário.');
            header('Location: ' . BASE_URL . 'admin/users/register');
            exit();
        }
    }

    // Formulário de edição
    public function showEditForm(int $id)
    {
        $user = $this->userModel->buscarPorId($id);
        if (!$user) {
            header("HTTP/1.0 404 Not Found");
            $this->visualizacao('errors/404');
            exit();
        }

        $data = array_merge($this->getBaseData(), [
            'user'           => $user,
            'edicao_erro'    => Session::obter('edicao_erro'),
            'edicao_sucesso' => Session::obter('edicao_sucesso')
        ]);

        Session::remover('edicao_erro');
        Session::remover('edicao_sucesso');

        $this->visualizacao('admin/users/edit', $data);
    }

    // Processa atualização
    public function atualizar(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'dashboard');
            exit();
        }

        // Captura campos do formulário
        $data = [
           'nome_usu' => strip_tags($_POST['nome_usu'] ?? ''),
            'cpf_usu'                => $_POST['cpf_usu'] ?? null,
            'rg_usu'                 => $_POST['rg_usu'] ?? null,
            'rg_emissor_usu'         => $_POST['rg_emissor_usu'] ?? null,
            'data_expedicao_rg_usu'  => $_POST['data_expedicao_rg_usu'] ?? null,
            'data_nascimento_usu'    => $_POST['data_nascimento_usu'] ?? null,
            'tel1_usu'               => $_POST['tel1_usu'] ?? null,
            'tel2_usu'               => $_POST['tel2_usu'] ?? null,
            'tel3_usu'               => $_POST['tel3_usu'] ?? null,
            'email_usu'              => filter_var($_POST['email_usu'] ?? '', FILTER_VALIDATE_EMAIL),
            'perfil'                 => filter_var($_POST['perfil'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
            'status'                 => $_POST['status'] ?? null
        ];
        

        $senha = $_POST['senha'] ?? '';
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';
        if (!empty($senha)) {
            if (strlen($senha) < 6 || $senha !== $confirmar_senha) {
                Session::definir('edicao_erro', 'Nova senha inválida ou não coincide.');
                header('Location: ' . BASE_URL . 'admin/users/edit/' . $id);
                exit();
            }
            $data['senha_hash_usu'] = password_hash($senha, PASSWORD_DEFAULT);
        }

        // Limpar formatação dos telefones e CPF
        if (isset($data['tel1_usu']) && $data['tel1_usu']) {
            $data['tel1_usu'] = preg_replace('/[^0-9]/', '', $data['tel1_usu']);
        }
        if (isset($data['tel2_usu']) && $data['tel2_usu']) {
            $data['tel2_usu'] = preg_replace('/[^0-9]/', '', $data['tel2_usu']);
        }
        if (isset($data['tel3_usu']) && $data['tel3_usu']) {
            $data['tel3_usu'] = preg_replace('/[^0-9]/', '', $data['tel3_usu']);
        }
        if (isset($data['cpf_usu']) && $data['cpf_usu']) {
            $data['cpf_usu'] = preg_replace('/[^0-9]/', '', $data['cpf_usu']);
        }
        
        // Remove campos nulos
        $updateData = array_filter($data, fn($v) => $v !== null);

        // Validações obrigatórias
        if (empty($updateData['nome_usu']) || empty($updateData['email_usu']) || empty($updateData['perfil'])) {
            Session::definir('edicao_erro', 'Nome, e-mail e perfil são obrigatórios.');
            header('Location: ' . BASE_URL . 'admin/users/edit/' . $id);
            exit();
        }

        try {
            $validator = new \App\Core\DuplicateValidator();
            
            // Verificar email global
            $emailDuplicatas = $validator->verificarEmailGlobal($updateData['email_usu'], 'usuario', $id);
            if (!empty($emailDuplicatas)) {
                $tipo = $emailDuplicatas[0]['tipo'];
                Session::definir('edicao_erro', "E-mail já cadastrado como {$tipo}.");
                header('Location: ' . BASE_URL . 'admin/users/edit/' . $id);
                exit();
            }
            
            // Verificar CPF global
            if (isset($updateData['cpf_usu']) && $updateData['cpf_usu']) {
                $cpfDuplicatas = $validator->verificarCpfGlobal($updateData['cpf_usu'], 'usuario', $id);
                if (!empty($cpfDuplicatas)) {
                    $tipo = $cpfDuplicatas[0]['tipo'];
                    Session::definir('edicao_erro', "CPF já cadastrado como {$tipo}.");
                    header('Location: ' . BASE_URL . 'admin/users/edit/' . $id);
                    exit();
                }
            }

            $this->userModel->atualizarUsuario($id, $updateData);

            Session::definir('edicao_sucesso', 'Usuário atualizado com sucesso!');
            header('Location: ' . BASE_URL . 'admin/users/edit/' . $id);
            exit();
        } catch (\PDOException $e) {
            error_log('Erro ao atualizar usuário: ' . $e->getMessage());
            Session::definir('edicao_erro', 'Erro ao atualizar usuário.');
            header('Location: ' . BASE_URL . 'admin/users/edit/' . $id);
            exit();
        }
    }

    // Alteração de status
    public function alterarStatus()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . 'admin/users');
        exit();
    }

    $id = $_POST['id'] ?? 0;
    $novoStatus = $_POST['status'] ?? '';

    if (!$id || !in_array($novoStatus, ['ativo', 'inativo'])) {
        Session::definir('erro', 'Status inválido.');
        header('Location: ' . BASE_URL . 'admin/users');
        exit();
    }

    $success = $this->userModel->alterarStatus((int)$id, $novoStatus);

    Session::definir($success ? 'sucesso' : 'erro', $success ? 'Status alterado com sucesso!' : 'Erro ao alterar status.');
    header('Location: ' . BASE_URL . 'admin/users');
    exit();
}

    // Busca de usuários
    public function pesquisar()
{
    $searchTerm = $_GET['nome'] ?? '';

    // Se houver termo de busca, retorna todos os campos do usuário
    if ($searchTerm) {
        $usuarios = $this->userModel->buscarPorNome($searchTerm); 
    } else {
        $usuarios = [];
    }

    // Passa todos os dados para a view
    $data = array_merge($this->getBaseData(), [
        'usuarios'   => $usuarios,
        'searchTerm' => $searchTerm
    ]);

    $this->visualizacao('admin/users/user_search', $data);
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
        if ($this->userModel->verificarEmailDuplicado($email, $excludeId ?: null)) {
            echo json_encode([
                'duplicado' => true,
                'tipo' => 'Usuário',
                'nome' => ''
            ]);
            exit();
        }
        
        // Verificar em outras tabelas
        $validator = new \App\Core\DuplicateValidator();
        $duplicatas = $validator->verificarEmailGlobal($email, 'usuario', $excludeId ?: null);
        
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
        if ($this->userModel->verificarCpfDuplicado($cpf, $excludeId ?: null)) {
            echo json_encode([
                'duplicado' => true,
                'tipo' => 'Usuário',
                'nome' => ''
            ]);
            exit();
        }
        
        // Verificar em outras tabelas
        $validator = new \App\Core\DuplicateValidator();
        $duplicatas = $validator->verificarCpfGlobal($cpf, 'usuario', $excludeId ?: null);
        
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
