<div class="form-container">
    <div class="form-sidebar">
        <header class="sidebar-header">
            <h2 class="sidebar-title">Cadastro de OS</h2>
        </header>
    </div>
    <div class="form-main">
        <form action="<?= BASE_URL ?>admin/os/create" method="POST">
            <div class="progress-bar"><div class="progress-bar-inner" id="progress-bar"></div></div>

            <?php if (!empty($cadastro_erro)): ?>
                <div class="error-message"><?= htmlspecialchars($cadastro_erro) ?></div>
            <?php endif; ?>

            <?php if (!empty($cadastro_sucesso)): ?>
                <div class="success-message"><?= htmlspecialchars($cadastro_sucesso) ?></div>
            <?php endif; ?>

            <div class="form-section">
                <h3 class="form-section-title">1. Informações do Serviço</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="tipo_servico">Tipo de Serviço:</label>
                        <div class="input-group"><i class="fa-solid fa-cog input-icon"></i><select id="tipo_servico" name="tipo_servico" required>
                            <option value="">Selecione</option>
                            <option value="instalacao" <?= ($_POST['tipo_servico'] ?? '') === 'instalacao' ? 'selected' : '' ?>>Instalação</option>
                            <option value="manutencao" <?= ($_POST['tipo_servico'] ?? '') === 'manutencao' ? 'selected' : '' ?>>Manutenção</option>
                        </select></div>
                    </div>
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <div class="input-group"><i class="fa-solid fa-info-circle input-icon"></i><select id="status" name="status" required>
                            <option value="">Selecione</option>
                            <option value="aberta" <?= ($_POST['status'] ?? '') === 'aberta' ? 'selected' : '' ?>>Aberta</option>
                            <option value="em andamento" <?= ($_POST['status'] ?? '') === 'em andamento' ? 'selected' : '' ?>>Em andamento</option>
                            <option value="encerrada" <?= ($_POST['status'] ?? '') === 'encerrada' ? 'selected' : '' ?>>Encerrada</option>
                        </select></div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">2. Datas</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="data_agendamento">Data de Agendamento:</label>
                        <div class="input-group"><i class="fa-solid fa-calendar input-icon"></i><input type="datetime-local" id="data_agendamento" name="data_agendamento" value="<?= $_POST['data_agendamento'] ?? date('Y-m-d\TH:i') ?>"></div>
                    </div>
                    <div class="form-group">
                        <label for="data_encerramento">Data de Encerramento:</label>
                        <div class="input-group"><i class="fa-solid fa-calendar-check input-icon"></i><input type="datetime-local" id="data_encerramento" name="data_encerramento" value="<?= htmlspecialchars($_POST['data_encerramento'] ?? '') ?>"></div>
                    </div>
                </div>
                <p class="muted">A <strong>Data de Abertura</strong> será gerada automaticamente pelo sistema no momento do cadastro.</p>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">3. Responsáveis</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="cliente_input">Cliente (digite nome ou CPF/CNPJ):</label>
                        <div class="input-group">
                            <i class="fa-solid fa-user input-icon"></i>
                            <input type="text" id="cliente_input" list="clientes_list" placeholder="Digite nome ou CPF/CNPJ..." onchange="setClienteId(this.value)" required>
                            <input type="hidden" id="id_cli_fk" name="id_cli_fk" required>
                            <datalist id="clientes_list">
                                <?php foreach ($clientes as $cliente): 
                                    $nomeCliente = $cliente->tipo_pessoa === 'juridica' ? $cliente->razao_social : $cliente->nome_cli;
                                    $documento = $cliente->tipo_pessoa === 'juridica' ? $cliente->cnpj : $cliente->cpf_cli;
                                    $documento = !empty($documento) ? $documento : 'Sem documento';
                                    $displayText = $nomeCliente . ' - ' . $documento;
                                ?>
                                    <option value="<?= htmlspecialchars($displayText) ?>" data-id="<?= $cliente->id_cli ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="id_tec_fk">Técnico:</label>
                        <div class="input-group"><i class="fa-solid fa-user-tie input-icon"></i><select id="id_tec_fk" name="id_tec_fk" required>
                            <option value="">Selecione o técnico</option>
                            <?php foreach ($tecnicos as $tec): ?>
                                <option value="<?= $tec->id_tec ?>" <?= ($_POST['id_tec_fk'] ?? '') == $tec->id_tec ? 'selected' : '' ?>><?= htmlspecialchars($tec->nome_tec) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="usuario_responsavel_display">Usuário responsável:</label>
                        <div class="input-group">
                            <i class="fa-solid fa-user-shield input-icon"></i>
                            <input type="text" id="usuario_responsavel_display" value="<?= htmlspecialchars($currentUserEmail ?? '') ?>" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                            <input type="hidden" id="id_usu_fk" name="id_usu_fk" value="<?= $currentUserId ?? '' ?>" required>
                        </div>
                    </div>
                </div>
            </div>      
            <div class="form-section">
                <h3 class="form-section-title">4. Materiais/Produtos</h3>
                <div id="materiais-container">
                    <div class="material-item">
                        <div class="form-row">
                            <div class="form-group" style="flex: 2;">
                                <label for="produto_1">Produto:</label>
                                <div class="input-group"><i class="fa-solid fa-box input-icon"></i><select id="produto_1" name="produtos[1][id_prod]">
                                    <option value="">Selecione um produto</option>
                                    <?php foreach ($produtos as $produto): ?>
                                        <option value="<?= $produto->id_prod ?>" data-estoque="<?= $produto->qtde ?>">
                                            <?= htmlspecialchars($produto->nome . ' - ' . $produto->marca . ' - ' . $produto->modelo . ' (Estoque: ' . $produto->qtde . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select></div>
                            </div>
                            <div class="form-group">
                                <label for="quantidade_1">Quantidade:</label>
                                <div class="input-group"><i class="fa-solid fa-hashtag input-icon"></i><input type="number" id="quantidade_1" name="produtos[1][quantidade]" min="1" value="1"></div>
                            </div>
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" class="btn-add-material" onclick="adicionarMaterial()">
                                    <i class="fa-solid fa-plus"></i> Adicionar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="materiais-adicionados" class="materiais-list" style="display: none;">
                    <h4>Materiais Adicionados:</h4>
                    <div id="materiais-list-content"></div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">5. Observações</h3>
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label for="servico_prestado">Serviço Prestado / Observações:</label>
                        <div class="input-group"><i class="fa-solid fa-tools input-icon"></i><textarea id="servico_prestado" name="servico_prestado" rows="4" placeholder="Descreva o serviço prestado..."><?= htmlspecialchars($_POST['servico_prestado'] ?? '') ?></textarea></div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <button type="submit">Cadastrar OS</button>
            </div>
        </form>
    </div>
</div>

<script>
let materialCount = 1;

function adicionarMaterial() {
    materialCount++;
    const container = document.getElementById('materiais-container');

    const materialItem = document.createElement('div');
    materialItem.className = 'material-item';
    materialItem.innerHTML = `
        <div class="form-row">
            <div class="form-group" style="flex: 2;">
                <label for="produto_${materialCount}">Produto:</label>
                <div class="input-group">
                    <i class="fa-solid fa-box input-icon"></i>
                    <select id="produto_${materialCount}" name="produtos[${materialCount}][id_prod]" required>
                        <option value="">Selecione um produto</option>
                        <?php foreach ($produtos as $produto): ?>
                            <option value="<?= $produto->id_prod ?>" data-estoque="<?= $produto->qtde ?>">
                                <?= htmlspecialchars($produto->nome . ' - ' . $produto->marca . ' - ' . $produto->modelo . ' (Estoque: ' . $produto->qtde . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="quantidade_${materialCount}">Quantidade:</label>
                <div class="input-group">
                    <i class="fa-solid fa-hashtag input-icon"></i>
                    <input type="number" id="quantidade_${materialCount}" name="produtos[${materialCount}][quantidade]" min="1" value="1" required>
                </div>
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <button type="button" class="btn-remove-material" onclick="removerMaterial(this)">
                    <i class="fa-solid fa-minus"></i> Remover
                </button>
            </div>
        </div>
    `;

    container.appendChild(materialItem);
    
    // Inicializar autocomplete no novo select
    const newSelect = materialItem.querySelector('select');
    if (typeof initAutocomplete === 'function') {
        initAutocomplete(newSelect);
    }
    
    atualizarMateriaisAdicionados();
}

function removerMaterial(button) {
    button.closest('.material-item').remove();
    materialCount--;
    atualizarMateriaisAdicionados();
}

function atualizarMateriaisAdicionados() {
    const materiaisAdicionados = document.getElementById('materiais-adicionados');
    const materiaisListContent = document.getElementById('materiais-list-content');
    const materiais = [];

    // Coletar todos os materiais selecionados
    for (let i = 1; i <= materialCount; i++) {
        const produtoSelect = document.getElementById(`produto_${i}`);
        const quantidadeInput = document.getElementById(`quantidade_${i}`);

        if (produtoSelect && quantidadeInput && produtoSelect.value) {
            const option = produtoSelect.options[produtoSelect.selectedIndex];
            const estoque = option.getAttribute('data-estoque');
            const quantidade = quantidadeInput.value;

            materiais.push({
                nome: option.text.split(' (Estoque:')[0],
                quantidade: quantidade,
                estoque: estoque
            });
        }
    }

    if (materiais.length > 0) {
        materiaisListContent.innerHTML = materiais.map(material =>
            `<div class="material-item-summary">
                <span>${material.nome}</span>
                <span>Quantidade: ${material.quantidade}</span>
                <span>Estoque: ${material.estoque}</span>
            </div>`
        ).join('');
        materiaisAdicionados.style.display = 'block';
    } else {
        materiaisAdicionados.style.display = 'none';
    }
}

// Atualizar materiais adicionados quando houver mudança
document.addEventListener('change', function(e) {
    // Garantir que e.target.id exista antes de usar startsWith
    if (e.target && e.target.id && (e.target.id.startsWith('produto_') || e.target.id.startsWith('quantidade_'))) {
        atualizarMateriaisAdicionados();
    }
});

// Validação de estoque com alertas robustos
document.addEventListener('change', function(e) {
    if (e.target.id && e.target.id.startsWith('quantidade_')) {
        const input = e.target;
        const produtoSelect = document.getElementById(e.target.id.replace('quantidade', 'produto'));
        const selectedOption = produtoSelect.options[produtoSelect.selectedIndex];
        const estoque = parseInt(selectedOption.getAttribute('data-estoque') || 0);
        const quantidade = parseInt(input.value);
        const produtoNome = selectedOption.text.split(' (Estoque:')[0];

        // Validação para números negativos
        if (isNaN(quantidade) || quantidade < 0) {
            alert('⚠️ ERRO: Não é possível adicionar quantidade negativa!\n\nPor favor, informe uma quantidade válida (maior que 0).');
            input.value = 1;
            input.focus();
            return;
        }
        
        // Validação para quantidade zero
        if (quantidade === 0) {
            alert('⚠️ ATENÇÃO: A quantidade mínima para adicionar um material é 1 unidade.');
            input.value = 1;
            input.focus();
            return;
        }
        
        // Validação de estoque disponível
        if (estoque <= 0) {
            alert('❌ PRODUTO SEM ESTOQUE\n\nO produto "' + produtoNome + '" está fora de estoque.\nEstoque atual: 0 unidades\n\nSelecione outro produto ou aguarde reposição.');
            input.value = 1;
            return;
        }
        
        // Validação para quantidade acima do estoque
        if (quantidade > estoque) {
            alert('❌ ESTOQUE INSUFICIENTE\n\nProduto: ' + produtoNome + '\nQuantidade solicitada: ' + quantidade + ' unidades\nEstoque disponível: ' + estoque + ' unidades\n\nA quantidade foi ajustada para o máximo disponível.');
            input.value = estoque;
            input.focus();
        }
        
        // Alerta de estoque baixo
        if (estoque <= 20 && estoque > 0) {
            alert('⚠️ ALERTA: ESTOQUE BAIXO\n\nProduto: ' + produtoNome + '\nEstoque atual: ' + estoque + ' unidades\n\n⚠️ Este produto está com estoque baixo (≤20 unidades).\nConsidere reabastecer em breve.');
        }
    }
});

// Alerta de estoque baixo ao selecionar produto
document.addEventListener('change', function(e) {
    if (e.target.id && e.target.id.startsWith('produto_')) {
        const select = e.target;
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const estoque = parseInt(selectedOption.getAttribute('data-estoque') || 0);
            const produtoNome = selectedOption.text.split(' (Estoque:')[0];
            
            if (estoque <= 20 && estoque > 0) {
                alert('⚠️ ALERTA: ESTOQUE BAIXO\n\nProduto selecionado: ' + produtoNome + '\nEstoque atual: ' + estoque + ' unidades\n\n⚠️ Este produto está com estoque baixo (≤20 unidades).\nConsidere reabastecer em breve.');
            }
        }
    }
});

// Validação em tempo real durante digitação
document.addEventListener('input', function(e) {
    if (e.target.id && e.target.id.startsWith('quantidade_')) {
        const input = e.target;
        const valor = input.value;
        
        // Remove caracteres não numéricos
        if (!/^\d*$/.test(valor)) {
            input.value = valor.replace(/[^\d]/g, '');
        }
        
        // Impede valores que começam com 0 (exceto 0 sozinho)
        if (valor.length > 1 && valor.charAt(0) === '0') {
            input.value = valor.substring(1);
        }
    }
});

// Validação no submit do formulário
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const quantidadeInputs = document.querySelectorAll('input[type="number"][id*="quantidade_"]');
            let hasError = false;
            
            quantidadeInputs.forEach(function(input) {
                const quantidade = parseInt(input.value);
                if (isNaN(quantidade) || quantidade < 0) {
                    e.preventDefault();
                    alert('❌ ERRO DE VALIDAÇÃO\n\nNão é possível cadastrar OS com quantidades negativas!\n\nCorreja os valores antes de continuar.');
                    input.focus();
                    hasError = true;
                    return false;
                }
            });
        });
    }
});

function setClienteId(value) {
    const options = document.querySelectorAll('#clientes_list option');
    for (let option of options) {
        if (option.value === value) {
            document.getElementById('id_cli_fk').value = option.getAttribute('data-id');
            return;
        }
    }
    document.getElementById('id_cli_fk').value = '';
}
</script>

<script src="<?= BASE_URL ?>assets/js/os-form-validation.js"></script>
