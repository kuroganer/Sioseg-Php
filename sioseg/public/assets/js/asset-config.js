/**
 * Configuração de Assets - SIOSeG (ES5 Compatible)
 */

window.AssetConfig = {
    pageScripts: {
        '/dashboard': {
            admin: ['assets/js/admin-dashboard.js'],
            funcionario: ['assets/js/admin-dashboard.js']
        },
        '/cliente/portal': ['assets/js/cliente-portal.js'],
        '/admin/os': {
            list: ['assets/js/admin-os-list.js'],
            edit: ['assets/js/os-edit.js']
        },
        '/funcionario/os': {
            edit: ['assets/js/os-edit.js'],
            calendario: ['assets/js/calendario-os.js']
        },
        '/tecnico/os': ['assets/js/tecnico.js']
    },

    globalScripts: [
        'assets/js/theme-switcher.js',
        'assets/js/duplicate-validation.js'
    ],

    loadPageScripts: function() {
        var currentPath = window.location.pathname;
        var userProfile = window.userProfile || 'guest';
        
        // Carrega scripts globais
        for (var i = 0; i < this.globalScripts.length; i++) {
            if (window.AssetLoader) {
                window.AssetLoader.loadScript(this.globalScripts[i]);
            }
        }

        // Carrega scripts específicos
        for (var pagePath in this.pageScripts) {
            if (currentPath.indexOf(pagePath) !== -1) {
                this.loadScriptsForPage(pagePath, this.pageScripts[pagePath], userProfile);
                break;
            }
        }
    },

    loadScriptsForPage: function(pagePath, scripts, userProfile) {
        if (Array.isArray && Array.isArray(scripts)) {
            for (var i = 0; i < scripts.length; i++) {
                if (window.AssetLoader) {
                    window.AssetLoader.loadScript(scripts[i]);
                }
            }
        } else if (typeof scripts === 'object') {
            if (scripts[userProfile]) {
                for (var i = 0; i < scripts[userProfile].length; i++) {
                    if (window.AssetLoader) {
                        window.AssetLoader.loadScript(scripts[userProfile][i]);
                    }
                }
            }
            
            if (pagePath.indexOf('/os') !== -1) {
                var currentPath = window.location.pathname;
                
                if (currentPath.indexOf('/edit') !== -1 && scripts.edit) {
                    for (var i = 0; i < scripts.edit.length; i++) {
                        if (window.AssetLoader) {
                            window.AssetLoader.loadScript(scripts.edit[i]);
                        }
                    }
                } else if (currentPath.indexOf('/calendario') !== -1 && scripts.calendario) {
                    for (var i = 0; i < scripts.calendario.length; i++) {
                        if (window.AssetLoader) {
                            window.AssetLoader.loadScript(scripts.calendario[i]);
                        }
                    }
                } else if (scripts.list) {
                    for (var i = 0; i < scripts.list.length; i++) {
                        if (window.AssetLoader) {
                            window.AssetLoader.loadScript(scripts.list[i]);
                        }
                    }
                }
            }
        }
    },

    init: function() {
        var self = this;
        document.addEventListener('DOMContentLoaded', function() {
            self.loadPageScripts();
        });
    }
};

// Auto-inicializa
window.AssetConfig.init();