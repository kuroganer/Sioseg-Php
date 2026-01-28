<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/relatorios.css">

<div class="relatorios-container">
    <div class="relatorios-header">
        <h1><i class="fas fa-chart-bar"></i> Relatórios</h1>
        <p>Selecione o tipo de relatório que deseja gerar</p>
    </div>

    <?php if (isset($sucesso)): ?>
        <div class="alerta sucesso">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($sucesso) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($erro)): ?>
        <div class="alerta erro">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <div class="relatorios-grid">
        <div class="relatorio-card primary">
            <div class="icon">
                <i class="fas fa-chart-pie"></i>
            </div>
            <h3>Resumo Geral</h3>
            <p>Visão geral das OS, performance dos técnicos e produtos mais utilizados</p>
            <a href="<?= BASE_URL ?>admin/relatorios/resumo-geral" class="btn btn-primary">
                <i class="fas fa-eye"></i> Visualizar
            </a>
        </div>

        <div class="relatorio-card success">
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <h3>Performance Técnicos</h3>
            <p>Produtividade e avaliações dos técnicos por período</p>
            <a href="<?= BASE_URL ?>admin/relatorios/performance-tecnicos" class="btn btn-success">
                <i class="fas fa-chart-line"></i> Visualizar
            </a>
        </div>

        <div class="relatorio-card warning">
            <div class="icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <h3>Relatório de OS</h3>
            <p>Relatório detalhado de uma OS específica com materiais utilizados</p>
            <a href="<?= BASE_URL ?>admin/relatorios/buscar-os" class="btn btn-warning">
                <i class="fas fa-search"></i> Buscar OS
            </a>
        </div>

        <div class="relatorio-card info">
            <div class="icon">
                <i class="fas fa-boxes"></i>
            </div>
            <h3>Relatório de Produtos</h3>
            <p>Análise de consumo, estoque e movimentação de produtos</p>
            <a href="<?= BASE_URL ?>admin/relatorios/produtos" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> Visualizar
            </a>
        </div>
    </div>

    <div class="filtros-section">
        <h4><i class="fas fa-filter"></i> Filtros Rápidos</h4>
        <div class="filtros-grid">
            <a href="<?= BASE_URL ?>admin/relatorios/resumo-geral?data_inicio=<?= date('Y-m-01') ?>&data_fim=<?= date('Y-m-d') ?>" class="filtro-rapido">
                <i class="fas fa-calendar-day"></i> Este Mês
            </a>
            <a href="<?= BASE_URL ?>admin/relatorios/resumo-geral?data_inicio=<?= date('Y-m-01', strtotime('-1 month')) ?>&data_fim=<?= date('Y-m-t', strtotime('-1 month')) ?>" class="filtro-rapido">
                <i class="fas fa-calendar-minus"></i> Mês Anterior
            </a>
            <a href="<?= BASE_URL ?>admin/relatorios/resumo-geral?data_inicio=<?= date('Y-01-01') ?>&data_fim=<?= date('Y-12-31') ?>" class="filtro-rapido">
                <i class="fas fa-calendar-alt"></i> Este Ano
            </a>
            <a href="<?= BASE_URL ?>admin/relatorios/resumo-geral?data_inicio=<?= date('Y-m-d', strtotime('-30 days')) ?>&data_fim=<?= date('Y-m-d') ?>" class="filtro-rapido">
                <i class="fas fa-calendar-week"></i> Últimos 30 Dias
            </a>
        </div>
    </div>
</div>