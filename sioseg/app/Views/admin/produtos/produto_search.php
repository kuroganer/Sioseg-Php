<div class="searcher-container"> 
    <h2>Consultar Produtos</h2>

    <form action="<?= BASE_URL ?>admin/produtos/search" method="GET">
        <div class="form-group">
            <label for="prodName">Consultar pelo nome:</label>
            <input type="text" id="prodName" name="nome" placeholder="Digite o nome do produto..." value="<?= htmlspecialchars($searchTerm); ?>">
        </div>
        <button type="submit">Consultar</button>
    </form>

    <div class="results-grid">
        <?php if (!empty($produtos)): ?>
            <?php foreach ($produtos as $produto): ?>
                <div class="result-card">
                    <div class="card-header">
                        <h3><?= htmlspecialchars($produto->nome ?? '-') ?></h3>
                        <span class="status-badge status-<?= htmlspecialchars(strtolower($produto->status ?? 'inativo')) ?>">
                            <?= htmlspecialchars(ucfirst($produto->status ?? 'inativo')) ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <p><strong>Marca:</strong> <?= htmlspecialchars($produto->marca ?? '-') ?></p>
                        <p><strong>Modelo:</strong> <?= htmlspecialchars($produto->modelo ?? '-') ?></p>
                        <p><strong>Descrição:</strong> <?= htmlspecialchars($produto->descricao ?? '-') ?></p>
                        <p><strong>Quantidade:</strong> <?= htmlspecialchars($produto->qtde ?? '0') ?></p>
                    </div>
                    <div class="card-footer">
                        <a href="<?= BASE_URL ?>admin/produtos/edit/<?= $produto->id_prod ?>">Editar</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">
                <?= $searchTerm ? 'Nenhum produto encontrado com esse nome.' : 'Digite um nome e clique em Consultar.'; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
