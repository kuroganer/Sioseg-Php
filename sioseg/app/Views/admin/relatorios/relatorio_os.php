<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/relatorios.css">

<div class="relatorios-container">
    <div class="relatorios-header">
        <h1><i class="fas fa-file-alt"></i> Relatório OS #<?= $os->id_os ?></h1>
        <p>Relatório detalhado da Ordem de Serviço</p>
    </div>

    <div class="acoes-container">
        <a href="<?= BASE_URL ?>admin/relatorios/buscar-os" class="btn-acao btn-voltar">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <a href="<?= BASE_URL ?>admin/relatorios/pdf/os/<?= $os->id_os ?>" class="btn-acao btn-pdf">
            <i class="fas fa-file-pdf"></i> PDF
        </a>
        <a href="<?= BASE_URL ?>admin/relatorios/excel/os/<?= $os->id_os ?>" class="btn-acao btn-excel">
            <i class="fas fa-file-excel"></i> Excel
        </a>
        <button onclick="window.print()" class="btn-acao btn-imprimir">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>

    <div class="tabela-container">
        <div class="tabela-header">
            <h4><i class="fas fa-info-circle"></i> Informações da Ordem de Serviço</h4>
        </div>
        <div style="padding: 25px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <div>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr class="table-row-border">
                            <td class="table-cell-label">OS Nº:</td>
                            <td class="table-cell-value">#<?= $os->id_os ?></td>
                        </tr>
                        <tr class="table-row-border">
                            <td class="table-cell-label">Status:</td>
                            <td style="padding: 12px 0;">
                                <?php
                                $statusColors = [
                                    'aberta' => '#ffc107',
                                    'em andamento' => '#17a2b8', 
                                    'concluida' => '#28a745',
                                    'encerrada' => '#343a40'
                                ];
                                $color = $statusColors[$os->status] ?? '#6c757d';
                                ?>
                                <span style="background: <?= $color ?>; color: white; padding: 4px 12px; border-radius: 15px; font-size: 12px; font-weight: bold;">
                                    <?= ucfirst($os->status) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="table-cell-label">Tipo de Serviço:</td>
                            <td class="table-cell-value"><?= ucfirst($os->tipo_servico) ?></td>
                        </tr>
                    </table>
                </div>
                <div>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr class="table-row-border">
                            <td class="table-cell-label">Data Abertura:</td>
                            <td class="table-cell-value"><?= date('d/m/Y H:i', strtotime($os->data_abertura)) ?></td>
                        </tr>
                        <tr class="table-row-border">
                            <td class="table-cell-label">Data Agendamento:</td>
                            <td class="table-cell-value"><?= date('d/m/Y H:i', strtotime($os->data_agendamento)) ?></td>
                        </tr>
                        <?php if ($os->data_encerramento): ?>
                        <tr class="table-row-border">
                            <td class="table-cell-label">Data Encerramento:</td>
                            <td class="table-cell-value"><?= date('d/m/Y H:i', strtotime($os->data_encerramento)) ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td class="table-cell-label">Responsável:</td>
                            <td class="table-cell-value"><?= htmlspecialchars($os->responsavel) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Serviço Prestado/Observação -->
    <div class="tabela-container" style="margin-bottom: 30px;">
        <div class="tabela-header">
            <h4><i class="fas fa-clipboard-list"></i> Serviço Prestado/Observação</h4>
        </div>
        <div style="padding: 25px;">
            <div class="servico-container">
                <?= $os->servico_prestado ? nl2br(htmlspecialchars($os->servico_prestado)) : '<em class="text-muted">Não informado</em>' ?>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
        <!-- Informações do Cliente -->
        <div class="tabela-container">
            <div class="tabela-header">
                <h4><i class="fas fa-user"></i> Dados do Cliente</h4>
            </div>
            <div style="padding: 25px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr class="table-row-border">
                        <td class="table-cell-label">Nome/Razão Social:</td>
                        <td style="padding: 12px 0;">
                            <?= $os->tipo_pessoa === 'juridica' 
                                ? htmlspecialchars($os->razao_social) 
                                : htmlspecialchars($os->nome_cli) ?>
                        </td>
                    </tr>
                    <tr class="table-row-border">
                        <td class="table-cell-label">Tipo:</td>
                        <td class="table-cell-value"><?= $os->tipo_pessoa === 'juridica' ? 'Pessoa Jurídica' : 'Pessoa Física' ?></td>
                    </tr>
                    <tr class="table-row-border">
                        <td class="table-cell-label">Telefone:</td>
                        <td class="table-cell-value"><?= htmlspecialchars($os->tel1_cli) ?></td>
                    </tr>
                    <tr>
                        <td class="table-cell-label" style="vertical-align: top;">Endereço:</td>
                        <td class="table-cell-value">
                            <?= htmlspecialchars($os->endereco) ?><br>
                            <?= htmlspecialchars($os->bairro) ?> - <?= htmlspecialchars($os->cidade) ?>/<?= $os->uf ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Informações do Técnico -->
        <div class="tabela-container">
            <div class="tabela-header">
                <h4><i class="fas fa-wrench"></i> Técnico Responsável</h4>
            </div>
            <div style="padding: 25px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr class="table-row-border">
                        <td class="table-cell-label">Nome:</td>
                        <td class="table-cell-value"><?= htmlspecialchars($os->nome_tec) ?></td>
                    </tr>
                    <tr class="table-row-border">
                        <td class="table-cell-label">Telefone:</td>
                        <td class="table-cell-value"><?= htmlspecialchars($os->tel_tecnico) ?></td>
                    </tr>
                    <tr class="table-row-border">
                        <td class="table-cell-label">Status Técnico:</td>
                        <td style="padding: 12px 0;">
                            <span style="background: <?= $os->conclusao_tecnico === 'concluida' ? '#28a745' : '#ffc107' ?>; color: white; padding: 4px 12px; border-radius: 15px; font-size: 12px; font-weight: bold;">
                                <?= ucfirst($os->conclusao_tecnico) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="table-cell-label">Status Cliente:</td>
                        <td style="padding: 12px 0;">
                            <span style="background: <?= $os->conclusao_cliente === 'concluida' ? '#28a745' : '#ffc107' ?>; color: white; padding: 4px 12px; border-radius: 15px; font-size: 12px; font-weight: bold;">
                                <?= ucfirst($os->conclusao_cliente) ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="tabela-container">
        <div class="tabela-header">
            <h4><i class="fas fa-box"></i> Materiais Utilizados</h4>
        </div>
        <?php if (!empty($materiais)): ?>
            <div class="tabela-responsiva">
                <table class="tabela">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Quantidade</th>
                            <th>Descrição</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materiais as $material): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($material['nome']) ?></strong></td>
                                <td><?= htmlspecialchars($material['marca']) ?></td>
                                <td><?= htmlspecialchars($material['modelo']) ?></td>
                                <td>
                                    <span style="background: #007bff; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                                        <?= $material['qtd_usada'] ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($material['descricao']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-info-circle empty-state-icon"></i>
                <p>Nenhum material foi utilizado nesta OS.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($os->nota): ?>
        <div class="tabela-container">
            <div class="tabela-header">
                <h4><i class="fas fa-star"></i> Avaliação do Cliente</h4>
            </div>
            <div style="padding: 25px;">
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 30px; align-items: center;">
                    <div style="text-align: center;">
                        <div style="font-size: 3rem; color: #ffc107; margin-bottom: 10px;">
                            <?= $os->nota ?> ★
                        </div>
                        <p class="text-muted" style="margin: 0;">Nota do Serviço</p>
                    </div>
                    <div>
                        <?php if ($os->comentario): ?>
                            <div style="font-weight: bold; color: var(--color-text-primary); margin-bottom: 10px;">Comentário:</div>
                            <div class="comentario-container">
                                <?= nl2br(htmlspecialchars($os->comentario)) ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted" style="font-style: italic; margin: 0;">
                                Nenhum comentário foi deixado pelo cliente.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>