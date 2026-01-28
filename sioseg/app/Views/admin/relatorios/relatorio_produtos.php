<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/relatorios.css">

<div class="relatorios-container">
    <div class="relatorios-header">
        <h1><i class="fas fa-boxes"></i> Relatório de Produtos</h1>
        <p>Análise completa do consumo e estoque de produtos</p>
    </div>

    <div class="acoes-container">
        <a href="<?= BASE_URL ?>admin/relatorios" class="btn-acao btn-voltar">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <a href="<?= BASE_URL ?>admin/relatorios/pdfProdutos?data_inicio=<?= $dataInicio ?>&data_fim=<?= $dataFim ?>" class="btn-acao btn-pdf" target="_blank">
            <i class="fas fa-file-pdf"></i> PDF
        </a>
        <a href="<?= BASE_URL ?>admin/relatorios/excelProdutos?data_inicio=<?= $dataInicio ?>&data_fim=<?= $dataFim ?>" class="btn-acao btn-excel">
            <i class="fas fa-file-excel"></i> Excel
        </a>
        <button onclick="window.print()" class="btn-acao btn-imprimir">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>

    <div class="filtros-section">
        <h4><i class="fas fa-filter"></i> Filtros</h4>
        <form method="GET" class="form-filtro">
            <div class="form-group">
                <label for="data_inicio">Data Início</label>
                <input type="date" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($dataInicio) ?>" class="form-control">
            </div>
            <div class="form-group">
                <label for="data_fim">Data Fim</label>
                <input type="date" id="data_fim" name="data_fim" value="<?= htmlspecialchars($dataFim) ?>" class="form-control">
            </div>


            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Filtrar
            </button>
        </form>
    </div>

    <!-- Estatísticas Gerais -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-info">
                <h3><?= $estatisticas['total_produtos_cadastrados'] ?? 0 ?></h3>
                <p>Produtos Cadastrados</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-box"></i>
            </div>
        </div>
        <div class="stat-card success">
            <div class="stat-info">
                <h3><?= $estatisticas['produtos_utilizados'] ?? 0 ?></h3>
                <p>Produtos Utilizados</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="stat-card warning">
            <div class="stat-info">
                <h3><?= $estatisticas['total_consumido'] ?? 0 ?></h3>
                <p>Total Consumido</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-arrow-down"></i>
            </div>
        </div>
        <div class="stat-card info">
            <div class="stat-info">
                <h3><?= number_format($estatisticas['media_consumo_por_uso'] ?? 0, 1) ?></h3>
                <p>Média por Uso</p>
            </div>
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 30px;">
        <!-- Produtos Mais Consumidos -->
        <div class="tabela-container">
            <div class="tabela-header">
                <h4><i class="fas fa-fire"></i> Produtos Mais Consumidos</h4>
            </div>
            <?php if (!empty($produtosConsumidos)): ?>
                <div class="tabela-responsiva">
                    <table class="tabela">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Categoria</th>
                                <th>Estoque</th>
                                <th>Consumido</th>
                                <th>OS</th>
                                <th>Média/OS</th>
                                <th>Última Uso</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produtosConsumidos as $produto): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($produto['nome']) ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($produto['marca']) ?> - <?= htmlspecialchars($produto['modelo']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($produto['categoria']) ?></td>
                                    <td>
                                        <span style="background: <?= $produto['qtd_estoque'] <= 10 ? '#dc3545' : ($produto['qtd_estoque'] <= 20 ? '#ffc107' : '#28a745') ?>; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                                            <?= $produto['qtd_estoque'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="background: #007bff; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                                            <?= $produto['total_consumido'] ?>
                                        </span>
                                    </td>
                                    <td><?= $produto['os_utilizadas'] ?></td>
                                    <td><?= number_format($produto['media_por_os'], 1) ?></td>
                                    <td><?= date('d/m/Y', strtotime($produto['ultima_utilizacao'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    Nenhum produto foi consumido no período selecionado.
                </div>
            <?php endif; ?>
        </div>

        <!-- Status do Estoque -->
        <div class="tabela-container">
            <div class="tabela-header">
                <h4><i class="fas fa-warehouse"></i> Status do Estoque</h4>
            </div>
            <div style="padding: 20px;">
                <?php if (!empty($estoqueAtual)): ?>
                    <?php 
                    $criticos = array_filter($estoqueAtual, fn($p) => $p['status_estoque'] === 'critico');
                    $baixos = array_filter($estoqueAtual, fn($p) => $p['status_estoque'] === 'baixo');
                    ?>
                    
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span style="font-weight: bold; color: #dc3545;">Estoque Crítico (≤10)</span>
                            <span style="background: #dc3545; color: white; padding: 2px 8px; border-radius: 10px; font-size: 12px;">
                                <?= count($criticos) ?>
                            </span>
                        </div>
                        <?php if (!empty($criticos)): ?>
                            <?php foreach (array_slice($criticos, 0, 5) as $produto): ?>
                                <div style="padding: 8px 0; border-bottom: 1px solid var(--color-border); font-size: 14px;">
                                    <div style="font-weight: bold; color: var(--color-text-primary);"><?= htmlspecialchars($produto['nome']) ?></div>
                                    <div class="text-muted" style="font-size: 12px;">
                                        Estoque: <?= $produto['qtd_estoque'] ?> | Mínimo: <?= $produto['qtd_minima'] ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span style="font-weight: bold; color: #ffc107;">Estoque Baixo (≤20)</span>
                            <span style="background: #ffc107; color: #212529; padding: 2px 8px; border-radius: 10px; font-size: 12px;">
                                <?= count($baixos) ?>
                            </span>
                        </div>
                        <?php if (!empty($baixos)): ?>
                            <?php foreach (array_slice($baixos, 0, 5) as $produto): ?>
                                <div style="padding: 8px 0; border-bottom: 1px solid var(--color-border); font-size: 14px;">
                                    <div style="font-weight: bold; color: var(--color-text-primary);"><?= htmlspecialchars($produto['nome']) ?></div>
                                    <div class="text-muted" style="font-size: 12px;">
                                        Estoque: <?= $produto['qtd_estoque'] ?> | Mínimo: <?= $produto['qtd_minima'] ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div style="text-align: center; margin-top: 20px;">
                        <div style="font-size: 24px; color: #28a745; margin-bottom: 5px;">
                            <?= count($estoqueAtual) - count($criticos) - count($baixos) ?>
                        </div>
                        <div class="text-muted" style="font-size: 12px;">Produtos com estoque normal</div>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        Nenhum produto encontrado.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tabela Completa do Estoque -->
    <?php if (!empty($estoqueAtual)): ?>
        <div class="tabela-container">
            <div class="tabela-header">
                <h4><i class="fas fa-list"></i> Estoque Completo</h4>
            </div>
            <div class="tabela-responsiva">
                <table class="tabela">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Estoque Atual</th>
                            <th>Estoque Mínimo</th>
                            <th>Total Usado</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estoqueAtual as $produto): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($produto['nome']) ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($produto['marca']) ?> - <?= htmlspecialchars($produto['modelo']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($produto['categoria']) ?></td>
                                <td>
                                    <span style="font-weight: bold; font-size: 16px;">
                                        <?= $produto['qtd_estoque'] ?>
                                    </span>
                                </td>
                                <td><?= $produto['qtd_minima'] ?></td>
                                <td><?= $produto['total_usado_historico'] ?></td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'critico' => '#dc3545',
                                        'baixo' => '#ffc107',
                                        'normal' => '#28a745'
                                    ];
                                    $statusTexts = [
                                        'critico' => 'Crítico',
                                        'baixo' => 'Baixo',
                                        'normal' => 'Normal'
                                    ];
                                    $color = $statusColors[$produto['status_estoque']];
                                    $text = $statusTexts[$produto['status_estoque']];
                                    ?>
                                    <?php
                                    $statusColors = [
                                        'critico' => '#dc3545',
                                        'baixo' => '#ffc107',
                                        'normal' => '#28a745'
                                    ];
                                    $statusTexts = [
                                        'critico' => 'Crítico',
                                        'baixo' => 'Baixo',
                                        'normal' => 'Normal'
                                    ];
                                    $color = $statusColors[$produto['status_estoque']];
                                    $text = $statusTexts[$produto['status_estoque']];
                                    ?>
                                    <span style="background: <?= $color ?>; color: white; padding: 4px 12px; border-radius: 15px; font-size: 12px; font-weight: bold;">
                                        <?= $text ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>