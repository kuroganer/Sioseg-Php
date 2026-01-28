document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('form');
    var progressBar = document.getElementById('progress-bar');
    var isEditMode = window.currentUri.includes('/edit');

    // --- FUNÇÕES PARA REMOVER MÁSCARAS ---
    var removeMask = function(value) {
        return value ? value.replace(/\D/g, '') : '';
    };

    var removePhoneMask = function(value) {
        return value ? value.replace(/[()\s-]/g, '') : '';
    };

    var maskCpf = function(value) {
        return value
            .replace(/\D/g, '')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    };

    var maskRg = function(value) {
        value = value.replace(/[^\dX]/gi, '').toUpperCase();
        return value.replace(/(\d{1})(\d{0,3})(\d{0,3})/, function(match, p1, p2, p3) {
            var result = p1;
            if (p2) result += '.' + p2;
            if (p3) result += '.' + p3;
            return result;
        });
    };

    var maskPhone = function(value) {
        value = value.replace(/\D/g, '');
        if (value.length > 10) {
            return value.replace(/(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
        } else {
            return value.replace(/(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
        }
    };
    
    var applyMask = function(field, maskFunction) {
        if (field) {
            field.addEventListener('input', function(e) {
                e.target.value = maskFunction(e.target.value);
            });
        }
    };

    var validateCpf = function(cpf) {
        cpf = cpf.replace(/\D/g, '');
        if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false;
        var sum = 0;
        for (var i = 0; i < 9; i++) sum += parseInt(cpf[i]) * (10 - i);
        var digit1 = 11 - (sum % 11);
        if (digit1 > 9) digit1 = 0;
        sum = 0;
        for (var i = 0; i < 10; i++) sum += parseInt(cpf[i]) * (11 - i);
        var digit2 = 11 - (sum % 11);
        if (digit2 > 9) digit2 = 0;
        return digit1 === parseInt(cpf[9]) && digit2 === parseInt(cpf[10]);
    };

    var validateEmail = function(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    };

    var validatePhone = function(phone) {
        var digits = phone.replace(/\D/g, '');
        return digits.length >= 10 && digits.length <= 11;
    };

    var validatePassword = function(password) {
        return password.length >= 6;
    };

    var showFieldError = function(field, message) {
        if (!field) return;
        field.style.borderColor = '#e74c3c';
        var errorDiv = field.parentNode.querySelector('.field-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.style.color = '#e74c3c';
            errorDiv.style.fontSize = '12px';
            errorDiv.style.marginTop = '5px';
            field.parentNode.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
    };

    var clearFieldError = function(field) {
        if (!field) return;
        field.style.borderColor = '';
        var errorDiv = field.parentNode.querySelector('.field-error');
        if (errorDiv) errorDiv.remove();
    };

    function initializeFormScripts() {
        applyMask(document.getElementById('cpf_tec'), maskCpf);
        applyMask(document.getElementById('tel_pessoal'), maskPhone);
        applyMask(document.getElementById('tel_empresa'), maskPhone);
        applyMask(document.getElementById('rg_tec'), maskRg);

        var cpfInput = document.getElementById('cpf_tec');
        var emailInput = document.getElementById('email_tec');
        var telPessoalInput = document.getElementById('tel_pessoal');
        var telEmpresaInput = document.getElementById('tel_empresa');
        var senhaInput = document.getElementById('senha');
        var confirmarSenhaInput = document.getElementById('confirmar_senha');

        if (cpfInput) {
            cpfInput.addEventListener('blur', function() {
                if (this.value && !validateCpf(this.value)) {
                    showFieldError(this, 'CPF inválido');
                } else {
                    clearFieldError(this);
                }
            });
        }
        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                if (this.value && !validateEmail(this.value)) {
                    showFieldError(this, 'Email inválido');
                } else {
                    clearFieldError(this);
                }
            });
        }
        if (telPessoalInput) {
            telPessoalInput.addEventListener('blur', function() {
                if (this.value && !validatePhone(this.value)) {
                    showFieldError(this, 'Telefone deve ter 10 ou 11 dígitos');
                } else {
                    clearFieldError(this);
                }
            });
        }

        if (telEmpresaInput) {
            telEmpresaInput.addEventListener('blur', function() {
                if (this.value && !validatePhone(this.value)) {
                    showFieldError(this, 'Telefone deve ter 10 ou 11 dígitos');
                } else {
                    clearFieldError(this);
                }
            });
        }

        if (senhaInput) {
            senhaInput.addEventListener('blur', function() {
                if (this.value && !validatePassword(this.value)) {
                    showFieldError(this, 'Senha deve ter pelo menos 6 caracteres');
                } else {
                    clearFieldError(this);
                }
            });
        }

        var feedbackDiv = document.getElementById('password-feedback');
        function checkPasswords() {
            if (!confirmarSenhaInput || !senhaInput || !feedbackDiv) return;
            var senha = senhaInput.value;
            var confirmarSenha = confirmarSenhaInput.value;

            if (confirmarSenha.length === 0) {
                feedbackDiv.textContent = '';
                clearFieldError(confirmarSenhaInput);
                return;
            }

            if (senha === confirmarSenha) {
                feedbackDiv.textContent = '✓ As senhas coincidem.';
                feedbackDiv.style.color = 'green';
                clearFieldError(confirmarSenhaInput);
            } else {
                feedbackDiv.textContent = '✗ As senhas não coincidem.';
                feedbackDiv.style.color = 'red';
            }
        }
        if(senhaInput) senhaInput.addEventListener('input', checkPasswords);
        if(confirmarSenhaInput) confirmarSenhaInput.addEventListener('input', checkPasswords);
    }
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Remove máscaras dos campos antes do envio
            var cpfField = document.getElementById('cpf_tec');
            var telPessoalField = document.getElementById('tel_pessoal');
            var telEmpresaField = document.getElementById('tel_empresa');
            var rgField = document.getElementById('rg_tec');

            if (cpfField && cpfField.value) cpfField.value = removeMask(cpfField.value);
            if (telPessoalField && telPessoalField.value) telPessoalField.value = removePhoneMask(telPessoalField.value);
            if (telEmpresaField && telEmpresaField.value) telEmpresaField.value = removePhoneMask(telEmpresaField.value);
            if (rgField && rgField.value) rgField.value = removeMask(rgField.value);

            var hasErrors = false;

            var fieldErrors = document.querySelectorAll('.field-error');
            for (var i = 0; i < fieldErrors.length; i++) {
                fieldErrors[i].remove();
            }
            
            var allFields = document.querySelectorAll('input, select');
            for (var i = 0; i < allFields.length; i++) {
                allFields[i].style.borderColor = '';
            }

            var emailInput = document.getElementById('email_tec');
            var telPessoalInput = document.getElementById('tel_pessoal');
            var senhaInput = document.getElementById('senha');
            var confirmarSenhaInput = document.getElementById('confirmar_senha');
            var cpfInput = document.getElementById('cpf_tec');
            
            if (cpfInput && !validateCpf(cpfInput.value)) {
                showFieldError(cpfInput, 'CPF inválido');
                hasErrors = true;
            }

            if (!emailInput.value.trim()) {
                showFieldError(emailInput, 'Email é obrigatório');
                hasErrors = true;
            } else if (!validateEmail(emailInput.value)) {
                showFieldError(emailInput, 'Email inválido');
                hasErrors = true;
            }

            if (!telPessoalInput.value.trim()) {
                showFieldError(telPessoalInput, 'Telefone pessoal é obrigatório');
                hasErrors = true;
            } else if (!validatePhone(telPessoalInput.value)) {
                showFieldError(telPessoalInput, 'Telefone inválido');
                hasErrors = true;
            }

            if (isEditMode) {
                // Senha é opcional na edição
                if (senhaInput.value) {
                    if (!validatePassword(senhaInput.value)) {
                        showFieldError(senhaInput, 'Senha deve ter pelo menos 6 caracteres');
                        hasErrors = true;
                    }
                    if (senhaInput.value !== confirmarSenhaInput.value) {
                        showFieldError(confirmarSenhaInput, 'Senhas não coincidem');
                        hasErrors = true;
                    }
                } else if (confirmarSenhaInput.value) {
                    showFieldError(senhaInput, 'Digite a nova senha');
                    hasErrors = true;
                }
            } else {
                // Senha é obrigatória no cadastro
                if (!senhaInput.value) {
                    showFieldError(senhaInput, 'Senha é obrigatória');
                    hasErrors = true;
                } else if (!validatePassword(senhaInput.value)) {
                    showFieldError(senhaInput, 'Senha deve ter pelo menos 6 caracteres');
                    hasErrors = true;
                }

                if (!confirmarSenhaInput.value) {
                    showFieldError(confirmarSenhaInput, 'Confirmação de senha é obrigatória');
                    hasErrors = true;
                } else if (senhaInput.value !== confirmarSenhaInput.value) {
                    showFieldError(confirmarSenhaInput, 'Senhas não coincidem');
                    hasErrors = true;
                }
            }

            if (hasErrors) {
                e.preventDefault();
                return false;
            }
        });
    }

    function checkDuplicate(value, type, callback) {
        if (!value) {
            callback(false);
            return;
        }
        
        const formData = new FormData();
        formData.append(type === 'email' ? 'email' : 'cpf', value);
        
        const endpoint = window.BASE_URL + 'admin/tecnicos/verificar-' + type;
        
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
    const emailField = document.getElementById('email_tec');
    if (emailField) {
        emailField.addEventListener('blur', function() {
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
    const cpfField = document.getElementById('cpf_tec');
    if (cpfField) {
        cpfField.addEventListener('blur', function() {
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

    initializeFormScripts();
});