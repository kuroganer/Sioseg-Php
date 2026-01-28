document.addEventListener('DOMContentLoaded', function () {
    const BASE_URL = window.BASE_URL || '/';

    // Função para mostrar notificações (toasts)
    function showToast(message, isSuccess = true) {
        const toast = document.createElement('div');
        toast.className = `toast ${isSuccess ? 'success' : 'error'}`;
        toast.innerHTML = `<i class="fas ${isSuccess ? 'fa-check-circle' : 'fa-exclamation-triangle'}"></i> ${message}`;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => document.body.removeChild(toast), 300);
        }, 4000);
    }

    // Função genérica para requisições fetch (simplificada)
    async function sendRequest(url, options) {
        try {
            const response = await fetch(url, options);
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: 'Erro desconhecido na resposta.' }));
                throw new Error(errorData.message || `Erro HTTP: ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            console.error('Falha na requisição:', error);
            showToast(error.message, false);
            return null;
        }
    }

    // 1. Lógica para alterar o status da OS (Iniciar / Concluir)
    document.querySelectorAll('.btn-change-status').forEach(button => {
        button.addEventListener('click', async function () {
            const osId = this.dataset.osId;
            const novoStatus = this.dataset.status;
            const osCard = document.getElementById(`os-card-${osId}`);

            const data = await sendRequest(`${BASE_URL}tecnico/os/alterarStatus`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_os: osId, status: novoStatus })
            });

            if (data && data.success) {
                showToast(data.message);
                // Atualiza a UI
                const statusBadge = osCard.querySelector('.status-badge');
                const actionsContainer = osCard.querySelector('.os-actions');

                if (novoStatus === 'em andamento') {
                    statusBadge.textContent = 'Em Andamento';
                    statusBadge.className = 'status-badge status-em-andamento';
                    this.textContent = 'Concluir Serviço';
                    this.dataset.status = 'concluida';
                } else if (novoStatus === 'concluida') {
                    statusBadge.textContent = 'Concluído (Técnico)';
                    statusBadge.className = 'status-badge status-concluida';
                    actionsContainer.innerHTML = '<p class="concluded-message"><i class="fas fa-check-circle"></i> Serviço concluído. Aguardando confirmação do cliente.</p>';
                    // Foca na seção de materiais para estorno
                    const materialSection = osCard.querySelector('.materiais-usados-section');
                    if (materialSection) {
                        materialSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        materialSection.style.border = '2px solid var(--accent-color)';
                        setTimeout(() => { materialSection.style.border = '1px solid #ddd'; }, 3000);
                    }
                }
            }
        });
    });

    // 2. Lógica para registrar novos produtos
    document.querySelectorAll('.btn-registrar-produtos').forEach(button => {
        button.addEventListener('click', async function () {
            const osId = this.dataset.osId;
            const form = this.closest('.add-materiais-form');
            const selects = form.querySelectorAll('select[name="produto_id"]');
            const inputs = form.querySelectorAll('input[name="quantidade"]');
            
            const produtos = [];
            selects.forEach((select, index) => {
                const quantidade = parseInt(inputs[index].value, 10);
                if (select.value && quantidade > 0) {
                    produtos.push({
                        id_prod: parseInt(select.value, 10),
                        quantidade: quantidade
                    });
                }
            });

            if (produtos.length === 0) {
                showToast('Nenhum produto selecionado para registrar.', false);
                return;
            }

            const data = await sendRequest(`${BASE_URL}tecnico/os/registrarProdutos`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_os: osId, produtos: produtos })
            });

            if (data && data.success) {
                showToast(data.message);
                // Recarrega a página para atualizar a lista de materiais usados e disponíveis
                setTimeout(() => window.location.reload(), 1500);
            }
        });
    });

    // 3. Lógica para estorno (atualizar quantidade de material usado)
    document.querySelectorAll('.input-qtd-usada').forEach(input => {
        const originalValue = input.value;

        input.addEventListener('change', async function () {
            const osId = this.dataset.osId;
            const prodId = this.dataset.prodId;
            const novaQtd = parseInt(this.value, 10);
            const qtdOriginal = parseInt(this.dataset.originalQty, 10);

            if (isNaN(novaQtd) || novaQtd < 0) {
                showToast('Quantidade inválida.', false);
                this.value = originalValue; // Restaura valor
                return;
            }

            if (novaQtd > qtdOriginal) {
                showToast('A quantidade não pode ser aumentada. Apenas estorno (diminuir).', false);
                this.value = originalValue; // Restaura valor
                return;
            }

            const data = await sendRequest(`${BASE_URL}tecnico/os/atualizar-material`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_os: osId, id_prod: prodId, qtd: novaQtd })
            });

            if (data && data.success) {
                const estorno = qtdOriginal - novaQtd;
                showToast(estorno > 0 ? `Estorno de ${estorno} unidade(s) realizado com sucesso!` : 'Quantidade atualizada.');
                // Recarrega a página para refletir a mudança no estoque dos produtos disponíveis
                setTimeout(() => window.location.reload(), 1500);
            } else {
                this.value = originalValue; // Restaura em caso de erro
            }
        });
    });

    // Lógica para adicionar mais campos de produto dinamicamente
    document.querySelectorAll('.btn-add-more-material').forEach(button => {
        button.addEventListener('click', function() {
            const container = this.closest('.add-materiais-form').querySelector('.dynamic-materials-container');
            const clone = container.querySelector('.material-entry').cloneNode(true);
            
            // Limpa os valores do clone
            clone.querySelector('select').selectedIndex = 0;
            clone.querySelector('input').value = 1;

            // Adiciona botão de remover
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn-remove-material-entry';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.onclick = () => container.removeChild(clone);
            clone.appendChild(removeBtn);

            container.appendChild(clone);
        });
    });

    // Adiciona CSS para o Toast
    const style = document.createElement('style');
    style.textContent = `
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #333;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            z-index: 1050;
            transform: translateX(120%);
            transition: transform 0.3s ease-in-out;
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .toast.show {
            transform: translateX(0);
        }
        .toast.success {
            background-color: #28a745;
        }
        .toast.error {
            background-color: #dc3545;
        }
    `;
    document.head.appendChild(style);
});