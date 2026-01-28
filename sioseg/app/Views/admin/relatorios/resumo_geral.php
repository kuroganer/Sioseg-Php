<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/relatorios.css">

<div class="relatorios-container">
    <div class="relatorios-header">
        <h1><i class="fas fa-chart-pie"></i> Resumo Geral</h1>
        <p>Período: <?= date('d/m/Y', strtotime($dataInicio)) ?> a <?= date('d/m/Y', strtotime($dataFim)) ?></p>
    </div>

    <div class="acoes-container">
        <a href="<?= BASE_URL ?>admin/relatorios" class="btn-acao btn-voltar">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <a href="<?= BASE_URL ?>admin/relatorios/pdf/resumo-geral?data_inicio=<?= $dataInicio ?>&data_fim=<?= $dataFim ?>" class="btn-acao btn-pdf">
            <i class="fas fa-file-pdf"></i> PDF
        </a>
        <a href="<?= BASE_URL ?>admin/relatorios/excel/resumo-geral?data_inicio=<?= $dataInicio ?>&data_fim=<?= $dataFim ?>" class="btn-acao btn-excel">
            <i class="fas fa-file-excel"></i> Excel
        </a>
        <button onclick="window.print()" class="btn-acao btn-imprimir">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>

    <div class="filtros-section">
        <h4><i class="fas fa-filter"></i> Filtrar Período</h4>
        <form method="GET" class="form-filtro">
            <div class="form-group">
                <label for="data_inicio">Data Início</label>
                <input type="date" id="data_inicio" name="data_inicio" value="<?= $dataInicio ?>" class="form-control">
            </div>
            <div class="form-group">
                <label for="data_fim">Data Fim</label>
                <input type="date" id="data_fim" name="data_fim" value="<?= $dataFim ?>" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Filtrar
            </button>
        </form>
    </div>

    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-info">
                <h3><?= $resumo['total_os'] ?? 0 ?></h3>
                <p>Total de OS</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
        </div>
        <div class="stat-card danger clickable-card" data-status="aberta" data-count="<?= $resumo['abertas'] ?? 0 ?>">
            <div class="stat-info">
                <h3><?= $resumo['abertas'] ?? 0 ?></h3>
                <p>OS Abertas</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
        </div>
        <div class="stat-card warning clickable-card" data-status="em andamento" data-count="<?= $resumo['em_andamento'] ?? 0 ?>">
            <div class="stat-info">
                <h3><?= $resumo['em_andamento'] ?? 0 ?></h3>
                <p>Em Andamento</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-cog"></i>
            </div>
        </div>
        <div class="stat-card success clickable-card" data-status="concluida" data-count="<?= $resumo['concluidas'] ?? 0 ?>">
            <div class="stat-info">
                <h3><?= $resumo['concluidas'] ?? 0 ?></h3>
                <p>OS Concluídas</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    
    <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-top: 20px;">
        <div class="stat-card secondary clickable-card" data-status="encerrada" data-count="<?= $resumo['encerradas'] ?? 0 ?>">
            <div class="stat-info">
                <h3><?= $resumo['encerradas'] ?? 0 ?></h3>
                <p>OS Encerradas</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-archive"></i>
            </div>
        </div>
        <div class="stat-card info clickable-card" data-status="pendente_confirmacao" data-count="<?= $resumo['pendente_confirmacao'] ?? 0 ?>">
            <div class="stat-info">
                <h3><?= $resumo['pendente_confirmacao'] ?? 0 ?></h3>
                <p>Pendente Confirmação</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="stat-card dark clickable-card" data-status="cancelada" data-count="<?= $resumo['canceladas'] ?? 0 ?>">
            <div class="stat-info">
                <h3><?= $resumo['canceladas'] ?? 0 ?></h3>
                <p>OS Canceladas</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
        <div class="stat-card warning">
            <div class="stat-info">
                <h3><?= number_format($resumo['media_avaliacao'] ?? 0, 1) ?></h3>
                <p>Média Avaliação</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-star"></i>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 30px;">
        <!-- Performance Técnicos -->
        <div class="tabela-container">
            <div class="tabela-header">
                <h4><i class="fas fa-users"></i> Performance dos Técnicos</h4>
            </div>
            <?php if (!empty($performanceTecnicos)): ?>
                <div class="tabela-responsiva">
                    <table class="tabela">
                        <thead>
                            <tr>
                                <th>Técnico</th>
                                <th>Total OS</th>
                                <th>Concluídas</th>
                                <th>Taxa Conclusão</th>
                                <th>Média Avaliação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($performanceTecnicos as $tecnico): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($tecnico['nome_tec']) ?></strong></td>
                                    <td><?= $tecnico['total_os'] ?></td>
                                    <td><?= $tecnico['os_concluidas'] ?></td>
                                    <td>
                                        <?php 
                                        // Taxa de conclusão baseada apenas em OSs efetivamente concluídas (não encerradas administrativamente)
                                        $taxa = $tecnico['total_os'] > 0 ? ($tecnico['os_concluidas'] / $tecnico['total_os']) * 100 : 0;
                                        echo '<span style="color: ' . ($taxa >= 80 ? '#28a745' : ($taxa >= 60 ? '#ffc107' : '#dc3545')) . '; font-weight: bold;">' . number_format($taxa, 1) . '%</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($tecnico['media_avaliacao']): ?>
                                            <span style="color: #28a745; font-weight: bold;">
                                                <?= number_format($tecnico['media_avaliacao'], 1) ?>/5 ★
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Sem avaliações</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    Nenhum dado encontrado para o período selecionado.
                </div>
            <?php endif; ?>
        </div>

        <!-- Top Produtos -->
        <div class="tabela-container">
            <div class="tabela-header">
                <h4><i class="fas fa-box"></i> Top 5 Produtos</h4>
            </div>
            <div style="padding: 20px;">
                <?php if (!empty($topProdutos)): ?>
                    <?php foreach ($topProdutos as $index => $produto): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0; <?= $index < count($topProdutos) - 1 ? 'border-bottom: 1px solid #e9ecef;' : '' ?>">
                            <div>
                                <div style="font-weight: bold; color: var(--color-text-primary);"><?= htmlspecialchars($produto['nome']) ?></div>
                                <div class="text-muted" style="font-size: 12px;"><?= htmlspecialchars($produto['marca']) ?></div>
                            </div>
                            <div style="text-align: right;">
                                <div style="background: #007bff; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                                    <?= $produto['total_usado'] ?>
                                </div>
                                <div class="text-muted" style="font-size: 11px; margin-top: 2px;">
                                    <?= $produto['os_utilizadas'] ?> OS
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        Nenhum produto utilizado no período.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalhes das OS -->
<div id="osModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Detalhes das OS</h3>
            <button onclick="closeModal()" class="modal-close">&times;</button>
        </div>
        <div id="modalContent">Carregando...</div>
    </div>
</div>

<style>
.clickable-card {
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}
.clickable-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>

<script>
function closeModal() {
    document.getElementById('osModal').style.display = 'none';
}

// Dados das OS carregados do PHP
const osData = {
    'aberta': <?= json_encode($osAbertas) ?>,
    'em andamento': <?= json_encode($osEmAndamento) ?>,
    'concluida': <?= json_encode($osConcluidas) ?>,
    'encerrada': <?= json_encode($osEncerradas) ?>,
    'pendente_confirmacao': <?= json_encode($osPendentes) ?>,
    'cancelada': <?= json_encode($osCanceladas) ?>
};

function showOSDetails(status, count) {
    if (count == 0) return;
    
    const modal = document.getElementById('osModal');
    const title = document.getElementById('modalTitle');
    const content = document.getElementById('modalContent');
    
    const statusNames = {
        'total': 'Todas as OS',
        'aberta': 'OS Abertas',
        'em andamento': 'OS em Andamento',
        'concluida': 'OS Concluídas',
        'encerrada': 'OS Encerradas',
        'pendente_confirmacao': 'OS Pendentes de Confirmação',
        'cancelada': 'OS Canceladas'
    };
    
    title.textContent = statusNames[status] || 'Detalhes das OS';
    modal.style.display = 'block';
    
    let html = '';
    
    if (status === 'total') {
        // Para "total", mostrar todas as OS
        html = '<p>Funcionalidade "Todas as OS" em desenvolvimento...</p>';
    } else if (osData[status] && osData[status].length > 0) {
        // Mostrar tabela com as OS do status específico
        html = '<table style="width: 100%; border-collapse: collapse;">';
        html += '<tr style="background: #f8f9fa;"><th style="padding: 10px; border: 1px solid #ddd;">Nº OS</th><th style="padding: 10px; border: 1px solid #ddd;">Cliente</th><th style="padding: 10px; border: 1px solid #ddd;">Técnico</th><th style="padding: 10px; border: 1px solid #ddd;">Serviço</th><th style="padding: 10px; border: 1px solid #ddd;">Data do Agendamento</th></tr>';
        
        osData[status].forEach(os => {
            const cliente = os.tipo_pessoa === 'juridica' && os.razao_social ? os.razao_social : os.nome_cli;
            const dataFormatada = os.data_agendamento ? new Date(os.data_agendamento).toLocaleString('pt-BR') : 'Não agendada';
            
            html += `<tr>`;
            html += `<td style="padding: 8px; border: 1px solid #ddd;">#${os.id_os}</td>`;
            html += `<td style="padding: 8px; border: 1px solid #ddd;">${cliente}</td>`;
            html += `<td style="padding: 8px; border: 1px solid #ddd;">${os.nome_tec}</td>`;
            html += `<td style="padding: 8px; border: 1px solid #ddd;">${os.servico_prestado || 'N/A'}</td>`;
            html += `<td style="padding: 8px; border: 1px solid #ddd;">${dataFormatada}</td>`;
            html += `</tr>`;
        });
        
        html += '</table>';
    } else {
        html = '<p>Nenhuma OS encontrada para este status no período selecionado.</p>';
    }
    
    content.innerHTML = html;
}

// Adicionar event listeners aos cards
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.clickable-card').forEach(card => {
        card.addEventListener('click', function() {
            const status = this.dataset.status;
            const count = parseInt(this.dataset.count);
            showOSDetails(status, count);
        });
    });
    
    // Fechar modal ao clicar fora
    document.getElementById('osModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
});
</script>