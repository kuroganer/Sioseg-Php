<?php
// As variáveis já estão disponíveis devido ao extract() na classe View
// $error_message e $success_message já existem diretamente
$base_url = defined('BASE_URL') ? BASE_URL : '/';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIOSeG - Login</title>
    <base href="<?php echo $base_url; ?>" />
    <!-- Favicon (ícone exibido no navegador) -->
    <link rel="icon" type="image/png" href="<?= $base_url; ?>assets/img/icone.png">
    <link rel="shortcut icon" href="<?= $base_url; ?>assets/img/icone.png">
    <link rel="apple-touch-icon" href="<?= $base_url; ?>assets/img/icone.png">
    <link rel="stylesheet" type="text/css" href="assets/css/stylelogin.css">
    <link rel="stylesheet" type="text/css" href="assets/css/login-alerts.css">
    <link rel="stylesheet" type="text/css" href="assets/css/lgpd-notice.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
    :root {
        --primary-color: #1E3A8A;
        --primary-dark: #2563EB;
        --accent-color: #3B82F6;
        --bg-color: #F3F4F6;
        --card-bg-color: #ffffff;
        --text-color: #374151;
        --text-light: #FFFFFF;
        --border-color: #D1D5DB;
        --label-color: #4B5563;
    }

    [data-theme="dark"] {
        --primary-color: #3B82F6;
        --primary-dark: #60A5FA;
        --accent-color: #60A5FA;
        --bg-color: #111827;
        --card-bg-color: #1F2937;
        --text-color: #E5E7EB;
        --border-color: #4B5563;
        --label-color: #9CA3AF;
    }

    /* Estilos específicos do LGPD para a página de login */
    .lgpd-notice {
        margin: 0 0 20px 0;
        font-size: 12px;
    }

    .privacy-modal-content {
        margin: 5vh auto;
    }

    @media (max-width: 768px) {
        .privacy-modal-content {
            width: 90%;
            margin: 2vh auto;
        }
    }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-cogs"></i>
                    <h1>SIOSeG</h1>
                </div>
                <p class="subtitle">Sistema Integrado de Ordens de Serviço e Gestão</p>
            </div>

            <form method="post" action="login" class="login-form">

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

                <div class="form-group">
                    <label for="emailInput">
                        <i class="fas fa-envelope"></i>
                        Email
                    </label>
                    <input type="email" id="emailInput" name="email" placeholder="Digite seu email" required>
                </div>

                <div class="form-group">
                    <label for="senhaInput">
                        <i class="fas fa-lock"></i>
                        Senha
                    </label>
                    <input type="password" id="senhaInput" name="senha" placeholder="Digite sua senha" required>
                </div>

                <div class="lgpd-notice">
                    <p><i class="fas fa-shield-alt"></i> Ao fazer login, você concorda com nossa <a href="#" onclick="openPrivacyPolicy()">Política de Privacidade</a> e o tratamento de dados pessoais conforme a LGPD.</p>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Entrar no Sistema
                </button>
                
                <div class="forgot-password">
                    <a href="forgot-password">
                        <i class="fas fa-key"></i>
                        Esqueceu a senha?
                    </a>
                </div>
            </form>

            <div class="login-footer">
                <p class="auto-detect">✨ Tipo de usuário detectado automaticamente</p>
            </div>
        </div>
    </div>

    <!-- Modal Política de Privacidade -->
    <div id="privacyModal" class="privacy-modal">
        <div class="privacy-modal-content">
            <div class="privacy-modal-header">
                <h2><i class="fas fa-shield-alt"></i> Política de Privacidade - LGPD</h2>
                <span class="privacy-close" onclick="closePrivacyPolicy()">&times;</span>
            </div>
            <div class="privacy-modal-body">
                <h3>1. Princípios do Tratamento (Art. 6º LGPD)</h3>
                <p>Seguimos os princípios estabelecidos pela LGPD:</p>
                <ul>
                    <li><strong>Finalidade:</strong> Dados coletados para propósitos específicos do sistema de OS</li>
                    <li><strong>Adequação:</strong> Tratamento compatível com as finalidades informadas</li>
                    <li><strong>Necessidade:</strong> Limitação ao mínimo necessário para as finalidades</li>
                    <li><strong>Transparência:</strong> Informações claras sobre o tratamento</li>
                </ul>

                <h3>2. Base Legal (Art. 7º LGPD)</h3>
                <p>O tratamento dos dados pessoais é realizado com base em:</p>
                <ul>
                    <li><strong>Inciso I:</strong> Consentimento do titular (clientes)</li>
                    <li><strong>Inciso II:</strong> Cumprimento de obrigação legal (funcionários/técnicos)</li>
                    <li><strong>Inciso V:</strong> Execução de contrato (prestação de serviços)</li>
                    <li><strong>Inciso VI:</strong> Legítimo interesse (gestão operacional)</li>
                </ul>

                <h3>3. Dados Coletados</h3>
                <p>Coletamos apenas dados necessários conforme Art. 6º, III (necessidade):</p>
                <ul>
                    <li>Dados de identificação (nome, CPF, RG, email)</li>
                    <li>Dados de contato (telefone, endereço)</li>
                    <li>Dados de acesso (email e senha hash)</li>
                    <li>Dados operacionais (ordens de serviço, avaliações)</li>
                </ul>

                <h3>4. Direitos do Titular (Art. 18º LGPD)</h3>
                <p>Conforme a LGPD, você possui os seguintes direitos:</p>
                <ul>
                    <li><strong>Inciso I:</strong> Confirmação da existência de tratamento</li>
                    <li><strong>Inciso II:</strong> Acesso aos dados</li>
                    <li><strong>Inciso III:</strong> Correção de dados incompletos, inexatos ou desatualizados</li>
                    <li><strong>Inciso IV:</strong> Anonimização, bloqueio ou eliminação</li>
                    <li><strong>Inciso V:</strong> Portabilidade dos dados</li>
                    <li><strong>Inciso VI:</strong> Eliminação dos dados tratados com consentimento</li>
                    <li><strong>Inciso VIII:</strong> Revogação do consentimento</li>
                </ul>

                <h3>5. Segurança (Art. 46º LGPD)</h3>
                <p>Implementamos medidas técnicas e administrativas conforme Art. 46º:</p>
                <ul>
                    <li>Criptografia de senhas (hash bcrypt)</li>
                    <li>Controle de acesso por perfis</li>
                    <li>Logs de auditoria</li>
                    <li>Backup seguro dos dados</li>
                </ul>

                <h3>6. Responsabilidades (Art. 41º e 42º LGPD)</h3>
                <p>Como controlador dos dados, assumimos as responsabilidades do Art. 41º, adotando medidas eficazes para demonstrar cumprimento das normas de proteção de dados.</p>

                <h3>7. Comunicação de Incidentes (Art. 48º LGPD)</h3>
                <p>Em caso de incidente de segurança, comunicaremos à ANPD e aos titulares conforme estabelecido no Art. 48º da LGPD.</p>

                <h3>8. Exercício de Direitos</h3>
                <p>Para exercer qualquer direito previsto no Art. 18º da LGPD ou esclarecer dúvidas sobre o tratamento de dados pessoais, entre em contato através dos canais disponíveis no sistema.</p>
            </div>
            <div class="privacy-modal-footer">
                <button onclick="closePrivacyPolicy()" class="privacy-btn">Entendi</button>
            </div>
        </div>
    </div>

    <script>
        function openPrivacyPolicy() {
            document.getElementById('privacyModal').style.display = 'block';
        }

        function closePrivacyPolicy() {
            document.getElementById('privacyModal').style.display = 'none';
        }

        // Fechar modal clicando fora dele
        window.onclick = function(event) {
            const modal = document.getElementById('privacyModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
