<?php
// Define a URL base para os assets
$base_url = defined('BASE_URL') ? BASE_URL : '/';

// Codifica a lista de OS para ser usada em JavaScript
$osListJson = json_encode($data['osList'] ?? []);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?= $base_url ?>" />
    <title>Painel do Técnico - SIOSEG</title>
    <!-- Fontes externas -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Estilos CSS do painel do técnico -->
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/globals.css">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/tecnico.css">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/tecnico-layout.css">
</head>
<body class="tecnico-body">

<header class="tecnico-header">
    <div class="header-title">
        <h1>Painel do Técnico</h1>
        <p>Bem-vindo, <?= htmlspecialchars($data['userName'] ?? 'Técnico') ?>! Aqui estão suas ordens de serviço.</p>
    </div>
    <div class="user-controls">
        <div class="theme-switch-wrapper">
            <span>☀️</span>
            <label class="theme-switch">
                <input type="checkbox" id="theme-toggle" />
                <span class="slider"></span>
            </label>
            <span>🌙</span>
        </div>
        <a href="<?= $base_url ?>logout" class="btn" style="color: var(--text-dark);">Sair</a>
    </div>
</header>

<div class="tecnico-container">
    <main class="main-content">
        <!-- Conteúdo do index.php movido para cá -->
        <div class="os-panel os-list-container" id="lista-os">
            <h2>Ordens de Serviço do Dia</h2>
            <p>Carregando ordens de serviço...</p>
        </div>

        <div class="os-panel os-details-container" id="detalhes-os">
            <h2>Detalhes da Ordem de Serviço</h2>
            <p>Selecione uma Ordem de Serviço na lista ao lado para ver os detalhes.</p>
        </div>
    </main>
</div>

<!-- Scripts JavaScript -->
<script src="<?= $base_url ?>assets/js/theme-switcher.js"></script>
<script>
    // Passa a URL base e os dados da OS para o JavaScript
    const BASE_URL = '<?= $base_url ?>';
    const osDoDia = <?= json_encode($data['osList'] ?? []) ?>;
</script>
<script src="<?= $base_url ?>assets/js/tecnico.js"></script>
</body>
</html>