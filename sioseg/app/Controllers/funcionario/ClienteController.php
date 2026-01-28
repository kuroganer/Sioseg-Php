<?php

namespace App\Controllers\Funcionario;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Cliente;

class ClienteController extends Controller
{
    private Cliente $clienteModel;

    public function __construct()
    {
        parent::__construct();
        $this->clienteModel = new Cliente();
        Session::exigirPermissao(['funcionario']);
    }

    private function getFuncionarioMenu(): array
    {
        return [
            [
                'text' => 'Dashboard',
                'route' => BASE_URL . 'dashboard',
                'sub_items' => []
            ],
            [
                'text' => 'Clientes',
                'route' => '#',
                'sub_items' => [
                    ['text' => 'Gerenciar Cliente', 'route' => BASE_URL . 'funcionario/clientes'],
                    ['text' => 'Cadastrar Cliente', 'route' => BASE_URL . 'funcionario/clientes/register'],
                    ['text' => 'Pesquisar Cliente', 'route' => BASE_URL . 'funcionario/clientes/search'],
                ]
            ],
            [
                'text' => 'Configurações do Sistema',
                'route' => BASE_URL . 'funcionario/settings',
                'sub_items' => []
            ],
            [
                'text' => 'Relatórios',
                'route' => BASE_URL . 'funcionario/reports',
                'sub_items' => []
            ]
        ];
    }

    private function getBaseData(): array
    {
        return [
            'userEmail'   => Session::obter('email'),
            'userProfile' => Session::obter('perfil'),
            'menuItems'   => $this->getFuncionarioMenu()
        ];
    }

    // Listagem de clientes
    public function index()
    {
        $currentPage = (int)($_GET['page'] ?? 1);
        $recordsPerPage = 50;
        
        $totalRecords = $this->clienteModel->contarAtivos();
        $pagination = new \App\Core\Pagination($currentPage, $totalRecords, $recordsPerPage, BASE_URL . 'funcionario/clientes');
        
        $clientes = $this->clienteModel->obterAtivosComPaginacao($pagination->getOffset(), $pagination->getLimit());
        
        $data = array_merge($this->getBaseData(), [
            'clientes' => $clientes,
            'pagination' => $pagination,
            'sucesso'  => Session::obter('sucesso'),
            'erro'     => Session::obter('erro')
        ]);

        Session::remover('sucesso');
        Session::remover('erro');

        $this->visualizacao('funcionario/clientes/index', $data);
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

        $this->visualizacao('funcionario/clientes/cliente_register', $data);
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
            'nome_cli'              => (isset($_POST['tipo_pessoa']) && $_POST['tipo_pessoa'] === 'fisica') ? strip_tags($_POST['nome_cli'] ?? '') : null,
            'nome_social'           => isset($_POST['nome_social']) ? trim($_POST['nome_social']) : null,
            'cnpj'                  => $_POST['cnpj'] ?? null,
            'cpf_cli'               => $_POST['cpf_cli'] ?? null,
            'rg_cli'                => $_POST['rg_cli'] ?? null,
            'rg_emissor_cli'        => isset($_POST['rg_emissor_cli']) ? trim($_POST['rg_emissor_cli']) : null,
            'data_expedicao_rg_cli' => $_POST['data_expedicao_rg_cli'] ?? null,
            'data_nascimento_cli'   => $_POST['data_nascimento_cli'] ?? null,
            'tipo_pessoa'           => $_POST['tipo_pessoa'],
            'tel1_cli'              => $_POST['tel1_cli'] ?? '',
            'tel2_cli'              => $_POST['tel2_cli'] ?? null,
            'razao_social'          => (isset($_POST['tipo_pessoa']) && $_POST['tipo_pessoa'] === 'juridica') ? trim($_POST['razao_social'] ?? '') : null,
            'email_cli'             => filter_var($_POST['email_cli'] ?? '', FILTER_VALIDATE_EMAIL),
            'senha_hash_cli'        => password_hash($_POST['senha'] ?? '', PASSWORD_DEFAULT),
            'tipo_moradia'          => isset($_POST['tipo_moradia']) ? trim($_POST['tipo_moradia']) : null,
            'logradouro'            => isset($_POST['logradouro']) ? trim($_POST['logradouro']) : null,
            'cidade'                => isset($_POST['cidade']) ? trim($_POST['cidade']) : null,
            'bairro'                => isset($_POST['bairro']) ? trim($_POST['bairro']) : null,
            'uf'                    => $_POST['uf'] ?? '',
            'cep'                   => $_POST['cep'] ?? null,
            'ponto_referencia'      => isset($_POST['ponto_referencia']) ? trim($_POST['ponto_referencia']) : null,
            'complemento'           => $_POST['complemento'] ?? null,
            'num_end'               => $_POST['num_end'] ?? null,
            'status'                => $_POST['status'] ?? 'ativo',
            'data_cadastro_cli'     => date('Y-m-d H:i:s')
        ];

        // Limpeza de dados com base no tipo de pessoa para evitar dados inconsistentes
        if ($data['tipo_pessoa'] === 'fisica') {
            $data['razao_social'] = null;
            $data['cnpj'] = null;
        } elseif ($data['tipo_pessoa'] === 'juridica') {
            $data['nome_cli'] = null;
            $data['cpf_cli'] = null;
            $data['nome_social'] = null; // Nome social não se aplica a PJ
        }

        // Validações básicas obrigatórias
        // O campo 'endereco' é obrigatório no BD. Vamos usar o 'logradouro' do formulário para preenchê-lo.
        $data['endereco'] = $data['logradouro'];

        if (
            empty($data['tipo_pessoa']) ||
            empty($data['email_cli']) ||
            empty($data['tel1_cli']) ||
            empty($data['endereco']) || // Agora validamos o campo 'endereco' que acabamos de preencher
            empty($data['num_end']) ||
            empty($data['bairro']) ||
            empty($data['cidade']) ||
            empty($data['uf']) ||
            empty($_POST['senha']) ||
            empty($_POST['confirmar_senha'])
        ) {
            Session::definir('cadastro_erro', 'Todos os campos obrigatórios devem ser preenchidos.');
            header('Location: ' . BASE_URL . 'funcionario/clientes/register');
            exit();
        }

        if (strlen($_POST['senha']) < 6 || $_POST['senha'] !== $_POST['confirmar_senha']) {
            Session::definir('cadastro_erro', 'Senha inválida ou não coincide.');
            header('Location: ' . BASE_URL . 'funcionario/clientes/register');
            exit();
        }

        $data['senha_hash_cli'] = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        
        // Limpar formatação dos telefones
        $data['tel1_cli'] = preg_replace('/[^0-9]/', '', $data['tel1_cli']);
        if ($data['tel2_cli']) {
            $data['tel2_cli'] = preg_replace('/[^0-9]/', '', $data['tel2_cli']);
        }
        
        // Limpar formatação de CPF/CNPJ
        if ($data['cpf_cli']) {
            $data['cpf_cli'] = preg_replace('/[^0-9]/', '', $data['cpf_cli']);
        }
        // Remover apenas pontuação/espacos do CNPJ para preservar letras
        if ($data['cnpj']) {
            $data['cnpj'] = preg_replace('/[.\-\/\s]/', '', $data['cnpj']);
        }
            // Limpar formatação do CEP: armazenar somente dígitos
            if (isset($data['cep'])) {
                $data['cep'] = preg_replace('/[^0-9]/', '', $data['cep']);
                if ($data['cep'] === '') {
                    $data['cep'] = null;
                }
            }
        // Normalizar RG (remover espaços excessivos)
        if (isset($data['rg_cli'])) {
            $data['rg_cli'] = trim($data['rg_cli']);
            if ($data['rg_cli'] === '') {
                $data['rg_cli'] = null;
            }
        }
        // Normalizar RG (remover espaços excessivos)
        if (isset($data['rg_cli'])) {
            $data['rg_cli'] = trim($data['rg_cli']);
            if ($data['rg_cli'] === '') {
                $data['rg_cli'] = null;
            }
        }
        
        // Validar telefone
        if (strlen($data['tel1_cli']) < 10 || strlen($data['tel1_cli']) > 11) {
            Session::definir('cadastro_erro', 'Telefone deve ter 10 ou 11 dígitos.');
            header('Location: ' . BASE_URL . 'funcionario/clientes/register');
            exit();
        }

        try {
            $validator = new \App\Core\DuplicateValidator();
            
            // Verificar email global
            $emailDuplicatas = $validator->verificarEmailGlobal($data['email_cli']);
            if (!empty($emailDuplicatas)) {
                $tipo = $emailDuplicatas[0]['tipo'];
                Session::definir('cadastro_erro', "E-mail já cadastrado como {$tipo}.");
                header('Location: ' . BASE_URL . 'funcionario/clientes/register');
                exit();
            }
            
            // Verificar CPF global
            if ($data['tipo_pessoa'] === 'fisica' && $data['cpf_cli']) {
                $cpfDuplicatas = $validator->verificarCpfGlobal($data['cpf_cli']);
                if (!empty($cpfDuplicatas)) {
                    $tipo = $cpfDuplicatas[0]['tipo'];
                    Session::definir('cadastro_erro', "CPF já cadastrado como {$tipo}.");
                    header('Location: ' . BASE_URL . 'funcionario/clientes/register');
                    exit();
                }
            }
            
            // Verificar CNPJ local
            if ($data['tipo_pessoa'] === 'juridica' && $data['cnpj'] && 
                $this->clienteModel->verificarCnpjDuplicado($data['cnpj'])) {
                Session::definir('cadastro_erro', 'CNPJ já cadastrado.');
                header('Location: ' . BASE_URL . 'funcionario/clientes/register');
                exit();
            }

            // Verificar RG local para pessoa física (se informado)
            if ($data['tipo_pessoa'] === 'fisica' && !empty($data['rg_cli'])) {
                if ($this->clienteModel->verificarRgDuplicado($data['rg_cli'])) {
                    Session::definir('cadastro_erro', 'RG já cadastrado.');
                    header('Location: ' . BASE_URL . 'funcionario/clientes/register');
                    exit();
                }
            }

            $this->clienteModel->criar($data);

            Session::definir('cadastro_sucesso', 'Cliente criado com sucesso!');
            header('Location: ' . BASE_URL . 'funcionario/clientes/register');
            exit();
        } catch (\PDOException $e) {
            error_log('Erro ao criar cliente: ' . $e->getMessage());
            Session::definir('cadastro_erro', 'Erro ao cadastrar cliente.');
            header('Location: ' . BASE_URL . 'funcionario/clientes/register');
            exit();
        }
    }

    // Formulário de edição
    public function showEditForm(int $id)
    {
        $cliente = $this->clienteModel->buscarPorId($id);
        if (!$cliente) {
            header("HTTP/1.0 404 Not Found");
            $this->visualizacao('errors/404');
            exit();
        }

        $data = array_merge($this->getBaseData(), [
            'cliente'        => $cliente,
            'edicao_erro'    => Session::obter('edicao_erro'),
            'edicao_sucesso' => Session::obter('edicao_sucesso')
        ]);

        Session::remover('edicao_erro');
        Session::remover('edicao_sucesso');

        $this->visualizacao('funcionario/clientes/edit', $data);
    }

    // Processa atualização
    public function atualizar(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'dashboard');
            exit();
        }

        $data = [
            'nome_cli'              => (isset($_POST['tipo_pessoa']) && $_POST['tipo_pessoa'] === 'fisica') ? strip_tags($_POST['nome_cli'] ?? '') : null,
            'nome_social'           => isset($_POST['nome_social']) ? trim($_POST['nome_social']) : null,
            'cnpj'                  => $_POST['cnpj'] ?? null,
            'cpf_cli'               => $_POST['cpf_cli'] ?? null,
            'rg_cli'                => $_POST['rg_cli'] ?? null,
            'rg_emissor_cli'        => isset($_POST['rg_emissor_cli']) ? trim($_POST['rg_emissor_cli']) : null,
            'data_expedicao_rg_cli' => $_POST['data_expedicao_rg_cli'] ?? null,
            'data_nascimento_cli'   => $_POST['data_nascimento_cli'] ?? null,
            'tipo_pessoa'           => $_POST['tipo_pessoa'] ?? '',
            'tel1_cli'              => $_POST['tel1_cli'] ?? '',
            'tel2_cli'              => $_POST['tel2_cli'] ?? null,
            'razao_social'          => (isset($_POST['tipo_pessoa']) && $_POST['tipo_pessoa'] === 'juridica') ? trim($_POST['razao_social'] ?? '') : null,
            'email_cli'             => filter_var($_POST['email_cli'] ?? '', FILTER_VALIDATE_EMAIL),
            'tipo_moradia'          => isset($_POST['tipo_moradia']) ? trim($_POST['tipo_moradia']) : null,
            'logradouro'            => isset($_POST['logradouro']) ? trim($_POST['logradouro']) : null,
            'cidade'                => isset($_POST['cidade']) ? trim($_POST['cidade']) : null,
            'bairro'                => isset($_POST['bairro']) ? trim($_POST['bairro']) : null,
            'uf'                    => $_POST['uf'] ?? '',
            'cep'                   => $_POST['cep'] ?? null,
            'ponto_referencia'      => isset($_POST['ponto_referencia']) ? trim($_POST['ponto_referencia']) : null,
            'complemento'           => $_POST['complemento'] ?? null,
            'num_end'               => $_POST['num_end'] ?? null,
            'status'                => $_POST['status'] ?? 'ativo'
        ];

        // Limpeza de dados com base no tipo de pessoa
        if ($data['tipo_pessoa'] === 'fisica') {
            $data['razao_social'] = null;
            $data['cnpj'] = null;
        } elseif ($data['tipo_pessoa'] === 'juridica') {
            $data['nome_cli'] = null;
            $data['cpf_cli'] = null;
            $data['nome_social'] = null;
            $data['rg_cli'] = null;
            $data['rg_emissor_cli'] = null;
            $data['data_expedicao_rg_cli'] = null;
            $data['data_nascimento_cli'] = null;
        }

        // O campo 'endereco' é obrigatório no BD
        $data['endereco'] = $data['logradouro'];

        // Validações básicas condicionais por tipo de pessoa
        $data['tipo_pessoa'] = isset($data['tipo_pessoa']) ? strtolower($data['tipo_pessoa']) : '';

        $commonRequired = ['email_cli', 'tipo_pessoa', 'tel1_cli', 'endereco', 'num_end', 'bairro', 'cidade', 'uf'];
        $missing = [];
        foreach ($commonRequired as $field) {
            if (empty($data[$field])) {
                $missing[] = $field;
            }
        }

        if ($data['tipo_pessoa'] === 'fisica') {
            if (empty($data['nome_cli'])) $missing[] = 'nome_cli';
            if (empty($data['cpf_cli'])) $missing[] = 'cpf_cli';
        } elseif ($data['tipo_pessoa'] === 'juridica') {
            if (empty($data['razao_social'])) $missing[] = 'razao_social';
            if (empty($data['cnpj'])) $missing[] = 'cnpj';
        }

        if (!empty($missing)) {
            Session::definir('edicao_erro', 'Todos os campos obrigatórios devem ser preenchidos.');
            header('Location: ' . BASE_URL . 'funcionario/clientes/edit/' . $id);
            exit();
        }

        // Limpar formatação dos telefones
        $data['tel1_cli'] = preg_replace('/[^0-9]/', '', $data['tel1_cli']);
        if ($data['tel2_cli']) {
            $data['tel2_cli'] = preg_replace('/[^0-9]/', '', $data['tel2_cli']);
        }
        
        // Limpar formatação de CPF/CNPJ
        if ($data['cpf_cli']) {
            $data['cpf_cli'] = preg_replace('/[^0-9]/', '', $data['cpf_cli']);
        }
        // Remover apenas pontuação/espacos do CNPJ para preservar letras
        if ($data['cnpj']) {
            $data['cnpj'] = preg_replace('/[.\-\/\s]/', '', $data['cnpj']);
        }
            // Limpar formatação do CEP: armazenar somente dígitos
            if (isset($data['cep'])) {
                $data['cep'] = preg_replace('/[^0-9]/', '', $data['cep']);
                if ($data['cep'] === '') {
                    $data['cep'] = null;
                }
            }
        
        // Validar telefone
        if (strlen($data['tel1_cli']) < 10 || strlen($data['tel1_cli']) > 11) {
            Session::definir('edicao_erro', 'Telefone deve ter 10 ou 11 dígitos.');
            header('Location: ' . BASE_URL . 'funcionario/clientes/edit/' . $id);
            exit();
        }
        
        // Atualizar senha se fornecida
        if (!empty($_POST['senha'])) {
            if (strlen($_POST['senha']) < 6 || $_POST['senha'] !== $_POST['confirmar_senha']) {
                Session::definir('edicao_erro', 'Senha inválida ou não coincide.');
                header('Location: ' . BASE_URL . 'funcionario/clientes/edit/' . $id);
                exit();
            }
            $data['senha_hash_cli'] = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        }

        try {
            $validator = new \App\Core\DuplicateValidator();
            
            // Verificar email global
            $emailDuplicatas = $validator->verificarEmailGlobal($data['email_cli'], 'cliente', $id);
            if (!empty($emailDuplicatas)) {
                $tipo = $emailDuplicatas[0]['tipo'];
                Session::definir('edicao_erro', "E-mail já cadastrado como {$tipo}.");
                header('Location: ' . BASE_URL . 'funcionario/clientes/edit/' . $id);
                exit();
            }
            
            // Verificar CPF global
            if ($data['tipo_pessoa'] === 'fisica' && $data['cpf_cli']) {
                $cpfDuplicatas = $validator->verificarCpfGlobal($data['cpf_cli'], 'cliente', $id);
                if (!empty($cpfDuplicatas)) {
                    $tipo = $cpfDuplicatas[0]['tipo'];
                    Session::definir('edicao_erro', "CPF já cadastrado como {$tipo}.");
                    header('Location: ' . BASE_URL . 'funcionario/clientes/edit/' . $id);
                    exit();
                }
            }
            
            // Verificar CNPJ local
            if ($data['tipo_pessoa'] === 'juridica' && $data['cnpj'] && 
                $this->clienteModel->verificarCnpjDuplicado($data['cnpj'], $id)) {
                Session::definir('edicao_erro', 'CNPJ já cadastrado para outro cliente.');
                header('Location: ' . BASE_URL . 'funcionario/clientes/edit/' . $id);
                exit();
            }

            // Verificar RG local para pessoa física (se informado) — exclui o próprio registro
            if ($data['tipo_pessoa'] === 'fisica' && !empty($data['rg_cli'])) {
                if ($this->clienteModel->verificarRgDuplicado($data['rg_cli'], $id)) {
                    Session::definir('edicao_erro', 'RG já cadastrado para outro cliente.');
                    header('Location: ' . BASE_URL . 'funcionario/clientes/edit/' . $id);
                    exit();
                }
            }

            $this->clienteModel->atualizarCliente($id, $data);

            Session::definir('edicao_sucesso', 'Cliente atualizado com sucesso!');
            header('Location: ' . BASE_URL . 'funcionario/clientes/edit/' . $id);
            exit();
        } catch (\PDOException $e) {
            error_log('Erro ao atualizar cliente: ' . $e->getMessage());
            Session::definir('edicao_erro', 'Erro ao atualizar cliente.');
            header('Location: ' . BASE_URL . 'funcionario/clientes/edit/' . $id);
            exit();
        }
    }

    // Alteração de status
    public function alterarStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'funcionario/clientes');
            exit();
        }

        $id = $_POST['id'] ?? 0;
        $novoStatus = $_POST['status'] ?? '';

        if (!$id || !in_array($novoStatus, ['ativo', 'inativo'])) {
            Session::definir('erro', 'Status inválido.');
            header('Location: ' . BASE_URL . 'funcionario/clientes');
            exit();
        }

        $success = $this->clienteModel->alterarStatus((int)$id, $novoStatus);

        Session::definir($success ? 'sucesso' : 'erro', $success ? 'Status alterado com sucesso!' : 'Erro ao alterar status.');
        header('Location: ' . BASE_URL . 'funcionario/clientes');
        exit();
    }

    // Busca de clientes
    public function pesquisar()
    {
        $searchTerm = $_GET['nome'] ?? '';
        $clientes = [];

        if ($searchTerm) {
            // Verifica se é um documento (contém números)
            $documento = preg_replace('/[^0-9]/', '', $searchTerm);
            if (strlen($documento) >= 9 && preg_match('/[0-9]/', $searchTerm)) {
                // Busca por CPF/CNPJ
                $cliente = $this->clienteModel->buscarPorCpfCnpj($searchTerm);
                $clientes = $cliente ? [$cliente] : [];
            } else {
                // Busca por nome
                $clientes = $this->clienteModel->buscarPorNomeAtivo($searchTerm);
            }
        }

        $data = array_merge($this->getBaseData(), [
            'clientes'   => $clientes,
            'searchTerm' => $searchTerm
        ]);

        $this->visualizacao('funcionario/clientes/cliente_search', $data);
    }

    // Busca cliente por CPF/CNPJ (AJAX) - apenas clientes ativos
    public function buscarPorCpfCnpj()
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');
        header('Access-Control-Allow-Headers: Content-Type');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['erro' => 'Método não permitido']);
            exit();
        }

        $documento = $_GET['documento'] ?? '';
        if (empty($documento)) {
            echo json_encode(['cliente' => null]);
            exit();
        }

        try {
            $cliente = $this->clienteModel->buscarPorCpfCnpj($documento);
            if ($cliente) {
                $nomeExibicao = $cliente->tipo_pessoa === 'fisica' 
                    ? $cliente->nome_cli 
                    : $cliente->razao_social;
                echo json_encode([
                    'cliente' => [
                        'id' => $cliente->id_cli,
                        'nome' => $nomeExibicao,
                        'documento' => $cliente->tipo_pessoa === 'fisica' ? $cliente->cpf_cli : $cliente->cnpj
                    ]
                ]);
            } else {
                echo json_encode(['cliente' => null]);
            }
        } catch (\Exception $e) {
            error_log('Erro na busca por CPF/CNPJ: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['erro' => 'Erro interno do servidor']);
        }
        exit();
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
        if ($this->clienteModel->verificarEmailDuplicado($email, $excludeId ?: null)) {
            echo json_encode([
                'duplicado' => true,
                'tipo' => 'Cliente',
                'nome' => ''
            ]);
            exit();
        }
        
        // Verificar em outras tabelas
        $validator = new \App\Core\DuplicateValidator();
        $duplicatas = $validator->verificarEmailGlobal($email, 'cliente', $excludeId ?: null);
        
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
        if ($this->clienteModel->verificarCpfDuplicado($cpf, $excludeId ?: null)) {
            echo json_encode([
                'duplicado' => true,
                'tipo' => 'Cliente',
                'nome' => ''
            ]);
            exit();
        }
        
        // Verificar em outras tabelas
        $validator = new \App\Core\DuplicateValidator();
        $duplicatas = $validator->verificarCpfGlobal($cpf, 'cliente', $excludeId ?: null);
        
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

    // Verificar CNPJ duplicado (AJAX)
    public function verificarCnpj()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['erro' => 'Método não permitido']);
            exit();
        }
        
        $cnpj = $_POST['cnpj'] ?? '';
        $excludeId = (int)($_POST['exclude_id'] ?? 0);
        
        if (empty($cnpj)) {
            echo json_encode(['duplicado' => false]);
            exit();
        }
        
    // Limpar formatação: remover apenas pontuação/espacos para preservar
    // identificadores alfanuméricos.
    $cnpj = preg_replace('/[.\-\/\s]/', '', $cnpj);
        
        $duplicado = $this->clienteModel->verificarCnpjDuplicado($cnpj, $excludeId ?: null);
        
        if ($duplicado) {
            echo json_encode([
                'duplicado' => true,
                'tipo' => 'Cliente',
                'nome' => ''
            ]);
        } else {
            echo json_encode(['duplicado' => false]);
        }
        exit();
    }
}
