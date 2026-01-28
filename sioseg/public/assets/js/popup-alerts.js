/**
 * Sistema de Pop-up de Alertas
 * Pop-ups puros sem modificar layout existente
 */
class PopupAlerts {
    constructor() {
        this.alertsShown = this.loadCache();
        this.popupQueue = [];
        this.isShowingPopup = false;
        this.init();
        this.setupThemeListener();
    }
    
    setupThemeListener() {
        // Observa mudanças no atributo data-theme do HTML
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'data-theme') {
                    this.updatePopupTheme();
                }
            });
        });
        
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-theme']
        });
    }
    
    updatePopupTheme() {
        // Força atualização dos estilos dos popups quando o tema muda
        const existingPopups = document.querySelectorAll('.popup-alert');
        existingPopups.forEach(popup => {
            // Força re-render dos estilos CSS
            popup.style.display = 'none';
            popup.offsetHeight; // Trigger reflow
            popup.style.display = '';
        });
    }
    
    loadCache() {
        try {
            const cached = localStorage.getItem('alertsShown');
            if (cached) {
                const data = JSON.parse(cached);
                // Limpar cache antigo (mais de 24 horas)
                const now = Date.now();
                const filtered = data.filter(item => (now - item.timestamp) < 86400000);
                this.saveCache(filtered);
                return new Set(filtered.map(item => item.key));
            }
        } catch (error) {
            console.error('Erro ao carregar cache:', error);
        }
        return new Set();
    }
    
    saveCache(data) {
        try {
            localStorage.setItem('alertsShown', JSON.stringify(data));
        } catch (error) {
            console.error('Erro ao salvar cache:', error);
        }
    }
    
    addToCache(key) {
        this.alertsShown.add(key);
        try {
            const cached = JSON.parse(localStorage.getItem('alertsShown') || '[]');
            cached.push({ key, timestamp: Date.now() });
            this.saveCache(cached);
        } catch (error) {
            console.error('Erro ao adicionar ao cache:', error);
        }
    }

    init() {
        this.startMonitoring();
        this.createPopupStyles();
    }

    createPopupStyles() {
        if (document.getElementById('popup-alert-styles')) return;

        const style = document.createElement('style');
        style.id = 'popup-alert-styles';
        style.textContent = `
            /* Variáveis CSS para popups */
            :root {
                --popup-bg-light: #ffffff;
                --popup-bg-dark: #1E293B;
                --popup-text-light: #495057;
                --popup-text-dark: #FFFFFF;
                --popup-border-light: #e9ecef;
                --popup-border-dark: #475569;
                --popup-shadow-light: rgba(0,0,0,0.4);
                --popup-shadow-dark: rgba(0,0,0,0.6);
                --popup-footer-bg-light: #f8f9fa;
                --popup-footer-bg-dark: #374151;
                --popup-item-bg-light: #f8f9fa;
                --popup-item-bg-dark: #374151;
                --popup-item-hover-light: #ffffff;
                --popup-item-hover-dark: #475569;
            }

            [data-theme='dark'] {
                --popup-bg: var(--popup-bg-dark);
                --popup-text: var(--popup-text-dark);
                --popup-border: var(--popup-border-dark);
                --popup-shadow: var(--popup-shadow-dark);
                --popup-footer-bg: var(--popup-footer-bg-dark);
                --popup-item-bg: var(--popup-item-bg-dark);
                --popup-item-hover: var(--popup-item-hover-dark);
            }

            [data-theme='light'], :root {
                --popup-bg: var(--popup-bg-light);
                --popup-text: var(--popup-text-light);
                --popup-border: var(--popup-border-light);
                --popup-shadow: var(--popup-shadow-light);
                --popup-footer-bg: var(--popup-footer-bg-light);
                --popup-item-bg: var(--popup-item-bg-light);
                --popup-item-hover: var(--popup-item-hover-light);
            }

            .popup-alert {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: var(--popup-bg);
                color: var(--popup-text);
                border-radius: 15px;
                box-shadow: 0 20px 60px var(--popup-shadow);
                z-index: 10000;
                max-width: 600px;
                width: 95%;
                max-height: 80vh;
                overflow: hidden;
                animation: popupSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
                font-family: 'Poppins', Arial, sans-serif;
                border: 1px solid var(--popup-border);
            }

            .popup-alert-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.6);
                backdrop-filter: blur(3px);
                z-index: 9999;
                animation: fadeIn 0.3s ease-out;
            }

            .popup-alert-header {
                padding: 25px 30px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                position: relative;
            }

            .popup-alert-header.danger {
                background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
                color: white;
            }

            .popup-alert-header.info {
                background: linear-gradient(135deg, #20a136 0%, #F97316 100%);
                color: white;
            }

            .popup-alert-header h5 {
                margin: 0;
                font-size: 1.3em;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .popup-alert-body {
                padding: 30px;
                max-height: 50vh;
                overflow-y: auto;
                line-height: 1.6;
                background: var(--popup-bg);
                color: var(--popup-text);
            }

            .popup-alert-footer {
                padding: 20px 30px;
                background: var(--popup-footer-bg);
                border-top: 1px solid var(--popup-border);
                display: flex;
                justify-content: flex-end;
                gap: 12px;
            }

            .popup-close-btn {
                background: rgba(255,255,255,0.2);
                border: none;
                width: 35px;
                height: 35px;
                border-radius: 50%;
                cursor: pointer;
                color: white;
                font-size: 18px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s;
            }

            .popup-close-btn:hover {
                background: rgba(255,255,255,0.3);
                transform: scale(1.1);
            }

            .popup-btn {
                padding: 12px 24px;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                font-size: 0.95em;
                font-weight: 500;
                transition: all 0.2s;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }

            .popup-btn-primary {
                background: linear-gradient(135deg, #20a136, #F97316);
                color: white;
                box-shadow: 0 4px 15px rgba(32, 161, 54, 0.3);
            }

            .popup-btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(32, 161, 54, 0.4);
            }

            .popup-btn-secondary {
                background: #6c757d;
                color: white;
                box-shadow: 0 4px 15px rgba(108, 117, 125, 0.2);
            }

            .popup-btn-secondary:hover {
                background: #545b62;
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(108, 117, 125, 0.3);
            }

            .popup-os-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .popup-os-item {
                padding: 20px;
                border: 1px solid var(--popup-border);
                border-radius: 10px;
                margin-bottom: 15px;
                background: var(--popup-item-bg);
                color: var(--popup-text);
                box-shadow: 0 2px 10px var(--popup-shadow);
                transition: all 0.2s;
            }

            .popup-os-item:hover {
                transform: translateY(-2px);
                background: var(--popup-item-hover);
                box-shadow: 0 4px 20px var(--popup-shadow);
            }

            .popup-os-item strong {
                color: #dc3545;
                font-size: 1.1em;
            }

            .popup-os-item .client-name {
                color: var(--popup-text);
                font-weight: 500;
                margin: 8px 0;
                opacity: 0.9;
            }

            .popup-os-item .tech-name {
                color: var(--popup-text);
                font-size: 0.9em;
                opacity: 0.7;
            }
            
            .popup-os-item .tech-contact {
                color: #28a745;
                font-size: 0.85em;
                margin-top: 4px;
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .popup-os-item .delay-badge {
                background: linear-gradient(135deg, #dc3545, #c82333);
                color: white;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 0.85em;
                font-weight: 600;
                display: inline-block;
                margin-top: 8px;
            }

            .popup-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 15px;
            }

            .popup-table th {
                background: var(--popup-footer-bg);
                padding: 12px;
                text-align: left;
                font-weight: 600;
                color: var(--popup-text);
                border-bottom: 2px solid var(--popup-border);
            }

            .popup-table td {
                padding: 12px;
                border-bottom: 1px solid var(--popup-border);
                vertical-align: top;
                color: var(--popup-text);
                background: var(--popup-bg);
            }

            .popup-table tr:hover td {
                background: var(--popup-item-hover);
            }

            @keyframes popupSlideIn {
                from {
                    opacity: 0;
                    transform: translate(-50%, -60%) scale(0.8);
                }
                to {
                    opacity: 1;
                    transform: translate(-50%, -50%) scale(1);
                }
            }

            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }

            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }

            .popup-fade-out {
                animation: fadeOut 0.3s ease-in forwards;
            }

            @media (max-width: 768px) {
                .popup-alert {
                    width: 95%;
                    max-width: none;
                }
                
                .popup-alert-header {
                    padding: 20px;
                }
                
                .popup-alert-body {
                    padding: 20px;
                }
                
                .popup-alert-footer {
                    padding: 15px 20px;
                    flex-direction: column;
                }
                
                .popup-btn {
                    width: 100%;
                    justify-content: center;
                }
            }
        `;
        document.head.appendChild(style);
    }

    async checkDelayedOS() {
        try {
            console.log('Verificando OSs atrasadas...');
            const response = await fetch(window.BASE_URL + 'api/os/delayed');
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                console.error('API response not ok:', response.status);
                return;
            }

            const data = await response.json();
            console.log('API data received:', data);
            
            if (data.success && data.delayed && data.delayed.length > 0) {
                // Filtrar apenas OSs que estão abertas ou em andamento
                const activeDelayed = data.delayed.filter(os => 
                    ['aberta', 'em andamento'].includes(os.status)
                );
                
                if (activeDelayed.length > 0) {
                    console.log(`Encontradas ${activeDelayed.length} OSs atrasadas ativas`);
                    const alertKey = `delayed-${activeDelayed.map(os => os.id_os).join('-')}`;
                    if (!this.alertsShown.has(alertKey)) {
                        console.log('Mostrando popup de OSs atrasadas');
                        this.showDelayedPopup(activeDelayed);
                        this.addToCache(alertKey);
                    } else {
                        console.log('Alerta já foi mostrado:', alertKey);
                    }
                } else {
                    console.log('Nenhuma OS atrasada ativa encontrada');
                }
            } else {
                console.log('Nenhuma OS atrasada encontrada');
            }
        } catch (error) {
            console.error('Erro ao verificar OSs atrasadas:', error);
        }
    }

    async checkStatusChanges() {
        try {
            const response = await fetch(window.BASE_URL + 'api/os/recent-changes');
            if (!response.ok) return;

            const data = await response.json();
            
            if (data.success && data.changes && data.changes.length > 0) {
                data.changes.forEach(change => {
                    // Mostrar apenas quando o status mudar para "em andamento"
                    if (change.status === 'em andamento') {
                        const alertKey = change.timestamp_key || `status-${change.id_os}-${change.status}-${Date.now()}`;
                        if (!this.alertsShown.has(alertKey)) {
                            this.showStatusChangePopup(change);
                            this.addToCache(alertKey);
                        }
                    }
                });
            }
        } catch (error) {
            console.error('Erro ao verificar mudanças de status:', error);
        }
    }

    showDelayedPopup(delayedOS) {
        const popup = this.createPopup('danger', '⚠️ OSs com Atraso Detectadas!');
        
        const osCount = delayedOS.length;
        const osList = delayedOS.map(os => `
            <li class="popup-os-item">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <strong>OS #${os.id_os}</strong>
                        <div class="client-name">${os.cliente_nome}</div>
                        <div class="tech-name">Técnico: ${os.nome_tec}</div>
                        ${os.tel_tecnico ? `<div class="tech-contact"><i class="fas fa-phone"></i> ${os.tel_tecnico}</div>` : ''}
                    </div>
                    <div class="delay-badge">
                        ${Math.floor(os.hours_delayed)}h atraso
                    </div>
                </div>
            </li>
        `).join('');

        popup.body.innerHTML = `
            <div style="text-align: center; margin-bottom: 25px;">
                <div style="font-size: 2.5em; margin-bottom: 10px;">⚠️</div>
                <h4 style="color: #dc3545; margin: 0;">OSs com Atraso Detectadas</h4>
                <p style="color: var(--popup-text); opacity: 0.7; margin: 10px 0 0 0;">Encontradas <strong>${osCount}</strong> OS(s) com atraso registrado</p>
            </div>
            <ul class="popup-os-list">${osList}</ul>
        `;

        popup.footer.innerHTML = `
            <button class="popup-btn popup-btn-primary" onclick="popupAlerts.viewAllDelayed()">
                <i class="fas fa-list"></i> Ver Todos os Detalhes
            </button>
            <button class="popup-btn popup-btn-secondary" onclick="popupAlerts.closePopup()">
                <i class="fas fa-times"></i> Fechar
            </button>
        `;

        this.showPopup(popup.container);
    }

    showStatusChangePopup(change) {
        const popup = this.createPopup('info', '📋 Mudança de Status');
        
        const statusText = this.getStatusText(change.status);
        const statusColor = this.getStatusColor(change.status);

        popup.body.innerHTML = `
            <div style="text-align: center;">
                <div style="font-size: 2.5em; margin-bottom: 15px;">📋</div>
                <h4 style="color: #495057; margin-bottom: 20px;">OS #${change.id_os}</h4>
                <div style="background: var(--popup-footer-bg); padding: 20px; border-radius: 10px; margin-bottom: 20px; border: 1px solid var(--popup-border);">
                    <p style="margin: 0 0 10px 0; font-weight: 500; color: var(--popup-text);">${change.cliente_nome}</p>
                    <p style="margin: 0 0 8px 0; color: var(--popup-text); opacity: 0.7;">Técnico: ${change.nome_tec || 'Não atribuído'}</p>
                    <p style="margin: 0; color: #28a745; font-size: 0.9em;"><i class="fas fa-clock"></i> Alterado em: ${change.data_alteracao_formatada || this.formatDateTime(new Date())}</p>
                </div>
                <p style="margin-bottom: 15px; color: var(--popup-text);">Status alterado para:</p>
                <span style="background: linear-gradient(135deg, #20a136, #F97316); color: white; padding: 10px 20px; border-radius: 25px; font-size: 1em; font-weight: 500; display: inline-block;">
                    ${statusText}
                </span>
            </div>
        `;

        popup.footer.innerHTML = `
            <button class="popup-btn popup-btn-primary" onclick="popupAlerts.viewOSDetails(${change.id_os})">
                <i class="fas fa-eye"></i> Ver Detalhes
            </button>
            <button class="popup-btn popup-btn-secondary" onclick="popupAlerts.closePopup()">
                <i class="fas fa-times"></i> Fechar
            </button>
        `;

        this.showPopup(popup.container);
        
        // Auto-fechar após 8 segundos
        setTimeout(() => {
            this.closePopup();
        }, 8000);
    }

    createPopup(type, title) {
        const overlay = document.createElement('div');
        overlay.className = 'popup-alert-overlay';
        overlay.onclick = () => this.closePopup();

        const container = document.createElement('div');
        container.className = 'popup-alert';

        const header = document.createElement('div');
        header.className = `popup-alert-header ${type}`;
        header.innerHTML = `
            <h5>${title}</h5>
            <button class="popup-close-btn" onclick="popupAlerts.closePopup()">
                <i class="fas fa-times"></i>
            </button>
        `;

        const body = document.createElement('div');
        body.className = 'popup-alert-body';

        const footer = document.createElement('div');
        footer.className = 'popup-alert-footer';

        container.appendChild(header);
        container.appendChild(body);
        container.appendChild(footer);

        return { container, overlay, header, body, footer };
    }

    showPopup(popupContainer) {
        // Adicionar à fila de popups
        this.popupQueue.push(popupContainer);
        this.processPopupQueue();
    }

    processPopupQueue() {
        if (this.isShowingPopup || this.popupQueue.length === 0) {
            return;
        }

        this.isShowingPopup = true;
        const popupContainer = this.popupQueue.shift();

        // Fechar popup anterior se existir
        this.closePopup();

        const overlay = document.createElement('div');
        overlay.className = 'popup-alert-overlay';
        overlay.id = 'current-popup-overlay';
        overlay.onclick = () => this.closePopup();

        document.body.appendChild(overlay);
        document.body.appendChild(popupContainer);
        
        // Prevenir scroll do body
        document.body.style.overflow = 'hidden';
    }

    closePopup() {
        const overlay = document.getElementById('current-popup-overlay');
        const popup = document.querySelector('.popup-alert');
        
        if (overlay || popup) {
            if (overlay) {
                overlay.classList.add('popup-fade-out');
                setTimeout(() => overlay.remove(), 300);
            }
            if (popup) {
                popup.classList.add('popup-fade-out');
                setTimeout(() => {
                    popup.remove();
                    this.isShowingPopup = false;
                    // Processar próximo popup na fila
                    setTimeout(() => this.processPopupQueue(), 100);
                }, 300);
            }
            
            // Restaurar scroll do body
            document.body.style.overflow = '';
        }
    }

    async viewAllDelayed() {
        try {
            const response = await fetch(window.BASE_URL + 'api/os/delayed-details');
            if (!response.ok) throw new Error('Erro na requisição');

            const data = await response.json();
            
            if (data.success && data.delayed) {
                this.showDetailedDelayedPopup(data.delayed);
            }
        } catch (error) {
            alert('Erro ao carregar detalhes das OSs atrasadas');
        }
    }

    showDetailedDelayedPopup(delayedOS) {
        const popup = this.createPopup('danger', '📋 Detalhes das OSs Atrasadas');
        
        if (delayedOS.length === 0) {
            popup.body.innerHTML = '<p class="text-center">Nenhuma OS com atraso encontrada.</p>';
        } else {
            const tableRows = delayedOS.map(os => `
                <tr>
                    <td><strong style="color: #dc3545;">#${os.id_os}</strong></td>
                    <td>${os.cliente_nome}</td>
                    <td>
                        ${os.nome_tec}
                        ${os.tel_tecnico ? `<br><small style="color: #28a745;"><i class="fas fa-phone"></i> ${os.tel_tecnico}</small>` : ''}
                    </td>
                    <td>${this.formatDateTime(os.data_agendamento)}</td>
                    <td>
                        <span class="delay-badge">${Math.floor(os.hours_delayed)}h</span>
                    </td>
                </tr>
            `).join('');

            popup.body.innerHTML = `
                <div style="text-align: center; margin-bottom: 25px;">
                    <div style="font-size: 2.5em; margin-bottom: 10px;">📋</div>
                    <h4 style="color: var(--popup-text); margin: 0;">Detalhes Completos</h4>
                    <p style="color: var(--popup-text); opacity: 0.7; margin: 10px 0 0 0;">Todas as OSs com atraso registrado</p>
                </div>
                <div style="overflow-x: auto;">
                    <table class="popup-table">
                        <thead>
                            <tr>
                                <th>OS</th>
                                <th>Cliente</th>
                                <th>Técnico</th>
                                <th>Agendamento</th>
                                <th>Atraso</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${tableRows}
                        </tbody>
                    </table>
                </div>
            `;
        }

        popup.footer.innerHTML = `
            <button class="popup-btn popup-btn-secondary" onclick="popupAlerts.closePopup()">
                <i class="fas fa-times"></i> Fechar
            </button>
        `;

        this.showPopup(popup.container);
    }

    async viewOSDetails(osId) {
        try {
            const response = await fetch(window.BASE_URL + `api/os/details/${osId}`);
            if (!response.ok) throw new Error('Erro na requisição');

            const data = await response.json();
            
            if (data.success && data.os) {
                this.showOSDetailsPopup(data.os);
            }
        } catch (error) {
            alert('Erro ao carregar detalhes da OS');
        }
    }

    showOSDetailsPopup(os) {
        const popup = this.createPopup('info', `Detalhes da OS #${os.id_os}`);
        
        popup.body.innerHTML = `
            <div style="text-align: left;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="font-size: 2.5em; margin-bottom: 10px;">📋</div>
                    <h4 style="color: var(--popup-text); margin: 0;">OS #${os.id_os}</h4>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div style="background: var(--popup-footer-bg); padding: 15px; border-radius: 8px; border: 1px solid var(--popup-border);">
                        <h6 style="color: var(--popup-text); margin-bottom: 10px;"><i class="fas fa-user"></i> Cliente</h6>
                        <p style="margin: 0; font-weight: 500; color: var(--popup-text);">${os.cliente_nome}</p>
                        ${os.tel1_cli ? `<p style="margin: 5px 0 0 0; color: #28a745; font-size: 0.9em;"><i class="fas fa-phone"></i> ${os.tel1_cli}</p>` : ''}
                    </div>
                    
                    <div style="background: var(--popup-footer-bg); padding: 15px; border-radius: 8px; border: 1px solid var(--popup-border);">
                        <h6 style="color: var(--popup-text); margin-bottom: 10px;"><i class="fas fa-tools"></i> Técnico</h6>
                        <p style="margin: 0; font-weight: 500; color: var(--popup-text);">${os.nome_tec || 'Não atribuído'}</p>
                        ${os.tel_pessoal ? `<p style="margin: 5px 0 0 0; color: #28a745; font-size: 0.9em;"><i class="fas fa-phone"></i> ${os.tel_pessoal}</p>` : ''}
                    </div>
                </div>
                
                <div style="background: var(--popup-footer-bg); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--popup-border);">
                    <h6 style="color: var(--popup-text); margin-bottom: 10px;"><i class="fas fa-info-circle"></i> Status e Datas</h6>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <p style="margin: 0 0 8px 0;"><strong>Status Atual:</strong></p>
                            <span style="background: linear-gradient(135deg, #20a136, #F97316); color: white; padding: 6px 12px; border-radius: 15px; font-size: 0.85em; font-weight: 500;">
                                ${this.getStatusText(os.status)}
                            </span>
                        </div>
                        <div>
                            <p style="margin: 0 0 5px 0;"><strong>Agendamento:</strong></p>
                            <p style="margin: 0; font-size: 0.9em; color: var(--popup-text);">${this.formatDateTime(os.data_agendamento)}</p>
                            ${os.data_encerramento ? 
                                `<p style="margin: 8px 0 0 0; color: var(--popup-text);"><strong>Encerramento:</strong></p>
                                <p style="margin: 0; font-size: 0.9em; color: var(--popup-text);">${this.formatDateTime(os.data_encerramento)}</p>` : 
                                ''
                            }
                        </div>
                    </div>
                </div>
                
                ${os.servico_prestado ? `
                <div style="background: var(--popup-footer-bg); padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid var(--popup-border);">
                    <h6 style="color: var(--popup-text); margin-bottom: 10px;"><i class="fas fa-clipboard-list"></i> Serviço</h6>
                    <p style="margin: 0; line-height: 1.5; color: var(--popup-text);">${os.servico_prestado}</p>
                </div>
                ` : ''}
                
                ${os.endereco ? `
                <div style="background: var(--popup-footer-bg); padding: 15px; border-radius: 8px; border: 1px solid var(--popup-border);">
                    <h6 style="color: var(--popup-text); margin-bottom: 10px;"><i class="fas fa-map-marker-alt"></i> Endereço</h6>
                    <p style="margin: 0; line-height: 1.5; color: var(--popup-text);">${os.endereco}${os.bairro ? `, ${os.bairro}` : ''}${os.cidade ? ` - ${os.cidade}` : ''}</p>
                </div>
                ` : ''}
            </div>
        `;

        popup.footer.innerHTML = `
            <button class="popup-btn popup-btn-primary" onclick="window.open('${window.BASE_URL}admin/os/edit/${os.id_os}', '_blank')">
                <i class="fas fa-edit"></i> Editar OS
            </button>
            <button class="popup-btn popup-btn-secondary" onclick="popupAlerts.closePopup()">
                <i class="fas fa-times"></i> Fechar
            </button>
        `;

        this.showPopup(popup.container);
    }

    getStatusText(status) {
        const texts = {
            'aberta': 'Aberta',
            'em andamento': 'Em Andamento',
            'concluida_tecnico': 'Concluída pelo Técnico',
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
            'concluida_tecnico': 'info',
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
        console.log('Iniciando monitoramento de alertas...');
        
        // Verificação inicial imediata para debug
        this.checkDelayedOS();
        this.checkStatusChanges();
        
        // Verificação após 3 segundos
        setTimeout(() => {
            this.checkDelayedOS();
            this.checkStatusChanges();
        }, 3000);
        
        // Verificações periódicas
        setInterval(() => {
            this.checkDelayedOS();
        }, 300000); // 5 minutos para OSs atrasadas
        
        setInterval(() => {
            this.checkStatusChanges();
        }, 120000); // 2 minutos para mudanças de status
    }
}

// Inicializar apenas para admin e funcionário no dashboard
document.addEventListener('DOMContentLoaded', function() {
    const userProfile = document.body.dataset.userProfile;
    const currentUrl = window.location.pathname;
    
    // Verificar se está no dashboard
    const isDashboard = currentUrl.includes('/dashboard') || currentUrl.endsWith('/dashboard');
    
    if ((userProfile === 'admin' || userProfile === 'funcionario') && isDashboard) {
        console.log('Inicializando sistema de alertas no dashboard para:', userProfile);
        window.popupAlerts = new PopupAlerts();
    } else {
        console.log('Sistema de alertas não inicializado - fora do dashboard ou perfil não autorizado');
    }
});