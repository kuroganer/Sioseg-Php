# Organização Completa dos Assets - Sistema SIOSEG

## Estrutura Geral de Arquivos CSS

### Arquivos CSS Globais:
- **`globals.css`** - Reset CSS e estilos globais para todo o sistema
- **`layout.css`** - Layout principal e estrutura base
- **`navigation.css`** - Estilos de navegação e menus
- **`forms.css`** - Estilos para formulários
- **`tables.css`** - Estilos para tabelas
- **`alerts.css`** - Estilos para alertas e mensagens
- **`search_container.css`** - Estilos para containers de busca

### Arquivos CSS Específicos por Módulo:
- **`stylelogin.css`** - Estilos da página de login
- **`login-alerts.css`** - Alertas específicos do login
- **`admin-dashboard.css`** - Dashboard do administrador
- **`cliente-portal.css`** - Portal do cliente
- **`tecnico.css`** - Painel principal do técnico
- **`tecnico-layout.css`** - Layout específico do técnico

## Estrutura de Arquivos JavaScript

### Arquivos JS Globais:
- **`theme-switcher.js`** - Alternador de tema claro/escuro (usado em todo o sistema)

### Arquivos JS Específicos por Módulo:
- **`admin-dashboard.js`** - Funcionalidades do dashboard admin
- **`admin-dashboard-config.js`** - Configurações do dashboard admin
- **`admin-os-list.js`** - Lista de OS do administrador
- **`cliente-portal.js`** - Portal do cliente (abas e avaliações)
- **`cliente_register_form.js`** - Formulário de cadastro de cliente
- **`tecnico-config.js`** - Configurações do painel do técnico
- **`tecnico.js`** - Funcionalidades principais do técnico

## Carregamento Condicional

### CSS (header.php):
```php
// Carrega CSS específicos baseado na URL e perfil do usuário
$currentUri = $_SERVER['REQUEST_URI'];

// Dashboard admin
if (strpos($currentUri, '/dashboard') !== false && ($userProfile === 'admin' || $userProfile === 'funcionario')) {
    echo '<link rel="stylesheet" href="' . $base_url . 'assets/css/admin-dashboard.css">';
}

// Portal do cliente
if (strpos($currentUri, '/cliente/portal') !== false) {
    echo '<link rel="stylesheet" href="' . $base_url . 'assets/css/cliente-portal.css">';
}

// Login
if (strpos($currentUri, '/login') !== false) {
    echo '<link rel="stylesheet" href="' . $base_url . 'assets/css/login-alerts.css">';
}
```

### JavaScript (footer.php):
```php
// Portal do cliente
if (strpos($currentUri, '/cliente/portal') !== false) {
    echo '<script src="' . $base_url . 'assets/js/cliente-portal.js"></script>';
}

// Lista de OS do admin
if (strpos($currentUri, '/admin/os') !== false) {
    echo '<script src="' . $base_url . 'assets/js/admin-os-list.js"></script>';
}
```

## Funcionalidades por Módulo

### Portal do Cliente
- **CSS**: `cliente-portal.css` - Estilos para abas, cards de serviço, avaliações
- **JS**: `cliente-portal.js` - Sistema de abas e avaliação por estrelas

### Dashboard Admin
- **CSS**: `admin-dashboard.css` - Layout do dashboard, gráficos, kanban
- **JS**: `admin-dashboard.js` + `admin-dashboard-config.js` - Gráficos Chart.js e configurações

### Painel do Técnico
- **CSS**: `tecnico.css` + `tecnico-layout.css` - Interface completa do técnico
- **JS**: `tecnico.js` + `tecnico-config.js` - Gerenciamento de OS e materiais

### Login
- **CSS**: `stylelogin.css` + `login-alerts.css` - Página de login e alertas
- **JS**: Nenhum específico (usa apenas theme-switcher global)

## Ordem de Carregamento

### CSS (sempre nesta ordem):
1. Fontes externas (Google Fonts, Font Awesome)
2. `globals.css` (reset e base)
3. Arquivos CSS globais (layout, navigation, forms, etc.)
4. CSS específicos condicionais

### JavaScript:
1. Configurações específicas (se houver)
2. `theme-switcher.js` (global)
3. Scripts inline de inicialização (se necessário)
4. Funcionalidades específicas
5. Bibliotecas externas (Chart.js, etc.)

## Benefícios da Organização

✅ **CSS e JS separados** - Sem código inline nas views
✅ **Carregamento condicional** - Apenas recursos necessários são carregados
✅ **Manutenibilidade** - Código organizado e fácil de encontrar
✅ **Performance** - Arquivos menores e específicos
✅ **Reutilização** - Componentes globais compartilhados
✅ **Escalabilidade** - Fácil adição de novos módulos

## Manutenção

- **Novos módulos**: Criar arquivos CSS/JS específicos e adicionar carregamento condicional
- **Estilos globais**: Modificar arquivos na pasta base (globals, layout, etc.)
- **Funcionalidades específicas**: Adicionar nos arquivos do módulo correspondente
- **Tema**: Modificações apenas em `theme-switcher.js`