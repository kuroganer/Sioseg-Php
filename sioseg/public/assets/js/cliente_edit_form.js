document.addEventListener('DOMContentLoaded', function () { // Executa quando o DOM estiver carregado
    // --- ELEMENTOS PRINCIPAIS ---
    var tipoSelect = document.getElementById('tipo_pessoa'); // Select para escolher tipo de pessoa (física/jurídica)
    var mainFormContent = document.getElementById('main-form-content'); // Container principal do formulário
    var fisicaFields = document.getElementById('fisica_fields'); // Campos específicos para pessoa física
    var juridicaFields = document.getElementById('juridica_fields'); // Campos específicos para pessoa jurídica
    var progressBar = document.getElementById('progress-bar'); // Barra de progresso do formulário
    var form = document.querySelector('form'); // Elemento do formulário

    // --- FUNÇÕES DE MÁSCARA ---
    var applyMask = function(element, maskFunction) { // Aplica máscara de formatação em tempo real
        if (!element) return; // Sai se elemento não existir
        element.addEventListener('input', function(e) { // Escuta mudanças no campo
            e.target.value = maskFunction(e.target.value); // Aplica a máscara no valor digitado
        });
    };
    
    var maskCpf = function(value) { // Formata CPF no padrão 000.000.000-00
        return value
            .replace(/\D/g, '') // Remove tudo que não é dígito
            .replace(/(\d{3})(\d)/, '$1.$2') // Adiciona primeiro ponto
            .replace(/(\d{3})(\d)/, '$1.$2') // Adiciona segundo ponto
            .replace(/(\d{3})(\d{1,2})$/, '$1-$2'); // Adiciona hífen
    };

    var maskCnpj = function(value) { // Formata CNPJ alfanumérico no padrão XX.XXX.XXX/XXXX-XX
        return value
            .replace(/[^a-zA-Z0-9]/g, '') // Remove tudo exceto letras e números
            .toUpperCase() // Converte para maiúscula
            .replace(/(\w{2})(\w)/, '$1.$2') // Adiciona primeiro ponto
            .replace(/(\w{3})(\w)/, '$1.$2') // Adiciona segundo ponto
            .replace(/(\w{3})(\w)/, '$1/$2') // Adiciona barra
            .replace(/(\w{4})(\w{1,2})$/, '$1-$2'); // Adiciona hífen
    };

    var maskPhone = function(value) { // Formata telefone (00) 0000-0000 ou (00) 00000-0000
        value = value.replace(/\D/g, ''); // Remove tudo que não é dígito
        if (value.length <= 10) { // Fixo com até 10 dígitos
            return value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
        } else { // Celular com 11 dígitos
            return value.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
        }
    };
    
    var maskCep = function(value) { // Formata CEP no padrão 00000-000
        return value.replace(/\D/g, '').replace(/(\d{5})(\d{3})$/, '$1-$2'); // Remove não-dígitos e adiciona hífen
    };

    var maskRg = function(value) { // Formata RG no padrão X.XXX.XXX
        value = value.replace(/[^\dX]/gi, '').toUpperCase();
        return value.replace(/(\d{1})(\d{0,3})(\d{0,3})/, function(match, p1, p2, p3) {
            var result = p1;
            if (p2) result += '.' + p2;
            if (p3) result += '.' + p3;
            return result;
        });
    };

    // --- FUNÇÕES PARA REMOVER MÁSCARAS ---
    var removeMask = function(value) { // Remove todos os caracteres não numéricos
        return value ? value.replace(/\D/g, '') : '';
    };

    var removeCnpjMask = function(value) { // Remove somente pontuação e espaços do CNPJ, preservando letras
        return value ? value.replace(/[.\-\/\s]/g, '') : '';
    };

    var removePhoneMask = function(value) { // Remove formatação de telefone
        return value ? value.replace(/[()\s-]/g, '') : '';
    };

    // --- FUNÇÕES DE VALIDAÇÃO ---
    var validateCpf = function(cpf) { // Valida CPF usando algoritmo oficial
        cpf = cpf.replace(/\D/g, ''); // Remove formatação
        if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false; // Verifica tamanho e sequências iguais
        
        let sum = 0; // Calcula primeiro dígito verificador
        for (let i = 0; i < 9; i++) sum += parseInt(cpf[i]) * (10 - i);
        let digit1 = 11 - (sum % 11);
        if (digit1 > 9) digit1 = 0;
        
        sum = 0; // Calcula segundo dígito verificador
        for (let i = 0; i < 10; i++) sum += parseInt(cpf[i]) * (11 - i);
        let digit2 = 11 - (sum % 11);
        if (digit2 > 9) digit2 = 0;
        
        return digit1 === parseInt(cpf[9]) && digit2 === parseInt(cpf[10]); // Compara dígitos calculados com informados
    };

    var validateCnpj = function(cnpj) {
        var b = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        var c = String(cnpj).toUpperCase().replace(/[\/.-]/g,'');

        if(c.length !== 14)
            return false;

        if(/0{14}/.test(c))
            return false;

        for (var i = 0, n = 0; i < 12; n += (c.charCodeAt(i)-48) * b[++i]);
        if(c[12] != (((n %= 11) < 2) ? 0 : 11 - n))
            return false;

        for (var i = 0, n = 0; i <= 12; n += (c.charCodeAt(i)-48) * b[i++]);
        if(c[13] != (((n %= 11) < 2) ? 0 : 11 - n))
            return false;

        return true;
    };

    var validateEmail = function(email) { // Valida formato básico de email
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email); // Regex para formato email válido
    };

    var validatePhone = function(phone) { // Valida se telefone tem 10 ou 11 dígitos
        var digits = phone.replace(/\D/g, ''); // Remove formatação
        return digits.length >= 10 && digits.length <= 11; // Aceita fixo (10) ou celular (11)
    };

    var validatePassword = function(password) { // Valida se senha tem pelo menos 6 caracteres
        return password.length >= 6; // Mínimo de 6 caracteres
    };

    var showFieldError = function(field, message) { // Exibe mensagem de erro no campo
        if (!field) return;
        field.style.borderColor = '#e74c3c'; // Muda borda para vermelho
        let errorDiv = field.parentNode.querySelector('.field-error'); // Procura div de erro existente
        if (!errorDiv) { // Se não existe, cria nova div de erro
            errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.style.color = '#e74c3c'; // Texto vermelho
            errorDiv.style.fontSize = '12px'; // Fonte pequena
            errorDiv.style.marginTop = '5px'; // Espaçamento
            field.parentNode.appendChild(errorDiv); // Adiciona ao DOM
        }
        errorDiv.textContent = message; // Define mensagem de erro
    };

    var clearFieldError = function(field) { // Remove mensagem de erro do campo
        if (!field) return;
        field.style.borderColor = ''; // Remove cor vermelha da borda
        var errorDiv = field.parentNode.querySelector('.field-error'); // Procura div de erro
        if (errorDiv) errorDiv.remove(); // Remove div de erro se existir
    };

    // --- FUNÇÕES DE LÓGICA DO FORMULÁRIO ---
    let scriptsInitialized = false; // Flag para evitar inicialização dupla dos scripts

    function toggleFields() { // Alterna campos baseado no tipo de pessoa selecionado
        if (!tipoSelect) return; // Sai se select não existir

        if (tipoSelect.value) { // Se algum tipo foi selecionado
            if(mainFormContent) mainFormContent.style.display = 'block'; // Mostra formulário principal
            if(progressBar) progressBar.style.width = '50%'; // Atualiza barra de progresso
        } else { // Se nenhum tipo selecionado
            if(mainFormContent) mainFormContent.style.display = 'none'; // Esconde formulário
            if(progressBar) progressBar.style.width = '0%'; // Zera barra de progresso
        }

        if (tipoSelect.value === 'fisica') { // Se pessoa física selecionada
            if(fisicaFields) fisicaFields.style.display = 'block'; // Mostra campos PF
            if(juridicaFields) juridicaFields.style.display = 'none'; // Esconde campos PJ
            // Define campos obrigatórios para pessoa física
            if(document.getElementById('nome_cli')) document.getElementById('nome_cli').required = true;
            if(document.getElementById('cpf_cli')) document.getElementById('cpf_cli').required = true;
            if(document.getElementById('razao_social')) document.getElementById('razao_social').required = false;
            if(document.getElementById('cnpj')) document.getElementById('cnpj').required = false;
            if (!scriptsInitialized) initializeFormScripts(); // Inicializa scripts se necessário
        } else if (tipoSelect.value === 'juridica') { // Se pessoa jurídica selecionada
            if(fisicaFields) fisicaFields.style.display = 'none'; // Esconde campos PF
            if(juridicaFields) juridicaFields.style.display = 'block'; // Mostra campos PJ
            // Define campos obrigatórios para pessoa jurídica
            if(document.getElementById('nome_cli')) document.getElementById('nome_cli').required = false;
            if(document.getElementById('cpf_cli')) document.getElementById('cpf_cli').required = false;
            if(document.getElementById('razao_social')) document.getElementById('razao_social').required = true;
            if(document.getElementById('cnpj')) document.getElementById('cnpj').required = true;
            if (!scriptsInitialized) initializeFormScripts(); // Inicializa scripts se necessário
        } else { // Se nenhum tipo específico
            if(fisicaFields) fisicaFields.style.display = 'none'; // Esconde campos PF
            if(juridicaFields) juridicaFields.style.display = 'none'; // Esconde campos PJ
            // Remove obrigatoriedade de todos os campos
            if(document.getElementById('nome_cli')) document.getElementById('nome_cli').required = false;
            if(document.getElementById('cpf_cli')) document.getElementById('cpf_cli').required = false;
            if(document.getElementById('razao_social')) document.getElementById('razao_social').required = false;
            if(document.getElementById('cnpj')) document.getElementById('cnpj').required = false;
        }
    }


    function initializeFormScripts() { // Inicializa máscaras e validações dos campos
        // Aplica máscaras de formatação nos campos
        applyMask(document.getElementById('cpf_cli'), maskCpf); // Máscara CPF
        applyMask(document.getElementById('cnpj'), maskCnpj); // Máscara CNPJ
        applyMask(document.getElementById('tel1_cli'), maskPhone); // Máscara telefone 1
        applyMask(document.getElementById('tel2_cli'), maskPhone); // Máscara telefone 2
        applyMask(document.getElementById('rg_cli'), maskRg); // Máscara RG
        applyMask(document.getElementById('cep'), maskCep); // Máscara CEP

        // Obtém referências dos campos para validação em tempo real
        var cpfInput = document.getElementById('cpf_cli'); // Campo CPF
        var cnpjInput = document.getElementById('cnpj'); // Campo CNPJ
        var emailInput = document.getElementById('email_cli'); // Campo email
        var tel1Input = document.getElementById('tel1_cli'); // Campo telefone 1
        var tel2Input = document.getElementById('tel2_cli'); // Campo telefone 2
        var senhaInput = document.getElementById('senha_hash_cli'); // Campo senha (no edit form it may be optional)
        var confirmarSenhaInput = document.getElementById('confirmar_senha'); // Campo confirmar senha

        if (cpfInput) { // Se campo CPF existe
            cpfInput.addEventListener('blur', function() { // Valida quando campo perde foco
                if (this.value && !validateCpf(this.value)) { // Se tem valor e CPF é inválido
                    showFieldError(this, 'CPF inválido'); // Mostra erro
                } else { // Se CPF é válido ou campo vazio
                    clearFieldError(this); // Remove erro
                }
            });
        }

        if (cnpjInput) { // Se campo CNPJ existe
            cnpjInput.addEventListener('blur', function() { // Valida quando campo perde foco
                if (this.value && !validateCnpj(this.value)) { // Se tem valor e CNPJ é inválido
                    showFieldError(this, 'CNPJ inválido'); // Mostra erro
                } else { // Se CNPJ é válido ou campo vazio
                    clearFieldError(this); // Remove erro
                }
            });
        }

        if (emailInput) { // Se campo email existe
            emailInput.addEventListener('blur', function() { // Valida quando campo perde foco
                if (this.value && !validateEmail(this.value)) { // Se tem valor e email é inválido
                    showFieldError(this, 'Email inválido'); // Mostra erro
                } else { // Se email é válido ou campo vazio
                    clearFieldError(this); // Remove erro
                }
            });
        }

        if (tel1Input) { // Se campo telefone 1 existe
            tel1Input.addEventListener('blur', function() { // Valida quando campo perde foco
                if (this.value && !validatePhone(this.value)) { // Se tem valor e telefone é inválido
                    showFieldError(this, 'Telefone deve ter 10 ou 11 dígitos'); // Mostra erro
                } else { // Se telefone é válido ou campo vazio
                    clearFieldError(this); // Remove erro
                }
            });
        }

        if (tel2Input) { // Se campo telefone 2 existe
            tel2Input.addEventListener('blur', function() { // Valida quando campo perde foco
                if (this.value && !validatePhone(this.value)) { // Se tem valor e telefone é inválido
                    showFieldError(this, 'Telefone deve ter 10 ou 11 dígitos'); // Mostra erro
                } else { // Se telefone é válido ou campo vazio
                    clearFieldError(this); // Remove erro
                }
            });
        }

        if (senhaInput) { // Se campo senha existe
            senhaInput.addEventListener('blur', function() { // Valida quando campo perde foco
                if (this.value && !validatePassword(this.value)) { // Se tem valor e senha é inválida
                    showFieldError(this, 'Senha deve ter pelo menos 6 caracteres'); // Mostra erro
                } else { // Se senha é válida ou campo vazio
                    clearFieldError(this); // Remove erro
                }
            });
        }

        // Configura busca automática de endereço por CEP
        var cepInput = document.getElementById('cep'); // Campo CEP
        var cepLoading = document.getElementById('cep-loading'); // Indicador de carregamento
        if (cepInput) { // Se campo CEP existe
            cepInput.addEventListener('blur', function() { // Busca CEP quando campo perde foco
                var cep = this.value.replace(/\D/g, ''); // Remove formatação do CEP
                if (cep.length === 8) { // Se CEP tem 8 dígitos
                    if(cepLoading) cepLoading.style.display = 'inline'; // Mostra loading
                    fetch(`https://viacep.com.br/ws/${cep}/json/`) // Consulta API ViaCEP
                        .then(response => response.json()) // Converte resposta para JSON
                        .then(data => { // Processa dados retornados
                            if (!data.erro) { // Se CEP foi encontrado
                                document.getElementById('logradouro').value = data.logradouro; // Preenche logradouro
                                document.getElementById('bairro').value = data.bairro; // Preenche bairro
                                document.getElementById('cidade').value = data.localidade; // Preenche cidade
                                document.getElementById('uf').value = data.uf; // Preenche UF
                                updateEnderecoCompleto(); // Atualiza endereço completo
                                document.getElementById('num_end').focus(); // Foca no campo número
                                clearFieldError(this); // Remove erro
                            } else { // Se CEP não foi encontrado
                                showFieldError(this, 'CEP não encontrado'); // Mostra erro
                            }
                        })
                        .catch(error => { // Se houve erro na consulta
                            console.error('Erro ao buscar CEP:', error);
                            showFieldError(this, 'Erro ao buscar CEP'); // Mostra erro
                        })
                        .finally(() => { // Sempre executa no final
                            if(cepLoading) cepLoading.style.display = 'none'; // Esconde loading
                        });
                } else if (cep.length > 0) { // Se CEP tem dígitos mas não 8
                    showFieldError(this, 'CEP deve ter 8 dígitos'); // Mostra erro
                }
            });
        }

        // Monta endereço completo automaticamente baseado nos campos individuais
        var updateEnderecoCompleto = function() { // Função para atualizar endereço completo
            var logradouro = document.getElementById('logradouro').value; // Obtém logradouro
            var numero = document.getElementById('num_end').value; // Obtém número
            var complemento = document.getElementById('complemento').value; // Obtém complemento
            var bairro = document.getElementById('bairro').value; // Obtém bairro
            var cidade = document.getElementById('cidade').value; // Obtém cidade
            var uf = document.getElementById('uf').value; // Obtém UF
            
            var endereco = ''; // Inicia endereço vazio
            if (logradouro) endereco += logradouro; // Adiciona logradouro
            if (numero) endereco += ', ' + numero; // Adiciona número
            if (complemento) endereco += ', ' + complemento; // Adiciona complemento
            if (bairro) endereco += ' - ' + bairro; // Adiciona bairro
            if (cidade) endereco += ', ' + cidade; // Adiciona cidade
            if (uf) endereco += '/' + uf; // Adiciona UF
            
            document.getElementById('endereco').value = endereco; // Atualiza campo endereço completo
        };

        ['logradouro', 'num_end', 'complemento', 'bairro', 'cidade', 'uf'].forEach(function(id) { // Para cada campo de endereço
            var field = document.getElementById(id); // Obtém o campo
            if (field) field.addEventListener('input', updateEnderecoCompleto); // Atualiza endereço quando campo muda
        });

        // Configura verificação em tempo real de confirmação de senha
        var feedbackDiv = document.getElementById('password-feedback'); // Div para feedback visual
        function checkPasswords() { // Função para verificar se senhas coincidem
            if (!confirmarSenhaInput || !senhaInput || !feedbackDiv) return; // Sai se elementos não existem
            var senha = senhaInput.value; // Obtém senha
            var confirmarSenha = confirmarSenhaInput.value; // Obtém confirmação

            if (confirmarSenha.length === 0) { // Se confirmação está vazia
                feedbackDiv.textContent = ''; // Remove feedback
                clearFieldError(confirmarSenhaInput); // Remove erro
                return;
            }

            if (senha === confirmarSenha) { // Se senhas coincidem
                feedbackDiv.textContent = '✓ As senhas coincidem.'; // Feedback positivo
                feedbackDiv.style.color = 'green'; // Cor verde
                clearFieldError(confirmarSenhaInput); // Remove erro
            } else { // Se senhas não coincidem
                feedbackDiv.textContent = '✗ As senhas não coincidem.'; // Feedback negativo
                feedbackDiv.style.color = 'red'; // Cor vermelha
            }
        }
        if(senhaInput) senhaInput.addEventListener('input', checkPasswords); // Verifica quando senha muda
        if(confirmarSenhaInput) confirmarSenhaInput.addEventListener('input', checkPasswords); // Verifica quando confirmação muda

        scriptsInitialized = true; // Marca scripts como inicializados
    }

    // Funcionalidade de verificação de duplicatas
    function checkDuplicate(value, type, callback) {
        if (!value) {
            callback(false);
            return;
        }
        
        const formData = new FormData();
        formData.append(type === 'email' ? 'email' : (type === 'cpf' ? 'cpf' : 'cnpj'), value);
        
        const endpoint = window.BASE_URL + 'admin/clientes/verificar-' + type;
        
        fetch(endpoint, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            callback(data.duplicado, data.tipo, data.nome);
        })
        .catch(() => callback(false));
    }
    
    function showDuplicateError(field, message) {
        field.style.borderColor = '#e74c3c';
        const container = field.closest('.form-group');
        let errorDiv = container.querySelector('.duplicate-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'duplicate-error';
            errorDiv.style.color = '#e74c3c';
            errorDiv.style.fontSize = '12px';
            errorDiv.style.marginTop = '5px';
            errorDiv.style.fontWeight = 'bold';
            container.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
    }
    
    function clearDuplicateError(field) {
        field.style.borderColor = '';
        const container = field.closest('.form-group');
        const errorDiv = container.querySelector('.duplicate-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
    
    // Email duplicate check
    const emailFieldDup = document.getElementById('email_cli');
    if (emailFieldDup) {
        emailFieldDup.addEventListener('blur', function() {
            checkDuplicate(this.value, 'email', (isDuplicate, tipo, nome) => {
                if (isDuplicate) {
                    const message = tipo ? `Este email já está cadastrado como ${tipo}` : 'Este email já está cadastrado';
                    showDuplicateError(this, message);
                } else {
                    clearDuplicateError(this);
                }
            });
        });
    }
    
    // CPF duplicate check
    const cpfFieldDup = document.getElementById('cpf_cli');
    if (cpfFieldDup) {
        cpfFieldDup.addEventListener('blur', function() {
            checkDuplicate(this.value, 'cpf', (isDuplicate, tipo, nome) => {
                if (isDuplicate) {
                    const message = tipo ? `Este CPF já está cadastrado como ${tipo}` : 'Este CPF já está cadastrado';
                    showDuplicateError(this, message);
                } else {
                    clearDuplicateError(this);
                }
            });
        });
    }
    
    // CNPJ duplicate check
    const cnpjFieldDup = document.getElementById('cnpj');
    if (cnpjFieldDup) {
        cnpjFieldDup.addEventListener('blur', function() {
            checkDuplicate(this.value, 'cnpj', (isDuplicate, tipo, nome) => {
                if (isDuplicate) {
                    const message = tipo ? `Este CNPJ já está cadastrado como ${tipo}` : 'Este CNPJ já está cadastrado';
                    showDuplicateError(this, message);
                } else {
                    clearDuplicateError(this);
                }
            });
        });
    }

    // Configura validação completa no envio do formulário
    if (form) { // Se formulário existe
            form.addEventListener('submit', function(e) { // Escuta evento de envio
                // Remove máscaras dos campos antes do envio
                var cpfField = document.getElementById('cpf_cli');
                var cnpjField = document.getElementById('cnpj');
                var tel1Field = document.getElementById('tel1_cli');
                var tel2Field = document.getElementById('tel2_cli');
                var rgField = document.getElementById('rg_cli');
                var cepField = document.getElementById('cep');

                if (cpfField && cpfField.value) cpfField.value = removeMask(cpfField.value);
                // Para CNPJ preservamos letras, removendo apenas pontuação/espacos
                if (cnpjField && cnpjField.value) cnpjField.value = removeCnpjMask(cnpjField.value);
                if (tel1Field && tel1Field.value) tel1Field.value = removePhoneMask(tel1Field.value);
                if (tel2Field && tel2Field.value) tel2Field.value = removePhoneMask(tel2Field.value);
                if (rgField && rgField.value) rgField.value = removeMask(rgField.value);
                if (cepField && cepField.value) cepField.value = removeMask(cepField.value);

            var hasErrors = false; // Flag para controlar se há erros
            var tipoValue = tipoSelect ? tipoSelect.value : ''; // Obtém tipo selecionado

            // Remove todos os erros visuais anteriores
            document.querySelectorAll('.field-error').forEach(function(error) { error.remove(); }); // Remove divs de erro
            document.querySelectorAll('input, select').forEach(function(field) { field.style.borderColor = ''; }); // Remove bordas vermelhas

            // Valida se tipo de pessoa foi selecionado
            if (!tipoValue) { // Se nenhum tipo foi selecionado
                showFieldError(tipoSelect, 'Selecione o tipo de pessoa'); // Mostra erro
                hasErrors = true; // Marca que há erro
            }

            if (tipoValue === 'fisica') { // Se pessoa física foi selecionada
                // Validações específicas para pessoa física
                var nomeInput = document.getElementById('nome_cli'); // Campo nome
                var cpfInput = document.getElementById('cpf_cli'); // Campo CPF
                
                if (!nomeInput.value.trim()) { // Se nome está vazio
                    showFieldError(nomeInput, 'Nome é obrigatório'); // Mostra erro
                    hasErrors = true; // Marca erro
                }
                
                if (!cpfInput.value.trim()) { // Se CPF está vazio
                    showFieldError(cpfInput, 'CPF é obrigatório'); // Mostra erro
                    hasErrors = true; // Marca erro
                } else if (!validateCpf(cpfInput.value)) { // Se CPF é inválido
                    showFieldError(cpfInput, 'CPF inválido'); // Mostra erro
                    hasErrors = true; // Marca erro
                }
            } else if (tipoValue === 'juridica') { // Se pessoa jurídica foi selecionada
                // Validações específicas para pessoa jurídica
                var razaoInput = document.getElementById('razao_social'); // Campo razão social
                var cnpjInput = document.getElementById('cnpj'); // Campo CNPJ
                
                if (!razaoInput.value.trim()) { // Se razão social está vazia
                    showFieldError(razaoInput, 'Razão social é obrigatória'); // Mostra erro
                    hasErrors = true; // Marca erro
                }
                
                if (!cnpjInput.value.trim()) { // Se CNPJ está vazio
                    showFieldError(cnpjInput, 'CNPJ é obrigatório'); // Mostra erro
                    hasErrors = true; // Marca erro
                } else if (!validateCnpj(cnpjInput.value)) { // Se CNPJ é inválido
                    showFieldError(cnpjInput, 'CNPJ inválido'); // Mostra erro
                    hasErrors = true; // Marca erro
                }
            }

            // Obtém referências para validações comuns a todos os tipos
            var emailInput = document.getElementById('email_cli'); // Campo email
            var tel1Input = document.getElementById('tel1_cli'); // Campo telefone principal
            var senhaInput = document.getElementById('senha_hash_cli'); // Campo senha (optional on edit)
            var confirmarSenhaInput = document.getElementById('confirmar_senha'); // Campo confirmar senha
            var logradouroInput = document.getElementById('logradouro'); // Campo logradouro
            var numEndInput = document.getElementById('num_end'); // Campo número
            var bairroInput = document.getElementById('bairro'); // Campo bairro
            var cidadeInput = document.getElementById('cidade'); // Campo cidade
            var ufInput = document.getElementById('uf'); // Campo UF
            var enderecoInput = document.getElementById('endereco'); // Campo endereço completo

            if (!emailInput.value.trim()) {
                showFieldError(emailInput, 'Email é obrigatório');
                hasErrors = true;
            } else if (!validateEmail(emailInput.value)) {
                showFieldError(emailInput, 'Email inválido');
                hasErrors = true;
            }

            if (!tel1Input.value.trim()) {
                showFieldError(tel1Input, 'Telefone principal é obrigatório');
                hasErrors = true;
            } else if (!validatePhone(tel1Input.value)) {
                showFieldError(tel1Input, 'Telefone inválido');
                hasErrors = true;
            }

            // Password is OPTIONAL on edit: only validate if provided
            if (senhaInput && senhaInput.value) {
                if (!validatePassword(senhaInput.value)) {
                    showFieldError(senhaInput, 'Senha deve ter pelo menos 6 caracteres');
                    hasErrors = true;
                }
                if (!confirmarSenhaInput || confirmarSenhaInput.value !== senhaInput.value) {
                    showFieldError(confirmarSenhaInput || senhaInput, 'Confirmação de senha inválida');
                    hasErrors = true;
                }
            }

            if (!logradouroInput.value.trim()) {
                showFieldError(logradouroInput, 'Logradouro é obrigatório');
                hasErrors = true;
            }

            if (!numEndInput.value.trim()) {
                showFieldError(numEndInput, 'Número é obrigatório');
                hasErrors = true;
            }

            if (!bairroInput.value.trim()) {
                showFieldError(bairroInput, 'Bairro é obrigatório');
                hasErrors = true;
            }

            if (!cidadeInput.value.trim()) {
                showFieldError(cidadeInput, 'Cidade é obrigatória');
                hasErrors = true;
            }

            if (!ufInput.value.trim()) {
                showFieldError(ufInput, 'UF é obrigatório');
                hasErrors = true;
            } else if (ufInput.value.length !== 2) {
                showFieldError(ufInput, 'UF deve ter 2 caracteres');
                hasErrors = true;
            }

            if (!enderecoInput.value.trim()) {
                showFieldError(enderecoInput, 'Endereço completo é obrigatório');
                hasErrors = true;
            }

            if (hasErrors) { // Se há erros no formulário
                e.preventDefault(); // Impede envio do formulário
                // Rola a página para o primeiro erro encontrado
                var firstError = document.querySelector('.field-error'); // Encontra primeiro erro
                if (firstError) { // Se erro existe
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' }); // Rola suavemente até o erro
                }
            }
        });
    }

    // --- INICIALIZAÇÃO FINAL ---
    if(tipoSelect) { // Se select de tipo existe
        tipoSelect.addEventListener('change', toggleFields); // Escuta mudanças no select
        toggleFields(); // Executa para estado inicial
    }

    // Configura data máxima nos campos de data para impedir datas futuras
    var today = new Date().toISOString().split('T')[0]; // Obtém data atual no formato YYYY-MM-DD
    var dataNascimentoInput = document.getElementById('data_nascimento_cli'); // Campo data nascimento
    var dataExpedicaoRgInput = document.getElementById('data_expedicao_rg_cli'); // Campo data expedição RG
    if (dataNascimentoInput) dataNascimentoInput.setAttribute('max', today); // Define data máxima para nascimento
    if (dataExpedicaoRgInput) dataExpedicaoRgInput.setAttribute('max', today); // Define data máxima para expedição RG
});
