<?php 
use App\Core\Session;
use App\Controllers\DashboardController;

// Inicializa sessão
Session::inicializar();

// BASE_URL
$base_url = defined('BASE_URL') ? BASE_URL : '/';

// --- Menu personalizado por perfil ---
$menuItems = [];
$userProfile = Session::obterPerfilUsuario();
$userEmail = Session::obter('email') ?? '';

// Define a URL da página inicial com base no perfil do usuário
$homeUrl = $base_url . 'login'; // Padrão para não logado
if (Session::estaLogado()) {
    switch ($userProfile) {
        case 'admin':
        case 'funcionario':
            $homeUrl = $base_url . 'dashboard';
            break;
        case 'tecnico':
            $homeUrl = $base_url . 'tecnico/os';
            break;
        case 'cliente':
            $homeUrl = $base_url . 'cliente/portal';
            break;
        default:
            $homeUrl = $base_url . 'dashboard'; // Fallback para o dashboard principal
    }
}

// O menu agora é carregado apenas para admin e funcionário, que são os únicos
// perfis que usam este layout.
$menuItems = DashboardController::getMenuForProfile($userProfile);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <base href="<?= $base_url; ?>" />
    <title>SIOSeG - <?= htmlspecialchars(ucfirst($userProfile ?? 'Convidado')); ?></title>
    <!-- Favicon (ícone exibido no navegador) -->
    <link rel="icon" type="image/png" href="<?= $base_url; ?>assets/img/icone.png">
    <link rel="shortcut icon" href="<?= $base_url; ?>assets/img/icone.png">
    <link rel="apple-touch-icon" href="<?= $base_url; ?>assets/img/icone.png">
    <!-- Fontes e Ícones Globais -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Estilos CSS Globais - Mobile First -->
    <!-- CSS com cache busting -->
    <link rel="stylesheet" href="<?= $base_url; ?>assets/css/responsivo.css">
    <link rel="stylesheet" href="<?= $base_url; ?>assets/css/mobile-optimizations.css">
    <link rel="stylesheet" href="<?= $base_url; ?>assets/css/globals.css">
    <link rel="stylesheet" href="<?= $base_url; ?>assets/css/layout.css">
    <link rel="stylesheet" href="<?= $base_url; ?>assets/css/navigation.css">
    <link rel="stylesheet" href="<?= $base_url; ?>assets/css/forms.css">
    <link rel="stylesheet" href="<?= $base_url; ?>assets/css/tables.css">
    <link rel="stylesheet" href="<?= $base_url; ?>assets/css/alerts.css">
    <link rel="stylesheet" href="<?= $base_url; ?>assets/css/alert-system.css">
    <link rel="stylesheet" href="<?= $base_url; ?>assets/css/search_container.css">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/tecnico.css">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/autocomplete.css">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/lgpd-notice.css">

    <?php
    // Carrega CSS específicos baseado na URL e perfil do usuário
    $currentUri = $_SERVER['REQUEST_URI'];
    
    $cacheVersion = time();
    
    // Dashboard admin
    if (strpos($currentUri, '/dashboard') !== false && ($userProfile === 'admin' || $userProfile === 'funcionario')) {
        echo '<link rel="stylesheet" href="' . $base_url . 'assets/css/admin-dashboard.css?v=' . $cacheVersion . '">';
    }
    
    // Portal do cliente
    if (strpos($currentUri, '/cliente/portal') !== false) {
        echo '<link rel="stylesheet" href="' . $base_url . 'assets/css/cliente-portal.css?v=' . $cacheVersion . '">';
    }
    
    // Login
    if (strpos($currentUri, '/login') !== false) {
        echo '<link rel="stylesheet" href="' . $base_url . 'assets/css/login-alerts.css?v=' . $cacheVersion . '">';
    }
    
    // Edição de OS
    if (strpos($currentUri, '/os/edit') !== false) {
        echo '<link rel="stylesheet" href="' . $base_url . 'assets/css/os-edit.css?v=' . $cacheVersion . '">';
    }
    
    // Calendário de OS
    if (strpos($currentUri, '/os/calendario') !== false) {
        echo '<link rel="stylesheet" href="' . $base_url . 'assets/css/calendario-os.css?v=' . $cacheVersion . '">';
        echo '<link rel="stylesheet" href="' . $base_url . 'assets/css/os-details-table.css?v=' . $cacheVersion . '">';
        echo '<script src="' . $base_url . 'assets/js/calendario-os.js?v=' . $cacheVersion . '"></script>';
    }

    // Painel do Técnico
    if (strpos($currentUri, '/tecnico/os') !== false && strpos($currentUri, '/historico') === false) {
        echo '<script src="' . $base_url . 'assets/js/tecnico-painel.js?v=' . $cacheVersion . '" defer></script>';
    }
    
    // Registro e Edição de Usuário (Admin)
    if (strpos($currentUri, '/users/register') !== false || strpos($currentUri, '/users/create') !== false || strpos($currentUri, '/users/edit') !== false) {
        echo '<script src="' . $base_url . 'assets/js/user_register.js?v=' . $cacheVersion . '" defer></script>';
    }
    
    // Registro e Edição de Técnico (Admin)
    if (strpos($currentUri, '/tecnicos/register') !== false || strpos($currentUri, '/tecnicos/create') !== false || strpos($currentUri, '/tecnicos/edit') !== false) {
        echo '<script src="' . $base_url . 'assets/js/tecnico_register.js?v=' . $cacheVersion . '" defer></script>';
    }
    ?>
    <!-- Variáveis JavaScript globais -->
    <script>
        window.BASE_URL = '<?= $base_url; ?>';
        window.userProfile = '<?= $userProfile ?? "guest"; ?>';
        window.currentUri = '<?= $currentUri; ?>';
    </script>
    <!-- Evaluation modal assets (global) -->
    <link rel="stylesheet" href="<?= $base_url; ?>assets/css/evaluation-modal.css">
    <script src="<?= $base_url; ?>assets/js/view-evaluation.js" defer></script>
    
    <?php if ($userProfile === 'admin' || $userProfile === 'funcionario'): ?>
    <!-- Sistema de Alertas Pop-up para Admin e Funcionário -->
    <script src="<?= $base_url; ?>assets/js/popup-alerts.js?v=<?= $cacheVersion; ?>" defer></script>
    <?php endif; ?>
    
    <!-- Script que garante espaço para footer (seta --footer-height) -->
    <script src="<?= $base_url; ?>assets/js/footer-space.js" defer></script>
</head>
<body data-user-profile="<?= $userProfile ?? 'guest'; ?>">
    <!-- Header -->
    <header class="top-bar">
        <a href="<?= $homeUrl; ?>" class="site-logo-link">
            <div class="site-logo-text">SIOSeG</div>
        </a>
        
        <!-- Mobile Menu Toggle -->
        <?php if (Session::estaLogado() && !empty($menuItems)): ?>
            <div class="mobile-menu-container">
                <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        <?php endif; ?>
        
         <!-- Navigation Menu -->
    <?php if (Session::estaLogado() && !empty($menuItems)): ?>
        <nav class="main-navigation" id="main-navigation">
            <ul class="menu">
                <?php foreach ($menuItems as $item): ?>
                    <li class="<?= (!empty($item['sub_items']) ? 'has-submenu' : ''); ?>">
                        <a href="<?= htmlspecialchars($item['route'] ?? '#'); ?>">
                            <?= htmlspecialchars($item['text']); ?>
                        </a>

                        <?php if (!empty($item['sub_items']) && is_array($item['sub_items'])): ?>
                            <ul class="submenu">
                                <?php foreach ($item['sub_items'] as $subItem): ?>
                                    <li>
                                        <a href="<?= htmlspecialchars($subItem['route'] ?? '#'); ?>">
                                            <?= htmlspecialchars($subItem['text']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    <?php endif; ?>

        <?php if (Session::estaLogado()): ?>
            <div class="user-info">
                <div class="user-controls">
                    <label class="theme-switch">
                        <input type="checkbox" id="theme-toggle">
                        <span class="slider"></span>
                    </label>
                    <span class="user-text">
                        Olá, <strong><?= htmlspecialchars($userEmail); ?></strong> (<?= htmlspecialchars(ucfirst($userProfile)); ?>)
                    </span>
                    <a href="<?= $base_url; ?>logout" class="logout-link">Sair</a>
                </div>
            </div>
        <?php else: ?>
            <div class="user-info">
                <div class="user-controls">
                    <label class="theme-switch">
                        <input type="checkbox" id="theme-toggle">
                        <span class="slider"></span>
                    </label>
                    <a href="<?= $base_url; ?>login">Entrar</a>
                </div>
            </div>
        <?php endif; ?>
    </header>

    <!-- Banner -->
   <!--  <div class="main-banner-image"></div>-->
   

    <main>

<!-- Script Global para o Tema -->
<script src="<?= $base_url; ?>assets/js/theme-switcher.js"></script>

<script>
function initMobileMenu() {
    var toggle = document.getElementById('mobile-menu-toggle');
    var nav = document.getElementById('main-navigation');
    
    if (!toggle || !nav) return;
    
    // Menu toggle
    toggle.onclick = function() {
        if (nav.style.display === 'block') {
            nav.style.display = 'none';
            this.querySelector('i').className = 'fas fa-bars';
        } else {
            nav.style.display = 'block';
            this.querySelector('i').className = 'fas fa-times';
        }
    };
    
    // Submenus
    var submenus = nav.querySelectorAll('.has-submenu > a');
    for (var i = 0; i < submenus.length; i++) {
        submenus[i].onclick = function(e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                var submenu = this.parentElement.querySelector('.submenu');
                if (submenu) {
                    if (submenu.style.display === 'block') {
                        submenu.style.display = 'none';
                    } else {
                        submenu.style.display = 'block';
                    }
                }
            }
        };
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMobileMenu);
} else {
    initMobileMenu();
}
</script>
