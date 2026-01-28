<?php
namespace App\Controllers;

use App\Core\Session;
use App\Core\Controller;

class DashboardController extends Controller
{
    /**
     * Retorna a URL completa com BASE_URL garantindo barra correta.
     */
    private function baseUrl(string $path = ''): string
    {
        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        return $base . '/' . ltrim($path, '/');
    }

    /**
     * Retorna os itens de menu permitidos para um perfil.
     */
   public static function getMenuForProfile(string $profile): array
{
    $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : ''; // BASE_URL já contém o subdiretório

   $allMenuItems = [
    'admin' => [       
        ['text' => 'Usuários', 'route' => 'javascript:void(0);', 'sub_items' => [
            ['text' => 'Cadastrar Usuário', 'route' => $baseUrl . '/admin/users/register'],
            ['text' => 'Pesquisar Usuário', 'route' => $baseUrl . '/admin/users/search'],
            ['text' => 'Gerenciar Usuário', 'route' => $baseUrl . '/admin/users']         
        ]],
        ['text' => 'Técnicos', 'route' => 'javascript:void(0);', 'sub_items' => [
             ['text' => 'Cadastrar Técnico', 'route' => $baseUrl . '/admin/tecnicos/register'],
            ['text' => 'Pesquisar Técnico', 'route' => $baseUrl . '/admin/tecnicos/search'],
            ['text' => 'Gerenciar Técnicos', 'route' => $baseUrl . '/admin/tecnicos'],          
        ]],
        ['text' => 'Produtos', 'route' => 'javascript:void(0);', 'sub_items' => [
            ['text' => 'Cadastrar Produto', 'route' => $baseUrl . '/admin/produtos/register'],
            ['text' => 'Pesquisar Produto', 'route' => $baseUrl . '/admin/produtos/search'],
            ['text' => 'Gerenciar Produtos', 'route' => $baseUrl . '/admin/produtos']
            
        ]],
        ['text' => 'Clientes', 'route' => 'javascript:void(0);', 'sub_items' => [
            ['text' => 'Cadastrar Cliente', 'route' => $baseUrl . '/admin/clientes/register'],
            ['text' => 'Pesquisar Cliente', 'route' => $baseUrl . '/admin/clientes/search'],
            ['text' => 'Gerenciar Clientes', 'route' => $baseUrl . '/admin/clientes']
           
        ]],
        ['text' => 'Ordens de Serviço', 'route' => 'javascript:void(0);', 'sub_items' => [
             ['text' => 'Cadastrar OS', 'route' => $baseUrl . '/admin/os/register'],
            ['text' => 'Pesquisar OS', 'route' => BASE_URL . 'admin/os/search'],
            ['text' => 'Listar OS', 'route' => $baseUrl . '/admin/os'],
            ['text' => 'Calendário', 'route' => $baseUrl . '/admin/os/calendario']
        ]],        
        ['text' => 'Relatórios', 'route' => 'javascript:void(0);', 'sub_items' => [
            ['text' => 'Resumo Geral', 'route' => $baseUrl . '/admin/relatorios'],
            ['text' => 'Performance Técnicos', 'route' => $baseUrl . '/admin/relatorios/performance-tecnicos'],
            ['text' => 'Buscar OS', 'route' => $baseUrl . '/admin/relatorios/buscar-os'],
            ['text' => 'Relatório de Produtos', 'route' => $baseUrl . '/admin/relatorios/produtos'],
        ]],
    ],


    'funcionario'=>[        
        ['text' => 'Clientes', 'route' => 'javascript:void(0);', 'sub_items' => [
              ['text' => 'Cadastrar Cliente', 'route' => $baseUrl . '/funcionario/clientes/register'],
        ['text' => 'Pesquisar Cliente', 'route' => $baseUrl . '/funcionario/clientes/search'],
        ['text' => 'Gerenciar Clientes', 'route' => $baseUrl . '/funcionario/clientes'],
      
    ]],
    ['text' => 'Ordens de Serviço', 'route' => 'javascript:void(0);', 'sub_items' => [
         ['text' => 'Cadastrar OS', 'route' => $baseUrl . '/funcionario/os/register'],
        ['text' => 'Pesquisar OS', 'route' => $baseUrl . '/funcionario/os/search'],
        ['text' => 'Listar OS', 'route' => $baseUrl . '/funcionario/os'],       
        ['text' => 'Calendário', 'route' => $baseUrl . '/funcionario/os/calendario'],
    ]]
    ],
    'tecnico' => [],
    'cliente' => [],
];

return $allMenuItems[$profile] ?? [];

}

    /**
     * Página principal do dashboard.
     */
    public function index()
    {
        error_log("[" . date('d-M-Y H:i:s e') . "] DEBUG: DashboardController@index chamado.");

        if (!Session::estaLogado()) {
            error_log("[" . date('d-M-Y H:i:s e') . "] DEBUG: Usuário não logado, redirecionando para /login.");
            Session::definirFlash('login_erro', 'Você precisa estar logado para acessar o dashboard.');
            header('Location: ' . $this->baseUrl('login'));
            exit();
        }

        // Usando a chave 'perfil' que agora é definida corretamente pelo AuthController.
        $userProfile = Session::obter('perfil');

        // Redireciona o usuário para a página inicial correta com base no seu perfil.
        switch ($userProfile) {
            case 'cliente':
                header('Location: ' . $this->baseUrl('cliente/portal'));
                exit();

            case 'tecnico':
                header('Location: ' . $this->baseUrl('tecnico/os'));
                exit();

            case 'admin':
            case 'funcionario':
                // Apenas admin e funcionário continuam para o dashboard principal.
                break;

            default:
                // Se o perfil for desconhecido, desloga por segurança.
                header('Location: ' . $this->baseUrl('logout'));
                exit();
        }

        // Se o perfil for 'admin' ou 'funcionario', carrega o dashboard completo.
        $data = $this->loadDashboardData($userProfile);

        // Carrega a view correta baseada no perfil
        if ($userProfile === 'admin') {
            $this->visualizacao('admin/dashboard/index', $data);
        } elseif ($userProfile === 'funcionario') {
            $this->visualizacao('funcionario/dashboard/index', $data);
        }
    }

    /**
     * Busca os top técnicos baseado nas avaliações mais recentes
     */
    private function getTopTecnicosRecenteAvaliacao(int $limit = 3): array 
    {
        try {
            // Primeiro verifica se existem avaliações
            $db = \App\Config\Database::getInstance()->getConnection();
            $checkSql = "SELECT COUNT(*) FROM avaliacao_tecnica";
            $checkStmt = $db->query($checkSql);
            $totalAvaliacoes = $checkStmt->fetchColumn();
            
            if ($totalAvaliacoes == 0) {
                return [];
            }
            
            $sql = "SELECT 
                        t.nome_tec as nome,
                        AVG(av.nota) as media_avaliacoes,
                        COUNT(av.id_ava) as total_avaliacoes,
                        MAX(os.data_encerramento) as ultima_avaliacao
                    FROM tecnico t
                    INNER JOIN ordem_servico os ON t.id_tec = os.id_tec_fk
                    INNER JOIN avaliacao_tecnica av ON os.id_os = av.id_os_fk
                    WHERE os.data_encerramento >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    AND t.status = 'ativo'
                    AND os.status IN ('concluida', 'encerrada')
                    GROUP BY t.id_tec, t.nome_tec
                    HAVING COUNT(av.id_ava) >= 1
                    ORDER BY media_avaliacoes DESC, ultima_avaliacao DESC
                    LIMIT :limit";
            
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("Erro ao buscar top técnicos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Carrega todos os dados necessários para o dashboard de Admin e Funcionário.
     */
    private function loadDashboardData(string $userProfile): array
    {
        // Instancia os models necessários
        $osModel = new \App\Models\OrdemServico();
        $avaliacaoModel = new \App\Models\AvaliacaoTecnica();

        // Busca as estatísticas para os cards e gráficos
        $stats = [
            'abertas_hoje' => $osModel->contarPorStatusEData('aberta', date('Y-m-d')),
            // Total de OS com status 'aberta' (total aberto) - usado no gráfico de pizza quando desejado
            'abertas_total' => $osModel->contarPorStatus('aberta'),
            'total_concluidas' => $osModel->contarPorStatus('concluida'), // Alterado para buscar o total
            'em_andamento' => $osModel->contarPorStatus('em andamento'),
            'avaliacoes_pendentes' => $osModel->contarAvaliacoesPendentes(),
            // Nova métrica: ordens onde uma das partes já concluiu e a outra ainda não confirmou
            'conclusoes_pendentes' => $osModel->contarConclusoesPendentes(),
        ];

        // Busca os dados para o gráfico de avaliações dos últimos 7 dias
        $avaliacoesData = $avaliacaoModel->obterMediaAvaliacoesUltimosDias(7);
        $chartLabels = array_keys($avaliacoesData);
        $chartData = array_values($avaliacoesData);

        // Monta o array de dados para a view
        return [
            // Nome do usuário logado
            'userName' => Session::obter('nome_usu') ?? ($userProfile === 'admin' ? 'Admin' : 'Funcionário'),

            // Estatísticas para os cards (agora com dados reais)
            'stats' => $stats,

            // Top Técnicos por avaliações recentes
            'topTecnicos' => $this->getTopTecnicosRecenteAvaliacao(3),

            // Listas para o Kanban (limitadas às últimas 3-5 OS)
            'os_a_fazer' => $osModel->buscarPorStatusComDetalhes('aberta', 3),
            'os_em_andamento' => $osModel->buscarPorStatusComDetalhes('em andamento', 3),
            'os_concluidas' => $osModel->buscarPorStatusComDetalhes('concluida', 3),

            // Dados para o Gráfico (agora com dados reais)
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
        ];
    }

    /**
     * Retorna as OS com conclusões pendentes via AJAX
     */
    public function osPendentes()
    {
        header('Content-Type: application/json');
        
        if (!Session::estaLogado()) {
            http_response_code(401);
            echo json_encode(['error' => 'Não autorizado']);
            return;
        }
        
        try {
            $osModel = new \App\Models\OrdemServico();
            $osPendentes = $osModel->buscarConclusoesPendentes();
            echo json_encode($osPendentes);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro interno do servidor']);
        }
    }
}
