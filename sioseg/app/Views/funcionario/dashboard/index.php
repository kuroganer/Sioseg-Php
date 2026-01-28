<?php
// As variáveis usadas nesta view são passadas pelo `DashboardController`.
// O operador '??' (null coalescing) define um valor padrão caso a variável não exista.
// Isso evita erros se os dados não forem carregados corretamente.
$stats = $stats ?? [
    'abertas_hoje' => 0,
    'total_concluidas' => 0,
    'em_andamento' => 0,
    'avaliacoes_pendentes' => 0,
];
$topTecnicos = $topTecnicos ?? [];
$os_a_fazer = $os_a_fazer ?? [];
$os_em_andamento = $os_em_andamento ?? [];
$os_concluidas = $os_concluidas ?? [];
$userName = $userName ?? 'funcionario'; // Nome do usuário logado.
// Dados para o gráfico de avaliações.
$chartLabels = $chartLabels ?? ['D-6', 'D-5', 'D-4', 'D-3', 'D-2', 'Ontem', 'Hoje'];
$chartData = $chartData ?? [0, 0, 0, 0, 0, 0, 0];
?>

<div class="dashboard-container">
    <header class="dashboard-header">
        <div class="welcome-message">
            <h2>Olá <?= htmlspecialchars($userName) ?>, bem-vindo ao SIOSEG!</h2>
            <p>Aqui está um resumo do sistema.</p>
        </div>

    </header>
    <?php if (!empty($stats['conclusoes_pendentes']) && $stats['conclusoes_pendentes'] > 0): ?>
        <div style="max-width:800px;margin:10px auto;">
            <div style="background:#fff3cd;border:1px solid #ffeeba;padding:12px;border-radius:6px;color:#856404;">
                <strong>Atenção:</strong>
                Existem <strong><?= (int)$stats['conclusoes_pendentes'] ?></strong> ordem(ens) de serviço aguardando confirmação da outra parte (técnico/cliente).
                <button onclick="carregarOsPendentes()" style="margin-left:10px;background:none;border:none;color:#856404;text-decoration:underline;cursor:pointer;">Ver detalhes</button>
            </div>
        </div>
    <?php endif; ?>
    <hr class="header-divider">

    <div class="dashboard-grid">
        <!-- Coluna da Esquerda -->
        <div class="grid-column">
            <!-- Quadro 1: Resumo Geral -->
            <div class="dashboard-card">
                <h2 class="card-title">Resumo Geral</h2>
                <div class="chart-container">
                    <canvas id="overviewPieChart"></canvas>
                </div>
            </div>
            <!-- Quadro 3: Relatórios e Análise -->
            <div class="dashboard-card">
                <h2 class="card-title">Média de Avaliações (Últimos 7 dias)</h2>
                <div class="chart-container">
                    <canvas id="ratingsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Coluna da Direita -->
        <div class="grid-column">
            <!-- Quadro 2: Gerenciamento de OS -->
            <div class="dashboard-card card-large">
                <h2 class="card-title">Gerenciamento Rápido de OS</h2>
                <div class="kanban-board">
                    <div class="kanban-column">
                        <div class="column-header">A Fazer</div>
                        <div class="task-list" id="todo-tasks">
                            <?php foreach ($os_a_fazer as $os): ?>
                                <div class="kanban-task" id="os-<?= htmlspecialchars($os->id_os) ?>" draggable="true">
                                    <h4>OS #<?= htmlspecialchars($os->id_os) ?></h4>
                                    <p>Cliente: 
                                        <?php
                                            $nomeCliente = ($os->tipo_pessoa === 'juridica' && !empty($os->razao_social)) ? $os->razao_social : $os->nome_cli;
                                            echo htmlspecialchars($nomeCliente ?? 'N/A');
                                        ?>
                                    </p>
                                    <span class="task-assigned">Téc: <?= htmlspecialchars($os->nome_tec ?: 'N/A') ?></span>
                                    <div class="task-actions">
                                        <a href="<?= BASE_URL ?>funcionario/os/edit/<?= $os->id_os ?>" class="task-action-btn view-btn" title="Ver/Editar OS">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="kanban-column">
                        <div class="column-header">Em Andamento</div>
                        <div class="task-list" id="doing-tasks">
                            <?php foreach ($os_em_andamento as $os): ?>
                                <div class="kanban-task" id="os-<?= htmlspecialchars($os->id_os) ?>" draggable="true">
                                    <h4>OS #<?= htmlspecialchars($os->id_os) ?></h4>
                                    <p>Cliente: 
                                        <?php
                                            $nomeCliente = ($os->tipo_pessoa === 'juridica' && !empty($os->razao_social)) ? $os->razao_social : $os->nome_cli;
                                            echo htmlspecialchars($nomeCliente ?? 'N/A');
                                        ?>
                                    </p>
                                    <span class="task-assigned">Téc: <?= htmlspecialchars($os->nome_tec ?: 'N/A') ?></span>
                                    <div class="task-actions">
                                        <a href="<?= BASE_URL ?>funcionario/os/edit/<?= $os->id_os ?>" class="task-action-btn view-btn" title="Ver/Editar OS">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="kanban-column">
                        <div class="column-header">Concluído</div>
                        <div class="task-list" id="done-tasks">
                            <?php foreach ($os_concluidas as $os): ?>
                                <div class="kanban-task" id="os-<?= htmlspecialchars($os->id_os) ?>" draggable="true">
                                    <h4>OS #<?= htmlspecialchars($os->id_os) ?></h4>
                                    <p>Cliente: 
                                        <?php
                                            $nomeCliente = ($os->tipo_pessoa === 'juridica' && !empty($os->razao_social)) ? $os->razao_social : $os->nome_cli;
                                            echo htmlspecialchars($nomeCliente ?? 'N/A');
                                        ?>
                                    </p>
                                    <span class="task-assigned">Téc: <?= htmlspecialchars($os->nome_tec ?: 'N/A') ?></span>
                                    <div class="task-actions">
                                        <a href="<?= BASE_URL ?>funcionario/os/edit/<?= $os->id_os ?>" class="task-action-btn view-btn" title="Ver/Editar OS">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Quadro 4: Top Técnicos -->
            <div class="dashboard-card">
                <h2 class="card-title">Top 3 Técnicos por Avaliação</h2>
                <ul class="ranking-list">
                    <?php if (!empty($topTecnicos)): ?>
                        <?php foreach ($topTecnicos as $tecnico): ?>
                            <li>
                                <span class="ranking-name"><?= htmlspecialchars($tecnico->nome) ?></span>
                                <span class="ranking-score"><?= htmlspecialchars(number_format($tecnico->media_avaliacoes, 1)) ?> <i class="fas fa-star"></i></span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li style="text-align: center; color: var(--text-color); font-style: italic; padding: 20px 0;">
                            Nenhuma avaliação disponível
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal para OS Pendentes -->
<div id="modalOsPendentes" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;">
    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:20px;border-radius:8px;max-width:700px;width:95%;max-height:85%;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;border-bottom:1px solid #eee;padding-bottom:10px;">
            <h3 style="margin:0;color:#333;">OS Aguardando Confirmação</h3>
            <button onclick="fecharModal()" style="background:none;border:none;font-size:24px;cursor:pointer;color:#999;width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:50%;">&times;</button>
        </div>
        <div id="listaOsPendentes">Carregando...</div>
    </div>
</div>

<!-- Scripts específicos do Dashboard -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= defined('BASE_URL') ? BASE_URL : '' ?>assets/js/admin-dashboard.js"></script>
<script>
    // Passa os dados do PHP para o JavaScript para que o Chart.js possa usá-los.
    window.chartLabels = <?= json_encode($chartLabels) ?>;
    window.chartData = <?= json_encode($chartData) ?>;
    // Dados para o gráfico de pizza
    window.pieChartData = {
        abertas: <?= isset($stats['abertas_total']) ? $stats['abertas_total'] : $stats['abertas_hoje'] ?>,
        concluidas: <?= $stats['total_concluidas'] ?>,
        andamento: <?= $stats['em_andamento'] ?>,
        avaliacoes: <?= $stats['avaliacoes_pendentes'] ?>
    };
    // Define a BASE_URL para ser usada nas requisições AJAX do admin-dashboard.js
    const BASE_URL = '<?= defined('BASE_URL') ? BASE_URL : '' ?>';
    
    function carregarOsPendentes() {
        document.getElementById('modalOsPendentes').style.display = 'block';
        
        fetch(BASE_URL + 'funcionario/dashboard/os-pendentes')
            .then(response => response.json())
            .then(data => {
                let html = '';
                if (data.length === 0) {
                    html = '<p style="text-align:center;color:#666;">Nenhuma OS pendente encontrada.</p>';
                } else {
                    data.forEach(os => {
                        const cliente = os.razao_social || os.nome_cli || 'N/A';
                        const tecnico = os.nome_tec || 'N/A';
                        const telCliente = os.tel1_cli || 'N/A';
                        const telTecnico = os.tel_tec || 'N/A';
                        
                        html += `
                            <div style="border:1px solid #ddd;padding:15px;margin-bottom:15px;border-radius:6px;background:#f8f9fa;">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                                    <h4 style="margin:0;color:#333;">OS #${os.id_os}</h4>
                                    <a href="${BASE_URL}funcionario/os/edit/${os.id_os}" style="color:#007bff;text-decoration:none;font-size:12px;padding:4px 8px;border:1px solid #007bff;border-radius:4px;">Ver OS →</a>
                                </div>
                                <table style="width:100%;border-collapse:collapse;">
                                    <tr style="border-bottom:1px solid #e9ecef;">
                                        <td style="padding:8px 0;font-weight:bold;color:#495057;width:30%;">Cliente:</td>
                                        <td style="padding:8px 0;">${cliente}</td>
                                    </tr>
                                    <tr style="border-bottom:1px solid #e9ecef;">
                                        <td style="padding:8px 0;font-weight:bold;color:#495057;">Tel. Cliente:</td>
                                        <td style="padding:8px 0;">${telCliente}</td>
                                    </tr>
                                    <tr style="border-bottom:1px solid #e9ecef;">
                                        <td style="padding:8px 0;font-weight:bold;color:#495057;">Status Cliente:</td>
                                        <td style="padding:8px 0;">
                                            <span style="background:${os.conclusao_cliente === 'concluida' ? '#28a745' : '#ffc107'};color:white;padding:4px 12px;border-radius:15px;font-size:12px;font-weight:bold;">
                                                ${os.conclusao_cliente.charAt(0).toUpperCase() + os.conclusao_cliente.slice(1)}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr style="border-bottom:1px solid #e9ecef;">
                                        <td style="padding:8px 0;font-weight:bold;color:#495057;">Técnico:</td>
                                        <td style="padding:8px 0;">${tecnico}</td>
                                    </tr>
                                    <tr style="border-bottom:1px solid #e9ecef;">
                                        <td style="padding:8px 0;font-weight:bold;color:#495057;">Tel. Técnico:</td>
                                        <td style="padding:8px 0;">${telTecnico}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:8px 0;font-weight:bold;color:#495057;">Status Técnico:</td>
                                        <td style="padding:8px 0;">
                                            <span style="background:${os.conclusao_tecnico === 'concluida' ? '#28a745' : '#ffc107'};color:white;padding:4px 12px;border-radius:15px;font-size:12px;font-weight:bold;">
                                                ${os.conclusao_tecnico.charAt(0).toUpperCase() + os.conclusao_tecnico.slice(1)}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        `;
                    });
                }
                document.getElementById('listaOsPendentes').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('listaOsPendentes').innerHTML = '<p style="color:#dc3545;">Erro ao carregar dados.</p>';
            });
    }
    
    function fecharModal() {
        document.getElementById('modalOsPendentes').style.display = 'none';
    }
    
    // Fechar modal clicando fora
    document.getElementById('modalOsPendentes').onclick = function(e) {
        if (e.target === this) fecharModal();
    }
</script>