/**
 * Asset Loader - ES5 Compatible
 */

function AssetLoader() {
    this.baseUrl = this.getBaseUrl();
    this.loadedScripts = [];
    this.cacheBreaker = new Date().getTime();
    this.init();
}

AssetLoader.prototype.getBaseUrl = function() {
    if (typeof window.BASE_URL !== 'undefined') {
        return window.BASE_URL;
    }

    var path = window.location.pathname;
    var segments = path.split('/');
    
    for (var i = 0; i < segments.length; i++) {
        if (segments[i] === 'sioseg') {
            var basePath = segments.slice(0, i + 2).join('/');
            return window.location.origin + basePath + '/';
        }
    }

    return window.location.origin + '/sioseg/public/';
};

AssetLoader.prototype.init = function() {
    if (typeof window.BASE_URL === 'undefined') {
        window.BASE_URL = this.baseUrl;
    }
};

AssetLoader.prototype.loadScript = function(src, callback) {
    // Evita carregar o mesmo script duas vezes
    for (var i = 0; i < this.loadedScripts.length; i++) {
        if (this.loadedScripts[i] === src) {
            if (callback) callback();
            return;
        }
    }

    var script = document.createElement('script');
    script.src = this.baseUrl + src + '?v=' + this.cacheBreaker;
    script.async = true;
    
    var self = this;
    script.onload = function() {
        self.loadedScripts.push(src);
        if (callback) callback();
    };
    
    script.onerror = function(error) {
        console.error('Erro ao carregar script:', src);
    };
    
    document.head.appendChild(script);
};

AssetLoader.prototype.isScriptLoaded = function(src) {
    for (var i = 0; i < this.loadedScripts.length; i++) {
        if (this.loadedScripts[i] === src) {
            return true;
        }
    }
    return false;
};

// Inicializa globalmente
window.AssetLoader = new AssetLoader();

// Funções helper
window.isPage = function(pagePath) {
    return window.location.pathname.indexOf(pagePath) !== -1;
};

window.hasProfile = function(profile) {
    return typeof window.userProfile !== 'undefined' && window.userProfile === profile;
};