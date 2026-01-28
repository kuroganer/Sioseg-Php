<div class="form-container">
    <div class="form-sidebar">
        <header class="sidebar-header">
            <h2 class="sidebar-title">Editar Usuário</h2>
        </header>
    </div>
    <div class="form-main">
        <form action="<?= BASE_URL ?>admin/users/update/<?= $user->id_usu ?? '' ?>" method="post">
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
                        <label for="nome_usu">Nome Completo:</label>
                        <div class="input-group"><i class="fa-solid fa-user input-icon"></i><input type="text" id="nome_usu" name="nome_usu" value="<?= htmlspecialchars($user->nome_usu ?? '') ?>" required></div>
                    </div>
                    <div class="form-group">
                        <label for="data_nascimento_usu">Data de Nascimento:</label>
                        <input type="date" id="data_nascimento_usu" name="data_nascimento_usu" value="<?= htmlspecialchars($user->data_nascimento_usu ?? '') ?>" max="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="cpf_usu">CPF:</label>
                        <div class="input-group"><i class="fa-solid fa-id-card input-icon"></i><input type="text" id="cpf_usu" name="cpf_usu" value="<?= htmlspecialchars($user->cpf_usu ?? '') ?>" placeholder="000.000.000-00" maxlength="14" required></div>
                    </div>
                    <div class="form-group">
                        <label for="rg_usu">RG:</label>
                        <div class="input-group"><i class="fa-solid fa-id-badge input-icon"></i><input type="text" id="rg_usu" name="rg_usu" value="<?= htmlspecialchars($user->rg_usu ?? '') ?>" maxlength="12" placeholder="00.000.000-0" required></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="rg_emissor_usu">Órgão Emissor RG:</label>
                        <div class="input-group"><i class="fa-solid fa-building-columns input-icon"></i><input type="text" id="rg_emissor_usu" name="rg_emissor_usu" value="<?= htmlspecialchars($user->rg_emissor_usu ?? '') ?>"></div>
                    </div>
                    <div class="form-group">
                        <label for="data_expedicao_rg_usu">Data de Expedição RG:</label>
                        <input type="date" id="data_expedicao_rg_usu" name="data_expedicao_rg_usu" value="<?= htmlspecialchars($user->data_expedicao_rg_usu ?? '') ?>" max="<?= date('Y-m-d') ?>">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">2. Contato</h3>
                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <label for="email_usu">Email:</label>
                        <div class="input-group"><i class="fa-solid fa-envelope input-icon"></i><input type="email" id="email_usu" name="email_usu" value="<?= htmlspecialchars($user->email_usu ?? '') ?>" required></div>
                    </div>
                    <div class="form-group">
                        <label for="tel1_usu">Telefone Principal:</label>
                        <div class="input-group"><i class="fa-solid fa-phone input-icon"></i><input type="text" id="tel1_usu" name="tel1_usu" value="<?= htmlspecialchars($user->tel1_usu ?? '') ?>" required placeholder="(00) 90000-0000" maxlength="15"></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="tel2_usu">Telefone Secundário:</label>
                        <div class="input-group"><i class="fa-solid fa-phone input-icon"></i><input type="text" id="tel2_usu" name="tel2_usu" value="<?= htmlspecialchars($user->tel2_usu ?? '') ?>" placeholder="(00) 90000-0000" maxlength="15"></div>
                    </div>
                    <div class="form-group">
                        <label for="tel3_usu">Telefone Terciário:</label>
                        <div class="input-group"><i class="fa-solid fa-phone input-icon"></i><input type="text" id="tel3_usu" name="tel3_usu" value="<?= htmlspecialchars($user->tel3_usu ?? '') ?>" placeholder="(00) 90000-0000" maxlength="15"></div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">3. Configuração do Sistema</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="senha_hash_usu">Nova Senha:</label>
                        <div class="input-group"><i class="fa-solid fa-lock input-icon"></i><input type="password" id="senha_hash_usu" name="senha"></div>
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
                        <label for="perfil">Perfil do Usuário:</label>
                        <select id="perfil" name="perfil" required>
                            <?php
                            $perfis = ['admin', 'professor', 'secretaria', 'coordenador', 'suporte', 'administrativo', 'aluno', 'funcionario'];
                            foreach ($perfis as $perfil) {
                                $selected = ($user->perfil ?? '') === $perfil ? 'selected' : '';
                                echo "<option value=\"$perfil\" $selected>" . ucfirst($perfil) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Status do Usuário:</label>
                        <select id="status" name="status" required>
                            <option value="ativo" <?= ($user->status ?? '') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                            <option value="inativo" <?= ($user->status ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <button type="submit">Salvar Alterações</button>
            </div>

            <p class="mt-2" style="text-align: center; margin-top: 20px;">
                <a href="<?= BASE_URL ?>admin/users" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">Voltar à lista de usuários</a>
            </p>
        </form>
    </div>
</div>
