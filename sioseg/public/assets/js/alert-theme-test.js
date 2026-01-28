/**
 * Teste de compatibilidade do sistema de alertas com tema escuro
 * Este arquivo pode ser removido após os testes
 */

// Função para testar mudança de tema nos alertas
function testAlertThemeCompatibility() {
    console.log('Testando compatibilidade de tema dos alertas...');
    
    // Verifica se as variáveis CSS estão definidas
    const testElement = document.createElement('div');
    testElement.style.background = 'var(--popup-bg)';
    testElement.style.color = 'var(--popup-text)';
    document.body.appendChild(testElement);
    
    const computedStyle = getComputedStyle(testElement);
    const bgColor = computedStyle.backgroundColor;
    const textColor = computedStyle.color;
    
    console.log('Cores atuais dos popups:');
    console.log('- Background:', bgColor);
    console.log('- Text:', textColor);
    
    document.body.removeChild(testElement);
    
    // Testa mudança de tema
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    console.log('Tema atual:', currentTheme);
    
    return {
        currentTheme,
        bgColor,
        textColor,
        variablesWorking: bgColor !== 'var(--popup-bg)' && textColor !== 'var(--popup-text)'
    };
}

// Executa teste quando DOM estiver pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', testAlertThemeCompatibility);
} else {
    testAlertThemeCompatibility();
}

// Disponibiliza função globalmente para testes manuais
window.testAlertTheme = testAlertThemeCompatibility;