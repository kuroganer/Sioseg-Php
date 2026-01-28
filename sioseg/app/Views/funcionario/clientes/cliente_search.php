<div class="searcher-container"> 
    <h2>Consultar Clientes</h2>

    <form action="<?= BASE_URL ?>funcionario/clientes/search" method="GET">
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
                        <p><strong>Nome Social:</strong> <?= htmlspecialchars($cliente->nome_social ?? '-') ?></p>
                        <p><strong>CPF:</strong> <?= htmlspecialchars($cliente->cpf_cli ?? '-') ?></p>
                        <p><strong>RG:</strong> <?= htmlspecialchars($cliente->rg_cli ?? '-') ?> (<?= htmlspecialchars($cliente->rg_emissor_cli ?? '-') ?>)</p>
                        <p><strong>Data Expedição RG:</strong> <?= !empty($cliente->data_expedicao_rg_cli) ? htmlspecialchars(date('d/m/Y', strtotime($cliente->data_expedicao_rg_cli))) : '-' ?></p>
                        <p><strong>Data Nascimento:</strong> <?= !empty($cliente->data_nascimento_cli) ? htmlspecialchars(date('d/m/Y', strtotime($cliente->data_nascimento_cli))) : '-' ?></p>
                        <p><strong>Data Cadastro:</strong> <?= !empty($cliente->data_cadastro_cli) ? htmlspecialchars(date('d/m/Y', strtotime($cliente->data_cadastro_cli))) : '-' ?></p>
                        <p><strong>Telefone 1:</strong> <?= htmlspecialchars($cliente->tel1_cli ?? '-') ?></p>
                        <p><strong>Telefone 2:</strong> <?= htmlspecialchars($cliente->tel2_cli ?? '-') ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($cliente->email_cli ?? '-') ?></p>
                        <p><strong>Tipo Pessoa:</strong> <?= htmlspecialchars(ucfirst($cliente->tipo_pessoa) ?? '-') ?></p>
                        <p><strong>Razão Social:</strong> <?= htmlspecialchars($cliente->razao_social ?? '-') ?></p>
                        <p><strong>CNPJ:</strong> <?= htmlspecialchars($cliente->cnpj ?? '-') ?></p>
                        <p><strong>Endereço:</strong> <?= htmlspecialchars($cliente->endereco ?? '-') ?>, <?= htmlspecialchars($cliente->bairro ?? '-') ?>, <?= htmlspecialchars($cliente->cidade ?? '-') ?> - <?= htmlspecialchars($cliente->uf ?? '-') ?>, CEP: <?= htmlspecialchars($cliente->cep ?? '-') ?></p>
                        <p><strong>Complemento:</strong> <?= htmlspecialchars($cliente->complemento ?? '-') ?></p>
                        <p><strong>Ponto de Referência:</strong> <?= htmlspecialchars($cliente->ponto_referencia ?? '-') ?></p>
                    </div>
                    <div class="card-footer">
                        <a href="<?= BASE_URL ?>funcionario/clientes/edit/<?= $cliente->id_cli ?>">Editar</a>
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
