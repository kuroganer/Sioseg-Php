/**
 * Monitor de Status em Tempo Real
 * Monitora mudanças de status das OSs e notifica admin/funcionário
 */
class StatusMonitor {
    constructor() {
        this.lastCheck = Date.now();
        this.statusHistory = new Map();
        this.notificationSound = null;
        this.init();
    }

    init() {
        this.createNotificationSound();
        this.loadInitialStatus();
        this.startMonitoring();
    }

    createNotificationSound() {
        // Criar som de notificação usando Web Audio API
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            this.notificationSound = {
                context: audioContext,
                play: () => {
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();
                    
                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    
                    oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
                    oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1);
                    
                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                    
                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.3);
                }
            };
        } catch (error) {
            console.log('Web Audio API não disponível');
        }
    }

    async loadInitialStatus() {
        try {
            const response = await fetch('/api/os/current-status', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success && data.orders) {
                    data.orders.forEach(os => {
                        this.statusHistory.set(os.id_os, {
                            status: os.status,
                            lastUpdate: Date.now()
                        });
                    });
                }
            }
        } catch (error) {
            console.error('Erro ao carregar status inicial:', error);
        }
    }

    async checkStatusChanges() {
        try {
            const response = await fetch('/api/os/recent-changes', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Last-Check': this.lastCheck.toString()
                }
            });

            if (!response.ok) return;

            const data = await response.json();
            
            if (data.success && data.changes && data.changes.length > 0) {
                data.changes.forEach(change => {
                    this.processStatusChange(change);
                });
            }

            this.lastCheck = Date.now();
        } catch (error) {
            console.error('Erro ao verificar mudanças de status:', error);
        }
    }

    processStatusChange(change) {
        const previousStatus = this.statusHistory.get(change.id_os);
        
        if (!previousStatus || previousStatus.status !== change.status) {
            // Nova mudança de status detectada
            this.showStatusNotification(change, previousStatus?.status);
            this.playNotificationSound();
            
            // Atualizar histórico
            this.statusHistory.set(change.id_os, {
                status: change.status,
                lastUpdate: Date.now()
            });
        }
    }

    showStatusNotification(change, previousStatus) {
        const notification = this.createNotificationElement(change, previousStatus);
        
        // Adicionar ao container de alertas
        const container = document.getElementById('alert-container');
        if (container) {
            container.appendChild(notification);
            
            // Auto-remove após 12 segundos
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.classList.add('fade-out');
                    setTimeout(() => notification.remove(), 300);
                }
            }, 12000);
        }
    }

    createNotificationElement(change, previousStatus) {
        const div = document.createElement('div');
        div.className = 'alert alert-system alert-info alert-dismissible fade show';
        
        const statusIcon = this.getStatusIcon(change.status);
        const statusText = this.getStatusText(change.status);
        const statusColor = this.getStatusColor(change.status);
        
        const previousText = previousStatus ? 
            ` (anterior: ${this.getStatusText(previousStatus)})` : '';

        div.innerHTML = `
            <div class="alert-content">
                <div class="alert-icon">
                    <i class="${statusIcon}"></i>
                </div>
                <div class="alert-body">
                    <div class="alert-title">
                        🔄 Status Atualizado
                    </div>
                    <div class="alert-message">
                        <strong>OS #${change.id_os}</strong><br>
                        <small>${change.cliente_nome}</small><br>
                        <small>Técnico: ${change.nome_tec || 'Não atribuído'}</small><br>
                        <span class="status-indicator status-${change.status.replace(' ', '-')}"></span>
                        <span class="badge bg-${statusColor}">${statusText}</span>
                        ${previousText}
                    </div>
                    <div class="alert-actions">
                        <button class="btn btn-sm" onclick="statusMonitor.viewOSDetails(${change.id_os})">
                            Ver Detalhes
                        </button>
                    </div>
                </div>
                <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;

        return div;
    }

    playNotificationSound() {
        if (this.notificationSound && this.notificationSound.context.state === 'running') {
            try {
                this.notificationSound.play();
            } catch (error) {
                console.log('Erro ao reproduzir som de notificação');
            }
        }
    }

    async viewOSDetails(osId) {
        try {
            const response = await fetch(`/api/os/details/${osId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Erro na requisição');

            const data = await response.json();
            
            if (data.success && data.os) {
                this.showOSDetailsModal(data.os);
            }
        } catch (error) {
            console.error('Erro ao carregar detalhes da OS:', error);
            alert('Erro ao carregar detalhes da OS');
        }
    }

    showOSDetailsModal(os) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'osDetailsModal';
        
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalhes da OS #${os.id_os}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Informações Gerais</h6>
                                <p><strong>Cliente:</strong> ${os.cliente_nome}</p>
                                <p><strong>Técnico:</strong> ${os.nome_tec || 'Não atribuído'}</p>
                                <p><strong>Status:</strong> 
                                    <span class="badge bg-${this.getStatusColor(os.status)}">
                                        ${this.getStatusText(os.status)}
                                    </span>
                                </p>
                                <p><strong>Serviço:</strong> ${os.servico_prestado || 'Não informado'}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Datas</h6>
                                <p><strong>Agendamento:</strong> ${this.formatDateTime(os.data_agendamento)}</p>
                                ${os.data_encerramento ? 
                                    `<p><strong>Encerramento:</strong> ${this.formatDateTime(os.data_encerramento)}</p>` : 
                                    ''
                                }
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <a href="/admin/os/edit/${os.id_os}" class="btn btn-primary">Editar OS</a>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    getStatusIcon(status) {
        const icons = {
            'aberta': 'fas fa-clock',
            'em andamento': 'fas fa-play',
            'concluida': 'fas fa-check',
            'encerrada': 'fas fa-flag',
            'cancelada': 'fas fa-times'
        };
        return icons[status] || 'fas fa-info';
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

    formatDateTime(dateTime) {
        if (!dateTime) return 'Não informado';
        return new Date(dateTime).toLocaleString('pt-BR');
    }

    startMonitoring() {
        // Verificar mudanças a cada 20 segundos
        setInterval(() => {
            this.checkStatusChanges();
        }, 20000);
    }

    // Método para ativar som (necessário interação do usuário)
    enableSound() {
        if (this.notificationSound && this.notificationSound.context.state === 'suspended') {
            this.notificationSound.context.resume();
        }
    }
}

// Inicializar monitor apenas para admin e funcionário
document.addEventListener('DOMContentLoaded', function() {
    const userProfile = document.body.dataset.userProfile;
    if (userProfile === 'admin' || userProfile === 'funcionario') {
        window.statusMonitor = new StatusMonitor();
        
        // Ativar som na primeira interação do usuário
        document.addEventListener('click', function enableSoundOnFirstClick() {
            if (window.statusMonitor) {
                window.statusMonitor.enableSound();
            }
            document.removeEventListener('click', enableSoundOnFirstClick);
        }, { once: true });
    }
});