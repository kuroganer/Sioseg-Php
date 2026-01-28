document.addEventListener('DOMContentLoaded', function() {
    if (typeof osDoDia === 'undefined') {
        console.error('Dados das Ordens de Serviço (osDoDia) não encontrados.');
        return;
    }

    var listaOSContainer = document.getElementById('lista-os');
    var detalhesOSContainer = document.getElementById('detalhes-os');
    var estornoContainer = document.getElementById('estorno-container');
    
    // Alertas para o técnico
    mostrarAlertas();
    
    // Função para mostrar alertas
    function mostrarAlertas() {
        let alertas = [];
        
        // Verifica OS atrasadas
        if (typeof osAtrasadas !== 'undefined' && osAtrasadas.length > 0) {
            const osAtrasadasPendentes = osAtrasadas.filter(os => os.conclusao_tecnico !== 'concluida');
            if (osAtrasadasPendentes.length > 0) {
                const osNumbers = osAtrasadasPendentes.map(os => 'N#' + os.id_os).join(', ');
                alertas.push(`⚠️ ${osAtrasadasPendentes.length} OS ATRASADA(S): ${osNumbers}\n📞 Entre em contato com o administrador`);
            }
        }
        
        // Verifica OS em andamento
        const osEmAndamento = osDoDia.find(os => os.status.toLowerCase() === 'em andamento' && os.conclusao_tecnico !== 'concluida');
        if (osEmAndamento) {
            alertas.push(`⚡ OS em andamento: N#${osEmAndamento.id_os}`);
        }
        
        // Verifica OS para estorno
        const osParaEstornoCount = window.osParaEstornoCount || 0;
        if (osParaEstornoCount > 0) {
            alertas.push(`📦 ${osParaEstornoCount} OS aguardando estorno de materiais`);
        }
        
        if (alertas.length > 0) {
            alert(`🔔 ATENÇÃO TÉCNICO:\n\n${alertas.join('\n')}\n\nGerencie suas tarefas pendentes.`);
        }
    }
    
    // Função para alternar abas
    window.showTab = function(tabName, clickedButton = null) {
        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        const buttonToActivate = clickedButton || document.querySelector(`.tab-button[data-tab="${tabName}"]`);
        if (buttonToActivate) {
            buttonToActivate.classList.add('active');
        }
        
        const contentToActivate = document.getElementById(tabName);
        if (contentToActivate) {
            contentToActivate.classList.add('active');
        }
        
        if (tabName === 'estornos') {
            carregarEstornos();
        }
    };
    
    // Função para carregar estornos
    async function carregarEstornos() {
        estornoContainer.innerHTML = `
            <h2>📦 Estorno de Materiais</h2>
            <p>Carregando OS com materiais...</p>
        `;
        
        try {
            const response = await fetch(`${BASE_URL}tecnico/os/buscarOSParaEstorno`);
            const result = await response.json();
            
            let estornoHtml = `<h2>📦 Estorno de Materiais</h2>`;
            
            if (!result.success || result.data.length === 0) {
                estornoHtml += '<p>📝 Nenhuma OS com materiais disponível para estorno.</p>';
            } else {
                estornoHtml += '<p>Selecione os materiais que deseja estornar:</p>';
                
                result.data.forEach(os => {
                    estornoHtml += `
                        <div class="os-estorno-item">
                            <h3>OS #${os.id_os} - ${os.cliente_nome}</h3>
                            <div class="materiais-estorno">
                    `;
                    
                    os.produtos_usados.forEach(produto => {
                        estornoHtml += `
                            <div class="material-estorno">
                                <span class="material-nome">${produto.nome}</span>
                                <div class="estorno-controls">
                                    <label>Qtd a estornar:</label>
                                    <input type="number" min="0" max="${produto.qtd_usada}" value="0" 
                                           data-os="${os.id_os}" data-produto="${produto.id_prod}" 
                                           data-max="${produto.qtd_usada}" class="qtd-estorno">
                                    <span class="max-info">/ ${produto.qtd_usada}</span>
                                    <button class="btn-estornar" onclick="executarEstorno(${os.id_os}, ${produto.id_prod}, this)">Estornar</button>
                                </div>
                            </div>
                        `;
                    });
                    
                    estornoHtml += '</div></div>';
                });
            }
            
            estornoContainer.innerHTML = estornoHtml;
            
        } catch (error) {
            console.error('Erro ao carregar OS para estorno:', error);
            estornoContainer.innerHTML = `
                <h2>📦 Estorno de Materiais</h2>
                <p>⚠️ Erro ao carregar dados. Tente novamente.</p>
            `;
        }
    }

    function renderListaOS() {
        var osList = osDoDia.filter(function(os) {
            var status = os.status.toLowerCase();
            return status !== 'concluida' && status !== 'encerrada' && status !== 'cancelada' && os.conclusao_tecnico !== 'concluida';
        });

        listaOSContainer.innerHTML = `<h2>Suas Ordens de Serviço de Hoje</h2>`;

        if (osList.length === 0) {
            verificarFimDoDia();
            return;
        }

        osList.forEach(function(os) {
            var osItem = document.createElement('div');
            osItem.classList.add('os-item');
            osItem.setAttribute('data-id', os.id_os);
            osItem.innerHTML = '<div><h3>O.S. #' + os.id_os + ' - ' + os.cliente_nome + '</h3><p>' + os.desc_servico + '</p></div><span class="status status-' + os.status.toLowerCase().replace(' ', '-') + '">' + os.status + '</span>';
            osItem.addEventListener('click', function() { carregarDetalhesOS(os.id_os); });
            listaOSContainer.appendChild(osItem);
        });
    }

    function clearDetails() {
        detalhesOSContainer.innerHTML = `
            <h2>Detalhes do Atendimento</h2>
            <p>Clique em uma OS para visualizar informações completas e gerenciar o atendimento.</p>`;
        
        if (window.innerWidth <= 767) {
            listaOSContainer.classList.remove('hide-mobile');
            detalhesOSContainer.classList.remove('show-mobile');
        }
    }

    function verificarFimDoDia() {
        listaOSContainer.innerHTML += `
            <p>✨ Parabéns! Você concluiu todas as OS do dia.</p>
        `;
    }

    window.carregarDetalhesOS = function(osId) {
        var os = osDoDia.find(function(o) { return o.id_os == osId; });

        document.querySelectorAll('.os-item').forEach(function(item) { item.classList.remove('selected'); });
        var selectedOsItem = document.querySelector('.os-item[data-id="' + osId + '"]');
        if (selectedOsItem) {
            selectedOsItem.classList.add('selected');
        }

        if (window.innerWidth <= 767) {
            listaOSContainer.classList.add('hide-mobile');
            detalhesOSContainer.classList.add('show-mobile');
        }

        if (!os) {
            detalhesOSContainer.innerHTML = '<h2>Detalhes do Atendimento</h2><p>⚠️ Ordem de serviço não encontrada.</p>';
            return;
        }

        var enderecoDetalhado = '<div class="endereco-detalhes"><h4>📍 Localização do Atendimento</h4><div class="endereco-linha"><strong>' + (os.endereco || 'Endereço não informado') + '</strong>' + (os.num_end ? ', nº ' + os.num_end : '') + (os.complemento ? ' - ' + os.complemento : '') + '</div><div class="endereco-linha">' + (os.bairro || 'Bairro não informado') + ' - ' + (os.cidade || 'Cidade') + ', ' + (os.uf || 'UF') + (os.cep ? ' - CEP: ' + os.cep : '') + '</div>' + (os.tipo_moradia ? '<div class="endereco-linha"><strong>Tipo:</strong> ' + os.tipo_moradia + '</div>' : '') + (os.ponto_referencia ? '<div class="endereco-linha"><strong>Referência:</strong> ' + os.ponto_referencia + '</div>' : '') + '</div>';

        var agendamentoFormatted = '-';
        if (os.data_agendamento) {
            try {
                agendamentoFormatted = new Date(os.data_agendamento).toLocaleString('pt-BR');
            } catch (e) {
                agendamentoFormatted = os.data_agendamento;
            }
        }

        detalhesOSContainer.innerHTML = `
            <div class="mobile-header">
                <button class="btn-voltar" onclick="voltarParaLista()" style="display: ${window.innerWidth <= 767 ? 'inline-block' : 'none'};">← Voltar</button>
                <h2>📝 Ordem de Serviço N#${os.id_os}</h2>
            </div>
            
            <div class="cliente-info">
                <h4>👤 Informações do Cliente</h4>
                <p><strong>Nome:</strong> ${os.cliente_nome}</p>
                <div class="contatos">
                    <span><strong>📞 Telefone:</strong> ${os.cliente_telefone || 'Não informado'}</span>
                    ${os.tel2_cli ? `<span><strong>Tel. 2:</strong> ${os.tel2_cli}</span>` : ''}
                    ${os.cliente_contato ? `<span><strong>Contato:</strong> ${os.cliente_contato}</span>` : ''}
                </div>
            </div>
            
            ${enderecoDetalhado}
            
            <div class="servico-info">
                <h4>🔧 Detalhes do Serviço</h4>
                <p><strong>Descrição:</strong> ${os.desc_servico}</p>
                <p><strong>Status Atual:</strong> <span class="status status-${os.status.toLowerCase().replace(' ', '-')}" id="current-status-span">${os.status}</span></p>
                <p><strong>Data de Agendamento:</strong> ${agendamentoFormatted}</p>
            </div>

            <div class="status-buttons" style="display: ${os.status.toLowerCase() === 'concluida' ? 'none' : 'flex'};">
                <button class="btn btn-andamento" onclick="alterarStatus('${os.id_os}', 'em andamento')">▶️ Iniciar Atendimento</button>
                <button class="btn btn-concluida" onclick="alterarStatus('${os.id_os}', 'concluida')">✅ Finalizar Atendimento</button>
            </div>
        `;
    };



    // Função para carregar estornos
    async function carregarEstornos() {
        if (!estornoContainer) return;
        
        estornoContainer.innerHTML = `
            <h2>📦 Estorno de Materiais</h2>
            <p>Carregando OS com materiais...</p>
        `;
        
        try {
            const response = await fetch(`${BASE_URL}tecnico/os/buscarOSParaEstorno`);
            const result = await response.json();
            
            // Recupera OS já processadas do localStorage
            const osProcessadas = JSON.parse(localStorage.getItem('osEstornoProcessadas') || '[]');
            
            let estornoHtml = `<h2>📦 Estorno de Materiais</h2>`;
            
            if (!result.success || result.data.length === 0) {
                estornoHtml += '<p>📝 Nenhuma OS com materiais disponível para estorno.</p>';
            } else {
                // Filtra OS que não foram processadas
                const osPendentes = result.data.filter(os => !osProcessadas.includes(os.id_os));
                
                if (osPendentes.length === 0) {
                    estornoHtml += '<p>📝 Nenhuma OS com materiais disponível para estorno.</p>';
                } else {
                    estornoHtml += '<p>Selecione os materiais que deseja estornar:</p>';
                    
                    osPendentes.forEach(os => {
                        estornoHtml += `
                            <div class="os-estorno-item">
                                <h3>OS N#${os.id_os} - ${os.cliente_nome}</h3>
                                <div class="materiais-estorno">
                        `;
                        
                        os.produtos_usados.forEach(produto => {
                            estornoHtml += `
                                <div class="material-estorno">
                                    <span class="material-nome">${produto.nome}</span>
                                    <div class="estorno-controls">
                                        <label>Qtd a estornar:</label>
                                        <input type="number" min="0" max="${produto.qtd_usada}" value="0" 
                                               data-os="${os.id_os}" data-produto="${produto.id_prod}" 
                                               data-max="${produto.qtd_usada}" class="qtd-estorno">
                                        <span class="max-info">/ ${produto.qtd_usada}</span>
                                        <button class="btn-estornar" onclick="executarEstorno(${os.id_os}, ${produto.id_prod}, this)">Estornar</button>
                                    </div>
                                </div>
                            `;
                        });
                        
                        estornoHtml += '</div></div>';
                    });
                }
            }
            
            estornoContainer.innerHTML = estornoHtml;
            
        } catch (error) {
            console.error('Erro ao carregar OS para estorno:', error);
            estornoContainer.innerHTML = `
                <h2>📦 Estorno de Materiais</h2>
                <p>⚠️ Erro ao carregar dados. Tente novamente.</p>
            `;
        }
    }
    
    window.executarEstorno = async function(osId, produtoId, button) {
        const input = button.parentElement.querySelector('.qtd-estorno');
        const qtdEstorno = parseInt(input.value);
        const maxQtd = parseInt(input.dataset.max);
        
        if (qtdEstorno < 0 || isNaN(qtdEstorno)) {
            alert('Informe uma quantidade válida para estorno.');
            return;
        }
        
        if (qtdEstorno === 0) {
            if (!confirm('Confirma finalizar estorno sem devolver materiais?')) {
                return;
            }
            // Marca OS como processada no localStorage
            const osProcessadas = JSON.parse(localStorage.getItem('osEstornoProcessadas') || '[]');
            if (!osProcessadas.includes(osId)) {
                osProcessadas.push(osId);
                localStorage.setItem('osEstornoProcessadas', JSON.stringify(osProcessadas));
            }
            
            // Remove a OS da lista
            const osItem = button.closest('.os-estorno-item');
            osItem.remove();
            
            if (estornoContainer.querySelectorAll('.os-estorno-item').length === 0) {
                estornoContainer.innerHTML = '<h2>📦 Estorno de Materiais</h2><p>📝 Nenhuma OS com materiais disponível para estorno.</p>';
            }
            return;
        }
        
        if (qtdEstorno > maxQtd) {
            alert(`Quantidade não pode ser maior que ${maxQtd}.`);
            return;
        }
        
        if (!confirm(`Confirma o estorno de ${qtdEstorno} unidade(s)?`)) {
            return;
        }
        
        try {
            const response = await fetch(`${BASE_URL}tecnico/os/atualizar-material`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    id_os: osId, 
                    id_prod: produtoId, 
                    qtd: maxQtd - qtdEstorno 
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Estorno realizado com sucesso!');
                
                // Marca OS como processada no localStorage
                const osProcessadas = JSON.parse(localStorage.getItem('osEstornoProcessadas') || '[]');
                if (!osProcessadas.includes(osId)) {
                    osProcessadas.push(osId);
                    localStorage.setItem('osEstornoProcessadas', JSON.stringify(osProcessadas));
                }
                
                // Remove a OS inteira da lista após qualquer estorno
                const osItem = button.closest('.os-estorno-item');
                osItem.remove();
                
                // Se não sobrou nenhuma OS, mostra mensagem
                if (estornoContainer.querySelectorAll('.os-estorno-item').length === 0) {
                    estornoContainer.innerHTML = '<h2>📦 Estorno de Materiais</h2><p>📝 Nenhuma OS com materiais disponível para estorno.</p>';
                }
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (error) {
            console.error('Erro ao estornar:', error);
            alert('Erro ao conectar com o servidor.');
        }
    };



    window.voltarParaLista = function() {
        listaOSContainer.classList.remove('hide-mobile');
        detalhesOSContainer.classList.remove('show-mobile');
        document.querySelectorAll('.os-item').forEach(item => item.classList.remove('selected'));
    };

    window.alterarStatus = async function(osId, novoStatus) {
        const os = osDoDia.find(o => o.id_os == osId);
        const currentStatus = os.status.toLowerCase();

        if (novoStatus === 'concluida' && currentStatus === 'aberta') {
            alert('Por favor, marque a O.S. como "Em Andamento" antes de concluí-la.');
            return;
        }

        if (novoStatus === 'em andamento') {
            let osEmAndamento = osDoDia.find(o => o.status.toLowerCase() === 'em andamento' && o.id_os != osId);
            if (!osEmAndamento && typeof osAtrasadas !== 'undefined') {
                osEmAndamento = osAtrasadas.find(o => o.status.toLowerCase() === 'em andamento' && o.id_os != osId);
            }
            if (osEmAndamento) {
                alert(`Você já possui a OS N#${osEmAndamento.id_os} em andamento. Conclua-a antes de iniciar outra.`);
                return;
            }
        }

        try {
            const response = await fetch(`${BASE_URL}tecnico/os/alterarStatus`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id_os: osId, status: novoStatus }),
            });

            const result = await response.json();

            if (result.success) {
                if (novoStatus === 'concluida') {
                    os.conclusao_tecnico = 'concluida';
                } else {
                    os.status = novoStatus;
                }
                alert(result.message);

                if (novoStatus === 'concluida') {
                    renderListaOS();
                    clearDetails();
                    if (window.innerWidth <= 767) {
                        listaOSContainer.classList.remove('hide-mobile');
                        detalhesOSContainer.classList.remove('show-mobile');
                    }
                } else {
                    // Atualiza o status no painel de detalhes
                    document.getElementById('current-status-span').textContent = novoStatus;
                    document.getElementById('current-status-span').className = 'status status-' + novoStatus.toLowerCase().replace(' ', '-');
                    
                    // Atualiza o status na lista de OS
                    const osItemNaLista = document.querySelector(`.os-item[data-id="${osId}"] .status`);
                    if (osItemNaLista) {
                        osItemNaLista.textContent = novoStatus;
                        osItemNaLista.className = 'status status-' + novoStatus.toLowerCase().replace(' ', '-');
                    }
                }
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (error) {
            console.error('Erro ao alterar status:', error);
            alert('Erro ao conectar com o servidor.');
        }
    };

    renderListaOS();
});