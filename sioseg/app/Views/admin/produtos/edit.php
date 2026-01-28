<div class="form-container">
    <div class="form-sidebar">
        <header class="sidebar-header">
            <h2 class="sidebar-title">Editar Produto</h2>
        </header>
    </div>
    <div class="form-main">
        <form action="<?= BASE_URL ?>admin/produtos/update/<?= $produto->id_prod ?>" method="post">
            <div class="progress-bar"><div class="progress-bar-inner" id="progress-bar"></div></div>

            <?php if (!empty($edicao_erro)): ?>
                <div class="error-message"><?= htmlspecialchars($edicao_erro) ?></div>
            <?php endif; ?>

            <?php if (!empty($edicao_sucesso)): ?>
                <div class="success-message"><?= htmlspecialchars($edicao_sucesso) ?></div>
            <?php endif; ?>

            <div class="form-section">
                <h3 class="form-section-title">1. Informações Básicas</h3>
                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label for="nome">Nome do Produto:</label>
                        <div class="input-group"><i class="fa-solid fa-tag input-icon"></i><input type="text" id="nome" name="nome" value="<?= htmlspecialchars($produto->nome ?? '') ?>" required></div>
                    </div>
                    <div class="form-group">
                        <label for="qtde">Quantidade:</label>
                        <div class="input-group"><i class="fa-solid fa-hashtag input-icon"></i><input type="number" id="qtde" name="qtde" value="<?= htmlspecialchars($produto->qtde ?? '') ?>" required></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="marca">Marca:</label>
                        <div class="input-group"><i class="fa-solid fa-copyright input-icon"></i><input type="text" id="marca" name="marca" value="<?= htmlspecialchars($produto->marca ?? '') ?>"></div>
                    </div>
                    <div class="form-group">
                        <label for="modelo">Modelo:</label>
                        <div class="input-group"><i class="fa-solid fa-microchip input-icon"></i><input type="text" id="modelo" name="modelo" value="<?= htmlspecialchars($produto->modelo ?? '') ?>"></div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">2. Descrição</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="descricao">Descrição do Produto:</label>
                        <div class="input-group"><i class="fa-solid fa-align-left input-icon"></i><textarea id="descricao" name="descricao" rows="4"><?= htmlspecialchars($produto->descricao ?? '') ?></textarea></div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">3. Configurações</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <div class="input-group"><i class="fa-solid fa-toggle-on input-icon"></i><select id="status" name="status" required>
                            <option value="">Selecione</option>
                            <option value="ativo" <?= ($produto->status ?? '') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                            <option value="inativo" <?= ($produto->status ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                        </select></div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <button type="submit">Salvar Alterações</button>
            </div>

            <p class="mt-2" style="text-align: center; margin-top: 20px;">
                <a href="<?= BASE_URL ?>admin/produtos" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">Voltar à lista de produtos</a>
            </p>
        </form>
    </div>
</div>
