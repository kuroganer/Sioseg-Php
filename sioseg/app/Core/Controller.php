<?php

namespace App\Core;

// Certifique-se de que a classe View esteja sendo usada, se não estiver no mesmo namespace.
use App\Core\View;

abstract class Controller
{
    // Propriedade para armazenar a instância da View
    protected View $viewInstance;

    public function __construct()
    {
        // Inicializa a instância da classe View no construtor do Controller
        $this->viewInstance = new View();
    }

    /**
     * Carrega um modelo (Model).
     * @param string $model Nome do modelo a ser carregado.
     * @return object Retorna uma instância do modelo.
     */
    public function modelo(string $modelo)
    {
        // Converte o nome do modelo para o formato de caminho de arquivo.
        // Ex: "User" se torna "User.php"
        $caminhoModelo = APP_ROOT . '/app/Models/' . $modelo . '.php'; // Use APP_ROOT para consistência

        // Verifica se o arquivo do modelo existe.
        if (file_exists($caminhoModelo)) {
            // Inclui o arquivo do modelo.
            require_once $caminhoModelo;
            // Cria uma instância do modelo e a retorna.
            // Ex: "User" se torna new \App\Models\User();
            $classe = '\\App\\Models\\' . basename($caminhoModelo, '.php'); // Obtém o nome da classe sem a extensão
            return new $classe();
        } else {
            // Se o modelo não for encontrado, exibe uma mensagem de erro e encerra a execução.
            error_log("Modelo não encontrado: " . $caminhoModelo);
            die('Modelo não encontrado: ' . $caminhoModelo);
        }
    }

    /**
     * Carrega uma visualização (View) usando a classe App\Core\View.
     *
     * Este método agora delega a renderização da view para a classe App\Core\View,
     * e o mais importante, repassa o argumento $ehIndependente.
     *
     * @param string $visualizacao Caminho da view a ser carregada (ex: 'admin/dashboard').
     * @param array $dados Dados a serem passados para a view.
     * @param bool $ehIndependente Se true, renderiza apenas a view sem o cabeçalho/rodapé. Padrão é false.
     * @return void
     */
    protected function visualizacao(string $visualizacao, array $dados = [], bool $ehIndependente = false)
    {
        // Converte o caminho da view para o formato de arquivo.
        // Ex: "admin/dashboard" se torna "APP_ROOT/app/Views/admin/dashboard.php"
        $caminhoVisualizacao = APP_ROOT . '/app/Views/' . $visualizacao . '.php'; // Use APP_ROOT para consistência

        // Verifica se o arquivo da view existe.
        if (!file_exists($caminhoVisualizacao)) { // Use !file_exists para verificar se NÃO existe
            // Se a view não for encontrada, exibe uma mensagem de erro.
            error_log('Visualização não encontrada: ' . $caminhoVisualizacao);
            die('Visualização não encontrada: ' . $caminhoVisualizacao);
        }

        // Chama o método renderizar() da instância de View, passando todos os argumentos,
        // incluindo o novo $ehIndependente.
        $this->viewInstance->renderizar($caminhoVisualizacao, $dados, $ehIndependente);
    }
    /**
     * Redireciona para uma URL com parâmetros de query string.
     * @param string $url
     * @param array $parametros
     */
    protected function redirecionar($url, $parametros = [])
    {
        if (!empty($parametros)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($parametros);
        }
        header('Location: ' . $url);
        exit;
    }
}