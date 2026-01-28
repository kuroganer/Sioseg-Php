/**
 * JavaScript para Dashboard Administrativo - SIOSeG
 * Baseado no dashboard.html
 */

function AdminDashboard() {
    // referências aos charts para manter compatibilidade com diferentes versões do Chart.js
    this.pieChart = null;
    this.ratingsChart = null;
    this.init();
}

AdminDashboard.prototype.init = function() {
    var self = this;

    // Inicializa o Kanban e integração de tema imediatamente
    this.initKanban();
    this.initThemeIntegration();

    // Aguarda os dados da view (window.pieChartData) estarem disponíveis antes de inicializar os gráficos.
    // Faz alguns retries curtos para evitar condições de corrida entre o include do script e o script inline
    var retries = 0;
    var maxRetries = 6;
    var retryDelay = 100; // ms

    var tryInitCharts = function() {
        // Se os dados da view foram definidos, inicializa os charts
        if (typeof window !== 'undefined' && typeof window.pieChartData !== 'undefined') {
            self.initCharts();
            return;
        }

        // Se excedeu retries, inicializa mesmo assim (usar fallback)
        if (retries >= maxRetries) {
            self.initCharts();
            return;
        }

        retries++;
        setTimeout(tryInitCharts, retryDelay);
    };

    tryInitCharts();
};

/**
 * Inicializa os gráficos com Chart.js
 */
AdminDashboard.prototype.initCharts = function() {
    this.initPieChart();
    this.initRatingsChart();
};

/**
 * Inicializa o gráfico de pizza
 */
AdminDashboard.prototype.initPieChart = function() {
    var ctx = document.getElementById('overviewPieChart');
    if (!ctx) return;

    var pieData = window.pieChartData || {
        abertas: 0,
        concluidas: 0,
        andamento: 0,
        avaliacoes: 0
    };

    // Debug: mostra os dados recebidos (remover em produção se desejar)
    // debug logging removed for production

    // Garantir que os valores são números (Chart.js precisa de valores numéricos)
    var abertasVal = Number(pieData.abertas) || 0;
    var concluidasVal = Number(pieData.concluidas) || 0;
    var andamentoVal = Number(pieData.andamento) || 0;
    var avaliacoesVal = Number(pieData.avaliacoes) || 0;

    try {
        // Resolve cores a partir das variáveis CSS definidas no stylesheet para manter padronização
        var rootStyles = getComputedStyle(document.documentElement);
        var colPrimary = (rootStyles.getPropertyValue('--primary-color') || '#1E3A8A').trim();
        var colSuccess = (rootStyles.getPropertyValue('--success-color') || '#16a34a').trim();
        var colWarning = (rootStyles.getPropertyValue('--warning-color') || '#F97316').trim();
        var colInfo = (rootStyles.getPropertyValue('--info-color') || '#0ea5e9').trim();

        var createdPie = new Chart(ctx, {
            type: 'pie',
            data: {
            labels: ['OS Abertas', 'OS Concluídas', 'Em Andamento', 'Avaliações Pendentes'],
            datasets: [{
                // Padronização: mapear as fatias do pie para as mesmas cores dos status do Kanban
                // abertas -> warning, concluidas -> success, andamento -> info, avaliacoes -> primary
                data: [abertasVal, concluidasVal, andamentoVal, avaliacoesVal],
                backgroundColor: [colWarning, colSuccess, colInfo, colPrimary],
                borderWidth: 2,
                borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            color: '#555'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff'
                    }
                }
            }
        });

        try { this.pieChart = createdPie; } catch (e) { /* ignore */ }
    } catch (err) {
        try { if (typeof console !== 'undefined' && typeof console.log === 'function') console.log('Erro ao criar pie chart:', err); } catch (ee) {}
    }
    
};

/**
 * Inicializa o gráfico de avaliações
 */
AdminDashboard.prototype.initRatingsChart = function() {
    var ctx = document.getElementById('ratingsChart');
    if (!ctx) return;

    var chartLabels = window.chartLabels || ['D-6', 'D-5', 'D-4', 'D-3', 'D-2', 'Ontem', 'Hoje'];
    var chartData = window.chartData || [0, 0, 0, 0, 0, 0, 0];

    if (chartData.every(function(value) { return value === 0; })) {
        this.showNoDataMessage(ctx);
        return;
    }

    // Use cores padronizadas a partir das variáveis CSS
    var rootStyles = getComputedStyle(document.documentElement);
    var chartPrimary = (rootStyles.getPropertyValue('--primary-color') || '#1E3A8A').trim();
    var chartPrimaryAlpha = chartPrimary + '33';

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Média de Avaliações',
                data: chartData,
                borderColor: chartPrimary,
                backgroundColor: chartPrimaryAlpha,
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: chartPrimary,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#1E3A8A',
                    borderWidth: 1
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    max: 5,
                    min: 1,
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    },
                    ticks: {
                        color: '#555',
                        stepSize: 1,
                        callback: function(value) {
                            return (value % 1 === 0) ? value : '';
                        }
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    },
                    ticks: {
                        color: '#555'
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
    try { this.ratingsChart = Chart.getChart ? (Chart.getChart('ratingsChart') || null) : null; } catch (e) { /* ignore */ }
};

/**
 * Mostra mensagem quando não há dados para o gráfico
 */
AdminDashboard.prototype.showNoDataMessage = function(canvas) {
    var ctx = canvas.getContext('2d');
    var centerX = canvas.width / 2;
    var centerY = canvas.height / 2;

    ctx.fillStyle = '#555';
    ctx.font = '16px Poppins, Arial';
    ctx.textAlign = 'center';
    ctx.fillText('Nenhum dado disponível', centerX, centerY);

    ctx.font = '12px Poppins, Arial';
    ctx.fillText('As avaliações aparecerão aqui quando disponíveis', centerX, centerY + 25);
};

/**
 * Inicializa funcionalidades do Kanban
 */
AdminDashboard.prototype.initKanban = function() {
    var taskLists = document.querySelectorAll('.task-list');
    if (taskLists.length === 0) return;

    var self = this;
    taskLists.forEach(function(list) {
        list.addEventListener('dragover', function(e) { self.handleDragOver(e); });
        list.addEventListener('drop', function(e) { self.handleDrop(e); });
    });

    var tasks = document.querySelectorAll('.kanban-task');
    tasks.forEach(function(task) {
        task.draggable = true;
        task.addEventListener('dragstart', function(e) { self.handleDragStart(e); });
        task.addEventListener('dragend', function(e) { self.handleDragEnd(e); });
    });
};

/**
 * Eventos do drag and drop
 */
AdminDashboard.prototype.handleDragStart = function(e) {
    e.dataTransfer.setData('text/plain', e.target.id);
    e.target.classList.add('dragging');
};

AdminDashboard.prototype.handleDragEnd = function(e) {
    e.target.classList.remove('dragging');
};

AdminDashboard.prototype.handleDragOver = function(e) {
    e.preventDefault();
    var list = e.currentTarget;
    list.classList.add('drag-over');
};

AdminDashboard.prototype.handleDrop = function(e) {
    e.preventDefault();
    var list = e.currentTarget;
    list.classList.remove('drag-over');

    var taskId = e.dataTransfer.getData('text/plain');
    var task = document.getElementById(taskId);

    if (task && task !== e.target) {
        list.appendChild(task);
        this.updateTaskStatus(task, list.id);
    }
};

/**
 * Atualiza o status da tarefa no backend
 */
AdminDashboard.prototype.updateTaskStatus = function(task, newListId) {
    var h4Element = task.querySelector('h4');
    if (!h4Element) return;
    
    var match = h4Element.textContent.match(/#(\d+)/);
    var osId = match ? match[1] : null;
    if (!osId) return;

    var newStatus = '';
    switch (newListId) {
        case 'todo-tasks':
            newStatus = 'a_fazer';
            break;
        case 'doing-tasks':
            newStatus = 'em_andamento';
            break;
        case 'done-tasks':
            newStatus = 'concluida';
            break;
    }

    // status update: OS #<id> -> <newStatus> (console logging removed in production)

    task.style.opacity = '0.5';
    setTimeout(function() {
        task.style.opacity = '1';
    }, 500);
};

/**
 * Integra com sistema de temas do header
 */
AdminDashboard.prototype.initThemeIntegration = function() {
    var themeChangeTimeout;
    var self = this;
    
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'data-theme') {
                clearTimeout(themeChangeTimeout);
                themeChangeTimeout = setTimeout(function() {
                    self.onThemeChange();
                }, 100);
            }
        });
    });

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-theme']
    });
};

/**
 * Callback para mudança de tema
 */
AdminDashboard.prototype.onThemeChange = function() {
    setTimeout(function() {
        var textColor = getComputedStyle(document.documentElement).getPropertyValue('--text-color').trim();
        
        var ratingsChart = Chart.getChart('ratingsChart');
        if (ratingsChart) {
            ratingsChart.options.scales.x.ticks.color = textColor;
            ratingsChart.options.scales.y.ticks.color = textColor;
            ratingsChart.update('none');
        }

        var pieChart = Chart.getChart('overviewPieChart');
        if (pieChart) {
            pieChart.options.plugins.legend.labels.color = textColor;
            pieChart.update('none');
        }
    }, 50);
};

/**
 * Atualiza dados do dashboard
 */
AdminDashboard.prototype.refreshData = function() {
    // refreshData called (console logging removed in production)
};

/**
 * Mostra mensagem de erro
 */
AdminDashboard.prototype.showError = function(message) {
    var errorDiv = document.createElement('div');
    errorDiv.className = 'dashboard-error';
    errorDiv.textContent = message;

    var container = document.querySelector('.dashboard-container');
    if (container) {
        container.insertBefore(errorDiv, container.firstChild);

        setTimeout(function() {
            errorDiv.remove();
        }, 5000);
    }
};

/**
 * Mostra loading state
 */
AdminDashboard.prototype.showLoading = function() {
    var container = document.querySelector('.dashboard-container');
    if (container) {
        container.classList.add('dashboard-loading');
    }
};

/**
 * Remove loading state
 */
AdminDashboard.prototype.hideLoading = function() {
    var container = document.querySelector('.dashboard-container');
    if (container) {
        container.classList.remove('dashboard-loading');
    }
};

// CSS adicional para funcionalidades JavaScript
var additionalStyles = '.kanban-task.dragging { opacity: 0.5; transform: rotate(2deg); } .task-list.drag-over { background-color: rgba(30, 58, 138, 0.06); border: 2px dashed var(--primary-color); border-radius: 8px; } .task-actions { display: flex; gap: 5px; margin-top: 10px; opacity: 0; transition: opacity 0.3s ease; justify-content: flex-end; } .kanban-task:hover .task-actions { opacity: 1; } .task-action-btn { background: var(--primary-color); color: white; border: none; border-radius: 4px; padding: 6px 10px; cursor: pointer; font-size: 0.8em; transition: all 0.3s ease; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; min-width: 32px; height: 28px; } .task-action-btn:hover { background: var(--warning-color); transform: translateY(-1px); box-shadow: 0 2px 4px rgba(0,0,0,0.08); } .task-action-btn i { font-size: 0.9em; }';

// Adiciona estilos ao documento
var styleSheet = document.createElement('style');
styleSheet.textContent = additionalStyles;
document.head.appendChild(styleSheet);

// Inicializa o dashboard quando o DOM estiver carregado (compatível com browsers antigos)
(function() {
    function createDashboard() {
        try {
            window.adminDashboard = new AdminDashboard();
        } catch (e) {
            try { if (typeof console !== 'undefined' && typeof console.log === 'function') console.log('Erro iniciando AdminDashboard', e); } catch (ee) {}
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', createDashboard);
    } else {
        // DOM já carregado
        createDashboard();
    }
})();

// Exporta para uso global
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdminDashboard;
}