// Script para abrir modal de avaliação ao clicar no botão
(function(){
    // view-evaluation.js loaded
    function $(sel, ctx){ return (ctx || document).querySelector(sel); }
    function $all(sel, ctx){ return Array.from((ctx || document).querySelectorAll(sel)); }

    function openModal(osId, nota, comentario){
        const modal = $('#evaluation-modal');
        if(!modal) return;

        $('#evaluation-os-id').textContent = osId;
        $('#evaluation-note').textContent = nota !== null ? String(nota) : '-';
        // Use innerHTML para preservar quebras de linha, mas escape HTML para segurança
        const commentElement = $('#evaluation-comment');
        if (comentario && comentario.trim() !== '') {
            commentElement.innerHTML = comentario.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
        } else {
            commentElement.textContent = '-';
        }
    // Use flex here so the CSS rule '#evaluation-modal.modal { display: flex }' continues to work
    // and the .modal-content can be centered via align-items / justify-content.
    modal.classList.add('show');
    }

    function closeModal(){
        const modal = $('#evaluation-modal');
        if(!modal) return;
        modal.classList.remove('show');
    }

    document.addEventListener('click', function(e){
        // Accept new class `.btn-view-evaluation` and fallback to old `.view-evaluation-btn` for compatibility
        const btn = e.target.closest && (e.target.closest('.btn-view-evaluation') || e.target.closest('.view-evaluation-btn'));
        if(!btn) return;
        e.preventDefault();
        const osId = btn.getAttribute('data-os-id');
        if(!osId) return;

        // Detect role from URL to choose controller path (admin or funcionario)
        const path = window.location.pathname || '/';
        let base = '';
        if (path.indexOf('/admin/') !== -1) { base = 'admin'; }
        else if (path.indexOf('/funcionario/') !== -1) { base = 'funcionario'; }
        else if (path.indexOf('/funcionario') !== -1) { base = 'funcionario'; }
        else if (path.indexOf('/admin') !== -1) { base = 'admin'; }
        else { base = 'admin'; }

        // Build a robust base path in case the app is hosted in a subfolder (ex: /siosegCopia)
        // We split the pathname by the base segment to keep the project prefix (if present)
        let prefix = '';
        try {
            const parts = path.split('/' + base + '/');
            if (parts.length > 0) prefix = parts[0];
        } catch (e) {
            prefix = '';
        }
        // Ensure prefix starts with '/' but does not end with '/'
        if (prefix === '') prefix = '';

    const url = window.location.origin + prefix + '/' + base + '/os/getEvaluation?id=' + encodeURIComponent(osId);
    // requesting evaluation via AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onload = function(){
            if(xhr.status === 200){
                try{
                    const data = JSON.parse(xhr.responseText);
                    openModal(osId, data.nota ?? '-', data.comentario ?? '');
                }catch(err){
                    openModal(osId, '-', 'Erro ao processar resposta.');
                }
            }else if(xhr.status === 404){
                openModal(osId, '-', 'Nenhuma avaliação encontrada para esta OS.');
            }else{
                openModal(osId, '-', 'Erro ao carregar avaliação. Código: ' + xhr.status);
            }
        };
        xhr.onerror = function(){
            openModal(osId, '-', 'Erro de rede ao carregar avaliação.');
        };
        xhr.send();
    }, false);

    // Fecha modal ao clicar no X ou fora do conteúdo
    document.addEventListener('click', function(e){
        if(e.target.id === 'evaluation-close') return closeModal();
        const modal = document.getElementById('evaluation-modal');
        if(!modal) return;
        if(e.target === modal) closeModal();
    });

    // Fecha com ESC
    document.addEventListener('keydown', function(e){
        if(e.key === 'Escape'){
            closeModal();
        }
    });

})();
