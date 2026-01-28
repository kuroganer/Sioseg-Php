<div class="form-container">
    <div class="form-sidebar">
        <header class="sidebar-header">
            <h2 class="sidebar-title">Editar Técnico</h2>
        </header>
    </div>
    <div class="form-main">
        <form action="<?= BASE_URL ?>admin/tecnicos/update/<?= $tecnico->id_tec ?? '' ?>" method="post">
            <div class="progress-bar"><div class="progress-bar-inner" id="progress-bar"></div></div>

            <?php if (!empty($edicao_erro)): ?>
                <div class="error-message"><?= htmlspecialchars($edicao_erro) ?></div>
            <?php endif; ?>

            <?php if (!empty($edicao_sucesso)): ?>
                <div class="success-message"><?= htmlspecialchars($edicao_sucesso) ?></div>
            <?php endif; ?>

            <div class="form-section">
                <h3 class="form-section-title">1. Identificação</h3>
                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label for="nome_tec">Nome Completo:</label>
                        <div class="input-group"><i class="fa-solid fa-user input-icon"></i><input type="text" id="nome_tec" name="nome_tec" value="<?= htmlspecialchars($tecnico->nome_tec ?? '') ?>" required></div>
                    </div>
                    <div class="form-group">
                        <label for="data_nascimento_tec">Data de Nascimento:</label>
                        <input type="date" id="data_nascimento_tec" name="data_nascimento_tec" value="<?= htmlspecialchars($tecnico->data_nascimento_tec ?? '') ?>" max="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="cpf_tec">CPF:</label>
                        <div class="input-group"><i class="fa-solid fa-id-card input-icon"></i><input type="text" id="cpf_tec" name="cpf_tec" value="<?= htmlspecialchars($tecnico->cpf_tec ?? '') ?>" placeholder="000.000.000-00" maxlength="14" required></div>
                    </div>
                    <div class="form-group">
                        <label for="rg_tec">RG:</label>
                        <div class="input-group"><i class="fa-solid fa-id-badge input-icon"></i><input type="text" id="rg_tec" name="rg_tec" value="<?= htmlspecialchars($tecnico->rg_tec ?? '') ?>" maxlength="12" placeholder="00.000.000-0" required></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="rg_emissor_tec">Órgão Emissor RG:</label>
                        <div class="input-group"><i class="fa-solid fa-building-columns input-icon"></i><input type="text" id="rg_emissor_tec" name="rg_emissor_tec" value="<?= htmlspecialchars($tecnico->rg_emissor_tec ?? '') ?>"></div>
                    </div>
                    <div class="form-group">
                        <label for="data_expedicao_rg_tec">Data de Expedição RG:</label>
                        <input type="date" id="data_expedicao_rg_tec" name="data_expedicao_rg_tec" value="<?= htmlspecialchars($tecnico->data_expedicao_rg_tec ?? '') ?>" max="<?= date('Y-m-d') ?>">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">2. Contato</h3>
                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label for="email_tec">Email:</label>
                        <div class="input-group"><i class="fa-solid fa-envelope input-icon"></i><input type="email" id="email_tec" name="email_tec" value="<?= htmlspecialchars($tecnico->email_tec ?? '') ?>" required></div>
                    </div>
                    <div class="form-group">
                        <label for="tel_pessoal">Telefone Pessoal:</label>
                        <div class="input-group"><i class="fa-solid fa-phone input-icon"></i><input type="text" id="tel_pessoal" name="tel_pessoal" value="<?= htmlspecialchars($tecnico->tel_pessoal ?? '') ?>" required placeholder="(00) 90000-0000" maxlength="15"></div>
                    </div>
                    <div class="form-group">
                        <label for="tel_empresa">Telefone da Empresa:</label>
                        <div class="input-group"><i class="fa-solid fa-building input-icon"></i><input type="text" id="tel_empresa" name="tel_empresa" value="<?= htmlspecialchars($tecnico->tel_empresa ?? '') ?>" placeholder="(00) 90000-0000" maxlength="15"></div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">3. Configuração do Sistema</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="senha">Nova Senha:</label>
                        <div class="input-group"><i class="fa-solid fa-lock input-icon"></i><input type="password" id="senha" name="senha"></div>
                        <small>Deixe em branco para manter a senha atual. Mínimo de 6 caracteres.</small>
                    </div>
                    <div class="form-group">
                        <label for="confirmar_senha">Confirmar Nova Senha:</label>
                        <div class="input-group"><i class="fa-solid fa-lock input-icon"></i><input type="password" id="confirmar_senha" name="confirmar_senha"></div>
                        <div id="password-feedback" class="password-feedback"></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status do Técnico:</label>
                        <select id="status" name="status" required>
                            <option value="ativo" <?= ($tecnico->status ?? '') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                            <option value="inativo" <?= ($tecnico->status ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <button type="submit">Salvar Alterações</button>
            </div>

            <p class="mt-2" style="text-align: center; margin-top: 20px;">
                <a href="<?= BASE_URL ?>admin/tecnicos" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">Voltar à lista de técnicos</a>
            </p>
        </form>
    </div>
</div>
