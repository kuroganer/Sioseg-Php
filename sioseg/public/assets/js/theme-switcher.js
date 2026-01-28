document.addEventListener('DOMContentLoaded', function () {
    // --- LÓGICA DO TEMA (Executa sempre) ---
    const themeToggle = document.getElementById('theme-toggle');

    const setTheme = (theme) => {
        const html = document.documentElement;
        html.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        if (themeToggle) {
            themeToggle.checked = (theme === 'dark');
        }
        // Define a variável CSS para a cor de foco dos inputs, que depende do tema
        html.style.setProperty('--accent-color-trans', theme === 'dark' ? 'rgba(96, 165, 250, 0.3)' : 'rgba(59, 130, 246, 0.3)');
        
        // Força atualização de elementos com estilos inline nos relatórios
        updateReportsTheme(theme);
   };

    const updateReportsTheme = (theme) => {
        // Atualiza elementos com cores hardcoded que podem ter sido perdidos
        const elementsToUpdate = document.querySelectorAll('.relatorios-container [style*="color:"]');
        elementsToUpdate.forEach(element => {
            const style = element.getAttribute('style');
            if (style && (style.includes('#6c757d') || style.includes('#495057') || style.includes('#343a40'))) {
                element.classList.add('text-muted');
            }
        });
        
        // Força re-render de tabelas nos relatórios
        const tables = document.querySelectorAll('.relatorios-container table');
        tables.forEach(table => {
            table.style.color = 'var(--color-text-primary)';
        });
    };

    const currentTheme = localStorage.getItem('theme');
    if (currentTheme) {
        setTheme(currentTheme);
    } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        setTheme('dark');
    } else {
        setTheme('light');
    }

    if (themeToggle) {
        themeToggle.addEventListener('change', function() {
            setTheme(this.checked ? 'dark' : 'light');
        });
    }
    
    // Aplica correções iniciais nos relatórios se estiver na página de relatórios
    if (document.querySelector('.relatorios-container')) {
        updateReportsTheme(currentTheme || 'light');
    }
});