(function(){
    // Atualiza a variável CSS --footer-height e aplica padding-bottom inline com prioridade important
    function getFooter() {
        return document.querySelector('.footer') || document.querySelector('footer') || document.querySelector('.site-footer');
    }

    function applyFooterHeight(h) {
        try {
            document.documentElement.style.setProperty('--footer-height', h + 'px');
        } catch (e) {}
        // Atualiza a variável CSS e aplica padding-bottom inline como fallback importante
        try {
            // inline important garante espaçamento mesmo que alguma regra sobrescreva o body
            document.body.style.setProperty('padding-bottom', h + 'px', 'important');
        } catch (e) {
            document.body.style.paddingBottom = h + 'px';
        }
        

    }

    function measureAndApply(footer) {
        if (!footer) return;
        var r = footer.getBoundingClientRect();
        var h = Math.ceil(r.height) || footer.offsetHeight || 0;
        applyFooterHeight(h);
    }

    function initForFooter(footer) {
        if (!footer) return;
        measureAndApply(footer);

        // ResizeObserver para mudanças de tamanho
        if (window.ResizeObserver) {
            try {
                var ro = new ResizeObserver(function(){ measureAndApply(footer); });
                ro.observe(footer);
            } catch (e) {
                // fallback para mutation observer
                var mo = new MutationObserver(function(){ measureAndApply(footer); });
                mo.observe(footer, { childList: true, subtree: true, attributes: true });
            }
        } else {
            var mo2 = new MutationObserver(function(){ measureAndApply(footer); });
            mo2.observe(footer, { childList: true, subtree: true, attributes: true });
        }

        // Reaplica no resize/orientationchange
        window.addEventListener('resize', function(){ measureAndApply(footer); });
        window.addEventListener('orientationchange', function(){ setTimeout(function(){ measureAndApply(footer); }, 120); });

        // Garante reaplicação após load
        window.addEventListener('load', function(){ measureAndApply(footer); });
    }

    function waitForFooter(retries) {
        var footer = getFooter();
        if (footer) {
            initForFooter(footer);
            return;
        }
        if (retries > 0) {
            setTimeout(function(){ waitForFooter(retries - 1); }, 150);
        } else {
            window.addEventListener('load', function(){
                var f = getFooter();
                if (f) initForFooter(f);
            });
        }
    }

    // Start
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function(){ waitForFooter(40); });
    } else {
        waitForFooter(40);
    }
})();
