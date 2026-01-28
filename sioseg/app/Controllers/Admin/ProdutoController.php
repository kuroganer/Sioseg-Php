<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Produto;

class ProdutoController extends Controller
{
    private Produto $produtoModel;

    public function __construct()
    {
        parent::__construct();
        $this->produtoModel = new Produto();
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
                'text' => 'Produtos',
                'route' => '#',
                'sub_items' => [
                    ['text' => 'Gerenciar Produto', 'route' => BASE_URL . 'admin/produtos'],
                    ['text' => 'Cadastrar Produto', 'route' => BASE_URL . 'admin/produtos/register'],
                    ['text' => 'Pesquisar Produto', 'route' => BASE_URL . 'admin/produtos/search'],
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

    // Listagem de produtos
    public function index()
    {
        $currentPage = (int)($_GET['page'] ?? 1);
        $recordsPerPage = 50;
        
        $totalRecords = $this->produtoModel->contarTodos();
        $pagination = new \App\Core\Pagination($currentPage, $totalRecords, $recordsPerPage, BASE_URL . 'admin/produtos');
        
        $produtos = $this->produtoModel->obterTodosComPaginacao($pagination->getOffset(), $pagination->getLimit());
        
        $data = array_merge($this->getBaseData(), [
            'produtos' => $produtos,
            'pagination' => $pagination,
            'sucesso'  => Session::obter('sucesso'),
            'erro'     => Session::obter('erro')
        ]);

        Session::remover('sucesso');
        Session::remover('erro');

        $this->visualizacao('admin/produtos/index', $data);
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

        $this->visualizacao('admin/produtos/produto_register', $data);
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
            'marca'     => isset($_POST['marca']) ? trim(strip_tags($_POST['marca'])) : null,
            'modelo'    => isset($_POST['modelo']) ? trim(strip_tags($_POST['modelo'])) : null,
            'descricao' => isset($_POST['descricao']) ? trim(strip_tags($_POST['descricao'])) : null,
            'qtde'      => (int) ($_POST['qtde'] ?? 0),
            'nome'      => trim(strip_tags($_POST['nome'] ?? '')),
            'status'    => $_POST['status'] ?? 'ativo',
        ];

        // Validações mais robustas
        $erros = [];
        
        if (empty($data['nome'])) {
            $erros[] = 'Nome do produto é obrigatório';
        }
        
        if ($data['qtde'] < 0) {
            $erros[] = 'Quantidade não pode ser negativa';
        }
        
        if ($data['qtde'] === 0) {
            $erros[] = 'Quantidade deve ser maior que zero';
        }
        
        if (!empty($erros)) {
            Session::definir('cadastro_erro', implode('. ', $erros) . '.');
            header('Location: ' . BASE_URL . 'admin/produtos/register');
            exit();
        }

        try {
            $this->produtoModel->criar($data);

            Session::definir('cadastro_sucesso', 'Produto criado com sucesso!');
            header('Location: ' . BASE_URL . 'admin/produtos/register');
            exit();
        } catch (\PDOException $e) {
            error_log('Erro ao criar produto: ' . $e->getMessage());
            Session::definir('cadastro_erro', 'Erro ao cadastrar produto.');
            header('Location: ' . BASE_URL . 'admin/produtos/register');
            exit();
        }
    }

    // Formulário de edição
    public function showEditForm(int $id)
    {
        $produto = $this->produtoModel->buscarPorId($id);
        if (!$produto) {
            header("HTTP/1.0 404 Not Found");
            $this->visualizacao('errors/404');
            exit();
        }

        $data = array_merge($this->getBaseData(), [
            'produto'        => $produto,
            'edicao_erro'    => Session::obter('edicao_erro'),
            'edicao_sucesso' => Session::obter('edicao_sucesso')
        ]);

        Session::remover('edicao_erro');
        Session::remover('edicao_sucesso');

        $this->visualizacao('admin/produtos/edit', $data);
    }

    // Processa atualização
    public function atualizar(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'dashboard');
            exit();
        }

        $data = [
            'marca'     => isset($_POST['marca']) ? trim(strip_tags($_POST['marca'])) : null,
            'modelo'    => isset($_POST['modelo']) ? trim(strip_tags($_POST['modelo'])) : null,
            'descricao' => isset($_POST['descricao']) ? trim(strip_tags($_POST['descricao'])) : null,
            'qtde'      => (int) ($_POST['qtde'] ?? 0),
            'nome'      => trim(strip_tags($_POST['nome'] ?? '')),
            'status'    => $_POST['status'] ?? null,
        ];

        // Validações mais robustas para edição
        $erros = [];
        
        if (empty($data['nome'])) {
            $erros[] = 'Nome do produto é obrigatório';
        }
        
        if ($data['qtde'] < 0) {
            $erros[] = 'Quantidade não pode ser negativa';
        }
        
        if (!empty($erros)) {
            Session::definir('edicao_erro', implode('. ', $erros) . '.');
            header('Location: ' . BASE_URL . 'admin/produtos/edit/' . $id);
            exit();
        }

        try {
            $this->produtoModel->atualizarProduto($id, array_filter($data, fn($v) => $v !== null));

            Session::definir('edicao_sucesso', 'Produto atualizado com sucesso!');
            header('Location: ' . BASE_URL . 'admin/produtos/edit/' . $id);
            exit();
        } catch (\PDOException $e) {
            error_log('Erro ao atualizar produto: ' . $e->getMessage());
            Session::definir('edicao_erro', 'Erro ao atualizar produto.');
            header('Location: ' . BASE_URL . 'admin/produtos/edit/' . $id);
            exit();
        }
    }

    // Alteração de status
    public function alterarStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/produtos');
            exit();
        }

        $id = $_POST['id'] ?? 0;
        $novoStatus = $_POST['status'] ?? '';

        if (!$id || !in_array($novoStatus, ['ativo', 'inativo'])) {
            Session::definir('erro', 'Status inválido.');
            header('Location: ' . BASE_URL . 'admin/produtos');
            exit();
        }

        $success = $this->produtoModel->alterarStatus((int)$id, $novoStatus);

        Session::definir($success ? 'sucesso' : 'erro', $success ? 'Status alterado com sucesso!' : 'Erro ao alterar status.');
        header('Location: ' . BASE_URL . 'admin/produtos');
        exit();
    }

    // Busca de produtos
    public function pesquisar()
    {
        $searchTerm = $_GET['nome'] ?? '';

        if ($searchTerm) {
            // Busca produtos com todos os status (ativo e inativo)
            $produtos = $this->produtoModel->buscarPorNomeTodosStatus($searchTerm);
        } else {
            $produtos = [];
        }

        $data = array_merge($this->getBaseData(), [
            'produtos'   => $produtos,
            'searchTerm' => $searchTerm
        ]);

        $this->visualizacao('admin/produtos/produto_search', $data);
    }
}
