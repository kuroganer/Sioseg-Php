/* Configurações e variáveis globais para o painel do técnico */

// Variáveis globais que serão definidas pelo PHP
window.TECNICO_CONFIG = {
    BASE_URL: '',
    osDoDia: []
};

// Função para inicializar as configurações vindas do PHP
window.initTecnicoConfig = function(baseUrl, osData) {
    window.TECNICO_CONFIG.BASE_URL = baseUrl;
    window.TECNICO_CONFIG.osDoDia = osData;
    
    // Mantém compatibilidade com código existente
    window.BASE_URL = baseUrl;
    window.osDoDia = osData;
    
    // Inicializa o painel após configurar as variáveis
    if (typeof initTecnicoPanel === 'function') {
        initTecnicoPanel();
    }
};