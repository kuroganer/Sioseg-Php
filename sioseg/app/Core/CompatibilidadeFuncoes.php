<?php

namespace App\Core;

/**
 * Classe de compatibilidade para manter as funções antigas funcionando
 * durante a migração gradual para português
 */
class CompatibilidadeFuncoes
{
    /**
     * Adiciona métodos de compatibilidade para a classe Controller
     */
    public static function adicionarCompatibilidadeController()
    {
        // Verifica se a classe Controller já foi carregada
        if (class_exists('App\Core\Controller')) {
            // Adiciona aliases para as funções antigas
            if (!method_exists('App\Core\Controller', 'model')) {
                eval('
                    namespace App\Core;
                    abstract class Controller {
                        public function model(string $model) {
                            return $this->modelo($model);
                        }
                        
                        protected function view(string $view, array $data = [], bool $isStandalone = false) {
                            return $this->visualizacao($view, $data, $isStandalone);
                        }
                        
                        protected function redirect($url, $params = []) {
                            return $this->redirecionar($url, $params);
                        }
                    }
                ');
            }
        }
    }

    /**
     * Adiciona métodos de compatibilidade para a classe Session
     */
    public static function adicionarCompatibilidadeSession()
    {
        if (class_exists('App\Core\Session')) {
            // Cria aliases para as funções antigas da Session
            class_alias('App\Core\Session', 'App\Core\SessionCompativel');
        }
    }

    /**
     * Adiciona métodos de compatibilidade para os Models
     */
    public static function adicionarCompatibilidadeModels()
    {
        // Esta função pode ser expandida conforme necessário
        // para adicionar compatibilidade com modelos específicos
    }
}

// Função global para manter compatibilidade com funções antigas da Session
if (!function_exists('session_init_compat')) {
    function session_init_compat() {
        return \App\Core\Session::inicializar();
    }
}

if (!function_exists('session_set_compat')) {
    function session_set_compat($chave, $valor) {
        return \App\Core\Session::definir($chave, $valor);
    }
}

if (!function_exists('session_get_compat')) {
    function session_get_compat($chave, $padrao = null) {
        return \App\Core\Session::obter($chave, $padrao);
    }
}

if (!function_exists('session_has_compat')) {
    function session_has_compat($chave) {
        return \App\Core\Session::tem($chave);
    }
}

if (!function_exists('session_remove_compat')) {
    function session_remove_compat($chave) {
        return \App\Core\Session::remover($chave);
    }
}

if (!function_exists('session_is_logged_in_compat')) {
    function session_is_logged_in_compat() {
        return \App\Core\Session::estaLogado();
    }
}

if (!function_exists('session_get_user_profile_compat')) {
    function session_get_user_profile_compat() {
        return \App\Core\Session::obterPerfilUsuario();
    }
}

if (!function_exists('session_require_login_compat')) {
    function session_require_login_compat() {
        return \App\Core\Session::exigirLogin();
    }
}

if (!function_exists('session_require_permission_compat')) {
    function session_require_permission_compat($perfis = []) {
        return \App\Core\Session::exigirPermissao($perfis);
    }
}

if (!function_exists('session_destroy_compat')) {
    function session_destroy_compat() {
        return \App\Core\Session::destruir();
    }
}

if (!function_exists('session_set_flash_compat')) {
    function session_set_flash_compat($tipo, $mensagem) {
        return \App\Core\Session::definirFlash($tipo, $mensagem);
    }
}

if (!function_exists('session_get_flash_compat')) {
    function session_get_flash_compat($tipo, $padrao = null) {
        return \App\Core\Session::obterFlash($tipo, $padrao);
    }
}

if (!function_exists('session_has_flash_compat')) {
    function session_has_flash_compat($tipo) {
        return \App\Core\Session::temFlash($tipo);
    }
}