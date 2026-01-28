<?php
namespace App\Controllers;

use App\Core\Session;
use App\Core\Controller;
use App\Models\Usuario;

class AuthController extends Controller // Controlador responsável pela autenticação de usuários
{
    private Usuario $userModel; // Modelo para operações com usuários

    public function __construct() // Inicializa o controlador
    {
        parent::__construct();
        $this->userModel = new Usuario(); // Instancia modelo de usuário
    }

    private function baseUrl(string $path = ''): string // Gera URL base do sistema
    {
        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : ''; // Obtém URL base configurada
        return $base . '/' . ltrim($path, '/'); // Retorna URL completa
    }

    public function showLoginForm() // Exibe formulário de login
    {
        $error_message   = Session::obterFlash('login_erro');
        $success_message = Session::obterFlash('login_sucesso');

        $data = [
            'error_message'   => $error_message,
            'success_message' => $success_message
        ];

        $this->visualizacao('auth/login', $data, true); // Renderiza view de login
    }

    public function login() // Processa tentativa de login
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // Verifica se é requisição POST
            Session::definirFlash('login_erro', 'Método de requisição inválido.'); // Define erro
            header('Location: ' . $this->baseUrl('login')); // Redireciona para login
            exit();
        }

        $email_input  = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL); // Valida e limpa email
        $senha_input  = trim($_POST['senha'] ?? ''); // Limpa senha

        if (!$email_input || !$senha_input) {
            Session::definirFlash('login_erro', 'Por favor, preencha todos os campos.');
            header('Location: ' . $this->baseUrl('login'));
            exit();
        }

        try { // Inicia bloco de tratamento de erros
            $usuario = null;
            $tipo_usuario = null;
            $id_field = null;
            $hash_field = null;
            $email_field = null;
            $perfil_real = null;

            // Tenta encontrar o usuário em cada tabela automaticamente
            // 1. Verifica na tabela de usuários (admin/funcionario)
            $usuario = $this->userModel->buscarPorEmail($email_input);
            if ($usuario) {
                $id_field = 'id_usu';
                $hash_field = 'senha_hash_usu';
                $email_field = 'email_usu';
                $perfil_real = $usuario->perfil ?? null;
                $tipo_usuario = $perfil_real;
            }

            // 2. Se não encontrou, verifica na tabela de clientes
            if (!$usuario) {
                $clienteModel = new \App\Models\Cliente();
                $usuario = $clienteModel->buscarPorEmail($email_input);
                if ($usuario) {
                    $id_field = 'id_cli';
                    $hash_field = 'senha_hash_cli';
                    $email_field = 'email_cli';
                    $perfil_real = 'cliente';
                    $tipo_usuario = 'cliente';
                }
            }

            // 3. Se não encontrou, verifica na tabela de técnicos
            if (!$usuario) {
                $tecnicoModel = new \App\Models\Tecnico();
                $usuario = $tecnicoModel->buscarPorEmail($email_input);
                if ($usuario) {
                    $id_field = 'id_tec';
                    $hash_field = 'senha_hash_tec';
                    $email_field = 'email_tec';
                    $perfil_real = 'tecnico';
                    $tipo_usuario = 'tecnico';
                }
            }

            if (!$usuario) {
                Session::definirFlash('login_erro', 'Email não encontrado no sistema.');
                header('Location: ' . $this->baseUrl('login'));
                exit();
            }

            $hash_senha = $usuario->{$hash_field} ?? '';
            $status_usuario = $usuario->status ?? ''; // Obtém o status do usuário do banco de dados

            // Verifica se o usuário existe, tem perfil válido, senha correta E está ativo
            if ($usuario && $perfil_real && $hash_senha && password_verify($senha_input, $hash_senha)) { // Verifica credenciais
                // Bloqueia login se status não for 'ativo' (case-insensitive)
                if (strtolower($status_usuario) !== 'ativo') { // Verifica se conta está ativa
                    Session::definirFlash('login_erro', 'Sua conta está inativa. Entre em contato com o administrador.'); // Mensagem específica para conta inativa
                    header('Location: ' . $this->baseUrl('login')); // Redireciona para login
                    exit(); // Impede continuação do login
                }
                Session::definir($id_field, $usuario->{$id_field}); // Armazena ID na sessão
                Session::definir('email', $usuario->{$email_field}); // Armazena email na sessão
                Session::definir('tipo_usuario', $tipo_usuario); // Armazena tipo na sessão
                Session::definir('perfil', $perfil_real); // Armazena perfil na sessão
                
                // Armazena nome específico baseado no tipo de usuário
                if ($perfil_real === 'cliente') { // Se é cliente
                    Session::definir('nome_cli', $usuario->nome_cli); // Armazena nome do cliente
                } elseif ($perfil_real === 'tecnico') { // Se é técnico
                    Session::definir('nome_tec', $usuario->nome_tec); // Armazena nome do técnico
                } elseif (in_array($perfil_real, ['admin', 'funcionario'])) { // Se é admin ou funcionário
                    Session::definir('nome_usu', $usuario->nome_usu); // Armazena nome do usuário
                }

                session_regenerate_id(true); // Regenera ID da sessão por segurança

                header('Location: ' . $this->baseUrl('dashboard')); // Redireciona para dashboard
                exit();
            } else {
                Session::definirFlash('login_erro', 'Credenciais inválidas.');
            }
        } catch (\PDOException $e) { // Captura erros de banco de dados
            error_log("Erro de banco de dados no login: " . $e->getMessage()); // Registra erro no log
            Session::definirFlash('login_erro', 'Ocorreu um erro interno. Tente novamente mais tarde.'); // Mensagem genérica para usuário
        }

        header('Location: ' . $this->baseUrl('login')); // Redireciona para login em caso de erro
        exit();
    }

    public function logout() // Processa logout do usuário
    {
        Session::destruir(); // Destrói sessão atual
        Session::definirFlash('login_sucesso', 'Você foi desconectado.'); // Define mensagem de sucesso
        header('Location: ' . $this->baseUrl('login')); // Redireciona para login
        exit();
    }

    public function showForgotPasswordForm() // Exibe formulário de esqueceu senha
    {
        $error_message = Session::obterFlash('forgot_erro');
        $success_message = Session::obterFlash('forgot_sucesso');

        $data = [
            'error_message' => $error_message,
            'success_message' => $success_message
        ];

        $this->visualizacao('auth/forgot-password', $data, true);
    }

    public function processForgotPassword() // Processa verificação de dados
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::definirFlash('forgot_erro', 'Método de requisição inválido.');
            header('Location: ' . $this->baseUrl('forgot-password'));
            exit();
        }

        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $documento = preg_replace('/[^0-9]/', '', trim($_POST['documento'] ?? ''));

        if (!$email || !$documento) {
            Session::definirFlash('forgot_erro', 'Por favor, preencha todos os campos.');
            header('Location: ' . $this->baseUrl('forgot-password'));
            exit();
        }

        try {
            $usuario = $this->verificarUsuarioParaRecuperacao($email, $documento);
            
            if ($usuario) {
                // Armazena dados na sessão para o próximo passo
                Session::definir('reset_user_id', $usuario['id']);
                Session::definir('reset_user_type', $usuario['type']);
                Session::definir('reset_verified', true);
                
                header('Location: ' . $this->baseUrl('reset-password'));
                exit();
            } else {
                Session::definirFlash('forgot_erro', 'Email ou CPF/CNPJ não encontrado ou não correspondem.');
            }
        } catch (\Exception $e) {
            error_log("Erro na recuperação de senha: " . $e->getMessage());
            Session::definirFlash('forgot_erro', 'Ocorreu um erro interno. Tente novamente.');
        }

        header('Location: ' . $this->baseUrl('forgot-password'));
        exit();
    }

    public function showResetPasswordForm() // Exibe formulário de nova senha
    {
        if (!Session::obter('reset_verified')) {
            Session::definirFlash('forgot_erro', 'Acesso negado. Faça a verificação primeiro.');
            header('Location: ' . $this->baseUrl('forgot-password'));
            exit();
        }

        $error_message = Session::obterFlash('reset_erro');
        $success_message = Session::obterFlash('reset_sucesso');

        $data = [
            'error_message' => $error_message,
            'success_message' => $success_message
        ];

        $this->visualizacao('auth/reset-password', $data, true);
    }

    public function processResetPassword() // Processa alteração de senha
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::definirFlash('reset_erro', 'Método de requisição inválido.');
            header('Location: ' . $this->baseUrl('reset-password'));
            exit();
        }

        if (!Session::obter('reset_verified')) {
            Session::definirFlash('forgot_erro', 'Acesso negado. Faça a verificação primeiro.');
            header('Location: ' . $this->baseUrl('forgot-password'));
            exit();
        }

        $nova_senha = trim($_POST['nova_senha'] ?? '');
        $confirmar_senha = trim($_POST['confirmar_senha'] ?? '');

        if (!$nova_senha || !$confirmar_senha) {
            Session::definirFlash('reset_erro', 'Por favor, preencha todos os campos.');
            header('Location: ' . $this->baseUrl('reset-password'));
            exit();
        }

        if ($nova_senha !== $confirmar_senha) {
            Session::definirFlash('reset_erro', 'As senhas não coincidem.');
            header('Location: ' . $this->baseUrl('reset-password'));
            exit();
        }

        if (strlen($nova_senha) < 6) {
            Session::definirFlash('reset_erro', 'A senha deve ter pelo menos 6 caracteres.');
            header('Location: ' . $this->baseUrl('reset-password'));
            exit();
        }

        try {
            $userId = Session::obter('reset_user_id');
            $userType = Session::obter('reset_user_type');
            $senhaHash = password_hash($nova_senha, PASSWORD_DEFAULT);

            $sucesso = $this->atualizarSenhaUsuario($userId, $userType, $senhaHash);

            if ($sucesso) {
                // Limpa dados da sessão
                Session::remover('reset_user_id');
                Session::remover('reset_user_type');
                Session::remover('reset_verified');
                
                Session::definirFlash('login_sucesso', 'Senha alterada com sucesso! Faça login com sua nova senha.');
                header('Location: ' . $this->baseUrl('login'));
                exit();
            } else {
                Session::definirFlash('reset_erro', 'Erro ao alterar senha. Tente novamente.');
            }
        } catch (\Exception $e) {
            error_log("Erro ao alterar senha: " . $e->getMessage());
            Session::definirFlash('reset_erro', 'Ocorreu um erro interno. Tente novamente.');
        }

        header('Location: ' . $this->baseUrl('reset-password'));
        exit();
    }

    private function verificarUsuarioParaRecuperacao(string $email, string $documento): array|false
    {
        $documento = preg_replace('/[^0-9]/', '', $documento);
        
        // Verifica usuários (admin/funcionario)
        $usuario = $this->userModel->buscarPorEmail($email);
        if ($usuario && $this->verificarDocumento($usuario->cpf_usu ?? '', $documento)) {
            return ['id' => $usuario->id_usu, 'type' => 'usuario'];
        }

        // Verifica clientes
        $clienteModel = new \App\Models\Cliente();
        $cliente = $clienteModel->buscarPorEmail($email);
        if ($cliente) {
            $cpfCliente = $cliente->cpf_cli ?? '';
            $cnpjCliente = $cliente->cnpj ?? '';
            if ($this->verificarDocumento($cpfCliente, $documento) || $this->verificarDocumento($cnpjCliente, $documento)) {
                return ['id' => $cliente->id_cli, 'type' => 'cliente'];
            }
        }

        // Verifica técnicos
        $tecnicoModel = new \App\Models\Tecnico();
        $tecnico = $tecnicoModel->buscarPorEmail($email);
        if ($tecnico && $this->verificarDocumento($tecnico->cpf_tec ?? '', $documento)) {
            return ['id' => $tecnico->id_tec, 'type' => 'tecnico'];
        }

        return false;
    }

    private function verificarDocumento(?string $documentoBanco, string $documentoInput): bool
    {
        if (empty($documentoBanco)) return false;
        
        $docBanco = preg_replace('/[^0-9]/', '', $documentoBanco);
        $docInput = preg_replace('/[^0-9]/', '', $documentoInput);
        
        return !empty($docBanco) && !empty($docInput) && $docBanco === $docInput;
    }

    private function atualizarSenhaUsuario(int $id, string $tipo, string $senhaHash): bool
    {
        switch ($tipo) {
            case 'usuario':
                return $this->userModel->atualizarUsuario($id, ['senha_hash_usu' => $senhaHash]);
            case 'cliente':
                $clienteModel = new \App\Models\Cliente();
                return $clienteModel->atualizarCliente($id, ['senha_hash_cli' => $senhaHash]);
            case 'tecnico':
                $tecnicoModel = new \App\Models\Tecnico();
                return $tecnicoModel->atualizarTecnico($id, ['senha_hash_tec' => $senhaHash]);
            default:
                return false;
        }
    }

    public function verifyUserData() // Método AJAX para verificar dados
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método inválido']);
            exit();
        }

        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $documento = preg_replace('/[^0-9]/', '', trim($_POST['documento'] ?? ''));

        if (!$email || !$documento) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            exit();
        }

        try {
            $usuario = $this->verificarUsuarioParaRecuperacao($email, $documento);
            
            if ($usuario) {
                // Armazena dados na sessão para o próximo passo
                Session::definir('reset_user_id', $usuario['id']);
                Session::definir('reset_user_type', $usuario['type']);
                Session::definir('reset_verified', true);
                
                echo json_encode(['success' => true, 'message' => 'Dados verificados com sucesso']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Email ou CPF/CNPJ não encontrado ou não correspondem']);
            }
        } catch (\Exception $e) {
            error_log("Erro na verificação AJAX: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
        
        exit();
    }
}