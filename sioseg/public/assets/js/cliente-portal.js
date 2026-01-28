/* JavaScript específico para o portal do cliente */

document.addEventListener('DOMContentLoaded', function() {
    const BASE_URL = window.BASE_URL || '/';
    const osConfirmadaId = window.osConfirmadaId || null; // Esta variável será definida no portal.php
    
    // Detecta se é dispositivo móvel
    const isMobile = window.innerWidth <= 768 || /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    
    // Adiciona classe para dispositivos móveis
    if (isMobile) {
        document.body.classList.add('mobile-device');
    }
    
    // Melhora a experiência de toque em dispositivos móveis
    if ('ontouchstart' in window) {
        document.body.classList.add('touch-device');
    }
    
    // Alerta para OS aguardando confirmação do cliente
    const osAguardandoConfirmacao = document.querySelectorAll('form[id^="form-confirm-"]');
    if (osAguardandoConfirmacao.length > 0) {
        const osNumbers = Array.from(osAguardandoConfirmacao).map(form => '#' + form.dataset.osId).join(', ');
        alert(`✅ ATENÇÃO: Você tem ${osAguardandoConfirmacao.length} OS aguardando sua confirmação de conclusão: ${osNumbers}\n\nO técnico finalizou o atendimento e aguarda sua confirmação.`);
    }
    
    // Função para alternar entre abas
    window.showTab = function(tabName, clickedButton = null) {
        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        // Ativa o botão correto
        const buttonToActivate = clickedButton || document.querySelector(`.tab-button[data-tab="${tabName}"]`);
        if (buttonToActivate) {
            buttonToActivate.classList.add('active');
        }
        
        // Ativa o conteúdo da aba
        const contentToActivate = document.getElementById(tabName);
        if (contentToActivate) {
            contentToActivate.classList.add('active');
        }

        // If opening historico or avaliacoes, ensure there's a spacer at the end to account for fixed footer
        if (tabName === 'historico' || tabName === 'avaliacoes') {
            try {
                var containerId = tabName === 'historico' ? 'historico' : 'avaliacoes';
                var hist = document.getElementById(containerId);
                if (hist) {
                    var spacer = hist.querySelector('.footer-spacer');
                    // compute footer height
                    var fh = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--footer-height')) || 0;
                    if (!fh) {
                        var f = document.querySelector('.footer') || document.querySelector('footer');
                        fh = f ? Math.ceil(f.getBoundingClientRect().height) : 0;
                    }
                    var extra = 20; // extra padding to be safe
                    var needed = Math.max(0, fh + extra);

                    if (!spacer) {
                        spacer = document.createElement('div');
                        spacer.className = 'footer-spacer';
                        spacer.setAttribute('aria-hidden', 'true');
                        hist.appendChild(spacer);
                    }
                    spacer.style.height = needed + 'px';
                    spacer.style.width = '100%';
                    spacer.style.pointerEvents = 'none';
                }
            } catch (e) {
                // silently ignore
            }
        }
    };

    // Lógica para o botão de confirmar conclusão
    document.querySelectorAll('form[id^="form-confirm-"]').forEach(form => {
        form.addEventListener('submit', function(event) {
            // Previne o envio padrão do formulário
            event.preventDefault(); 
            
            const osId = this.dataset.osId;
            
            // Pergunta de confirmação
            if (confirm('Confirmar que o serviço da OS #' + osId + ' foi concluído?')) {
                // Se o usuário confirmar, o formulário é enviado
                this.submit();
            }
            // Se o usuário cancelar, nada acontece.
        });
    });

    // Verifica se uma OS foi recém-confirmada para perguntar sobre a avaliação
    if (osConfirmadaId) {
        if (confirm('Serviço confirmado! Deseja avaliar o atendimento agora?')) {
            // 1. Muda para a aba de Histórico
            showTab('historico'); // Agora funciona sem um evento de clique

            // 2. Encontra o formulário de avaliação específico
            const avaliacaoForm = document.getElementById('avaliacao-form-' + osConfirmadaId);

            if (avaliacaoForm) {
                // Apenas adiciona destaque, sem scroll automático
                avaliacaoForm.classList.add('highlight');
                setTimeout(() => {
                    avaliacaoForm.classList.remove('highlight');
                }, 3000);
            }
        }
    };

    // Sistema de avaliação por estrelas com melhorias para mobile
    document.querySelectorAll('.estrelas').forEach(rating => {
        const stars = rating.querySelectorAll('span[data-value]');
        const input = rating.parentElement.querySelector('input[name="nota"]');
        
        if (stars.length > 0 && input) {
            stars.forEach(star => {
                // Evento de clique (funciona em desktop e mobile)
                star.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    rating.setAttribute('data-rating', value);
                    input.value = value;
                    
                    // Atualiza visual das estrelas
                    updateStarsVisual(stars, value);
                    
                    // Feedback tátil em dispositivos móveis
                    if (navigator.vibrate && isMobile) {
                        navigator.vibrate(50);
                    }
                });
                
                // Eventos de hover apenas para desktop
                if (!isMobile) {
                    star.addEventListener('mouseover', function() {
                        const value = this.getAttribute('data-value');
                        updateStarsVisual(stars, value, true);
                    });
                }
                
                // Melhora a área de toque em dispositivos móveis
                if (isMobile) {
                    star.style.padding = '8px';
                    star.style.margin = '0 2px';
                }
            });
            
            // Evento mouseleave apenas para desktop
            if (!isMobile) {
                rating.addEventListener('mouseleave', function() {
                    const currentRating = rating.getAttribute('data-rating') || 0;
                    updateStarsVisual(stars, currentRating);
                });
            }
        }
    });
    
    // Função auxiliar para atualizar visual das estrelas
    function updateStarsVisual(stars, value, isHover = false) {
        stars.forEach((s, index) => {
            if (index < value) {
                s.classList.add('preenchida');
                s.style.color = 'var(--accent-color)';
            } else {
                s.classList.remove('preenchida');
                s.style.color = 'var(--text-muted)';
            }
        });
    }
    
    // Scroll automático removido para evitar problemas de layout
    
    // Contador de caracteres para textarea de comentários
    document.querySelectorAll('textarea[name="comentario"]').forEach(textarea => {
        const maxLength = 5000;
        
        // Cria contador visual
        const counter = document.createElement('div');
        counter.className = 'char-counter';
        counter.style.cssText = 'font-size: 0.8em; color: var(--text-muted); text-align: right; margin-top: 5px;';
        
        // Insere contador após o textarea
        textarea.parentNode.insertBefore(counter, textarea.nextSibling);
        
        // Função para atualizar contador
        function updateCounter() {
            const remaining = maxLength - textarea.value.length;
            counter.textContent = `${textarea.value.length}/${maxLength} caracteres`;
            
            if (remaining < 100) {
                counter.style.color = 'var(--danger-color, #dc3545)';
            } else if (remaining < 500) {
                counter.style.color = 'var(--warning-color, #ffc107)';
            } else {
                counter.style.color = 'var(--text-muted)';
            }
        }
        
        // Eventos para atualizar contador
        textarea.addEventListener('input', updateCounter);
        textarea.addEventListener('paste', () => setTimeout(updateCounter, 10));
        
        // Inicializa contador
        updateCounter();
    });
    
    // Otimiza formulários para mobile
    if (isMobile) {
        // Previne zoom em inputs no iOS
        const inputs = document.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            if (input.type !== 'range' && input.type !== 'checkbox' && input.type !== 'radio') {
                input.style.fontSize = '16px';
            }
        });
        
        // Melhora a experiência de textarea em mobile
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('focus', function() {
                // Pequeno delay para permitir que o teclado apareça
                setTimeout(() => {
                    const rect = this.getBoundingClientRect();
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    const targetTop = rect.top + scrollTop - 100; // Offset para não ficar colado no topo
                    
                    window.scrollTo({
                        top: Math.max(0, targetTop),
                        behavior: 'smooth'
                    });
                }, 300);
            });
            
            // Validação adicional para limite de caracteres
            if (textarea.name === 'comentario') {
                textarea.addEventListener('keydown', function(e) {
                    if (this.value.length >= 5000 && e.key !== 'Backspace' && e.key !== 'Delete' && !e.ctrlKey) {
                        e.preventDefault();
                    }
                });
            }
        });
    }
    
    // Melhora a experiência de confirmação em mobile
    document.querySelectorAll('.confirm-button').forEach(button => {
        if (isMobile) {
            button.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.95)';
            });
            
            button.addEventListener('touchend', function() {
                this.style.transform = 'scale(1)';
            });
        }
    });
    
    // Detecta mudanças de orientação e ajusta layout
    window.addEventListener('orientationchange', function() {
        setTimeout(() => {
            // Força recálculo do layout após mudança de orientação
            window.dispatchEvent(new Event('resize'));
        }, 100);
    });
    
    // Melhora performance em dispositivos móveis
    if (isMobile) {
        // Debounce para eventos de scroll
        let scrollTimeout;
        window.addEventListener('scroll', function() {
            if (scrollTimeout) {
                clearTimeout(scrollTimeout);
            }
            scrollTimeout = setTimeout(() => {
                // Lógica de scroll se necessária
            }, 100);
        }, { passive: true });
    }
    
    // Adiciona suporte a gestos de swipe para navegação entre abas (mobile)
    if (isMobile && 'ontouchstart' in window) {
        let startX = 0;
        let startY = 0;
        let currentTab = 0;
        const tabs = ['servicos', 'historico', 'avaliacoes'];
        
        document.addEventListener('touchstart', function(e) {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        }, { passive: true });
        
        document.addEventListener('touchend', function(e) {
            if (!startX || !startY) return;
            
            const endX = e.changedTouches[0].clientX;
            const endY = e.changedTouches[0].clientY;
            
            const diffX = startX - endX;
            const diffY = startY - endY;
            
            // Verifica se é um swipe horizontal
            if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
                const activeTabButton = document.querySelector('.tab-button.active');
                if (activeTabButton) {
                    const currentTabName = activeTabButton.getAttribute('data-tab');
                    currentTab = tabs.indexOf(currentTabName);
                    
                    if (diffX > 0 && currentTab < tabs.length - 1) {
                        // Swipe left - próxima aba
                        showTab(tabs[currentTab + 1]);
                    } else if (diffX < 0 && currentTab > 0) {
                        // Swipe right - aba anterior
                        showTab(tabs[currentTab - 1]);
                    }
                }
            }
            
            startX = 0;
            startY = 0;
        }, { passive: true });
    }
    
    // Validação global para campos de comentário
    document.addEventListener('submit', function(e) {
        const form = e.target;
        const comentarioField = form.querySelector('textarea[name="comentario"]');
        
        if (comentarioField && comentarioField.value.length > 5000) {
            e.preventDefault();
            alert('O comentário não pode exceder 5000 caracteres. Atual: ' + comentarioField.value.length);
            comentarioField.focus();
        }
    });
});

/* Bloco adicionado ao final do arquivo para scroll/padding seguro */
(function(){
    // Safe scroll that ensures the element is fully visible above the fixed footer.
    function safeScroll(el, offsetExtra = 12) {
        if (!el) return;
        // read footer height from CSS variable or compute from DOM
        var footerHeight = 0;
        try {
            var cssVar = getComputedStyle(document.documentElement).getPropertyValue('--footer-height');
            if (cssVar) footerHeight = parseInt(cssVar) || 0;
        } catch (e) {}

        if (!footerHeight) {
            var footer = document.querySelector('.footer') || document.querySelector('footer');
            if (footer) footerHeight = Math.ceil(footer.getBoundingClientRect().height) || footer.offsetHeight || 0;
        }

        var rect = el.getBoundingClientRect();
        var absoluteTop = rect.top + window.pageYOffset;

        var viewportAvailable = Math.max(0, window.innerHeight - footerHeight - offsetExtra);

        var target;
        if (rect.height <= viewportAvailable) {
            // Center the element within the available viewport area above the footer
            var desiredTopInViewport = Math.max(12, (viewportAvailable - rect.height) / 2);
            target = Math.max(0, absoluteTop - desiredTopInViewport);
        } else {
            // Element taller than available space: align top with small offset so user sees start
            target = Math.max(0, absoluteTop - 12);
        }

        window.scrollTo({ top: target, behavior: 'smooth' });

        // focus first focusable element without scrolling again
        try {
            var focusable = el.querySelector('input, textarea, button, [tabindex]');
            if (focusable) focusable.focus({ preventScroll: true });
        } catch (e) {}
        // After scroll, ensure element bottom is not hidden by footer (adjust if necessary)
        setTimeout(function(){
            try {
                var r2 = el.getBoundingClientRect();
                var bottomAllowed = window.innerHeight - footerHeight - 8;
                if (r2.bottom > bottomAllowed) {
                    var delta = r2.bottom - bottomAllowed;
                    window.scrollBy({ top: delta + 8, behavior: 'smooth' });
                }
            } catch (e) {}
        }, 260);
    }

    // Retry wrapper: attempts several times to ensure element fully visible after animations/layout changes
    function ensureVisibleWithRetries(el, attempts = 6, delay = 220) {
        if (!el) return;
        var tries = 0;
        function attempt() {
            if (!el) return;
            try {
                // Pre-scroll to element top to handle cases where element is above the viewport
                var rpre = el.getBoundingClientRect();
                var absoluteTopPre = rpre.top + window.pageYOffset;
                window.scrollTo({ top: Math.max(0, absoluteTopPre - 80), behavior: 'auto' });
            } catch (e) {}
            safeScroll(el);
            setTimeout(function(){
                try {
                    var footer = document.querySelector('.footer') || document.querySelector('footer');
                    var fh = footer ? Math.ceil(footer.getBoundingClientRect().height) : (parseInt(getComputedStyle(document.documentElement).getPropertyValue('--footer-height')) || 0);
                    var r = el.getBoundingClientRect();
                    var hidden = Math.max(0, r.bottom - (window.innerHeight - fh));
                    if (hidden > 2 && tries < attempts) {
                        tries++;
                        attempt();
                    }
                } catch (e) {
                    // ignore
                }
            }, delay);
        }
        attempt();
    }

    // Wait for element by one or more IDs to exist and be displayed, then ensure visible
    function waitForVisibleAndEnsure(id1, id2, timeoutMs = 5000) {
        var start = Date.now();
        function findAndCheck() {
            var el = document.getElementById(id1) || (id2 ? document.getElementById(id2) : null);
        // debug logs removed
            if (el) {
                // check visibility
                var rect = el.getBoundingClientRect();
                if (rect.width > 0 && rect.height > 0) {
                    // element visible, ensuring
                    ensureVisibleWithRetries(el);
                    return;
                }
            }
            if (Date.now() - start < timeoutMs) {
                setTimeout(findAndCheck, 150);
            }
        }
        findAndCheck();
    }

    // Abre o formulário de avaliação dentro do modal global #evaluation-modal
    function openFormInEvaluationModal(formEl, osId) {
        try {
            // monta um clone simples do formulário ou copia conteúdo relevante
            var modal = document.getElementById('evaluation-modal');
            var modalBody = modal && modal.querySelector('#evaluation-body');
            var modalOsId = modal && modal.querySelector('#evaluation-os-id');
            if (!modal || !modalBody) return;

            // Se receber o elemento form, tenta extrair nota/comentário campos
            var nota = '';
            var comentario = '';
            try {
                var inputNota = formEl.querySelector('input[name="nota"]');
                var textarea = formEl.querySelector('textarea[name="comentario"]');
                if (inputNota) nota = inputNota.value || inputNota.getAttribute('value') || '';
                if (textarea) comentario = textarea.value || textarea.textContent || '';
            } catch (e) {}

            modalOsId.textContent = osId || (formEl.dataset && formEl.dataset.osId) || '';
            modalBody.innerHTML = '<p><strong>Nota:</strong> ' + (nota || '-') + '</p>' +
                                  '<p><strong>Comentário:</strong></p>' +
                                  '<div style="white-space:pre-wrap;">' + (comentario || '-') + '</div>';

            // populate modal only; do not automatically display it
            modal.querySelector('.modal-content').focus();
        } catch (e) {
            console.warn('openFormInEvaluationModal falhou', e);
        }
    }

    // Show/hide helpers for the evaluation modal
    function showEvaluationModal() {
        var modal = document.getElementById('evaluation-modal');
        if (!modal) return;
        modal.classList.add('show');
        try { modal.querySelector('.modal-content').focus(); } catch(e){}
    }

    function hideEvaluationModal() {
        var modal = document.getElementById('evaluation-modal');
        if (!modal) return;
        modal.classList.remove('show');
    }

    document.addEventListener('DOMContentLoaded', function(){
        if (window.osConfirmadaId) {
            // tenta encontrar o formulário e garante que esteja visível antes de rolar
            var confirmId = 'form-confirm-' + window.osConfirmadaId;
            var avaliacaoId = 'avaliacao-form-' + window.osConfirmadaId;
            // abre a aba historico se necessário
            showTab('historico');
            // Se o formulário de avaliação existir, abrir no modal em vez de rolar
            var avaliacaoForm = document.getElementById(avaliacaoId);
            if (avaliacaoForm) {
                // Populate modal but do NOT auto-open it; give user control
                openFormInEvaluationModal(avaliacaoForm, window.osConfirmadaId);
                // optionally, you can show modal by calling showEvaluationModal();
            } else {
                waitForVisibleAndEnsure(confirmId, avaliacaoId);
            }
        }
    });

    document.addEventListener('click', function(e){
        var clicked = e.target.closest('[id^="avaliacao-form-"], [id^="form-confirm-"]');
        if (clicked) {
            // se for um id, espera o elemento ficar visível
            if (clicked.id) {
                waitForVisibleAndEnsure(clicked.id);
            } else {
                setTimeout(function(){ ensureVisibleWithRetries(clicked); }, 150);
            }
        }
    });

    // Wire the modal close button (if present) to hide modal
    document.addEventListener('click', function(e){
        var c = e.target.closest('#evaluation-close');
        if (c) {
            e.preventDefault();
            hideEvaluationModal();
        }
    });

    // Observe mudanças dentro do histórico para rolar automaticamente quando novos formulários surgirem
    (function(){
        var containers = [];
        var h = document.getElementById('historico');
        var a = document.getElementById('avaliacoes');
        if (h) containers.push(h);
        if (a) containers.push(a);
        if (containers.length === 0) return;

        var mo = new MutationObserver(function(mutations){
            mutations.forEach(function(m){
                m.addedNodes.forEach(function(node){
                    if (node.nodeType === 1) {
                        var af = node.querySelector('[id^="avaliacao-form-"]') || node.querySelector('form[id^="form-confirm-"]');
                        if (af) {
                            // ao detectar um novo form, tenta usar modal ou garantir visibilidade
                            try { openFormInEvaluationModal(af); } catch(e) { ensureVisibleWithRetries(af); }
                        }
                        // ensure spacer exists/updated for container where node was added
                        try {
                            var parentContainer = node.closest('#historico, #avaliacoes');
                            if (parentContainer) {
                                var fh = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--footer-height')) || 0;
                                if (!fh) {
                                    var f = document.querySelector('.footer') || document.querySelector('footer');
                                    fh = f ? Math.ceil(f.getBoundingClientRect().height) : 0;
                                }
                                var extra = 20;
                                var needed = Math.max(0, fh + extra);
                                var spacer = parentContainer.querySelector('.footer-spacer');
                                if (!spacer) {
                                    spacer = document.createElement('div');
                                    spacer.className = 'footer-spacer';
                                    spacer.setAttribute('aria-hidden', 'true');
                                    parentContainer.appendChild(spacer);
                                }
                                spacer.style.height = needed + 'px';
                            }
                        } catch(e) {}
                    }
                });
            });
        });

        containers.forEach(function(c){ mo.observe(c, { childList: true, subtree: true }); });
    })();
    
    // Nota: removido listener que impedia envio do formulário ao clicar dentro de .avaliacao-form
})();