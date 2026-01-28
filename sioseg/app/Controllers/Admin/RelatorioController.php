<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Relatorio;
use App\Models\OrdemServico;
use App\Models\Cliente;
use App\Models\Tecnico;
use App\Controllers\DashboardController;
use App\Core\RelatorioFactory;

class RelatorioController extends Controller
{
    private $relatorioModel;
    private $osModel;
    private $clienteModel;
    private $tecnicoModel;

    public function __construct()
    {
        parent::__construct();
        $this->relatorioModel = new Relatorio();
        $this->osModel = new OrdemServico();
        $this->clienteModel = new Cliente();
        $this->tecnicoModel = new Tecnico();
    }

    private function getBaseData()
    {
        return [
            'pageTitle' => 'Relatórios - SIOSeG',
            'currentPage' => 'relatorios',
            'menuItems' => DashboardController::getMenuForProfile(Session::obter('perfil'))
        ];
    }

    public function index()
    {
        $data = array_merge($this->getBaseData(), [
            'sucesso' => Session::obter('sucesso'),
            'erro' => Session::obter('erro')
        ]);

        Session::remover('sucesso');
        Session::remover('erro');

        $this->visualizacao('admin/relatorios/index', $data);
    }

    public function resumoGeral()
    {
        $dataInicio = $_GET['data_inicio'] ?? date('Y-01-01');
        $dataFim = $_GET['data_fim'] ?? date('Y-m-d');
        
        $resumo = $this->relatorioModel->obterResumoOS($dataInicio, $dataFim);
        $performanceTecnicos = $this->relatorioModel->obterPerformanceTecnicos($dataInicio, $dataFim);
        $topProdutos = $this->relatorioModel->obterTopProdutos(5);
        
        // Carregar OS por status para o modal
        $osAbertas = $this->relatorioModel->obterOSPorStatus('aberta', $dataInicio, $dataFim);
        $osEmAndamento = $this->relatorioModel->obterOSPorStatus('em andamento', $dataInicio, $dataFim);
        $osConcluidas = $this->relatorioModel->obterOSPorStatus('concluida', $dataInicio, $dataFim);
        $osEncerradas = $this->relatorioModel->obterOSPorStatus('encerrada', $dataInicio, $dataFim);
        $osPendentes = $this->relatorioModel->obterOSPorStatus('pendente_confirmacao', $dataInicio, $dataFim);
        $osCanceladas = $this->relatorioModel->obterOSPorStatus('cancelada', $dataInicio, $dataFim);
        
        $data = array_merge($this->getBaseData(), [
            'resumo' => $resumo,
            'performanceTecnicos' => $performanceTecnicos,
            'topProdutos' => $topProdutos,
            'osAbertas' => $osAbertas,
            'osEmAndamento' => $osEmAndamento,
            'osConcluidas' => $osConcluidas,
            'osEncerradas' => $osEncerradas,
            'osPendentes' => $osPendentes,
            'osCanceladas' => $osCanceladas,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'pageTitle' => 'Resumo Geral - Relatórios'
        ]);

        $this->visualizacao('admin/relatorios/resumo_geral', $data);
    }

    public function relatorioOS($osId)
    {
        $os = $this->relatorioModel->obterRelatorioOS($osId);
        if (!$os) {
            Session::definir('erro', 'OS não encontrada.');
            header('Location: ' . BASE_URL . 'admin/relatorios');
            exit();
        }

        $materiais = $this->relatorioModel->obterConsumoMateriais($osId);
        
        $data = array_merge($this->getBaseData(), [
            'os' => $os,
            'materiais' => $materiais,
            'pageTitle' => 'Relatório OS #' . $osId
        ]);

        $this->visualizacao('admin/relatorios/relatorio_os', $data);
    }

    public function performanceTecnicos()
    {
        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $dataFim = $_GET['data_fim'] ?? date('Y-m-d');
        
        $performance = $this->relatorioModel->obterPerformanceTecnicos($dataInicio, $dataFim);
        
        $data = array_merge($this->getBaseData(), [
            'performance' => $performance,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'pageTitle' => 'Performance Técnicos - Relatórios'
        ]);

        $this->visualizacao('admin/relatorios/performance_tecnicos', $data);
    }

    public function buscarOS()
    {
        $termo = $_GET['termo'] ?? '';
        $osList = [];
        
        if ($termo) {
            $osList = $this->osModel->buscarOS($termo);
        }
        
        $data = array_merge($this->getBaseData(), [
            'osList' => $osList,
            'termo' => $termo,
            'pageTitle' => 'Buscar OS - Relatórios'
        ]);

        $this->visualizacao('admin/relatorios/buscar_os', $data);
    }

    public function pdfOS($osId)
    {
        $os = $this->relatorioModel->obterRelatorioOS($osId);
        if (!$os) {
            echo 'OS não encontrada';
            return;
        }

        $materiais = $this->relatorioModel->obterConsumoMateriais($osId);
        
        try {
            RelatorioFactory::gerarPDFOS($os, $materiais);
        } catch (\Exception $e) {
            error_log("ERRO ao gerar PDF da OS $osId: " . $e->getMessage());
            echo "Erro ao gerar PDF: " . $e->getMessage();
        }
    }

    public function pdfResumoGeral()
    {
        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $dataFim = $_GET['data_fim'] ?? date('Y-m-d');
        
        $resumo = $this->relatorioModel->obterResumoOS($dataInicio, $dataFim);
        $performanceTecnicos = $this->relatorioModel->obterPerformanceTecnicos($dataInicio, $dataFim);
        $topProdutos = $this->relatorioModel->obterTopProdutos(5);
        
        RelatorioFactory::gerarPDFResumo($resumo, $performanceTecnicos, $topProdutos, $dataInicio, $dataFim);
    }

    public function excelOS($osId)
    {
        $os = $this->relatorioModel->obterRelatorioOS($osId);
        if (!$os) {
            echo 'OS não encontrada';
            return;
        }

        $materiais = $this->relatorioModel->obterConsumoMateriais($osId);
        RelatorioFactory::gerarExcelOS($os, $materiais);
    }

    public function excelResumoGeral()
    {
        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $dataFim = $_GET['data_fim'] ?? date('Y-m-d');
        
        $resumo = $this->relatorioModel->obterResumoOS($dataInicio, $dataFim);
        $performanceTecnicos = $this->relatorioModel->obterPerformanceTecnicos($dataInicio, $dataFim);
        $topProdutos = $this->relatorioModel->obterTopProdutos(5);
        
        RelatorioFactory::gerarExcelResumo($resumo, $performanceTecnicos, $topProdutos, $dataInicio, $dataFim);
    }



    public function relatorioProdutos()
    {
        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $dataFim = $_GET['data_fim'] ?? date('Y-m-d');
        $categoria = $_GET['categoria'] ?? '';
        
        $estatisticas = $this->relatorioModel->obterEstatisticasProdutos($dataInicio, $dataFim);
        $produtosConsumidos = $this->relatorioModel->obterProdutosConsumidos($dataInicio, $dataFim, $categoria);
        $estoqueAtual = $this->relatorioModel->obterEstoqueAtual($categoria);
        $categorias = $this->relatorioModel->obterCategoriasProdutos();
        
        $data = array_merge($this->getBaseData(), [
            'estatisticas' => $estatisticas,
            'produtosConsumidos' => $produtosConsumidos,
            'estoqueAtual' => $estoqueAtual,
            'categorias' => $categorias,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'categoria' => $categoria,
            'pageTitle' => 'Relatório de Produtos'
        ]);

        $this->visualizacao('admin/relatorios/relatorio_produtos', $data);
    }

    public function pdfProdutos()
    {
        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $dataFim = $_GET['data_fim'] ?? date('Y-m-d');
        $categoria = $_GET['categoria'] ?? '';
        
        $estatisticas = $this->relatorioModel->obterEstatisticasProdutos($dataInicio, $dataFim);
        $produtosConsumidos = $this->relatorioModel->obterProdutosConsumidos($dataInicio, $dataFim, $categoria);
        $estoqueAtual = $this->relatorioModel->obterEstoqueAtual($categoria);
        
        RelatorioFactory::gerarPDFProdutos($estatisticas, $produtosConsumidos, $estoqueAtual, $dataInicio, $dataFim);
    }

    public function excelProdutos()
    {
        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $dataFim = $_GET['data_fim'] ?? date('Y-m-d');
        $categoria = $_GET['categoria'] ?? '';
        
        $estatisticas = $this->relatorioModel->obterEstatisticasProdutos($dataInicio, $dataFim);
        $produtosConsumidos = $this->relatorioModel->obterProdutosConsumidos($dataInicio, $dataFim, $categoria);
        $estoqueAtual = $this->relatorioModel->obterEstoqueAtual($categoria);
        
        RelatorioFactory::gerarExcelProdutos($estatisticas, $produtosConsumidos, $estoqueAtual, $dataInicio, $dataFim);
    }

    public function ajaxOSPorStatus()
    {
        header('Content-Type: application/json');
        
        $status = $_GET['status'] ?? '';
        $dataInicio = $_GET['data_inicio'] ?? null;
        $dataFim = $_GET['data_fim'] ?? null;
        
        error_log("AJAX chamado - Status: $status, DataInicio: $dataInicio, DataFim: $dataFim");
        
        if (empty($status)) {
            echo json_encode(['success' => false, 'message' => 'Status não informado']);
            return;
        }
        
        try {
            $os = $this->relatorioModel->obterOSPorStatus($status, $dataInicio, $dataFim);
            error_log("OS encontradas: " . count($os));
            echo json_encode(['success' => true, 'os' => $os]);
        } catch (\Exception $e) {
            error_log("Erro no AJAX: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar OS: ' . $e->getMessage()]);
        }
    }
}