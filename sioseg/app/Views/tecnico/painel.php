<?php
// Extrai as variáveis do array $dados para uso direto na view (ex: $osList, $userName)
extract($dados);
$base_url = defined('BASE_URL') ? BASE_URL : '/';
$osListJson = json_encode($osList ?? []);
?>

<!-- Carrega o CSS específico do técnico -->
<link rel="stylesheet" href="<?= $base_url ?>assets/css/tecnico.css">
<link rel="stylesheet" href="<?= $base_url ?>assets/css/tecnico-layout.css">

<!-- O corpo da página já é fornecido pelo layout principal, então começamos com o header do técnico -->
<header class="tecnico-header">
    <div class="header-title">
        <h1>Painel do Técnico</h1>
        <p>Olá, <span class="user-name"><?= htmlspecialchars(explode(' ', $userName ?? 'Técnico')[0]) ?></span><span class="full-message">! Gerencie suas ordens de serviço de forma rápida e eficiente.</span></p>
    </div>
    <!-- O seletor de tema e o botão de sair agora ficam no header principal -->
</header>



<div class="tecnico-container">
    <div class="tab-navigation">
        <button class="tab-button active" data-tab="os-dia" onclick="showTab('os-dia', this)">📅 OS do Dia</button>
        <button class="tab-button" data-tab="estornos" onclick="showTab('estornos', this)">📦 Estornos</button>
    </div>
    
    <main class="main-content">
        <div class="tab-content active" id="os-dia">
            <div class="os-panel os-list-container" id="lista-os">
                <h2>Suas Ordens de Serviço de Hoje</h2>
                <p>Carregando suas atribuições...</p>
            </div>

            <div class="os-panel os-details-container" id="detalhes-os">
                <h2>Detalhes do Atendimento</h2>
                <p>Clique em uma OS para visualizar informações completas e gerenciar o atendimento.</p>
            </div>
        </div>
        
        <div class="tab-content" id="estornos">
            <div class="os-panel" id="estorno-container">
                <h2>📦 Estorno de Materiais</h2>
                <p>Carregando OS disponíveis para estorno...</p>
            </div>
        </div>
    </main>
</div>

<!-- Scripts -->
<script>
    // Passa a URL base e os dados da OS para o JavaScript
    const BASE_URL = '<?= $base_url ?>';
    const osDoDia = <?= json_encode($osList ?? []) ?>;
    const osAtrasadas = <?= json_encode($osAtrasadas ?? []) ?>;
    window.osParaEstornoCount = <?= $osParaEstorno ?? 0 ?>;
</script>
<script src="<?= $base_url ?>assets/js/tecnico.js"></script>

<style>
    /*
     Sobrescreve o padding do .main-content do layout principal
     para que o painel do técnico possa usar a tela inteira.
    */
    body > main.main-content {
        padding: 0;
        background-color: var(--background-light);
    }
    /* Remove o header do técnico do fluxo do main-content principal */
    .tecnico-header {
        width: 100%;
    }
    .tecnico-container {
        width: 100%;
    }
    
    /* Estilos para estorno */
    .btn-estorno {
        background-color: var(--success-color, #28a745);
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        margin-top: 15px;
        transition: all 0.3s ease;
    }
    
    .btn-estorno:hover {
        background-color: var(--success-hover, #218838);
        transform: translateY(-2px);
    }
    
    .estorno-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--border-color, #dee2e6);
    }
    
    .estorno-content {
        padding: 20px;
        background: var(--background-light, #f8f9fa);
        border-radius: 8px;
        border: 1px solid var(--border-color, #dee2e6);
    }
    
    .btn-voltar {
        background-color: var(--secondary-color, #6c757d);
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .btn-voltar:hover {
        background-color: var(--secondary-hover, #5a6268);
    }
    
    #estorno-container {
        padding-bottom: 120px !important;
        overflow: visible !important;
        height: auto !important;
        max-height: none !important;
    }
    
    .tab-content {
        padding-bottom: 120px !important;
        overflow: visible !important;
        height: auto !important;
        max-height: none !important;
    }
    
    @media (max-width: 767px) {
        .os-estorno-item {
            margin-bottom: 10px !important;
            padding: 10px !important;
            overflow: hidden !important;
        }
        
        .material-estorno {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 10px !important;
        }
        
        .estorno-controls {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            flex-wrap: wrap !important;
        }
        
        .qtd-estorno {
            width: 50px !important;
            flex-shrink: 0 !important;
        }
        
        .max-info {
            font-size: 0.75em !important;
            white-space: nowrap !important;
        }
    }
    
    @media (min-width: 768px) {
        .tecnico-container {
            padding-bottom: 120px !important;
        }
        
        #estorno-container {
            width: 100% !important;
            max-width: none !important;
        }
        
        .tab-content {
            width: 100% !important;
            max-width: none !important;
        }
    }
    
    /* Estilos para estorno de materiais */
    .os-estorno-item {
        background: var(--background-light, #f8f9fa);
        border: 1px solid var(--border-color, #dee2e6);
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .os-estorno-item h3 {
        margin: 0 0 15px 0;
        color: var(--text-color, #333);
        border-bottom: 1px solid var(--border-color, #dee2e6);
        padding-bottom: 10px;
    }
    
    .material-estorno {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        background: white;
        border-radius: 6px;
        margin-bottom: 10px;
        border: 1px solid var(--border-color, #dee2e6);
    }
    
    .material-nome {
        font-weight: 500;
        color: var(--text-color, #333);
    }
    
    .estorno-controls {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .qtd-estorno {
        width: 80px;
        padding: 5px;
        border: 1px solid var(--border-color, #dee2e6);
        border-radius: 4px;
        text-align: center;
    }
    
    .max-info {
        color: var(--text-muted, #666);
        font-size: 0.9em;
    }
    
    .btn-estornar {
        background-color: var(--warning-color, #ffc107);
        color: #000;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        transition: all 0.3s ease;
    }
    
    .btn-estornar:hover {
        background-color: var(--warning-hover, #e0a800);
    }
    
    .btn-estornar:disabled {
        background-color: var(--success-color, #28a745);
        color: white;
        cursor: not-allowed;
    }
    
    /* Estilos para navegação por abas - Mobile First */
    .tab-navigation {
        display: flex;
        background: var(--background-light, #f8f9fa);
        border-bottom: 2px solid var(--border-color, #dee2e6);
        margin-bottom: 15px;
        overflow-x: auto;
    }
    
    .tab-button {
        flex: 1;
        min-width: 120px;
        padding: 12px 16px;
        background: transparent;
        border: none;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        color: var(--text-muted, #666);
        transition: all 0.3s ease;
        border-bottom: 3px solid transparent;
        white-space: nowrap;
    }
    
    .tab-button:hover {
        background: var(--background-hover, #e9ecef);
        color: var(--text-color, #333);
    }
    
    .tab-button.active {
        color: var(--primary-color, #007bff);
        border-bottom-color: var(--primary-color, #007bff);
        background: white;
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: flex;
        flex-direction: column;
        width: 100%;
        overflow: hidden;
    }
    
    @media (min-width: 768px) {
        .tab-content.active {
            display: flex;
            flex-direction: row;
            gap: 15px;
            height: calc(100vh - 180px);
            width: 100%;
        }
    }
    

    
    /* Responsividade */
    @media (min-width: 768px) {
        .tab-navigation {
            margin-bottom: 20px;
        }
        
        .tab-button {
            padding: 15px 20px;
            font-size: 16px;
        }
    }
    
    /* Tema escuro */
    [data-theme="dark"] .tab-navigation {
        background: var(--background-secondary);
        border-bottom-color: var(--border-color);
    }
    
    [data-theme="dark"] .tab-button {
        color: var(--text-muted);
    }
    
    [data-theme="dark"] .tab-button:hover {
        background: var(--background-hover);
        color: var(--text-color);
    }
    
    [data-theme="dark"] .tab-button.active {
        background: var(--background-primary);
        color: var(--accent-color);
        border-bottom-color: var(--accent-color);
    }
    
    [data-theme="dark"] .os-estorno-item {
        background: var(--background-secondary);
        border-color: var(--border-color);
    }
    
    [data-theme="dark"] .os-estorno-item h3 {
        color: var(--text-color);
        border-bottom-color: var(--border-color);
    }
    
    [data-theme="dark"] .material-estorno {
        background: var(--background-primary);
        border-color: var(--border-color);
    }
    
    [data-theme="dark"] .material-nome {
        color: var(--text-color);
    }
    
    [data-theme="dark"] .qtd-estorno {
        background: var(--background-secondary);
        border-color: var(--border-color);
        color: var(--text-color);
    }
    
    [data-theme="dark"] .max-info {
        color: var(--text-muted);
    }
    
    /* Melhor direcionamento dos cards */
    .os-estorno-item {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .os-estorno-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    [data-theme="dark"] .os-estorno-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    
    .material-estorno {
        transition: background-color 0.2s ease;
    }
    
    .material-estorno:hover {
        background-color: var(--background-hover, #f1f3f4);
    }
    
    [data-theme="dark"] .material-estorno:hover {
        background-color: var(--background-hover);
    }

</style>