/**
 * Sistema de Diagnóstico - ES5 Compatible
 */

window.SiosegDiagnostics = {
    errors: [],

    runDiagnostics: function() {
        console.group('🔍 SIOSeG - Diagnóstico de Assets');
        
        this.checkEnvironment();
        this.checkAssets();
        this.checkScripts();
        this.checkStyles();
        
        console.groupEnd();
    },

    checkEnvironment: function() {
        console.group('🌍 Ambiente');
        console.log('URL atual:', window.location.href);
        console.log('BASE_URL:', window.BASE_URL || 'NÃO DEFINIDA');
        console.log('Perfil do usuário:', window.userProfile || 'NÃO DEFINIDO');
        console.log('User Agent:', navigator.userAgent);
        console.log('Protocolo:', window.location.protocol);
        console.groupEnd();
    },

    checkAssets: function() {
        console.group('📦 Assets');
        
        if (window.AssetLoader) {
            console.log('✅ AssetLoader carregado');
            console.log('Scripts carregados:', window.AssetLoader.loadedScripts);
        } else {
            console.error('❌ AssetLoader não encontrado');
        }

        if (window.AssetConfig) {
            console.log('✅ AssetConfig carregado');
        } else {
            console.error('❌ AssetConfig não encontrado');
        }
        
        console.groupEnd();
    },

    checkScripts: function() {
        console.group('📜 Scripts JavaScript');
        
        var scripts = document.querySelectorAll('script[src]');
        var loadedScripts = [];
        
        for (var i = 0; i < scripts.length; i++) {
            if (scripts[i].src) {
                loadedScripts.push(scripts[i].src);
            }
        }
        
        console.log('Scripts no DOM:', loadedScripts.length);
        for (var i = 0; i < loadedScripts.length; i++) {
            console.log('📄', loadedScripts[i]);
        }
        
        // Scripts que sempre esperamos
        var importantScripts = [
            'theme-switcher.js',
            'asset-loader.js'
        ];

        // Scripts que são esperados apenas em páginas específicas
        var currentPath = window.location.pathname || '';
        if (currentPath.indexOf('/dashboard') !== -1) {
            importantScripts.push('admin-dashboard.js');
        }
        if (currentPath.indexOf('/calendario') !== -1 || currentPath.indexOf('/os/calendario') !== -1) {
            importantScripts.push('calendario-os.js');
        }

        for (var i = 0; i < importantScripts.length; i++) {
            var scriptName = importantScripts[i];
            var found = false;
            for (var j = 0; j < loadedScripts.length; j++) {
                if (loadedScripts[j].indexOf(scriptName) !== -1) {
                    found = true;
                    break;
                }
            }
            if (found) {
                console.log('✅ ' + scriptName + ' encontrado');
            } else {
                console.warn('⚠️ ' + scriptName + ' não encontrado');
            }
        }
        
        console.groupEnd();
    },

    checkStyles: function() {
        console.group('🎨 Estilos CSS');
        
        var links = document.querySelectorAll('link[rel="stylesheet"]');
        console.log('Arquivos CSS carregados:', links.length);
        
        for (var i = 0; i < links.length; i++) {
            console.log('🎨', links[i].href);
        }
        
        console.groupEnd();
    },

    monitorErrors: function() {
        var self = this;
        
        window.addEventListener('error', function(e) {
            if (e.target.tagName === 'SCRIPT') {
                console.error('❌ Erro ao carregar script:', e.target.src);
                self.errors.push('Script error: ' + e.target.src);
            } else if (e.target.tagName === 'LINK') {
                console.error('❌ Erro ao carregar CSS:', e.target.href);
                self.errors.push('CSS error: ' + e.target.href);
            }
        });

        window.addEventListener('error', function(e) {
            console.error('❌ Erro JavaScript:', e.message, 'em', e.filename, 'linha', e.lineno);
            self.errors.push('JS error: ' + e.message);
        });
    },

    generateReport: function() {
        var scripts = document.querySelectorAll('script[src]');
        var styles = document.querySelectorAll('link[rel="stylesheet"]');
        
        var scriptList = [];
        for (var i = 0; i < scripts.length; i++) {
            scriptList.push(scripts[i].src);
        }
        
        var styleList = [];
        for (var i = 0; i < styles.length; i++) {
            styleList.push(styles[i].href);
        }
        
        var report = {
            timestamp: new Date().toISOString(),
            url: window.location.href,
            baseUrl: window.BASE_URL,
            userProfile: window.userProfile,
            userAgent: navigator.userAgent,
            online: navigator.onLine,
            scripts: scriptList,
            styles: styleList,
            loadedAssets: window.AssetLoader ? window.AssetLoader.loadedScripts : [],
            errors: this.errors
        };
        
        console.log('📋 Relatório de Diagnóstico:', report);
        return report;
    },

    init: function() {
        this.monitorErrors();
        
        var self = this;
        if (document.readyState === 'complete') {
            setTimeout(function() { self.runDiagnostics(); }, 1000);
        } else {
            window.addEventListener('load', function() {
                setTimeout(function() { self.runDiagnostics(); }, 1000);
            });
        }
    }
};

// Função global
window.diagnosticar = function() {
    window.SiosegDiagnostics.runDiagnostics();
    return window.SiosegDiagnostics.generateReport();
};

// Auto-inicializa em localhost
if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    window.SiosegDiagnostics.init();
}