// Autocomplete para campos de seleção
class AutocompleteSelect {
    constructor(selectElement, searchUrl, options = {}) {
        this.select = selectElement;
        this.searchUrl = searchUrl;
        this.options = {
            minLength: 2,
            placeholder: 'Digite para buscar...',
            ...options
        };
        
        this.init();
    }
    
    init() {
        // Verificar se o select original está desabilitado
        if (this.select.disabled) {
            return; // Não inicializar autocomplete se o campo estiver desabilitado
        }
        
        // Criar container
        this.container = document.createElement('div');
        this.container.className = 'autocomplete-container';
        this.container.style.position = 'relative';
        
        // Criar input de busca
        this.input = document.createElement('input');
        this.input.type = 'text';
        this.input.className = this.select.className;
        this.input.placeholder = this.options.placeholder;
        this.input.style.cssText = `
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid var(--border-color, #e1e5e9);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: var(--input-bg, #fff);
            color: var(--text-color, #333);
            box-sizing: border-box;
        `;
        
        // Criar lista de resultados
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'autocomplete-dropdown';
        this.dropdown.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--dropdown-bg, white);
            border: 2px solid var(--border-color, #e1e5e9);
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: none;
        `;
        
        // Substituir select original mantendo o ícone
        const inputGroup = this.select.parentNode;
        const icon = inputGroup.querySelector('.input-icon');
        
        this.select.style.display = 'none';
        inputGroup.insertBefore(this.container, this.select);
        
        // Manter o ícone se existir
        if (icon) {
            this.container.appendChild(icon.cloneNode(true));
        }
        
        this.container.appendChild(this.input);
        this.container.appendChild(this.dropdown);
        
        // Eventos
        this.input.addEventListener('input', this.handleInput.bind(this));
        this.input.addEventListener('focus', this.handleFocus.bind(this));
        this.input.addEventListener('blur', this.handleBlur.bind(this));
        
        // Definir valor inicial se select tem valor
        if (this.select.value) {
            const selectedOption = this.select.querySelector(`option[value="${this.select.value}"]`);
            if (selectedOption) {
                this.input.value = selectedOption.textContent;
            }
        }
    }
    
    async handleInput(e) {
        const term = e.target.value.trim();
        
        if (term.length < this.options.minLength) {
            this.hideDropdown();
            return;
        }
        
        try {
            const response = await fetch(`${this.searchUrl}?term=${encodeURIComponent(term)}`);
            const results = await response.json();
            this.showResults(results);
        } catch (error) {
            console.error('Erro na busca:', error);
        }
    }
    
    handleFocus() {
        if (this.input.value.length >= this.options.minLength) {
            this.handleInput({ target: this.input });
        }
    }
    
    handleBlur() {
        // Delay para permitir clique nos resultados
        setTimeout(() => {
            this.hideDropdown();
        }, 200);
    }
    
    showResults(results) {
        this.dropdown.innerHTML = '';
        
        if (results.length === 0) {
            const item = document.createElement('div');
            item.className = 'autocomplete-item';
            item.style.cssText = 'padding: 12px 15px; color: var(--text-muted, #666); font-style: italic; font-size: 14px;';
            item.textContent = 'Nenhum resultado encontrado';
            this.dropdown.appendChild(item);
        } else {
            results.forEach(result => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item';
                item.style.cssText = `
                    padding: 12px 15px;
                    cursor: pointer;
                    border-bottom: 1px solid var(--border-light, #f0f0f0);
                    font-size: 14px;
                    color: var(--text-color, #333);
                    transition: background-color 0.2s;
                `;
                item.textContent = result.text;
                item.dataset.value = result.id;
                
                if (result.estoque !== undefined) {
                    item.dataset.estoque = result.estoque;
                }
                
                item.addEventListener('mouseenter', () => {
                    item.style.backgroundColor = 'var(--hover-bg, #f5f5f5)';
                });
                
                item.addEventListener('mouseleave', () => {
                    item.style.backgroundColor = '';
                });
                
                item.addEventListener('click', () => {
                    this.selectItem(result);
                });
                
                this.dropdown.appendChild(item);
            });
        }
        
        this.dropdown.style.display = 'block';
    }
    
    selectItem(item) {
        this.input.value = item.text;
        this.select.value = item.id;
        
        // Disparar evento change no select original
        const event = new Event('change', { bubbles: true });
        this.select.dispatchEvent(event);
        
        // Para produtos, atualizar data-estoque
        if (item.estoque !== undefined) {
            this.select.querySelector(`option[value="${item.id}"]`)?.setAttribute('data-estoque', item.estoque);
        }
        
        this.hideDropdown();
    }
    
    hideDropdown() {
        this.dropdown.style.display = 'none';
    }
}

// Inicializar autocomplete quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    // Determinar URL base baseado no perfil do usuário
    const userProfile = window.userProfile || 'admin';
    const searchBaseUrl = userProfile === 'funcionario' ? 
        BASE_URL + 'funcionario/search/' : 
        BASE_URL + 'admin/search/';
    
    // Autocomplete para clientes
    const clienteSelects = document.querySelectorAll('select[name="id_cli_fk"]');
    clienteSelects.forEach(select => {
        new AutocompleteSelect(select, searchBaseUrl + 'clientes');
    });
    
    // Autocomplete para produtos
    const produtoSelects = document.querySelectorAll('select[name*="[id_prod]"]');
    produtoSelects.forEach(select => {
        new AutocompleteSelect(select, searchBaseUrl + 'produtos');
    });
});

// Função para inicializar autocomplete em novos elementos (para campos dinâmicos)
function initAutocomplete(element) {
    const userProfile = window.userProfile || 'admin';
    const searchBaseUrl = userProfile === 'funcionario' ? 
        BASE_URL + 'funcionario/search/' : 
        BASE_URL + 'admin/search/';
        
    if (element.name === 'id_cli_fk') {
        new AutocompleteSelect(element, searchBaseUrl + 'clientes');
    } else if (element.name && element.name.includes('[id_prod]')) {
        new AutocompleteSelect(element, searchBaseUrl + 'produtos');
    }
}