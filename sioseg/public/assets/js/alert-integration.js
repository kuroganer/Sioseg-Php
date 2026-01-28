/**
 * Integração do Sistema de Alertas
 * Adiciona indicadores visuais e integra todos os componentes
 */
class AlertIntegration {
    constructor() {
        this.alertCount = 0;
        this.menuIndicator = null;
        this.init();
    }

    init() {
        this.createMenuIndicator();
        this.bindEvents();
        this.startPeriodicCheck();
    }

    createMenuIndicator() {
        // Adicionar indicador no menu principal
        const navigation = document.querySelector('.main-navigation');
        if (navigation) {
            const indicator = document.createElement('div');
            indicator.id = 'alert-menu-indicator';
            indicator.className = 'alert-menu-indicator';
            indicator.style.cssText = `
                position: absolute;
                top: 10px;
                right: 10px;
                background: #dc3545;
                color: white;
                border-radius: 50%;
                width: 20px;
                height: 20px;
                font-size: 0.7em;
                display: none;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                z-index: 1000;
                animation: pulse 2s infinite;
            `;
            
            navigation.style.position = 'relative';
            navigation.appendChild(indicator);
            this.menuIndicator = indicator;
        }
    }

    updateAlertCount(count) {
        this.alertCount = count;
        
        if (this.menuIndicator) {
            if (count > 0) {
                this.menuIndicator.textContent = count > 99 ? '99+' : count.toString();
                this.menuIndicator.style.display = 'flex';
            } else {
                this.menuIndicator.style.display = 'none';
            }
        }

        // Atualizar título da página
        this.updatePageTitle(count);
    }

    updatePageTitle(count) {
        const originalTitle = document.title.replace(/^\(\d+\)\s/, '');
        
        if (count > 0) {
            document.title = `(${count}) ${originalTitle}`;
        } else {
            document.title = originalTitle;
        }
    }

    async checkAllAlerts() {
        let totalAlerts = 0;

        try {
            // Verificar OSs atrasadas
            const delayedResponse = await fetch('/api/os/delayed');
            if (delayedResponse.ok) {
                const delayedData = await delayedResponse.json();
                if (delayedData.success && delayedData.delayed) {
                    totalAlerts += delayedData.delayed.length;
                }
            }

            // Verificar mudanças de status recentes
            const changesResponse = await fetch('/api/os/recent-changes');
            if (changesResponse.ok) {
                const changesData = await changesResponse.json();
                if (changesData.success && changesData.changes) {
                    totalAlerts += changesData.changes.length;
                }
            }

            this.updateAlertCount(totalAlerts);

        } catch (error) {
            console.error('Erro ao verificar alertas:', error);
        }
    }

    showAlertSummary() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'alertSummaryModal';
        
        modal.innerHTML = `
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-bell"></i> Resumo de Alertas
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-danger text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-exclamation-triangle"></i> OSs com Atraso
                                        </h6>
                                    </div>
                                    <div class="card-body" id="delayed-os-summary">
                                        <div class="text-center">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Carregando...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-sync-alt"></i> Mudanças Recentes
                                        </h6>
                                    </div>
                                    <div class="card-body" id="recent-changes-summary">
                                        <div class="text-center">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Carregando...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="button" class="btn btn-primary" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i> Atualizar Página
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        // Carregar dados
        this.loadAlertSummaryData();
        
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    async loadAlertSummaryData() {
        // Carregar OSs atrasadas
        try {
            const response = await fetch('/api/os/delayed-details');
            if (response.ok) {
                const data = await response.json();
                const container = document.getElementById('delayed-os-summary');
                
                if (data.success && data.delayed && data.delayed.length > 0) {
                    container.innerHTML = `
                        <div class="list-group">
                            ${data.delayed.map(os => `
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">OS #${os.id_os}</h6>
                                        <small class="text-danger">${Math.floor(os.hours_delayed)}h de atraso</small>
                                    </div>
                                    <p class="mb-1">${os.cliente_nome}</p>
                                    <small>Técnico: ${os.nome_tec}</small>
                                </div>
                            `).join('')}
                        </div>
                    `;
                } else {
                    container.innerHTML = '<p class="text-center text-muted">Nenhuma OS com atraso</p>';
                }
            }
        } catch (error) {
            document.getElementById('delayed-os-summary').innerHTML = 
                '<p class="text-danger">Erro ao carregar dados</p>';
        }

        // Carregar mudanças recentes
        try {
            const response = await fetch('/api/os/recent-changes');
            if (response.ok) {
                const data = await response.json();
                const container = document.getElementById('recent-changes-summary');
                
                if (data.success && data.changes && data.changes.length > 0) {
                    container.innerHTML = `
                        <div class="list-group">
                            ${data.changes.map(change => `
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">OS #${change.id_os}</h6>
                                        <span class="badge bg-${this.getStatusColor(change.status)}">${this.getStatusText(change.status)}</span>
                                    </div>
                                    <p class="mb-1">${change.cliente_nome}</p>
                                    <small>Técnico: ${change.nome_tec || 'Não atribuído'}</small>
                                </div>
                            `).join('')}
                        </div>
                    `;
                } else {
                    container.innerHTML = '<p class="text-center text-muted">Nenhuma mudança recente</p>';
                }
            }
        } catch (error) {
            document.getElementById('recent-changes-summary').innerHTML = 
                '<p class="text-danger">Erro ao carregar dados</p>';
        }
    }

    getStatusColor(status) {
        const colors = {
            'aberta': 'warning',
            'em andamento': 'primary',
            'concluida': 'success',
            'encerrada': 'success',
            'cancelada': 'danger'
        };
        return colors[status] || 'secondary';
    }

    getStatusText(status) {
        const texts = {
            'aberta': 'Aberta',
            'em andamento': 'Em Andamento',
            'concluida': 'Concluída',
            'encerrada': 'Encerrada',
            'cancelada': 'Cancelada'
        };
        return texts[status] || status;
    }

    bindEvents() {
        // Adicionar botão de alertas no menu
        const userInfo = document.querySelector('.user-info');
        if (userInfo) {
            const alertButton = document.createElement('button');
            alertButton.className = 'btn btn-sm btn-outline-primary me-2';
            alertButton.innerHTML = '<i class="fas fa-bell"></i> Alertas';
            alertButton.onclick = () => this.showAlertSummary();
            
            const userControls = userInfo.querySelector('.user-controls');
            if (userControls) {
                userControls.insertBefore(alertButton, userControls.firstChild);
            }
        }

        // Adicionar estilos para animação
        if (!document.getElementById('alert-integration-styles')) {
            const style = document.createElement('style');
            style.id = 'alert-integration-styles';
            style.textContent = `
                @keyframes pulse {
                    0% { transform: scale(1); }
                    50% { transform: scale(1.1); }
                    100% { transform: scale(1); }
                }
                
                .alert-menu-indicator {
                    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                }
                
                .btn-outline-primary {
                    position: relative;
                }
            `;
            document.head.appendChild(style);
        }
    }

    startPeriodicCheck() {
        // Verificação inicial
        this.checkAllAlerts();
        
        // Verificações periódicas a cada 45 segundos
        setInterval(() => {
            this.checkAllAlerts();
        }, 45000);
    }
}

// Inicializar integração
document.addEventListener('DOMContentLoaded', function() {
    const userProfile = document.body.dataset.userProfile;
    if (userProfile === 'admin' || userProfile === 'funcionario') {
        window.alertIntegration = new AlertIntegration();
    }
});