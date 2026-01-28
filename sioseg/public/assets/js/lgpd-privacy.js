/**
 * LGPD Privacy Policy JavaScript
 * Gerencia a exibição e interação com a política de privacidade
 */

function openPrivacyPolicy() {
    const modal = document.getElementById('privacyModal');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    return false;
}

function closePrivacyPolicy() {
    const modal = document.getElementById('privacyModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    return false;
}

// Inicialização quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    // Fechar modal clicando fora dele
    window.onclick = function(event) {
        const modal = document.getElementById('privacyModal');
        if (event.target === modal) {
            closePrivacyPolicy();
        }
    };

    // Fechar modal com tecla ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closePrivacyPolicy();
        }
    });
});