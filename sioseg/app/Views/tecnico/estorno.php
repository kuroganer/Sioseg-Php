<?php
extract($dados);
$base_url = defined('BASE_URL') ? BASE_URL : '/';
?>

<link rel="stylesheet" href="<?= $base_url ?>assets/css/tecnico.css">
<link rel="stylesheet" href="<?= $base_url ?>assets/css/tecnico-layout.css">

<header class="tecnico-header">
    <div class="header-title">
        <h1>Estorno de Materiais</h1>
        <p>Olá, <span class="user-name"><?= htmlspecialchars(explode(' ', $userName ?? 'Técnico')[0]) ?></span>! Gerencie o estorno de materiais das suas OS.</p>
    </div>
</header>

<div class="tecnico-container">
    <main class="main-content">
        <div class="estorno-panel">
            <h2>📦 Estorno de Materiais</h2>
            <p>Aqui você pode gerenciar o estorno de materiais utilizados nas suas ordens de serviço.</p>
            
            <div class="estorno-actions">
                <button class="btn btn-primary" onclick="voltarPainel()">← Voltar ao Painel</button>
            </div>
            
            <div class="estorno-content">
                <p><em>Funcionalidade de estorno em desenvolvimento...</em></p>
            </div>
        </div>
    </main>
</div>

<script>
    const BASE_URL = '<?= $base_url ?>';
    
    function voltarPainel() {
        window.location.href = BASE_URL + 'tecnico/os';
    }
</script>

<style>
    body > main.main-content {
        padding: 0;
        background-color: var(--background-light);
    }
    
    .estorno-panel {
        background: var(--background-light);
        padding: 20px 20px 120px 20px;
        margin: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        overflow: visible !important;
        height: auto !important;
        max-height: none !important;
        min-height: fit-content;
        flex: 1;
    }
    
    .estorno-actions {
        margin: 20px 0;
    }
    
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background-color: var(--accent-color, #007bff);
        color: white;
    }
    
    .btn-primary:hover {
        background-color: var(--accent-hover, #0056b3);
    }
    
    @media (max-width: 767px) {
        .estorno-panel {
            margin: 10px;
            padding: 15px 15px 120px 15px;
        }
        
        .os-estorno-item {
            margin-bottom: 10px;
            padding: 10px;
            overflow: hidden;
        }
        
        .material-estorno {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        
        .estorno-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .qtd-estorno {
            width: 50px;
            flex-shrink: 0;
        }
        
        .max-info {
            font-size: 0.75em;
            white-space: nowrap;
        }
    }
    
    @media (min-width: 768px) {
        .tecnico-container {
            padding-bottom: 120px !important;
        }
        
        .main-content {
            width: 100%;
            max-width: none;
        }
        
        .estorno-panel {
            width: calc(100% - 40px);
            max-width: none;
            margin: 20px;
        }
    }
</style>