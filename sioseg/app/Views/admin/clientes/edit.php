<div class="form-container">
    <div class="form-sidebar">
        <header class="sidebar-header">
            <h2 class="sidebar-title">Editar Cliente</h2>
        </header>
    </div>
    <div class="form-main">
        <form action="<?= BASE_URL ?>admin/clientes/update/<?= $cliente->id_cli ?>" method="post" data-page="edit">
            <div class="progress-bar"><div class="progress-bar-inner" id="progress-bar"></div></div>

            <?php if (!empty($edicao_erro)): ?>
                <div class="error-message"><?= htmlspecialchars($edicao_erro) ?></div>
            <?php endif; ?>

            <?php if (!empty($edicao_sucesso)): ?>
                <div class="success-message"><?= htmlspecialchars($edicao_sucesso) ?></div>
            <?php endif; ?>

            <div class="form-section">
                <h3 class="form-section-title">1. Identificação</h3>
                <div class="form-group">
                    <label for="tipo_pessoa">Tipo de Pessoa:</label>
                    <select id="tipo_pessoa" name="tipo_pessoa" required>
                        <option value="">Selecione</option>
                        <option value="fisica" <?= ($cliente->tipo_pessoa ?? '') === 'fisica' ? 'selected' : '' ?>>Física</option>
                        <option value="juridica" <?= ($cliente->tipo_pessoa ?? '') === 'juridica' ? 'selected' : '' ?>>Jurídica</option>
                    </select>
                </div>
            </div>

            <div id="main-form-content">
                <div class="form-section" id="fisica_fields">
                    <h3 class="form-section-title">2. Dados Pessoais</h3>
                    <div class="form-row">
                        <div class="form-group" style="flex: 2;">
                            <label for="nome_cli">Nome Completo:</label>
                            <div class="input-group"><i class="fa-solid fa-user input-icon"></i><input type="text" id="nome_cli" name="nome_cli" value="<?= htmlspecialchars($_POST['nome_cli'] ?? $cliente->nome_cli ?? '') ?>"></div>
                        </div>
                        <div class="form-group" style="flex: 2;">
                            <label for="nome_social">Nome Social (Opcional):</label>
                            <div class="input-group"><i class="fa-solid fa-user-tag input-icon"></i><input type="text" id="nome_social" name="nome_social" value="<?= htmlspecialchars(($_POST['nome_social'] ?? $cliente->nome_social) ?? '') ?>"></div>
                        </div>
                        <div class="form-group">
                            <label for="data_nascimento_cli">Data de Nascimento:</label>
                            <input type="date" id="data_nascimento_cli" name="data_nascimento_cli" value="<?= htmlspecialchars($_POST['data_nascimento_cli'] ?? $cliente->data_nascimento_cli ?? '') ?>" max="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="cpf_cli">CPF:</label>
                            <div class="input-group"><i class="fa-solid fa-id-card input-icon"></i><input type="text" id="cpf_cli" name="cpf_cli" value="<?= htmlspecialchars($_POST['cpf_cli'] ?? $cliente->cpf_cli ?? '') ?>" placeholder="000.000.000-00" maxlength="14"></div>
                        </div>
                        <div class="form-group">
                            <label for="rg_cli">RG:</label>
                            <div class="input-group"><i class="fa-solid fa-id-badge input-icon"></i><input type="text" id="rg_cli" name="rg_cli" value="<?= htmlspecialchars($_POST['rg_cli'] ?? $cliente->rg_cli ?? '') ?>" maxlength="12" placeholder="00.000.000-0"></div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="rg_emissor_cli">Órgão Emissor RG:</label>
                            <div class="input-group"><i class="fa-solid fa-building-columns input-icon"></i><input type="text" id="rg_emissor_cli" name="rg_emissor_cli" value="<?= htmlspecialchars($_POST['rg_emissor_cli'] ?? $cliente->rg_emissor_cli ?? '') ?>"></div>
                        </div>
                        <div class="form-group">
                            <label for="data_expedicao_rg_cli">Data de Expedição RG:</label>
                            <input type="date" id="data_expedicao_rg_cli" name="data_expedicao_rg_cli" value="<?= htmlspecialchars($_POST['data_expedicao_rg_cli'] ?? $cliente->data_expedicao_rg_cli ?? '') ?>" max="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section" id="juridica_fields">
                    <h3 class="form-section-title">2. Dados Empresariais</h3>
                    <div class="form-row">
                        <div class="form-group" style="flex: 2;">
                            <label for="razao_social">Razão Social:</label>
                            <div class="input-group"><i class="fa-solid fa-building input-icon"></i><input type="text" id="razao_social" name="razao_social" value="<?= htmlspecialchars($_POST['razao_social'] ?? $cliente->razao_social ?? '') ?>"></div>
                        </div>
                        <div class="form-group">
                            <label for="cnpj">CNPJ:</label>
                            <div class="input-group"><i class="fa-solid fa-id-card input-icon"></i><input type="text" id="cnpj" name="cnpj" value="<?= htmlspecialchars($_POST['cnpj'] ?? $cliente->cnpj ?? '') ?>" placeholder="00.000.000/0000-00" maxlength="18"></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">3. Contato e Acesso</h3>
                    <div class="form-row">
                        <div class="form-group" style="flex: 2;">
                            <label for="email_cli">Email:</label>
                            <div class="input-group"><i class="fa-solid fa-envelope input-icon"></i><input type="email" id="email_cli" name="email_cli" value="<?= htmlspecialchars($_POST['email_cli'] ?? $cliente->email_cli ?? '') ?>" required></div>
                        </div>
                        <div class="form-group">
                            <label for="tel1_cli">Telefone Principal:</label>
                            <div class="input-group"><i class="fa-solid fa-phone input-icon"></i><input type="text" id="tel1_cli" name="tel1_cli" value="<?= htmlspecialchars($_POST['tel1_cli'] ?? $cliente->tel1_cli ?? '') ?>" required placeholder="(00) 90000-0000" maxlength="15"></div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="tel2_cli">Telefone Secundário (Opcional):</label>
                            <div class="input-group"><i class="fa-solid fa-phone input-icon"></i><input type="text" id="tel2_cli" name="tel2_cli" value="<?= htmlspecialchars($_POST['tel2_cli'] ?? $cliente->tel2_cli ?? '') ?>" placeholder="(00) 90000-0000" maxlength="15"></div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="senha_hash_cli">Nova Senha:</label>
                            <div class="input-group"><i class="fa-solid fa-lock input-icon"></i><input type="password" id="senha_hash_cli" name="senha"></div>
                            <small>Deixe em branco para manter a senha atual. Mínimo 6 caracteres.</small>
                        </div>
                        <div class="form-group">
                            <label for="confirmar_senha">Confirmar Nova Senha:</label>
                            <div class="input-group"><i class="fa-solid fa-lock input-icon"></i><input type="password" id="confirmar_senha" name="confirmar_senha"></div>
                            <div id="password-feedback" class="password-feedback"></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">4. Endereço</h3>
                    <div class="form-row">
                        <div class="form-group" style="flex: 1;">
                            <label for="cep">CEP:</label>
                            <div class="input-group"><i class="fa-solid fa-map-pin input-icon"></i><input type="text" id="cep" name="cep" value="<?= htmlspecialchars($_POST['cep'] ?? $cliente->cep ?? '') ?>" maxlength="9" placeholder="00000-000"></div>
                            <span id="cep-loading" class="cep-loading">Buscando CEP...</span>
                        </div>
                        <div class="form-group" style="flex: 3;">
                            <label for="logradouro">Logradouro (Rua/Avenida):</label>
                            <div class="input-group"><i class="fa-solid fa-road input-icon"></i><input type="text" id="logradouro" name="logradouro" value="<?= htmlspecialchars($_POST['logradouro'] ?? $cliente->logradouro ?? '') ?>" required></div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="flex: 1;">
                            <label for="num_end">Número:</label>
                            <div class="input-group"><i class="fa-solid fa-hashtag input-icon"></i><input type="text" id="num_end" name="num_end" value="<?= htmlspecialchars($_POST['num_end'] ?? $cliente->num_end ?? '') ?>" required></div>
                        </div>
                        <div class="form-group" style="flex: 2;">
                            <label for="complemento">Complemento:</label>
                            <div class="input-group"><i class="fa-solid fa-building-user input-icon"></i><input type="text" id="complemento" name="complemento" value="<?= htmlspecialchars($_POST['complemento'] ?? $cliente->complemento ?? '') ?>" placeholder="Apto, Bloco, etc."></div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="bairro">Bairro:</label>
                            <input type="text" id="bairro" name="bairro" value="<?= htmlspecialchars($_POST['bairro'] ?? $cliente->bairro ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="cidade">Cidade:</label>
                            <input type="text" id="cidade" name="cidade" value="<?= htmlspecialchars($_POST['cidade'] ?? $cliente->cidade ?? '') ?>" required>
                        </div>
                        <div class="form-group" style="flex: 0.5;">
                            <label for="uf">UF:</label>
                            <input type="text" id="uf" name="uf" value="<?= htmlspecialchars($_POST['uf'] ?? $cliente->uf ?? '') ?>" required maxlength="2">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="tipo_moradia">Tipo de Moradia:</label>
                            <div class="input-group"><i class="fa-solid fa-house input-icon"></i><input type="text" id="tipo_moradia" name="tipo_moradia" value="<?= htmlspecialchars($_POST['tipo_moradia'] ?? $cliente->tipo_moradia ?? '') ?>"></div>
                        </div>
                         <div class="form-group" style="flex: 2;">
                            <label for="ponto_referencia">Ponto de Referência:</label>
                            <div class="input-group"><i class="fa-solid fa-location-dot input-icon"></i><input type="text" id="ponto_referencia" name="ponto_referencia" value="<?= htmlspecialchars($_POST['ponto_referencia'] ?? $cliente->ponto_referencia ?? '') ?>"></div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="flex: 1;">
                            <label for="endereco">Endereço Completo (Automático):</label>
                            <div class="input-group"><i class="fa-solid fa-map-location-dot input-icon"></i><input type="text" id="endereco" name="endereco" value="<?= htmlspecialchars($_POST['endereco'] ?? $cliente->endereco ?? '') ?>" readonly required></div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">5. Configuração do Sistema</h3>
                    <div class="form-group">
                        <label for="status">Status do Cliente:</label>
                        <select id="status" name="status" required>
                            <option value="ativo" <?= ($cliente->status ?? '') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                            <option value="inativo" <?= ($cliente->status ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>
                </div>
                <div class="form-section">
                    <button type="submit">Salvar Alterações</button>
                </div>
                <p class="mt-2" style="text-align: center; margin-top: 20px;">
                    <a href="<?= BASE_URL ?>admin/clientes" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">Voltar à lista de clientes</a>
                </p>
            </div>
        </form>
    </div>
</div>

<!-- Inclui o script JS específico para este formulário (versão edit) -->
<script src="<?= BASE_URL ?>assets/js/cliente_edit_form.js"></script>
