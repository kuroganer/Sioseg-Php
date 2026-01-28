<?php
$base_url = defined('BASE_URL') ? BASE_URL : '/';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIOSeG - Nova Senha</title>
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
                    <h1>Nova Senha</h1>
                </div>
                <p class="subtitle">Defina sua nova senha de acesso</p>
            </div>

            <form method="post" action="reset-password" class="login-form" id="resetForm">
                
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

                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Dados verificados com sucesso! Defina sua nova senha de acesso.
                </div>

                <div class="form-group">
                    <label for="novaSenhaInput">
                        <i class="fas fa-lock"></i>
                        Nova Senha
                    </label>
                    <input type="password" id="novaSenhaInput" name="nova_senha" placeholder="Mínimo 6 caracteres" required minlength="6" autocomplete="new-password">
                    <div class="password-strength" id="passwordStrength"></div>
                </div>

                <div class="form-group">
                    <label for="confirmarSenhaInput">
                        <i class="fas fa-lock"></i>
                        Confirmar Senha
                    </label>
                    <input type="password" id="confirmarSenhaInput" name="confirmar_senha" placeholder="Digite a senha novamente" required minlength="6" autocomplete="new-password">
                    <div class="password-match" id="passwordMatch"></div>
                </div>

                <button type="submit" class="login-btn" id="submitBtn" disabled>
                    <i class="fas fa-save"></i>
                    Alterar Senha
                </button>
            </form>

            <div class="back-link" style="text-align: center; margin-top: 15px;">
                <a href="forgot-password" style="color: #007bff; text-decoration: none; font-size: 14px;">
                    <i class="fas fa-arrow-left"></i>
                    Voltar à Verificação
                </a>
            </div>
        </div>
    </div>

    <script>
        const novaSenhaInput = document.getElementById('novaSenhaInput');
        const confirmarSenhaInput = document.getElementById('confirmarSenhaInput');
        const passwordStrength = document.getElementById('passwordStrength');
        const passwordMatch = document.getElementById('passwordMatch');
        const submitBtn = document.getElementById('submitBtn');

        // Validação de força da senha
        novaSenhaInput.addEventListener('input', function() {
            const senha = this.value;
            const strength = calculatePasswordStrength(senha);
            
            passwordStrength.className = 'password-strength';
            
            if (senha.length === 0) {
                passwordStrength.textContent = '';
            } else if (strength < 3) {
                passwordStrength.textContent = 'Senha fraca - Use letras, números e símbolos';
                passwordStrength.classList.add('strength-weak');
            } else if (strength < 5) {
                passwordStrength.textContent = 'Senha média - Adicione mais caracteres';
                passwordStrength.classList.add('strength-medium');
            } else {
                passwordStrength.textContent = 'Senha forte';
                passwordStrength.classList.add('strength-strong');
            }
            
            checkFormValidity();
        });

        // Validação de confirmação de senha
        confirmarSenhaInput.addEventListener('input', function() {
            const novaSenha = novaSenhaInput.value;
            const confirmarSenha = this.value;
            
            passwordMatch.className = 'password-match';
            
            if (confirmarSenha.length === 0) {
                passwordMatch.textContent = '';
            } else if (novaSenha === confirmarSenha) {
                passwordMatch.textContent = 'Senhas coincidem';
                passwordMatch.classList.add('match-success');
            } else {
                passwordMatch.textContent = 'Senhas não coincidem';
                passwordMatch.classList.add('match-error');
            }
            
            checkFormValidity();
        });

        function calculatePasswordStrength(password) {
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            return strength;
        }

        function checkFormValidity() {
            const novaSenha = novaSenhaInput.value;
            const confirmarSenha = confirmarSenhaInput.value;
            
            const isValid = novaSenha.length >= 6 && 
                           confirmarSenha.length >= 6 && 
                           novaSenha === confirmarSenha;
            
            submitBtn.disabled = !isValid;
            
            if (isValid) {
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
            } else {
                submitBtn.style.opacity = '0.6';
                submitBtn.style.cursor = 'not-allowed';
            }
        }

        // Validação no submit
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const novaSenha = novaSenhaInput.value;
            const confirmarSenha = confirmarSenhaInput.value;

            if (novaSenha.length < 6) {
                e.preventDefault();
                alert('A senha deve ter pelo menos 6 caracteres.');
                return;
            }

            if (novaSenha !== confirmarSenha) {
                e.preventDefault();
                alert('As senhas não coincidem.');
                return;
            }
        });

        // Inicializa o estado do botão
        checkFormValidity();
    </script>
</body>
</html>