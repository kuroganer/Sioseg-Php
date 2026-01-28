<?php
$base_url = defined('BASE_URL') ? BASE_URL : '/';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIOSeG - Esqueceu a Senha</title>
    <base href="<?php echo $base_url; ?>" />
    <link rel="stylesheet" type="text/css" href="assets/css/stylelogin.css">
    <link rel="stylesheet" type="text/css" href="assets/css/login-alerts.css">
    <link rel="stylesheet" type="text/css" href="assets/css/forgot-password.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-key"></i>
                    <h1>Recuperar Senha</h1>
                </div>
                <p class="subtitle">Sistema Integrado de Ordens de Serviço</p>
            </div>

            <div class="step-indicator">
                <div class="step active" id="step1">1</div>
                <div class="step" id="step2">2</div>
            </div>

            <form method="post" action="forgot-password" class="login-form" id="forgotForm">
                
                <?php if ($error_message) : ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success_message) : ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <!-- Etapa 1: Verificação -->
                <div class="form-step active" id="formStep1">
                    <div class="form-group">
                        <label for="emailInput">
                            <i class="fas fa-envelope"></i>
                            Email de Acesso
                        </label>
                        <input type="email" id="emailInput" name="email" placeholder="seu.email@exemplo.com" required autocomplete="email">
                    </div>

                    <div class="form-group">
                        <label for="documentoInput">
                            <i class="fas fa-id-card"></i>
                            CPF ou CNPJ
                        </label>
                        <div class="documento-input">
                            <input type="text" id="documentoInput" name="documento" placeholder="000.000.000-00 ou 00.000.000/0000-00" required maxlength="18" autocomplete="off">
                            <span class="documento-type" id="documentoType"></span>
                        </div>
                    </div>

                    <button type="button" class="login-btn" onclick="verificarDados()">
                        <i class="fas fa-search"></i>
                        Verificar Dados
                    </button>
                </div>

                <!-- Etapa 2: Nova Senha (inicialmente oculta) -->
                <div class="form-step" id="formStep2">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Dados verificados com sucesso! Defina sua nova senha de acesso.
                    </div>

                    <div class="form-group">
                        <label for="novaSenhaInput">
                            <i class="fas fa-lock"></i>
                            Nova Senha
                        </label>
                        <input type="password" id="novaSenhaInput" name="nova_senha" placeholder="Mínimo 6 caracteres" minlength="6" autocomplete="new-password">
                    </div>

                    <div class="form-group">
                        <label for="confirmarSenhaInput">
                            <i class="fas fa-lock"></i>
                            Confirmar Senha
                        </label>
                        <input type="password" id="confirmarSenhaInput" name="confirmar_senha" placeholder="Digite a senha novamente" minlength="6" autocomplete="new-password">
                    </div>

                    <button type="button" class="login-btn" onclick="alterarSenha()">
                        <i class="fas fa-save"></i>
                        Alterar Senha
                    </button>

                    <button type="button" class="login-btn" onclick="voltarEtapa1()" style="background: #6c757d; margin-top: 10px;">
                        <i class="fas fa-arrow-left"></i>
                        Voltar
                    </button>
                </div>
            </form>

            <div class="back-link">
                <a href="login">
                    <i class="fas fa-arrow-left"></i>
                    Voltar ao Login
                </a>
            </div>
        </div>
    </div>

    <script>
        // Máscara para CPF/CNPJ
        document.getElementById('documentoInput').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            let formatted = '';
            let type = '';

            if (value.length <= 11) {
                // CPF
                formatted = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
                type = 'CPF';
            } else {
                // CNPJ
                formatted = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
                type = 'CNPJ';
            }

            e.target.value = formatted;
            document.getElementById('documentoType').textContent = type;
            
            // Validação visual
            if (isValidDocument(formatted)) {
                e.target.classList.remove('invalid');
                e.target.classList.add('valid');
            } else {
                e.target.classList.remove('valid');
                e.target.classList.add('invalid');
            }
        });
        
        // Validação do email
        document.getElementById('emailInput').addEventListener('input', function(e) {
            if (isValidEmail(e.target.value)) {
                e.target.classList.remove('invalid');
                e.target.classList.add('valid');
            } else {
                e.target.classList.remove('valid');
                e.target.classList.add('invalid');
            }
        });

        function verificarDados() {
            const email = document.getElementById('emailInput').value.trim();
            const documento = document.getElementById('documentoInput').value.trim();
            const btn = document.querySelector('.login-btn');

            if (!email || !documento) {
                alert('Por favor, preencha todos os campos.');
                return;
            }

            if (!isValidEmail(email)) {
                alert('Por favor, digite um email válido.');
                return;
            }

            if (!isValidDocument(documento)) {
                alert('Por favor, digite um CPF ou CNPJ válido.');
                return;
            }

            // Desabilita o botão e mostra loading
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';

            // Faz a verificação via AJAX
            const formData = new FormData();
            formData.append('email', email);
            formData.append('documento', documento);

            fetch('verify-user-data', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarEtapa2();
                } else {
                    alert(data.message || 'Erro na verificação dos dados.');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro de conexão. Tente novamente.');
            })
            .finally(() => {
                // Reabilita o botão
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-search"></i> Verificar Dados';
            });
        }

        function mostrarEtapa2() {
            document.getElementById('formStep1').classList.remove('active');
            document.getElementById('formStep2').classList.add('active');
            document.getElementById('step1').classList.add('completed');
            document.getElementById('step1').classList.remove('active');
            document.getElementById('step2').classList.add('active');
            
            // Torna os campos da etapa 2 obrigatórios
            document.getElementById('novaSenhaInput').required = true;
            document.getElementById('confirmarSenhaInput').required = true;
        }

        function voltarEtapa1() {
            document.getElementById('formStep2').classList.remove('active');
            document.getElementById('formStep1').classList.add('active');
            document.getElementById('step1').classList.remove('completed');
            document.getElementById('step1').classList.add('active');
            document.getElementById('step2').classList.remove('active');
            
            // Remove obrigatoriedade dos campos da etapa 2
            document.getElementById('novaSenhaInput').required = false;
            document.getElementById('confirmarSenhaInput').required = false;
            
            // Limpa os campos de senha
            document.getElementById('novaSenhaInput').value = '';
            document.getElementById('confirmarSenhaInput').value = '';
        }

        function alterarSenha() {
            const novaSenha = document.getElementById('novaSenhaInput').value;
            const confirmarSenha = document.getElementById('confirmarSenhaInput').value;

            if (!novaSenha || !confirmarSenha) {
                alert('Por favor, preencha todos os campos de senha.');
                return;
            }

            if (novaSenha.length < 6) {
                alert('A senha deve ter pelo menos 6 caracteres.');
                return;
            }

            if (novaSenha !== confirmarSenha) {
                alert('As senhas não coincidem.');
                return;
            }

            // Submete o formulário
            document.getElementById('forgotForm').action = 'reset-password';
            document.getElementById('forgotForm').submit();
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function isValidDocument(doc) {
            const numbers = doc.replace(/\D/g, '');
            return numbers.length === 11 || numbers.length === 14;
        }

        // Validação em tempo real das senhas
        document.getElementById('confirmarSenhaInput').addEventListener('input', function() {
            const novaSenha = document.getElementById('novaSenhaInput').value;
            const confirmarSenha = this.value;
            
            if (confirmarSenha && novaSenha !== confirmarSenha) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '';
            }
        });
    </script>
</body>
</html>