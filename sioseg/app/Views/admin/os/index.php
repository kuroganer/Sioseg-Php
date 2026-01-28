<div class="list-container">
    <h2>Lista de Ordens de Serviço</h2>

    <!-- Filtros -->
    <div class="filter-container">
        <div class="filter-row">
            <div class="filter-group">
                <label class="filter-label">Status:</label>
                <select id="filtroStatus" class="filter-select" style="min-width:160px;">
                    <option value="">Todos</option>
                    <option value="aberta">Aberta</option>
                    <option value="em andamento">Em Andamento</option>
                    <option value="concluida">Concluída</option>
                    <option value="encerrada">Encerrada</option>
                    <option value="pendente">Aguardando Confirmação</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Tipo:</label>
                <select id="filtroTipo" class="filter-select">
                    <option value="">Todos</option>
                    <option value="instalacao">Instalação</option>
                    <option value="manutencao">Manutenção</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Mensagens de sucesso ou erro -->
    <?php if (!empty($sucesso)): ?>
        <div class="success-message"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>

    <?php if (!empty($erro)): ?>
        <div class="error-message"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <!-- Tabela de OS -->
    <?php if (!empty($osList)): ?>
        <table>
            <thead>
                <tr>
                    <th>Nº OS</th>                    
                    <th>Tipo de Serviço</th>  
                     <th>Cliente</th>
                    <th>Técnico</th>
                    <th>Usuário</th>                   
                    <th>Data Abertura</th>
                    <th>Data Agendamento</th>
                    <th>Data Encerramento</th> 
                    <th>Serviço Prestado</th>                    
                     <th>Status</th>                
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($osList as $os): ?>
                    <tr>
                        <td><?= htmlspecialchars($os->id_os ?? '-') ?></td>                    
                        <td><?= htmlspecialchars(ucfirst($os->tipo_servico) ?? '-') ?></td>     
                                <?php
                                    // Preferir razão social para pessoa jurídica, depois nome_social, e por fim nome_cli
                                    $nomeCliente = $os->nome_cli ?? '';
                                    if (isset($os->tipo_pessoa) && $os->tipo_pessoa === 'juridica' && !empty($os->razao_social)) {
                                        $nomeCliente = $os->razao_social;
                                    } elseif (!empty($os->nome_social)) {
                                        $nomeCliente = $os->nome_social;
                                    }
                                ?>
                                <td><?= htmlspecialchars($nomeCliente ?: '-') ?></td>
                        <td><?= htmlspecialchars($os->nome_tec ?? '-') ?></td>
                        <td><?= htmlspecialchars($os->nome_usu ?? '-') ?></td>                     
                        <td><?= !empty($os->data_abertura) ? htmlspecialchars(date('d/m/Y H:i', strtotime($os->data_abertura))) : '-' ?></td>
                                   <td><?= !empty($os->data_agendamento) ? htmlspecialchars(date('d/m/Y H:i', strtotime($os->data_agendamento))) : '-' ?></td>
                        <td><?= !empty($os->data_encerramento) ? htmlspecialchars(date('d/m/Y H:i', strtotime($os->data_encerramento))) : '-' ?></td>
                            <td><?= htmlspecialchars($os->servico_prestado ?? '-') ?></td>         
                        <td><?= htmlspecialchars(ucwords($os->status) ?? '-') ?></td>                  
                                    <td class="action-cell">
                        <div class="actions-wrapper">
                            <!-- Botão Editar -->
                            <a href="<?= BASE_URL ?>admin/os/edit/<?= $os->id_os ?>" class="action-button edit-button">Editar</a>

                            <?php if ($os->status === 'concluida'): ?>
                                <button type="button" class="action-button btn-view-evaluation" data-os-id="<?= htmlspecialchars($os->id_os) ?>">Ver Avaliação</button>
                            <?php endif; ?>

                            <!-- Select de status estilizado: ocultar quando já estiver concluída -->
                            <?php if (!in_array($os->status, ['concluida', 'encerrada'])): ?>
                                <form action="<?= BASE_URL ?>admin/os/changeStatus" method="post" class="status-form">
                                    <input type="hidden" name="id" value="<?= $os->id_os ?>">

                                    <select name="status" class="status-select">
                                        <option value="aberta" <?= $os->status === 'aberta' ? 'selected' : '' ?>>Aberta</option>
                                        <option value="em andamento" <?= $os->status === 'em andamento' ? 'selected' : '' ?>>Em andamento</option>
                                        <option value="encerrada" <?= $os->status === 'encerrada' ? 'selected' : '' ?>>Encerrada</option>
                                    </select>

                                    <button type="submit" class="action-button">Alterar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nenhuma ordem de serviço encontrada.</p>
    <?php endif; ?>
    
    <?php if (isset($pagination)): ?>
        <?= $pagination->renderPaginationControls() ?>
    <?php endif; ?>
</div>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/pagination.css">

<script>
// Filtros da tabela de OS
function aplicarFiltros() {
    const filtroStatus = document.getElementById('filtroStatus').value.toLowerCase();
    const filtroTipo = document.getElementById('filtroTipo').value.toLowerCase();
    
    const linhas = document.querySelectorAll('tbody tr');
    
    linhas.forEach(linha => {
        const status = linha.cells[9].textContent.toLowerCase();
        const tipo = linha.cells[1].textContent.toLowerCase();
        
        // Verifica se é "pendente" (aguardando confirmação)
        const isPendente = status === 'em andamento' && linha.querySelector('.status-select');
        const statusFiltro = filtroStatus === 'pendente' ? isPendente : 
                           filtroStatus === '' || status.includes(filtroStatus);
        
        const mostrar = statusFiltro && (filtroTipo === '' || tipo.includes(filtroTipo));
        
        linha.style.display = mostrar ? '' : 'none';
    });
}

// Event listeners para filtragem automática
document.getElementById('filtroStatus').addEventListener('change', aplicarFiltros);
document.getElementById('filtroTipo').addEventListener('change', aplicarFiltros);
</script>
