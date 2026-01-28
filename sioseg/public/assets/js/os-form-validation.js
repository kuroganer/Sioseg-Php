/* JavaScript para validação de formulários de OS */

document.addEventListener('DOMContentLoaded', function() {
    
    
    // Garantir que campos de data vazios não causem problemas
    var dataEncerramentoField = document.getElementById('data_encerramento');
    if (dataEncerramentoField) {
        // Remove required se existir
        dataEncerramentoField.removeAttribute('required');
        
        // Adiciona validação para garantir que campos vazios sejam tratados corretamente
        dataEncerramentoField.addEventListener('blur', function() {
            if (this.value === '') {
                this.removeAttribute('required');
            }
        });
        
        // Validação antes do submit
        var form = dataEncerramentoField.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                var dataEncerramento = document.getElementById('data_encerramento');
                if (dataEncerramento && dataEncerramento.value === '') {
                    dataEncerramento.removeAttribute('required');
                }
            });
        }
    }
    
    // Validação robusta de estoque para produtos
    document.addEventListener('change', function(e) {
        if (e.target.id && e.target.id.startsWith('quantidade_')) {
            var input = e.target;
            var produtoSelect = document.getElementById(e.target.id.replace('quantidade', 'produto'));
            
            if (produtoSelect) {
                var selectedOption = produtoSelect.options[produtoSelect.selectedIndex];
                var estoque = selectedOption ? parseInt(selectedOption.getAttribute('data-estoque')) : 0;
                var quantidade = parseInt(input.value);
                var produtoNome = selectedOption ? selectedOption.text.split(' (Estoque:')[0] : 'produto';

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
        }
    });
    
    // Validação em tempo real durante digitação
    document.addEventListener('input', function(e) {
        if (e.target.id && e.target.id.startsWith('quantidade_')) {
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
    
    // Validação no submit do formulário
    var form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            var quantidadeInputs = document.querySelectorAll('input[type="number"][id*="quantidade_"]');
            var hasError = false;
            
            quantidadeInputs.forEach(function(input) {
                var quantidade = parseInt(input.value);
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
    
    // Alerta de estoque baixo ao selecionar produto
    document.addEventListener('change', function(e) {
        if (e.target.id && e.target.id.startsWith('produto_')) {
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
});