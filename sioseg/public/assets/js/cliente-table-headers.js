document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const clientRows = document.querySelectorAll('.client-row');
    const tableHeaders = document.querySelector('thead tr');
    
    // Headers originais
    const originalHeaders = [
        'ID', 'Nome/Razão Social', 'Email', 'Telefone 1', 'Tipo Pessoa', 
        'CPF/CNPJ', 'RG/Inscrição', 'Data Nasc./Fundação', 'Data Cadastro',
        'Endereço', 'Cidade', 'CEP', 'Status', 'Ações'
    ];
    
    // Headers para pessoa física
    const fisicaHeaders = [
        'ID', 'Nome', 'Email', 'Telefone 1', 'Tipo Pessoa', 
        'CPF', 'RG', 'Data Nascimento', 'Data Cadastro',
        'Endereço', 'Cidade', 'CEP', 'Status', 'Ações'
    ];
    
    // Headers para pessoa jurídica
    const juridicaHeaders = [
        'ID', 'Razão Social', 'Email', 'Telefone 1', 'Tipo Pessoa', 
        'CNPJ', 'Data Cadastro', 'Endereço', 'Cidade', 'CEP', 'Status', 'Ações'
    ];
    
    function updateTable(filterType) {
        if (!tableHeaders) return;
        
        const ths = tableHeaders.querySelectorAll('th');
        const allCells = document.querySelectorAll('tbody td');
        
        if (filterType === 'fisica') {
            // Mostra todas as colunas
            ths.forEach((th, index) => {
                th.style.display = '';
                th.textContent = fisicaHeaders[index] || th.textContent;
            });
            clientRows.forEach(row => {
                const cells = row.querySelectorAll('td');
                cells.forEach(cell => cell.style.display = '');
            });
        } else if (filterType === 'juridica') {
            // Esconde colunas RG (índice 6) e Data Nascimento (índice 7)
            ths.forEach((th, index) => {
                if (index === 6 || index === 7) {
                    th.style.display = 'none';
                } else {
                    th.style.display = '';
                    const headerIndex = index > 7 ? index - 2 : index;
                    th.textContent = juridicaHeaders[headerIndex] || th.textContent;
                }
            });
            clientRows.forEach(row => {
                const cells = row.querySelectorAll('td');
                cells.forEach((cell, index) => {
                    if (index === 6 || index === 7) {
                        cell.style.display = 'none';
                    } else {
                        cell.style.display = '';
                    }
                });
            });
        } else {
            // Mostra todas as colunas com headers originais
            ths.forEach((th, index) => {
                th.style.display = '';
                th.textContent = originalHeaders[index] || th.textContent;
            });
            clientRows.forEach(row => {
                const cells = row.querySelectorAll('td');
                cells.forEach(cell => cell.style.display = '');
            });
        }
    }
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active de todos os botões
            filterBtns.forEach(b => b.classList.remove('active'));
            // Adiciona active no botão clicado
            this.classList.add('active');
            
            const filterType = this.dataset.type;
            
            // Atualiza tabela
            updateTable(filterType);
            
            // Filtra linhas
            clientRows.forEach(row => {
                if (filterType === 'all' || row.dataset.type === filterType) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
});