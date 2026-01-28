/* JavaScript específico para a lista de OS do admin */

document.addEventListener('DOMContentLoaded', function() {
    
    // Gerenciamento de formulários de status
    document.querySelectorAll('.status-form').forEach(function(form) {
        var buttons = form.querySelectorAll('.status-button');
        var inputHidden = document.createElement('input');
        inputHidden.type = 'hidden';
        inputHidden.name = 'status';
        var activeButton = form.querySelector('.status-button.active');
        inputHidden.value = activeButton ? activeButton.dataset.value : 'aberta';
        form.appendChild(inputHidden);

        buttons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                // Remove active de todos
                buttons.forEach(function(b) { b.classList.remove('active'); });
                // Adiciona active no clicado
                btn.classList.add('active');
                // Atualiza o input hidden
                inputHidden.value = btn.dataset.value;
            });
        });
    });
});