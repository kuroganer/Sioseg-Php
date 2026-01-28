<?php
// ProjetoGES_MVC/app/Core/Session.php

namespace App\Core;

class Session
{
    /**
     * Inicializa a sessão com configurações de segurança.
     * Deve ser chamado uma única vez no início da aplicação (no Front Controller).
     */
    public static function inicializar()
    {
        // Só define parâmetros de cookie se a sessão ainda não estiver ativa
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'httponly' => true,
                'samesite' => 'Lax', // ou 'Strict'
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'
            ]);
            session_start();
            //session_regenerate_id(true); // Opcional: regenerar ID da sessão para segurança
        }
    }

    public static function definir($chave, $valor)
    {
        $_SESSION[$chave] = $valor;
    }

    public static function obter($chave, $padrao = null)
    {
        return $_SESSION[$chave] ?? $padrao;
    }

    public static function tem(string $chave): bool
    {
        return isset($_SESSION[$chave]);
    }

    public static function remover(string $chave)
    {
        if (isset($_SESSION[$chave])) {
            unset($_SESSION[$chave]);
        }
    }

    public static function estaLogado(): bool
    {
        // Uma verificação mais robusta e centralizada.
        // Considera logado se a chave 'perfil' estiver definida na sessão.
        return self::tem('perfil');
    }

    public static function obterPerfilUsuario(): ?string
    {
        // Retorna o perfil real do usuário, que é a fonte de verdade para permissões.
        return self::obter('perfil');
    }

    public static function exigirLogin()
    {
        if (!self::estaLogado()) {
            self::destruir();
            $baseUrl = (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http') 
                . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
            header('Location: ' . $baseUrl . 'login?erro=sessao_expirada');
            exit();
        }
    }

    public static function exigirPermissao(array $perfisPermitidos = [])
    {
        self::exigirLogin();
        $perfilUsuario = self::obterPerfilUsuario();
        if (!empty($perfisPermitidos) && !in_array($perfilUsuario, $perfisPermitidos)) {
            $baseUrl = (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http') 
                . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
            header('Location: ' . $baseUrl . 'dashboard?erro=acesso_negado');
            exit();
        }
    }

    public static function destruir()
    {
        session_unset();
        session_destroy();
    }

    // --- Mensagens Flash ---
    public static function definirFlash(string $tipo, string $mensagem)
    {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }
        $_SESSION['flash_messages'][$tipo] = $mensagem;
    }

    public static function obterFlash(string $tipo, $padrao = null): ?string
    {
        $mensagem = $_SESSION['flash_messages'][$tipo] ?? $padrao;
        if (isset($_SESSION['flash_messages'][$tipo])) {
            unset($_SESSION['flash_messages'][$tipo]);
        }
        return $mensagem;
    }

    public static function temFlash(string $tipo): bool
    {
        return isset($_SESSION['flash_messages'][$tipo]);
    }
}
