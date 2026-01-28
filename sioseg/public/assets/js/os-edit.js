/* JavaScript específico para edição de OS */

var novoMaterialCount = 1;

/**
 * Cria e submete um formulário dinamicamente para evitar aninhamento de <form>.
 * Isso garante que a requisição de remoção seja POST.
 */
function removerMaterial(button) {
    return removeMaterial(button);
}

function removeMaterial(button) {
    if (confirm('Tem certeza que deseja remover este material? Isso irá restaurar o estoque.')) {
        var url = button.dataset.url;
        
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = url;

            document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
}

function adicionarNovoMaterial() {
    novoMaterialCount++;
    var container = document.getElementById('novos-materiais-container');
    var primeiroItem = container.querySelector('.material-item');
    
    if (!primeiroItem) {
        console.error('Template de material não encontrado');
        return;
    }
    
    var novoItem = primeiroItem.cloneNode(true);

    // Atualiza os atributos 'id' e 'name' para serem únicos
    var inputs = novoItem.querySelectorAll('input, select');
    for (var i = 0; i < inputs.length; i++) {
        var input = inputs[i];
        var currentId = input.id;
        var currentName = input.name;
        
        if (currentId) {
            input.id = currentId.replace(/(_\d+)/, '_' + novoMaterialCount);
        }
        if (currentName) {
            input.name = currentName.replace(/\[(\d+)\]/, '[' + novoMaterialCount + ']');
        }
    }

    // Atualiza o botão 'Adicionar' para 'Remover'
    var addButton = novoItem.querySelector('.btn-add-material');
    if (addButton) {
        addButton.innerHTML = '<i class="fa-solid fa-minus"></i> Remover';
        addButton.className = 'btn-remove-material';
        addButton.onclick = function() { removerNovoMaterial(this); };
    }
    
    // Limpa valores do novo item
    var selectElement = novoItem.querySelector('select');
    var numberInput = novoItem.querySelector('input[type="number"]');
    if (selectElement) selectElement.value = "";
    if (numberInput) numberInput.value = "1";

    container.appendChild(novoItem);
    
    // Inicializar autocomplete no novo select
    if (selectElement && typeof initAutocomplete === 'function') {
        initAutocomplete(selectElement);
    }
}

function removerNovoMaterial(button) {
    button.closest('.material-item').remove();
}

document.addEventListener('DOMContentLoaded', function() {
    
    // --- Campos de data ---
    var dataEncerramentoField = document.getElementById('data_encerramento');
    if (dataEncerramentoField) {
        dataEncerramentoField.addEventListener('blur', function() {
            if (this.value === '') this.removeAttribute('required');
        });
    }

    // --- Validação de NOVOS materiais ---
    document.addEventListener('change', function(e) {
        if (e.target.id && e.target.id.indexOf('nova_quantidade_') === 0) {
            var input = e.target;
            var produtoSelect = document.getElementById(e.target.id.replace('nova_quantidade', 'novo_produto'));
            var selectedOption = produtoSelect.options[produtoSelect.selectedIndex];
            var estoque = selectedOption ? parseInt(selectedOption.getAttribute('data-estoque')) : 0;
            var quantidade = parseInt(input.value);
            var produtoNome = selectedOption ? selectedOption.text : 'produto';

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
                input.value = 0;
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
    
    // --- Validação em tempo real durante digitação ---
    document.addEventListener('input', function(e) {
        if (e.target.id && e.target.id.indexOf('nova_quantidade_') === 0) {
            var input = e.target;
            var valor = input.value;
            
            // Remove caracteres não numéricos exceto números
            if (!/^\d*$/.test(valor)) {
                input.value = valor.replace(/[^\d]/g, '');
            }
            
            // Impede valores que começam com 0 (exceto 0 sozinho)
            if (valor.length > 1 && valor.charAt(0) === '0') {
                input.value = valor.substring(1);
            }
        }
    });
    
    // Alerta de estoque baixo ao selecionar novo produto
    document.addEventListener('change', function(e) {
        if (e.target.id && e.target.id.indexOf('novo_produto_') === 0) {
            var select = e.target;
            var selectedOption = select.options[select.selectedIndex];
            if (selectedOption && selectedOption.value) {
                var estoque = parseInt(selectedOption.getAttribute('data-estoque') || 0);
                var produtoNome = selectedOption.text.split(' (Estoque:')[0];
                
                if (estoque <= 20 && estoque > 0) {
                    alert('⚠️ ALERTA: ESTOQUE BAIXO\n\nProduto selecionado: ' + produtoNome + '\nEstoque atual: ' + estoque + ' unidades\n\n⚠️ Este produto está com estoque baixo (≤20 unidades).\nConsidere reabastecer em breve.');
                }
            }
        }
    });

    // --- Validação de MATERIAIS EXISTENTES ---
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('quantidade-material-existente')) {
            var input = e.target;
            var quantidadeOriginal = parseInt(input.dataset.quantidadeOriginal);
            var estoqueDisponivelTotal = parseInt(input.dataset.estoqueDisponivel);
            var novaQuantidade = parseInt(input.value);
            var materialNome = input.dataset.materialNome || 'material';

            // Validação para números negativos
            if (isNaN(novaQuantidade) || novaQuantidade < 0) {
                alert('⚠️ ERRO: Quantidade não pode ser negativa!\n\nMaterial: ' + materialNome + '\nQuantidade original: ' + quantidadeOriginal + ' unidades\n\nA quantidade foi restaurada para o valor original.');
                input.value = quantidadeOriginal;
                input.focus();
                return;
            }
            
            // Validação para estoque disponível
            if (novaQuantidade > estoqueDisponivelTotal) {
                alert('❌ ESTOQUE INSUFICIENTE\n\nMaterial: ' + materialNome + '\nQuantidade solicitada: ' + novaQuantidade + ' unidades\nEstoque total disponível: ' + estoqueDisponivelTotal + ' unidades\n\nA quantidade foi ajustada para o máximo disponível.');
                input.value = estoqueDisponivelTotal;
                input.focus();
                return;
            }

            input.dataset.quantidadeOriginal = input.value;
        }
    });
    
    // --- Validação em tempo real para materiais existentes ---
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantidade-material-existente')) {
            var input = e.target;
            var valor = input.value;
            
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

    // --- Bloqueio apenas se status for CONCLUIDA ---
    // Tenta obter o status da variável global (preferencial) ou de um input.
    var osStatus = '';
    if (typeof window.OS_STATUS !== 'undefined') {
        osStatus = window.OS_STATUS.toLowerCase();
    }

    var updateButton = document.getElementById('update-os-button');
    var osStatusMessage = document.getElementById('os-status-message');
    var currentOsStatusSpan = document.getElementById('current-os-status');
    var osEditForm = document.getElementById('os-edit-form');

    // Lista de status que devem bloquear a edição
    var statusDeBloqueio = ['concluida', 'concluída'];

    if (statusDeBloqueio.includes(osStatus)) {
        // Desabilita botão de update
        if (updateButton) updateButton.setAttribute('disabled', 'disabled');

        // Mostra status da OS
        if (osStatusMessage) {
            osStatusMessage.style.display = 'block';
        }
        if (currentOsStatusSpan) {
            currentOsStatusSpan.textContent = osStatus;
        }

        // Desabilita todos os campos do formulário
        if (osEditForm) {
            var formElements = osEditForm.querySelectorAll('input, select, textarea, button');
            formElements.forEach(function(el) {
                if (el.id !== 'update-os-button') {
                    el.setAttribute('disabled', 'disabled');
                    if (el.hasAttribute('required')) el.removeAttribute('required');
                }
            });
            
            // Desabilita também os containers de autocomplete
            var autocompleteContainers = osEditForm.querySelectorAll('.autocomplete-container input');
            autocompleteContainers.forEach(function(input) {
                input.setAttribute('disabled', 'disabled');
                input.style.backgroundColor = '#f8f9fa';
                input.style.cursor = 'not-allowed';
            });
        }

        // Remove required do campo data_encerramento
        if (dataEncerramentoField && dataEncerramentoField.hasAttribute('required')) {
            dataEncerramentoField.removeAttribute('required');
        }

        // Esconder seção de adicionar novos materiais
        var adicionarMateriaisSection = document.getElementById('adicionar-materiais');
        if (adicionarMateriaisSection) adicionarMateriaisSection.style.display = 'none';
    }
});
