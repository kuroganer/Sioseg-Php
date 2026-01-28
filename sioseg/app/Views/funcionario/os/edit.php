<div class="form-container">
    <div class="form-sidebar">
        <header class="sidebar-header">
            <h2 class="sidebar-title">Editar OS #<?= $os->id_os ?></h2>
        </header>
    </div>
    <div class="form-main">
        <form action="<?= BASE_URL ?>funcionario/os/update/<?= $os->id_os ?>" method="POST" id="os-edit-form">
            <div class="progress-bar"><div class="progress-bar-inner" id="progress-bar"></div></div>

            <?php if (!empty($edicao_erro)): ?>
                <div class="error-message"><?= htmlspecialchars($edicao_erro) ?></div>
            <?php endif; ?>

            <?php if (!empty($edicao_sucesso)): ?>
                <div class="success-message"><?= htmlspecialchars($edicao_sucesso) ?></div>
            <?php endif; ?>

            <div class="form-section">
                <h3 class="form-section-title">1. Informações do Serviço</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="tipo_servico">Tipo de Serviço:</label>
                        <div class="input-group"><i class="fa-solid fa-cog input-icon"></i><select id="tipo_servico" name="tipo_servico" required>
                            <option value="">Selecione</option>
                            <option value="instalacao" <?= $os->tipo_servico === 'instalacao' ? 'selected' : '' ?>>Instalação</option>
                            <option value="manutencao" <?= $os->tipo_servico === 'manutencao' ? 'selected' : '' ?>>Manutenção</option>
                        </select></div>
                    </div>
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <div class="input-group"><i class="fa-solid fa-info-circle input-icon"></i><select id="status" name="status" required>
                            <option value="">Selecione</option>
                            <option value="aberta" <?= $os->status === 'aberta' ? 'selected' : '' ?>>Aberta</option>
                            <option value="em andamento" <?= $os->status === 'em andamento' ? 'selected' : '' ?>>Em andamento</option>
                            <?php if ($os->status === 'concluída'): ?>
                                <option value="concluída" selected>Concluída (Somente leitura)</option>
                            <?php endif; ?>
                            <option value="encerrada" <?= $os->status === 'encerrada' ? 'selected' : '' ?>>Encerrada</option>
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
                        <div class="input-group"><i class="fa-solid fa-calendar-check input-icon"></i><input type="datetime-local" id="data_encerramento" name="data_encerramento" value="<?= $os->data_encerramento ? date('Y-m-d\TH:i', strtotime($os->data_encerramento)) : '' ?>"></div>
                    </div>
                </div>
            </div>
            <div class="form-section">
                <h3 class="form-section-title">3. Responsáveis</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="cliente_busca">Cliente (digite nome ou CPF/CNPJ):</label>
                        <div class="input-group" style="position: relative;">
                            <i class="fa-solid fa-user input-icon"></i>
                            <input type="text" id="cliente_busca" placeholder="Digite nome ou CPF/CNPJ..." value="<?php foreach ($clientes as $cliente) { if ($cliente->id_cli == $os->id_cli_fk) { $nomeCliente = $cliente->tipo_pessoa === 'juridica' ? $cliente->razao_social : $cliente->nome_cli; $documento = $cliente->tipo_pessoa === 'juridica' ? $cliente->cnpj : $cliente->cpf_cli; $documento = !empty($documento) ? $documento : 'Sem documento'; echo htmlspecialchars($nomeCliente . ' - ' . $documento); break; } } ?>" required>
                            <input type="hidden" id="id_cli_fk" name="id_cli_fk" value="<?= $os->id_cli_fk ?>" required>
                            <div id="cliente_results" class="search-results" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="id_tec_fk">Técnico:</label>
                        <div class="input-group"><i class="fa-solid fa-user-tie input-icon"></i><select id="id_tec_fk" name="id_tec_fk" required>
                            <option value="">Selecione o técnico</option>
                            <?php foreach ($tecnicos as $tec): ?>
                                <option value="<?= $tec->id_tec ?>" <?= $os->id_tec_fk == $tec->id_tec ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tec->nome_tec) ?>
                                </option>
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
                <h3 class="form-section-title">5. Materiais/Produtos</h3>

                <div id="materiais-existentes" class="materiais-list">
                    <h4>Materiais na OS:</h4>
                    <div id="materiais-existentes-content">
                        <?php if (!empty($materiais)): ?>
                            <?php foreach ($materiais as $material): ?>
                                <div class="material-item-existing">
                                    <div class="form-row">
                                        <div class="form-group" style="flex: 2;">
                                            <label>Produto:</label>
                                            <div class="input-group">
                                                <i class="fa-solid fa-box input-icon"></i>
                                                <input type="text" value="<?= htmlspecialchars($material->nome . ' - ' . $material->marca . ' - ' . $material->modelo) ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Quantidade:</label>
                                            <div class="input-group">
                                                <i class="fa-solid fa-hashtag input-icon"></i>
                                                <input type="number"
                                                       name="materiais_existentes[<?= $material->id_prod ?>][quantidade]"
                                                       value="<?= $material->qtd_usada ?>"
                                                       min="0"
                                                       data-id-prod="<?= $material->id_prod ?>"
                                                       data-estoque-disponivel="<?= $material->estoque_atual_produto + $material->qtd_usada ?>"
                                                       data-quantidade-original="<?= $material->qtd_usada ?>"
                                                       class="quantidade-material-existente">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                        <button type="button" class="btn-remove-material-existing"
                            data-url="<?= BASE_URL ?>funcionario/os/remove-material/<?= $os->id_os ?>/<?= $material->id_prod ?>"
                            onclick="removerMaterial(this)">
                                                <i class="fa-solid fa-trash"></i> Remover
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: #6c757d; font-style: italic;">Nenhum material adicionado ainda.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="adicionar-materiais" style="margin-top: 20px; display: <?= in_array($os->status, ['aberta', 'em andamento']) ? 'block' : 'none' ?>;">
                    <h4>Adicionar Material:</h4>
                        <div id="novos-materiais-container">
                            <div class="material-item">
                                <div class="form-row">
                                    <div class="form-group" style="flex: 2;">
                                        <label for="novo_produto_1">Produto:</label>
                                        <div class="input-group"><i class="fa-solid fa-box input-icon"></i><select id="novo_produto_1" name="novos_produtos[1][id_prod]">
                                            <option value="">Selecione um produto</option>
                                            <?php foreach ($produtos as $produto): ?>
                                                <option value="<?= $produto->id_prod ?>" data-estoque="<?= $produto->qtde ?>">
                                                    <?= htmlspecialchars($produto->nome . ' - ' . $produto->marca . ' - ' . $produto->modelo . ' (Estoque: ' . $produto->qtde . ')') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="nova_quantidade_1">Quantidade:</label>
                                        <div class="input-group"><i class="fa-solid fa-hashtag input-icon"></i><input type="number" id="nova_quantidade_1" name="novos_produtos[1][quantidade]" min="1" value="1"></div>
                                    </div>
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="button" class="btn-add-material" onclick="adicionarNovoMaterial()">
                                            <i class="fa-solid fa-plus"></i> Adicionar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
                
                <div id="aviso-materiais" style="margin-top: 20px; padding: 15px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; display: <?= !in_array($os->status, ['aberta', 'em andamento']) ? 'block' : 'none' ?>;">
                    <p style="margin: 0; color: #856404;">
                        <i class="fa-solid fa-info-circle"></i>
                        <span id="aviso-texto">Não é possível adicionar materiais a uma OS <?= strtolower($os->status) ?>.</span>
                    </p>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">6. Observações</h3>
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label for="servico_prestado">Serviço Prestado / Observações:</label>
                        <div class="input-group"><i class="fa-solid fa-tools input-icon"></i><textarea id="servico_prestado" name="servico_prestado" rows="4" placeholder="Descreva o serviço prestado..."><?= htmlspecialchars($os->servico_prestado) ?></textarea></div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <button type="submit" id="update-os-button">Atualizar OS</button>
            </div>
            
            <div id="os-status-message" class="status-message" style="display: none;">
                <i class="fa-solid fa-info-circle"></i>
                Esta OS está <span id="current-os-status"></span> e não pode ser editada.
            </div>
        </form>
    </div>
</div>

<!-- JavaScript específico para edição de OS -->
<script>
// Passa o status da OS para o JavaScript
window.OS_STATUS = "<?= $os->status ?>";

// Controla visibilidade dos materiais baseado no status
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const adicionarMateriais = document.getElementById('adicionar-materiais');
    const avisoMateriais = document.getElementById('aviso-materiais');
    const avisoTexto = document.getElementById('aviso-texto');
    
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            const status = this.value;
            const podeAdicionar = ['aberta', 'em andamento'].includes(status);
            
            if (podeAdicionar) {
                adicionarMateriais.style.display = 'block';
                avisoMateriais.style.display = 'none';
            } else {
                adicionarMateriais.style.display = 'none';
                avisoMateriais.style.display = 'block';
                avisoTexto.textContent = `Não é possível adicionar materiais a uma OS ${status.toLowerCase()}.`;
            }
        });
    }
});

// Busca de cliente por CPF/CNPJ ou nome
let timeoutId;
document.getElementById('cliente_busca').addEventListener('input', function() {
    clearTimeout(timeoutId);
    const termo = this.value.trim();
    
    if (termo.length < 2) {
        document.getElementById('cliente_results').style.display = 'none';
        return;
    }
    
    timeoutId = setTimeout(() => {
        fetch(`<?= BASE_URL ?>funcionario/clientes/buscar-cpf-cnpj?documento=${encodeURIComponent(termo)}`)
            .then(response => response.json())
            .then(data => {
                const resultsDiv = document.getElementById('cliente_results');
                
                if (data.cliente) {
                    resultsDiv.innerHTML = `
                        <div class="search-result-item" onclick="selecionarCliente(${data.cliente.id}, '${data.cliente.nome}')">
                            <strong>${data.cliente.nome}</strong><br>
                            <small>Documento: ${data.cliente.documento}</small>
                        </div>
                    `;
                    resultsDiv.style.display = 'block';
                } else {
                    // Busca por nome se não encontrou por documento
                    fetch(`<?= BASE_URL ?>funcionario/search/clientes?term=${encodeURIComponent(termo)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.clientes && data.clientes.length > 0) {
                                resultsDiv.innerHTML = data.clientes.map(cliente => {
                                    const nome = cliente.tipo_pessoa === 'fisica' ? cliente.nome_cli : cliente.razao_social;
                                    const doc = cliente.tipo_pessoa === 'fisica' ? cliente.cpf_cli : cliente.cnpj;
                                    return `
                                        <div class="search-result-item" onclick="selecionarCliente(${cliente.id}, '${cliente.text}')">
                                            <strong>${cliente.text}</strong>
                                        </div>
                                    `;
                                }).join('');
                                resultsDiv.style.display = 'block';
                            } else {
                                resultsDiv.innerHTML = '<div class="search-result-item">Nenhum cliente encontrado</div>';
                                resultsDiv.style.display = 'block';
                            }
                        });
                }
            })
            .catch(error => {
                console.error('Erro na busca:', error);
            });
    }, 300);
});

function selecionarCliente(id, nome) {
    document.getElementById('cliente_busca').value = nome;
    document.getElementById('id_cli_fk').value = id;
    document.getElementById('cliente_results').style.display = 'none';
}

// Esconder resultados ao clicar fora
document.addEventListener('click', function(e) {
    if (!e.target.closest('#cliente_busca') && !e.target.closest('#cliente_results')) {
        document.getElementById('cliente_results').style.display = 'none';
    }
});


</script>
<script src="<?= BASE_URL ?>assets/js/os-edit.js"></script>

<!-- CSS específico para edição de OS -->
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/os-edit.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/cliente-search.css">

