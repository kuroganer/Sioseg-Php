/* JavaScript para validação de produtos */

document.addEventListener('DOMContentLoaded', function() {
    
    // Validação do campo quantidade
    const qtdeInput = document.getElementById('qtde');
    if (qtdeInput) {
        
        // Validação em tempo real durante digitação
        qtdeInput.addEventListener('input', function() {
            let valor = this.value;
            
            // Remove caracteres não numéricos
            if (!/^\d*$/.test(valor)) {
                this.value = valor.replace(/[^0-9]/g, '');
                mostrarAlerta('⚠️ ATENÇÃO: Apenas números são permitidos no campo quantidade!', 'warning');
                return;
            }
            
            // Impede valores que começam com 0 (exceto 0 sozinho)
            if (valor.length > 1 && valor.charAt(0) === '0') {
                this.value = valor.substring(1);
            }
        });
        
        // Validação ao sair do campo
        qtdeInput.addEventListener('blur', function() {
            const quantidade = parseInt(this.value);
            
            if (isNaN(quantidade) || quantidade < 0) {
                mostrarAlerta('❌ ERRO: Quantidade não pode ser negativa!\n\nPor favor, informe uma quantidade válida (0 ou maior).', 'error');
                this.value = 0;
                this.focus();
                return;
            }
            
            if (quantidade === 0) {
                mostrarAlerta('⚠️ ATENÇÃO: Produto será cadastrado com estoque ZERO!\n\nCertifique-se de que isso está correto.', 'warning');
            }
        });
        
        // Validação ao submeter o formulário
        const form = qtdeInput.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const quantidade = parseInt(qtdeInput.value);
                const nome = document.getElementById('nome')?.value || '';
                
                if (isNaN(quantidade) || quantidade < 0) {
                    e.preventDefault();
                    mostrarAlerta('❌ ERRO DE VALIDAÇÃO\n\nNão é possível cadastrar produto com quantidade negativa!\n\nQuantidade informada: ' + qtdeInput.value + '\nCorreja o valor antes de continuar.', 'error');
                    qtdeInput.focus();
                    return false;
                }
                
                if (nome.trim() === '') {
                    e.preventDefault();
                    mostrarAlerta('❌ CAMPO OBRIGATÓRIO\n\nO nome do produto é obrigatório!', 'error');
                    document.getElementById('nome')?.focus();
                    return false;
                }
                
                // Confirmação para produtos com estoque zero
                if (quantidade === 0) {
                    if (!confirm('⚠️ CONFIRMAÇÃO NECESSÁRIA\n\nVocê está cadastrando o produto "' + nome + '" com estoque ZERO.\n\nDeseja continuar?')) {
                        e.preventDefault();
                        return false;
                    }
                }
            });
        }
    }
    
    // Validação do campo nome
    const nomeInput = document.getElementById('nome');
    if (nomeInput) {
        nomeInput.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                mostrarAlerta('⚠️ CAMPO OBRIGATÓRIO: O nome do produto deve ser preenchido!', 'warning');
                this.focus();
            }
        });
    }
    
    // Função para mostrar alertas personalizados
    function mostrarAlerta(mensagem, tipo = 'info') {
        // Para compatibilidade, usa alert padrão
        // Pode ser substituído por uma biblioteca de notificações mais avançada
        alert(mensagem);
        
        // Log para debug
        console.log(`[${tipo.toUpperCase()}] ${mensagem}`);
    }
    
    // Validação adicional para campos numéricos em geral
    document.querySelectorAll('input[type="number"]').forEach(function(input) {
        // Impede entrada de valores negativos via teclado
        input.addEventListener('keydown', function(e) {
            // Permite: backspace, delete, tab, escape, enter
            if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
                // Permite: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true) ||
                // Permite: home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                return;
            }
            
            // Impede: sinal de menos (-)
            if (e.keyCode === 189 || e.keyCode === 109) {
                e.preventDefault();
                mostrarAlerta('⚠️ Números negativos não são permitidos!', 'warning');
                return;
            }
            
            // Garante que é um número
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    });
});