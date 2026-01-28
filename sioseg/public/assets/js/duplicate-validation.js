// Validação de duplicatas em tempo real
console.log('duplicate-validation.js carregado!');
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado, iniciando validação de duplicatas');
    
    // Função para verificar email duplicado
    function checkEmailDuplicate(email, excludeId, endpoint, callback) {
        if (!email || email.length < 5) {
            callback({duplicado: false});
            return;
        }
        
        const formData = new FormData();
        formData.append('email', email);
        if (excludeId) formData.append('exclude_id', excludeId);
        
        fetch(endpoint, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            callback(data);
        })
        .catch(error => {
            console.error('Erro ao verificar email:', error);
            callback({duplicado: false});
        });
    }
    
    // Função para verificar CPF duplicado
    function checkCpfDuplicate(cpf, excludeId, endpoint, callback) {
        if (!cpf || cpf.replace(/\D/g, '').length !== 11) {
            callback({duplicado: false});
            return;
        }
        
        const formData = new FormData();
        formData.append('cpf', cpf);
        if (excludeId) formData.append('exclude_id', excludeId);
        
        fetch(endpoint, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            callback(data);
        })
        .catch(error => {
            console.error('Erro ao verificar CPF:', error);
            callback({duplicado: false});
        });
    }
    
    // Função para verificar CNPJ duplicado
    function checkCnpjDuplicate(cnpj, excludeId, endpoint, callback) {
        if (!cnpj || cnpj.replace(/\D/g, '').length !== 14) {
            callback({duplicado: false});
            return;
        }
        
        const formData = new FormData();
        formData.append('cnpj', cnpj);
        if (excludeId) formData.append('exclude_id', excludeId);
        
        fetch(endpoint, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            callback(data);
        })
        .catch(error => {
            console.error('Erro ao verificar CNPJ:', error);
            callback({duplicado: false});
        });
    }
    
    // Função para mostrar erro de duplicata
    function showDuplicateError(field, message) {
        field.style.borderColor = '#e74c3c';
        let errorDiv = field.parentNode.querySelector('.duplicate-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'duplicate-error';
            errorDiv.style.color = '#e74c3c';
            errorDiv.style.fontSize = '12px';
            errorDiv.style.marginTop = '5px';
            errorDiv.style.fontWeight = 'bold';
            field.parentNode.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
    }
    
    // Função para limpar erro de duplicata
    function clearDuplicateError(field) {
        field.style.borderColor = '';
        const errorDiv = field.parentNode.querySelector('.duplicate-error');
        if (errorDiv) errorDiv.remove();
    }
    
    // Configurar validação para clientes
    const clienteEmailField = document.getElementById('email_cli');
    const clienteCpfField = document.getElementById('cpf_cli');
    const clienteCnpjField = document.getElementById('cnpj');
    const clienteExcludeId = document.querySelector('input[name="cliente_id"]')?.value;
    
    // Determinar endpoint baseado na URL atual
    const isAdmin = window.location.pathname.includes('/admin/');
    const isFuncionario = window.location.pathname.includes('/funcionario/');
    
    let clienteEmailEndpoint = BASE_URL + 'admin/clientes/verificar-email';
    let clienteCpfEndpoint = BASE_URL + 'admin/clientes/verificar-cpf';
    let clienteCnpjEndpoint = BASE_URL + 'admin/clientes/verificar-cnpj';
    
    if (isFuncionario) {
        clienteEmailEndpoint = BASE_URL + 'funcionario/clientes/verificar-email';
        clienteCpfEndpoint = BASE_URL + 'funcionario/clientes/verificar-cpf';
        clienteCnpjEndpoint = BASE_URL + 'funcionario/clientes/verificar-cnpj';
    }
    
    if (clienteEmailField) {
        console.log('Campo email_cli encontrado, adicionando listener');
        let emailTimeout;
        clienteEmailField.addEventListener('blur', function() {
            console.log('Email blur disparado:', this.value);
            clearTimeout(emailTimeout);
            emailTimeout = setTimeout(() => {
                checkEmailDuplicate(
                    this.value,
                    clienteExcludeId,
                    clienteEmailEndpoint,
                    (result) => {
                        if (result.duplicado) {
                            const message = result.tipo ? 
                                `Este email já está cadastrado como ${result.tipo}` : 
                                'Este email já está cadastrado';
                            showDuplicateError(this, message);
                        } else {
                            clearDuplicateError(this);
                        }
                    }
                );
            }, 500);
        });
    }
    
    if (clienteCpfField) {
        console.log('Campo cpf_cli encontrado, adicionando listener');
        let cpfTimeout;
        clienteCpfField.addEventListener('blur', function() {
            console.log('CPF blur disparado:', this.value);
            clearTimeout(cpfTimeout);
            cpfTimeout = setTimeout(() => {
                checkCpfDuplicate(
                    this.value,
                    clienteExcludeId,
                    clienteCpfEndpoint,
                    (result) => {
                        if (result.duplicado) {
                            const message = result.tipo ? 
                                `Este CPF já está cadastrado como ${result.tipo}` : 
                                'Este CPF já está cadastrado';
                            showDuplicateError(this, message);
                        } else {
                            clearDuplicateError(this);
                        }
                    }
                );
            }, 500);
        });
    }
    
    if (clienteCnpjField) {
        let cnpjTimeout;
        clienteCnpjField.addEventListener('blur', function() {
            clearTimeout(cnpjTimeout);
            cnpjTimeout = setTimeout(() => {
                checkCnpjDuplicate(
                    this.value,
                    clienteExcludeId,
                    clienteCnpjEndpoint,
                    (result) => {
                        if (result.duplicado) {
                            const message = result.tipo ? 
                                `Este CNPJ já está cadastrado como ${result.tipo}` : 
                                'Este CNPJ já está cadastrado';
                            showDuplicateError(this, message);
                        } else {
                            clearDuplicateError(this);
                        }
                    }
                );
            }, 500);
        });
    }
    
    // Configurar validação para técnicos
    const tecnicoEmailField = document.getElementById('email_tec');
    const tecnicoCpfField = document.getElementById('cpf_tec');
    const tecnicoExcludeId = document.querySelector('input[name="tecnico_id"]')?.value;
    
    if (tecnicoEmailField) {
        let emailTimeout;
        tecnicoEmailField.addEventListener('blur', function() {
            clearTimeout(emailTimeout);
            emailTimeout = setTimeout(() => {
                checkEmailDuplicate(
                    this.value,
                    tecnicoExcludeId,
                    BASE_URL + 'admin/tecnicos/verificar-email',
                    (result) => {
                        if (result.duplicado) {
                            const message = result.tipo ? 
                                `Este email já está cadastrado como ${result.tipo}` : 
                                'Este email já está cadastrado';
                            showDuplicateError(this, message);
                        } else {
                            clearDuplicateError(this);
                        }
                    }
                );
            }, 500);
        });
    }
    
    if (tecnicoCpfField) {
        let cpfTimeout;
        tecnicoCpfField.addEventListener('blur', function() {
            clearTimeout(cpfTimeout);
            cpfTimeout = setTimeout(() => {
                checkCpfDuplicate(
                    this.value,
                    tecnicoExcludeId,
                    BASE_URL + 'admin/tecnicos/verificar-cpf',
                    (result) => {
                        if (result.duplicado) {
                            const message = result.tipo ? 
                                `Este CPF já está cadastrado como ${result.tipo}` : 
                                'Este CPF já está cadastrado';
                            showDuplicateError(this, message);
                        } else {
                            clearDuplicateError(this);
                        }
                    }
                );
            }, 500);
        });
    }
    
    // Configurar validação para usuários
    const usuarioEmailField = document.getElementById('email_usu');
    const usuarioCpfField = document.getElementById('cpf_usu');
    const usuarioExcludeId = document.querySelector('input[name="user_id"]')?.value;
    
    if (usuarioEmailField) {
        let emailTimeout;
        usuarioEmailField.addEventListener('blur', function() {
            clearTimeout(emailTimeout);
            emailTimeout = setTimeout(() => {
                checkEmailDuplicate(
                    this.value,
                    usuarioExcludeId,
                    BASE_URL + 'admin/users/verificar-email',
                    (result) => {
                        if (result.duplicado) {
                            const message = result.tipo ? 
                                `Este email já está cadastrado como ${result.tipo}` : 
                                'Este email já está cadastrado';
                            showDuplicateError(this, message);
                        } else {
                            clearDuplicateError(this);
                        }
                    }
                );
            }, 500);
        });
    }
    
    if (usuarioCpfField) {
        let cpfTimeout;
        usuarioCpfField.addEventListener('blur', function() {
            clearTimeout(cpfTimeout);
            cpfTimeout = setTimeout(() => {
                checkCpfDuplicate(
                    this.value,
                    usuarioExcludeId,
                    BASE_URL + 'admin/users/verificar-cpf',
                    (result) => {
                        if (result.duplicado) {
                            const message = result.tipo ? 
                                `Este CPF já está cadastrado como ${result.tipo}` : 
                                'Este CPF já está cadastrado';
                            showDuplicateError(this, message);
                        } else {
                            clearDuplicateError(this);
                        }
                    }
                );
            }, 500);
        });
    }
    
    // Prevenir envio do formulário se houver duplicatas
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const duplicateErrors = this.querySelectorAll('.duplicate-error');
            if (duplicateErrors.length > 0) {
                e.preventDefault();
                alert('Corrija os erros de duplicação antes de continuar.');
                duplicateErrors[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });
});