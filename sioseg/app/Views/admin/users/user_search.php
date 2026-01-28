<div class="searcher-container"> 
    <h2>Consultar Usuários</h2>

    <form action="<?= BASE_URL ?>admin/users/search" method="GET">
        <div class="form-group">
            <label for="userName">Consultar pelo nome:</label>
            <input type="text" id="userName" name="nome" placeholder="Digite o nome do usuário..." value="<?= htmlspecialchars($searchTerm); ?>">
        </div>
        <button type="submit">Consultar</button>
    </form>

    <div class="results-grid">
        <?php if (!empty($usuarios)): ?>
            <?php foreach ($usuarios as $usuario): ?>
                <div class="result-card">
                    <div class="card-header">
                        <h3><?= htmlspecialchars($usuario->nome_usu ?? '-') ?></h3>
                        <span class="status-badge status-<?= ($usuario->status ?? '') === 'ativo' ? 'ativo' : 'inativo' ?>">
                            <?= ($usuario->status ?? '') === 'ativo' ? 'Ativo' : 'Inativo' ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <p><strong>CPF:</strong> <?= htmlspecialchars($usuario->cpf_usu ?? '-') ?></p>
                        <p><strong>RG:</strong> <?= htmlspecialchars($usuario->rg_usu ?? '-') ?> (<?= htmlspecialchars($usuario->rg_emissor_usu ?? '-') ?>)</p>
                        <p><strong>Data Expedição RG:</strong> <?= !empty($usuario->data_expedicao_rg_usu) ? htmlspecialchars(date('d/m/Y', strtotime($usuario->data_expedicao_rg_usu))) : '-' ?></p>
                        <p><strong>Data Nascimento:</strong> <?= !empty($usuario->data_nascimento_usu) ? htmlspecialchars(date('d/m/Y', strtotime($usuario->data_nascimento_usu))) : '-' ?></p>
                        <p><strong>Telefone 1:</strong> <?= htmlspecialchars($usuario->tel1_usu ?? '-') ?></p>
                        <p><strong>Telefone 2:</strong> <?= htmlspecialchars($usuario->tel2_usu ?? '-') ?></p>
                        <p><strong>Telefone 3:</strong> <?= htmlspecialchars($usuario->tel3_usu ?? '-') ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($usuario->email_usu ?? '-') ?></p>
                        <p><strong>Perfil:</strong> <?= htmlspecialchars($usuario->perfil ?? '-') ?></p>
                    </div>
                    <div class="card-footer">
                        <a href="<?= BASE_URL ?>admin/users/edit/<?= $usuario->id_usu ?>">Editar</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">
                <?= $searchTerm ? 'Nenhum usuário encontrado com esse nome.' : 'Digite um nome e clique em Consultar.'; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
