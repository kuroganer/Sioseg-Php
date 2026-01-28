<div class="searcher-container"> 
    <h2>Consultar Técnicos</h2>

    <form action="<?= BASE_URL ?>admin/tecnicos/search" method="GET">
        <div class="form-group">
            <label for="tecName">Consultar pelo nome:</label>
            <input type="text" id="tecName" name="nome" placeholder="Digite o nome do técnico..." value="<?= htmlspecialchars($searchTerm); ?>">
        </div>
        <button type="submit">Consultar</button>
    </form>

    <div class="results-grid">
        <?php if (!empty($tecnicos)): ?>
            <?php foreach ($tecnicos as $tecnico): ?>
                <div class="result-card">
                    <div class="card-header">
                        <h3><?= htmlspecialchars($tecnico->nome_tec ?? '-') ?></h3>
                        <span class="status-badge status-<?= htmlspecialchars($tecnico->status ?? 'inativo') ?>">
                            <?= htmlspecialchars(ucfirst($tecnico->status ?? 'inativo')) ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <p><strong>CPF:</strong> <?= htmlspecialchars($tecnico->cpf_tec ?? '-') ?></p>
                        <p><strong>RG:</strong> <?= htmlspecialchars($tecnico->rg_tec ?? '-') ?> (<?= htmlspecialchars($tecnico->rg_emissor_tec ?? '-') ?>)</p>
                        <p><strong>Data Expedição RG:</strong> <?= !empty($tecnico->data_expedicao_rg_tec) ? htmlspecialchars(date('d/m/Y', strtotime($tecnico->data_expedicao_rg_tec))) : '-' ?></p>
                        <p><strong>Data Nascimento:</strong> <?= !empty($tecnico->data_nascimento_tec) ? htmlspecialchars(date('d/m/Y', strtotime($tecnico->data_nascimento_tec))) : '-' ?></p>
                        <p><strong>Telefone Pessoal:</strong> <?= htmlspecialchars($tecnico->tel_pessoal ?? '-') ?></p>
                        <p><strong>Telefone Empresa:</strong> <?= htmlspecialchars($tecnico->tel_empresa ?? '-') ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($tecnico->email_tec ?? '-') ?></p>
                    </div>
                    <div class="card-footer">
                        <a href="<?= BASE_URL ?>admin/tecnicos/edit/<?= $tecnico->id_tec ?>">Editar</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">
                <?= $searchTerm ? 'Nenhum técnico encontrado com esse nome.' : 'Digite um nome e clique em Consultar.'; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
