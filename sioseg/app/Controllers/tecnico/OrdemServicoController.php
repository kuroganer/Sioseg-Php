<?php

namespace App\Controllers\Tecnico;

use App\Core\Controller;
use App\Core\Session;
use App\Models\OrdemServico;
use App\Models\MaterialUsado;
use App\Models\Produto;

class OrdemServicoController extends Controller
{
    private OrdemServico $osModel;
    private MaterialUsado $materialModel;
    private Produto $produtoModel;

    public function __construct()
    {
        parent::__construct();
        $this->osModel = new OrdemServico();
        $this->materialModel = new MaterialUsado();
        $this->produtoModel = new Produto();
        // Garante que apenas usuários com perfil 'tecnico' acessem esta área
        Session::exigirPermissao(['tecnico']);
    }

    // ...existing code...

    /**
     * Exibe a lista de Ordens de Serviço do dia para o técnico logado.
     */
    public function index()
    {
        $tecnicoId = Session::obter('id_tec');
        if (!$tecnicoId) {
            Session::definir('erro', 'Sessão de técnico inválida. Por favor, faça login novamente.');
            header('Location: ' . BASE_URL . 'login');
            exit();
        }

        // Busca as OS do dia para o técnico
        $osList = $this->osModel->buscarOSTecnicoDia($tecnicoId);
        
        // Busca OS atrasadas
        $osAtrasadas = $this->osModel->buscarOSAtrasadas($tecnicoId);

        // Conta OS que precisam de estorno (sempre 0 para não mostrar alerta)
        $osParaEstorno = 0;
        
        $data = [
            'userName'     => Session::obter('nome_tec') ?? 'Técnico',
            'userEmail'    => Session::obter('email'),
            'userProfile'  => Session::obter('perfil'),
            'osList'       => $osList,
            'osAtrasadas'  => $osAtrasadas,
            'osParaEstorno' => $osParaEstorno,
            'sucesso'      => Session::obter('sucesso'),
            'erro'         => Session::obter('erro')
        ];

        Session::remover('sucesso');
        Session::remover('erro');

        // Usa o novo layout do técnico
        // Renderiza a view 'painel' dentro do layout principal do sistema
        $this->visualizacao('tecnico/painel', $data, false);
    }

    /**
     * Altera o status de uma OS via AJAX.
     */
    public function alterarStatus()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
            http_response_code(405);
            return;
        }


        $input = json_decode(file_get_contents('php://input'), true);
        $osId = $input['id_os'] ?? null;
        $novoStatus = $input['status'] ?? null;

        if (!$osId || !$novoStatus) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
            http_response_code(400);
            return;
        }

        $osId = (int)$osId;
        $success = false;

        // Se o técnico está marcando como 'concluida', usamos a nova lógica
        if ($novoStatus === 'concluida') {
            $success = $this->osModel->atualizarConclusao($osId, 'tecnico');
            $message = $success ? "O.S. #{$osId} marcada como concluída pelo técnico." : 'Falha ao marcar a O.S. como concluída.';
        } else {
            // Para outros status como 'em andamento', a lógica antiga se mantém
            $success = $this->osModel->alterarStatus($osId, $novoStatus);
            $message = $success ? "O.S. #{$osId} atualizada para {$novoStatus}." : 'Falha ao atualizar o status da O.S.';
        }

        if ($success) {
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => $message]);
            http_response_code(500);
        }
    }

    /**
     * Registra os produtos utilizados em uma OS e dá baixa no estoque.
     */
    public function registrarProdutos()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
            http_response_code(405);
            return;
        }


        $input = json_decode(file_get_contents('php://input'), true);
        $osId = $input['id_os'] ?? null;
        $produtos = $input['produtos'] ?? [];

        if (!$osId || empty($produtos)) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos. Nenhuma OS ou produto informado.']);
            http_response_code(400);
            return;
        }

        try {
            // Usa a conexão da model principal (osModel) para gerenciar a transação
            $this->osModel->iniciarTransacao();

            foreach ($produtos as $item) {
                $idProd = (int)$item['id_prod'];
                $quantidadeUsada = (int)$item['quantidade'];

                if ($quantidadeUsada <= 0) continue;

                // 1. Tenta decrementar o estoque de forma atômica
                $decrementOk = $this->produtoModel->decrementarEstoqueSeDisponivel($idProd, $quantidadeUsada);

                if (!$decrementOk) {
                    throw new \Exception("Estoque insuficiente para o produto ID: {$idProd}.");
                }

                // 2. Adiciona o material à OS (upsert)
                $this->materialModel->adicionarMaterial($osId, $idProd, $quantidadeUsada);
            }

            $this->osModel->confirmar();
            echo json_encode(['success' => true, 'message' => 'Produtos registrados e estoque atualizado com sucesso!']);

        } catch (\Exception $e) {
            // Reverte a transação principal
            try {
                $this->osModel->reverter();
            } catch (\Exception $rollbackEx) {
                error_log('Falha ao dar rollback: ' . $rollbackEx->getMessage());
            }
            error_log("Erro ao registrar produtos na OS #{$osId}: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao registrar produtos: ' . $e->getMessage()]);
            http_response_code(500);
        }
    }

    /**
     * Atualiza a quantidade de um material em uma OS (apenas para diminuir).
     * Esta função é chamada via AJAX pelo painel do técnico.
     */
    public function atualizarMaterial()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
            http_response_code(405);
            return;
        }


        $input = json_decode(file_get_contents('php://input'), true);
        $osId = $input['id_os'] ?? null;
        $prodId = $input['id_prod'] ?? null;
        $novaQtd = $input['qtd'] ?? null;

        // Validação inicial dos dados recebidos
        if (!$osId || !$prodId || !is_numeric($novaQtd) || (int)$novaQtd < 0) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
            http_response_code(400);
            return;
        }

        $osId = (int)$osId;
        $prodId = (int)$prodId;
        $novaQtd = (int)$novaQtd;

        try {
            // Inicia a transação principal para garantir a consistência
            $this->osModel->iniciarTransacao();

            // 1. Busca o material original na OS para saber a quantidade anterior
            $materialOriginal = $this->materialModel->obterMaterialPorOSEProduto($osId, $prodId);

            if (!$materialOriginal) {
                throw new \Exception("Material não encontrado nesta Ordem de Serviço.");
            }

            $qtdOriginal = (int)$materialOriginal->qtd_usada;

            // 2. Regra de negócio: A nova quantidade não pode ser maior que a original
            if ($novaQtd > $qtdOriginal) {
                throw new \Exception("A quantidade não pode ser aumentada. Apenas estorno é permitido.");
            }

            // 3. Calcula a diferença a ser devolvida ao estoque
            $diferenca = $qtdOriginal - $novaQtd;

            if ($diferenca > 0) {
                // 4. Atualiza a quantidade na tabela material_usado
                $this->materialModel->atualizarQuantidadeMaterial($osId, $prodId, $novaQtd);

                // 5. Devolve a diferença ao estoque do produto usando operação atômica de incremento
                $this->produtoModel->incrementarEstoque($prodId, $diferenca);
                

            }

            // Se tudo deu certo, commita a transação
            $this->osModel->confirmar();

            echo json_encode(['success' => true, 'message' => 'Quantidade atualizada e estoque estornado com sucesso!']);

        } catch (\Exception $e) {
            // Se algo deu errado, reverte a transação
            try {
                $this->osModel->reverter();
            } catch (\Exception $rollbackEx) {
                error_log('Falha ao dar rollback: ' . $rollbackEx->getMessage());
            }
            error_log("Erro ao atualizar material na OS #{$osId}: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao processar: ' . $e->getMessage()]);
            http_response_code(500);
        }
    }

    /**
     * Exibe a página de estorno de materiais.
     */
    public function estorno()
    {
        $tecnicoId = Session::obter('id_tec');
        if (!$tecnicoId) {
            Session::definir('erro', 'Sessão de técnico inválida. Por favor, faça login novamente.');
            header('Location: ' . BASE_URL . 'login');
            exit();
        }

        $data = [
            'userName'    => Session::obter('nome_tec'),
            'sucesso'     => Session::obter('sucesso'),
            'erro'        => Session::obter('erro')
        ];

        Session::remover('sucesso');
        Session::remover('erro');

        $this->visualizacao('tecnico/estorno', $data, false);
    }

    /**
     * Busca OS concluídas pelo técnico com materiais para estorno.
     */
    public function buscarOSParaEstorno()
    {
        header('Content-Type: application/json');
        
        $tecnicoId = Session::obter('id_tec');
        if (!$tecnicoId) {
            echo json_encode(['success' => false, 'message' => 'Sessão inválida']);
            return;
        }
        
        try {
            // Busca OS concluídas pelo técnico nos últimos 5 dias que ainda não tiveram estorno
            $sql = "SELECT os.id_os, os.servico_prestado as desc_servico,
                           (CASE WHEN c.tipo_pessoa = 'juridica' AND COALESCE(c.razao_social,'') <> '' 
                                 THEN c.razao_social 
                                 WHEN COALESCE(c.nome_social,'') <> '' 
                                 THEN c.nome_social 
                                 ELSE c.nome_cli END) as cliente_nome
                    FROM ordem_servico os
                    JOIN cliente c ON os.id_cli_fk = c.id_cli
                    WHERE os.id_tec_fk = :id_tec 
                    AND os.conclusao_tecnico = 'concluida'
                    AND os.data_agendamento >= DATE_SUB(CURDATE(), INTERVAL 5 DAY)
                    AND EXISTS (SELECT 1 FROM material_usado mu WHERE mu.id_os_fk = os.id_os AND mu.qtd_usada > 0)
                    ORDER BY os.id_os DESC";
            
            $stmt = $this->osModel->getDb()->prepare($sql);
            $stmt->execute([':id_tec' => $tecnicoId]);
            $osList = $stmt->fetchAll(\PDO::FETCH_OBJ);
            
            // Para cada OS, busca os materiais
            foreach ($osList as $os) {
                $os->produtos_usados = $this->materialModel->obterMateriaisPorOS($os->id_os);
            }
            
            echo json_encode(['success' => true, 'data' => $osList]);
            
        } catch (\Exception $e) {
            error_log("Erro ao buscar OS para estorno: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }

    /**
     * Exibe o histórico de Ordens de Serviço do técnico.
     */
    public function historico()
    {
        $tecnicoId = Session::obter('id_tec');
        if (!$tecnicoId) {
            Session::definir('erro', 'Sessão de técnico inválida. Por favor, faça login novamente.');
            header('Location: ' . BASE_URL . 'login');
            exit();
        }

        // O método buscarHistoricoPorTecnico precisa ser criado no Model
        $osList = $this->osModel->buscarHistoricoPorTecnico($tecnicoId);

        $data = [
            'userName'    => Session::obter('nome_tec'),
            'osList'      => $osList,
            'sucesso'     => Session::obter('sucesso'),
            'erro'        => Session::obter('erro')
        ];

        // A view de histórico precisa ser criada
        // TODO: Criar a view 'tecnico/os/historico.php' e renderizá-la no layout principal
        // $this->view('tecnico/os/historico', $data, false);
        echo "Página de histórico em construção.";
    }
    

}