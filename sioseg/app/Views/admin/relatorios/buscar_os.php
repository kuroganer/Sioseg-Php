<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/relatorios.css">

<div class="relatorios-container">
    <div class="relatorios-header">
        <h1><i class="fas fa-search"></i> Buscar OS para Relatório</h1>
        <p>Encontre a OS que deseja gerar o relatório</p>
    </div>

    <div class="acoes-container">
        <a href="<?= BASE_URL ?>admin/relatorios" class="btn-acao btn-voltar">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="filtros-section">
        <h4><i class="fas fa-search"></i> Buscar OS</h4>
        <form method="GET" class="form-filtro">
            <div class="form-group" style="flex: 3;">
                <label for="termo">Buscar por número da OS, cliente ou técnico</label>
                <input type="text" id="termo" name="termo" class="form-control" 
                       placeholder="Digite o número da OS, nome do cliente ou técnico..." 
                       value="<?= htmlspecialchars($termo) ?>">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Buscar
            </button>
            <a href="<?= BASE_URL ?>admin/relatorios/buscar-os" class="btn btn-info">
                <i class="fas fa-times"></i> Limpar
            </a>
        </form>
    </div>

    <?php if (!empty($termo)): ?>
        <?php if (!empty($osList)): ?>
            <div class="tabela-container">
                <div class="tabela-header">
                    <h4><i class="fas fa-list"></i> Resultados da Busca (<?= count($osList) ?> encontrada(s))</h4>
                </div>
                <div class="tabela-responsiva">
                    <table class="tabela">
                        <thead>
                            <tr>
                                <th>OS</th>
                                <th>Cliente</th>
                                <th>Técnico</th>
                                <th>Serviço</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($osList as $os): ?>
                                <tr>
                                    <td><strong>#<?= $os->id_os ?></strong></td>
                                    <td>
                                        <?php if ($os->tipo_pessoa === 'juridica'): ?>
                                            <strong><?= htmlspecialchars($os->razao_social ?? 'N/A') ?></strong>
                                            <br><small class="text-muted">Pessoa Jurídica</small>
                                        <?php else: ?>
                                            <strong><?= htmlspecialchars($os->nome_cli ?? 'N/A') ?></strong>
                                            <br><small class="text-muted">Pessoa Física</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($os->nome_tec ?? 'N/A') ?></td>
                                    <td>
                                        <strong><?= ucfirst($os->tipo_servico) ?></strong>
                                        <?php if ($os->servico_prestado): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars(substr($os->servico_prestado, 0, 50)) ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'aberta' => '#ffc107',
                                            'em andamento' => '#17a2b8', 
                                            'concluida' => '#28a745',
                                            'encerrada' => '#6c757d'
                                        ];
                                        $color = $statusColors[$os->status] ?? '#6c757d';
                                        ?>
                                        <span style="background: <?= $color ?>; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                                            <?= ucfirst($os->status) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= date('d/m/Y', strtotime($os->data_agendamento)) ?></strong>
                                        <br><small class="text-muted"><?= date('H:i', strtotime($os->data_agendamento)) ?></small>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                            <a href="<?= BASE_URL ?>admin/relatorios/os/<?= $os->id_os ?>" 
                                               style="background: #007bff; color: white; padding: 6px 10px; border-radius: 4px; text-decoration: none; font-size: 12px;">
                                                <i class="fas fa-file-alt"></i> Ver
                                            </a>
                                            <a href="<?= BASE_URL ?>admin/relatorios/pdf/os/<?= $os->id_os ?>" 
                                               style="background: #dc3545; color: white; padding: 6px 8px; border-radius: 4px; text-decoration: none; font-size: 12px;">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>admin/relatorios/excel/os/<?= $os->id_os ?>" 
                                               style="background: #28a745; color: white; padding: 6px 8px; border-radius: 4px; text-decoration: none; font-size: 12px;">
                                                <i class="fas fa-file-excel"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="alerta info">
                <i class="fas fa-exclamation-triangle"></i> 
                <div>
                    Nenhuma OS encontrada com o termo "<strong><?= htmlspecialchars($termo) ?></strong>".
                    <br><br>
                    <strong>Dicas de busca:</strong>
                    <ul style="margin: 10px 0 0 20px;">
                        <li>Digite apenas o número da OS (ex: 123)</li>
                        <li>Digite o nome completo ou parte do nome do cliente</li>
                        <li>Digite o nome do técnico responsável</li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="busca-vazia">
            <i class="fas fa-search"></i>
            <h4>Digite um termo para buscar</h4>
            <p>
                Use o campo acima para encontrar a OS que deseja gerar o relatório.<br>
                Você pode buscar por número da OS, nome do cliente ou técnico.
            </p>
        </div>
    <?php endif; ?>
</div>