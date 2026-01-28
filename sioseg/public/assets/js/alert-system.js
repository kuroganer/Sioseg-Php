/**
 * Sistema de Alertas para OSs com Atraso e Notificações
 * Monitora OSs com atraso de 4+ horas e mudanças de status
 */
class AlertSystem {
    constructor() {
        this.alertContainer = null;
        this.checkInterval = 30000; // 30 segundos
        this.delayThreshold = 4; // 4 horas
        this.lastStatusCheck = {};
        this.init();
    }

    init() {
        this.createAlertContainer();
        this.startMonitoring();
        this.bindEvents();
    }

    createAlertContainer() {
        if (document.getElementById('alert-container')) return;

        const container = document.createElement('div');
        container.id = 'alert-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            pointer-events: none;
        `;
        document.body.appendChild(container);
        this.alertContainer = container;
    }

    async checkDelayedOS() {
        try {
            const response = await fetch('/api/os/delayed', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Erro na requisição');

            const data = await response.json();
            
            if (data.delayed && data.delayed.length > 0) {
                this.showDelayedAlert(data.delayed);
            }
        } catch (error) {
            console.error('Erro ao verificar OSs atrasadas:', error);
        }
    }

    async checkStatusChanges() {
        try {
            const response = await fetch('/api/os/status-changes', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Erro na requisição');

            const data = await response.json();
            
            if (data.changes && data.changes.length > 0) {
                data.changes.forEach(change => {
                    if (!this.lastStatusCheck[change.id_os] || 
                        this.lastStatusCheck[change.id_os] !== change.status) {
                        this.showStatusChangeAlert(change);
                        this.lastStatusCheck[change.id_os] = change.status;
                    }
                });
            }
        } catch (error) {
            console.error('Erro ao verificar mudanças de status:', error);
        }
    }

    showDelayedAlert(delayedOS) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
        alertDiv.style.cssText = `
            pointer-events: auto;
            margin-bottom: 10px;
            animation: slideIn 0.3s ease-out;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;

        const osCount = delayedOS.length;
        const osDetails = delayedOS.map(os => 
            `<li><strong>OS #${os.id_os}</strong> - ${os.nome_tec} - ${this.formatDelay(os.hours_delayed)}h de atraso</li>`
        ).join('');

        alertDiv.innerHTML = `
            <div style="display: flex; align-items: flex-start;">
                <i class="fas fa-exclamation-triangle" style="color: #dc3545; margin-right: 10px; margin-top: 2px;"></i>
                <div style="flex: 1;">
                    <strong>⚠️ OSs com Atraso Detectadas!</strong>
                    <p style="margin: 5px 0;">Encontradas ${osCount} OS(s) com atraso superior a ${this.delayThreshold} horas:</p>
                    <ul style="margin: 5px 0; padding-left: 20px; font-size: 0.9em;">
                        ${osDetails}
                    </ul>
                    <button class="btn btn-sm btn-outline-primary mt-2" onclick="alertSystem.viewDelayedOS()">
                        Ver Detalhes
                    </button>
                </div>
                <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;

        this.alertContainer.appendChild(alertDiv);
        
        // Auto-remove após 15 segundos
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 15000);
    }

    showStatusChangeAlert(change) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-info alert-dismissible fade show';
        alertDiv.style.cssText = `
            pointer-events: auto;
            margin-bottom: 10px;
            animation: slideIn 0.3s ease-out;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;

        const statusIcon = this.getStatusIcon(change.status);
        const statusText = this.getStatusText(change.status);

        alertDiv.innerHTML = `
            <div style="display: flex; align-items: flex-start;">
                <i class="${statusIcon}" style="margin-right: 10px; margin-top: 2px;"></i>
                <div style="flex: 1;">
                    <strong>📋 Mudança de Status</strong>
                    <p style="margin: 5px 0;">
                        <strong>OS #${change.id_os}</strong> - ${change.cliente_nome}<br>
                        <small>Técnico: ${change.nome_tec}</small><br>
                        Status alterado para: <span class="badge bg-${this.getStatusColor(change.status)}">${statusText}</span>
                    </p>
                </div>
                <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;

        this.alertContainer.appendChild(alertDiv);
        
        // Auto-remove após 10 segundos
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 10000);
    }

    viewDelayedOS() {
        // Criar modal para visualizar OSs atrasadas
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'delayedOSModal';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">OSs com Atraso</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="delayed-os-content">Carregando...</div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Carregar dados detalhados
        this.loadDelayedOSDetails();
        
        // Mostrar modal
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        // Remover modal quando fechado
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    async loadDelayedOSDetails() {
        try {
            const response = await fetch('/api/os/delayed-details', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Erro na requisição');

            const data = await response.json();
            const content = document.getElementById('delayed-os-content');
            
            if (data.delayed && data.delayed.length > 0) {
                content.innerHTML = this.renderDelayedOSTable(data.delayed);
            } else {
                content.innerHTML = '<p class="text-center">Nenhuma OS com atraso encontrada.</p>';
            }
        } catch (error) {
            console.error('Erro ao carregar detalhes:', error);
            document.getElementById('delayed-os-content').innerHTML = 
                '<p class="text-danger">Erro ao carregar dados.</p>';
        }
    }

    renderDelayedOSTable(delayedOS) {
        return `
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>OS</th>
                            <th>Cliente</th>
                            <th>Técnico</th>
                            <th>Agendamento</th>
                            <th>Atraso</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${delayedOS.map(os => `
                            <tr>
                                <td><strong>#${os.id_os}</strong></td>
                                <td>${os.cliente_nome}</td>
                                <td>${os.nome_tec}</td>
                                <td>${this.formatDateTime(os.data_agendamento)}</td>
                                <td><span class="badge bg-danger">${this.formatDelay(os.hours_delayed)}h</span></td>
                                <td><span class="badge bg-${this.getStatusColor(os.status)}">${this.getStatusText(os.status)}</span></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    formatDelay(hours) {
        return Math.floor(hours);
    }

    formatDateTime(dateTime) {
        return new Date(dateTime).toLocaleString('pt-BR');
    }

    getStatusIcon(status) {
        const icons = {
            'aberta': 'fas fa-clock text-warning',
            'em andamento': 'fas fa-play text-primary',
            'concluida': 'fas fa-check text-success',
            'encerrada': 'fas fa-flag text-success',
            'cancelada': 'fas fa-times text-danger'
        };
        return icons[status] || 'fas fa-info text-secondary';
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

    startMonitoring() {
        // Verificação inicial
        this.checkDelayedOS();
        this.checkStatusChanges();
        
        // Verificações periódicas
        setInterval(() => {
            this.checkDelayedOS();
            this.checkStatusChanges();
        }, this.checkInterval);
    }

    bindEvents() {
        // Adicionar estilos CSS para animações
        if (!document.getElementById('alert-system-styles')) {
            const style = document.createElement('style');
            style.id = 'alert-system-styles';
            style.textContent = `
                @keyframes slideIn {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                
                .alert {
                    border: none;
                    border-radius: 8px;
                }
                
                .alert-danger {
                    background: linear-gradient(135deg, #ff6b6b, #ee5a52);
                    color: white;
                }
                
                .alert-info {
                    background: linear-gradient(135deg, #74b9ff, #0984e3);
                    color: white;
                }
                
                .btn-close {
                    filter: invert(1);
                }
            `;
            document.head.appendChild(style);
        }
    }

    // Método para parar o monitoramento (se necessário)
    stopMonitoring() {
        if (this.monitoringInterval) {
            clearInterval(this.monitoringInterval);
        }
    }
}

// Inicializar o sistema quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se o usuário é admin ou funcionário
    const userProfile = document.body.dataset.userProfile;
    if (userProfile === 'admin' || userProfile === 'funcionario') {
        window.alertSystem = new AlertSystem();
    }
});