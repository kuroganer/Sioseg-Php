/* Configurações específicas para o dashboard do admin */

// Variáveis globais que serão definidas pelo PHP
window.ADMIN_DASHBOARD_CONFIG = {
    chartLabels: [],
    chartData: [],
    pieChartData: {
        abertas: 0,
        concluidas: 0,
        andamento: 0,
        avaliacoes: 0
    },
    BASE_URL: ''
};

// Função para inicializar as configurações vindas do PHP
window.initAdminDashboardConfig = function(chartLabels, chartData, pieChartData, baseUrl) {
    window.ADMIN_DASHBOARD_CONFIG.chartLabels = chartLabels;
    window.ADMIN_DASHBOARD_CONFIG.chartData = chartData;
    window.ADMIN_DASHBOARD_CONFIG.pieChartData = pieChartData;
    window.ADMIN_DASHBOARD_CONFIG.BASE_URL = baseUrl;
    
    // Mantém compatibilidade com código existente
    window.chartLabels = chartLabels;
    window.chartData = chartData;
    window.pieChartData = pieChartData;
    window.BASE_URL = baseUrl;
};