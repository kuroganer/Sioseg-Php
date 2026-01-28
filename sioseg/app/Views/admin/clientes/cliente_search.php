<div class="searcher-container"> 
    <h2>Consultar Clientes</h2>

    <form action="<?= BASE_URL ?>admin/clientes/search" method="GET">
        <div class="form-group">
            <label for="clientName">Consultar por nome ou CPF/CNPJ:</label>
            <input type="text" id="clientName" name="nome" placeholder="Digite o nome, CPF ou CNPJ do cliente..." value="<?= htmlspecialchars($searchTerm); ?>">
        </div>
        <button type="submit">Consultar</button>
    </form>

    <div class="results-grid">
        <?php if (!empty($clientes)): ?>
            <?php foreach ($clientes as $cliente): ?>
                <div class="result-card">
                    <div class="card-header">
                        <h3><?= htmlspecialchars($cliente->tipo_pessoa === 'fisica' ? ($cliente->nome_cli ?? '-') : ($cliente->razao_social ?? '-')) ?></h3>
                        <span class="status-badge status-<?= htmlspecialchars($cliente->status ?? 'inativo') ?>">
                            <?= htmlspecialchars(ucfirst($cliente->status ?? 'inativo')) ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <p><i class="fa-solid fa-envelope"></i> <strong>Email:</strong> <?= htmlspecialchars($cliente->email_cli ?? '-') ?></p>
                        <p><i class="fa-solid fa-phone"></i> <strong>Telefone:</strong> <?= htmlspecialchars($cliente->tel1_cli ?? '-') ?></p>
                        <?php if ($cliente->tipo_pessoa === 'fisica'): ?>
                            <p><i class="fa-solid fa-id-card"></i> <strong>CPF:</strong> <?= htmlspecialchars($cliente->cpf_cli ?? '-') ?></p>
                            <p><i class="fa-solid fa-id-badge"></i> <strong>RG:</strong> <?= htmlspecialchars($cliente->rg_cli ?? '-') ?></p>
                        <?php else: ?>
                            <p><i class="fa-solid fa-building"></i> <strong>Razão Social:</strong> <?= htmlspecialchars($cliente->razao_social ?? '-') ?></p>
                            <p><i class="fa-solid fa-id-card"></i> <strong>CNPJ:</strong> <?= htmlspecialchars($cliente->cnpj ?? '-') ?></p>
                        <?php endif; ?>
                        <p><i class="fa-solid fa-map-marker-alt"></i> <strong>Endereço:</strong> <?= htmlspecialchars($cliente->endereco ?? '-') ?>, <?= htmlspecialchars($cliente->num_end ?? 's/n') ?></p>
                        <p><i class="fa-solid fa-calendar-plus"></i> <strong>Cliente desde:</strong> <?= !empty($cliente->data_cadastro_cli) ? htmlspecialchars(date('d/m/Y', strtotime($cliente->data_cadastro_cli))) : '-' ?></p>
                    </div>
                    <div class="card-footer">
                        <a href="<?= BASE_URL ?>admin/clientes/edit/<?= $cliente->id_cli ?>">Editar</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">
                <?= $searchTerm ? 'Nenhum cliente encontrado com esse termo.' : 'Digite um nome, CPF ou CNPJ e clique em Consultar.'; ?>
            </div>
        <?php endif; ?>
       
    </div>
</div>
