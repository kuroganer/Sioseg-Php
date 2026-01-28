<div class="list-container">
    <h2>Lista de Clientes</h2>

    <!-- Mensagens de sucesso ou erro -->
    <?php if (!empty($sucesso)): ?>
        <div class="success-message"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>

    <?php if (!empty($erro)): ?>
        <div class="error-message"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="filter-container">
        <div class="filter-row">
            <div class="filter-group">
                <label class="filter-label">Status:</label>
                <select id="filtroStatus" class="filter-select" style="min-width:120px;">
                    <option value="">Todos</option>
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Tipo:</label>
                <select id="filtroTipo" class="filter-select">
                    <option value="">Todos</option>
                    <option value="fisica">Pessoa Física</option>
                    <option value="juridica">Pessoa Jurídica</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Tabela de clientes -->
    <?php if (!empty($clientes)): ?>
        <div class="table-wrapper" style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; min-width:1400px;">
            <thead>
                <tr>
                    <th>Nome/Razão Social</th>
                    <th>Email</th>
                    <th>Telefone 1</th>
                    <th>Tipo Pessoa</th>
                    <th>CPF/CNPJ</th>
                    <th>RG/Inscrição</th>
                    <th>Data Nasc./Fundação</th>
                    <th>Data Cadastro</th>
                    <th>Endereço</th>
                    <th>Cidade</th>
                    <th>CEP</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $cliente): ?>
                    <tr class="client-row<?= ($cliente->status ?? '') === 'inativo' ? ' status-inativo' : '' ?>" data-type="<?= htmlspecialchars($cliente->tipo_pessoa ?? '') ?>">
                        <td><?= htmlspecialchars($cliente->tipo_pessoa === 'juridica' ? ($cliente->razao_social ?? '-') : ($cliente->nome_cli ?? '-')) ?></td>
                        <td class="table-system__cell--email"><?= htmlspecialchars($cliente->email_cli ?? '-') ?></td>
                        <td class="table-system__cell--phone"><?= htmlspecialchars($cliente->tel1_cli ?? '-') ?></td>
                        <td><?= htmlspecialchars(ucfirst($cliente->tipo_pessoa) ?? '-') ?></td>
                        <td class="<?= $cliente->tipo_pessoa === 'juridica' ? 'table-system__cell--cnpj' : 'table-system__cell--cpf' ?>"><?= htmlspecialchars($cliente->tipo_pessoa === 'juridica' ? ($cliente->cnpj ?? '-') : ($cliente->cpf_cli ?? '-')) ?></td>
                        <td><?= $cliente->tipo_pessoa === 'juridica' ? '-' : htmlspecialchars($cliente->rg_cli ?? '-') ?></td>
                        <td class="table-system__cell--date"><?= $cliente->tipo_pessoa === 'juridica' ? '-' : (!empty($cliente->data_nascimento_cli) ? htmlspecialchars(date('d/m/Y', strtotime($cliente->data_nascimento_cli))) : '-') ?></td>
                        <td class="table-system__cell--date"><?= !empty($cliente->data_cadastro_cli) ? htmlspecialchars(date('d/m/Y', strtotime($cliente->data_cadastro_cli))) : '-' ?></td>
                        <td><?= htmlspecialchars($cliente->endereco ?? '-') ?></td>
                        <td><?= htmlspecialchars($cliente->cidade ?? '-') ?></td>
                        <td class="table-system__cell--cep"><?= htmlspecialchars($cliente->cep ?? '-') ?></td>
                        <td><?= ($cliente->status ?? '') === 'ativo' ? 'Ativo' : 'Inativo' ?></td>
                        <td class="action-cell">
                            <div class="actions-wrapper">
                                <!-- Link para edição -->
                                <a href="<?= BASE_URL ?>admin/clientes/edit/<?= $cliente->id_cli ?>" class="action-button edit-button">Editar</a>

                                <!-- Formulário para alterar status -->
                                <form action="<?= BASE_URL ?>admin/clientes/changeStatus" method="post" class="status-form">
                                    <input type="hidden" name="id" value="<?= $cliente->id_cli ?>">
                                    <input type="hidden" name="status" value="<?= $cliente->status === 'ativo' ? 'inativo' : 'ativo' ?>">
                                    <button type="submit" class="action-button"><?= $cliente->status === 'ativo' ? 'Desativar' : 'Ativar' ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php else: ?>
        <p>Nenhum cliente encontrado.</p>
    <?php endif; ?>
    
    <?php if (isset($pagination)): ?>
        <?= $pagination->renderPaginationControls() ?>
    <?php endif; ?>
</div>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/pagination.css">

<script>
// Filtros da tabela de clientes
function aplicarFiltros() {
    const filtroStatus = document.getElementById('filtroStatus').value.toLowerCase();
    const filtroTipo = document.getElementById('filtroTipo').value.toLowerCase();
    
    const linhas = document.querySelectorAll('tbody tr');
    
    linhas.forEach(linha => {
        const status = linha.cells[11].textContent.toLowerCase();
        const tipo = linha.getAttribute('data-type').toLowerCase();
        
        const mostrar = (filtroStatus === '' || status.includes(filtroStatus)) &&
                       (filtroTipo === '' || tipo.includes(filtroTipo));
        
        linha.style.display = mostrar ? '' : 'none';
    });
}

// Event listeners para filtragem automática
document.getElementById('filtroStatus').addEventListener('change', aplicarFiltros);
document.getElementById('filtroTipo').addEventListener('change', aplicarFiltros);
</script>
