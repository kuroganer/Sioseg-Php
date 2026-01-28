<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/relatorios.css">

<div class="relatorios-container">
    <div class="relatorios-header">
        <h1><i class="fas fa-users"></i> Performance dos Técnicos</h1>
        <p>Relatório de desempenho dos técnicos por período</p>
    </div>
    
    <div class="acoes-container">
        <a href="<?= BASE_URL ?>admin/relatorios" class="btn-acao btn-voltar">
            <i class="fas fa-arrow-left"></i> Voltar
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

    <?php if (!empty($performance)): ?>
        <div class="tabela-container">
            <div class="tabela-header">
                <h4><i class="fas fa-chart-line"></i> Performance dos Técnicos</h4>
            </div>
            <div class="tabela-responsiva">
                <table class="tabela">
                    <thead>
                        <tr>
                            <th>Técnico</th>
                            <th>Total OS</th>
                            <th>OS Concluídas</th>
                            <th>Tempo Médio (horas)</th>
                            <th>Avaliação Média</th>
                            <th>Total Avaliações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($performance as $perf): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($perf['nome_tec'] ?? '') ?></strong></td>
                                <td><?= $perf['total_os'] ?? 0 ?></td>
                                <td><?= $perf['os_concluidas'] ?? 0 ?></td>
                                <td>
                                    <?php if (!empty($perf['tempo_medio_minutos'])): ?>
                                        <?php 
                                            $tempo_medio = floatval($perf['tempo_medio_minutos']);
                                            $horas = intval($tempo_medio / 60);
                                            $minutos = (int)round($tempo_medio - ($horas * 60));
                                            echo $horas . 'h ' . $minutos . 'min';
                                        ?>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($perf['media_avaliacao'])): ?>
                                        <span style="color: #28a745; font-weight: bold;">
                                            <?= number_format($perf['media_avaliacao'], 1) ?>/5 ★
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $perf['total_avaliacoes'] ?? 0 ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="alerta info">
            <i class="fas fa-info-circle"></i>
            Nenhum dado de performance encontrado para o período selecionado.
        </div>
    <?php endif; ?>
</div>