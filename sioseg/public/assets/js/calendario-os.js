document.addEventListener('DOMContentLoaded', function() {
    var calendarGrid = document.querySelector('.calendar-grid');
    var detailsContainer = document.getElementById('os-details-container');
    var detailsContent = document.getElementById('details-content');
    var detailsTitle = document.getElementById('details-title');
    var closeDetailsBtn = document.getElementById('close-details');
    var todayBtn = document.getElementById('today-btn');
    var filterBtns = document.querySelectorAll('.filter-btn');
    
    var currentFilter = 'all';

    // Navegação para hoje
    if (todayBtn) {
        todayBtn.addEventListener('click', function() {
            var today = new Date();
            var year = today.getFullYear();
            var month = today.getMonth() + 1;
            var baseElement = document.querySelector('base');
            var rootUrl = baseElement ? baseElement.getAttribute('href') : '';
            var currentPath = window.location.pathname;
            
            if (currentPath.indexOf('/admin/') !== -1) {
                window.location.href = rootUrl + 'admin/os/calendario?year=' + year + '&month=' + month;
            } else {
                window.location.href = rootUrl + 'funcionario/os/calendario?year=' + year + '&month=' + month;
            }
        });
    }

    // Filtros de status
    for (var i = 0; i < filterBtns.length; i++) {
        filterBtns[i].addEventListener('click', function() {
            for (var j = 0; j < filterBtns.length; j++) {
                filterBtns[j].classList.remove('active');
            }
            this.classList.add('active');
            currentFilter = this.dataset.status;
            applyFilter();
        });
    }

    function applyFilter() {
        var dayCells = document.querySelectorAll('.day-cell:not(.empty)');
        
        for (var i = 0; i < dayCells.length; i++) {
            var cell = dayCells[i];
            // Reset styles
            cell.style.display = 'block';
            cell.style.opacity = '1';
            var badges = cell.querySelectorAll('.status-badge');
            for (var j = 0; j < badges.length; j++) {
                badges[j].style.transform = '';
                badges[j].style.boxShadow = '';
                badges[j].style.opacity = '';
            }
            
            var totalOs = parseInt(cell.dataset.totalOs) || 0;
            
            if (currentFilter === 'all' || totalOs === 0) {
                continue;
            }
            
            var statusCount = {
                'aberta': parseInt(cell.dataset.abertas, 10) || 0,
                'em andamento': parseInt(cell.dataset.andamento, 10) || 0,
                'concluida': parseInt(cell.dataset.concluidas, 10) || 0,
                'encerrada': parseInt(cell.dataset.encerradas, 10) || 0
            };
            
            if (statusCount[currentFilter] > 0) {
                // Destacar badges relevantes
                for (var k = 0; k < badges.length; k++) {
                    var badge = badges[k];
                    if (badge.dataset.status === currentFilter) {
                        badge.style.transform = 'scale(1.1)';
                        badge.style.boxShadow = '0 2px 8px rgba(0,0,0,0.3)';
                    } else {
                        badge.style.transform = 'scale(0.9)';
                        badge.style.opacity = '0.6';
                    }
                }
            } else {
                cell.style.opacity = '0.3';
            }
        }
    }

    // Click nos dias
    if (calendarGrid) {
        calendarGrid.addEventListener('click', function(event) {
            var dayCell = event.target.closest('.day-cell');

            if (!dayCell || dayCell.classList.contains('empty')) return;
            
            var totalOs = dayCell.dataset.totalOs;
            if (!totalOs || totalOs === '0' || parseInt(totalOs) === 0) return;

            var date = dayCell.dataset.date;
            if (!date) return;
            
            var baseElement = document.querySelector('base');
            var rootUrl = baseElement ? baseElement.getAttribute('href') : '';
            var currentPath = window.location.pathname;

            try {
                var formattedDate = new Date(date + 'T00:00:00').toLocaleDateString('pt-BR');
                
                if (detailsTitle) {
                    detailsTitle.innerHTML = '<i class="fas fa-calendar-alt"></i> Ordens de Serviço para ' + formattedDate + ' <span style="color: #666; font-size: 0.8em;">(' + totalOs + ' OS)</span>';
                }
                
                if (detailsContent) {
                    detailsContent.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Carregando detalhes...</div>';
                }
                
                if (detailsContainer) {
                    detailsContainer.style.display = 'block';
                    detailsContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }

                // Detecta se é admin ou funcionario baseado na URL
                var detailsUrl;
                if (currentPath.indexOf('/admin/') !== -1) {
                    detailsUrl = rootUrl + 'admin/os/details?date=' + date;
                } else {
                    detailsUrl = rootUrl + 'funcionario/os/details?date=' + date;
                }

                fetch(detailsUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(response) {
                    if (!response.ok) throw new Error('Erro na requisição');
                    return response.text();
                })
                .then(function(html) { 
                    if (detailsContent) {
                        detailsContent.innerHTML = html;
                    }
                })
                .catch(function(error) {
                    console.error('Erro ao buscar detalhes da OS:', error);
                    if (detailsContent) {
                        detailsContent.innerHTML = '<div style="text-align: center; padding: 40px; color: #dc3545;"><i class="fas fa-exclamation-triangle" style="font-size: 2em; margin-bottom: 10px;"></i><p>Erro ao carregar os detalhes.</p><button onclick="location.reload()" class="action-btn">Tentar novamente</button></div>';
                    }
                });
            } catch (error) {
                console.error('Erro ao processar data:', error);
                if (detailsContent) {
                    detailsContent.innerHTML = '<div style="text-align: center; padding: 40px; color: #dc3545;"><p>Erro ao processar a data selecionada.</p></div>';
                }
            }
        });
    }

    // Fechar detalhes
    if (closeDetailsBtn) {
        closeDetailsBtn.addEventListener('click', function() {
            if (detailsContainer) {
                detailsContainer.style.display = 'none';
            }
        });
    }
    
    // Fechar com ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && detailsContainer && detailsContainer.style.display === 'block') {
            detailsContainer.style.display = 'none';
        }
    });
});