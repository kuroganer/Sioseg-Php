<?php
define('APP_ROOT', dirname(__DIR__));

// --- ATIVAR ERROS ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', APP_ROOT . '/app_error.log');

date_default_timezone_set('America/Sao_Paulo');

// require_once __DIR__ . '/../vendor/autoload.php'; // REMOVA OU COMENTE ESTA LINHA

require_once __DIR__ . '/../app/Config/autoloader.php'; // ADICIONE ESTA LINHA

require_once APP_ROOT . '/app/Core/Router.php';

clearstatcache();

// --- SUBDIRETÓRIO E BASE URL ---
define('SUBDIRECTORY', '/sioseg/public'); // Subdiretório correto
define('BASE_URL', 'http://localhost' . SUBDIRECTORY . '/');

\App\Core\Session::inicializar();

$router = new App\Core\Router();

// --- ROTAS DE AUTENTICAÇÃO ---
$router->addRoute('GET',  '/login', 'AuthController@showLoginForm');
$router->addRoute('POST', '/login', 'AuthController@login');
$router->addRoute('GET',  '/logout', 'AuthController@logout');
$router->addRoute('GET',  '/forgot-password', 'AuthController@showForgotPasswordForm');
$router->addRoute('POST', '/forgot-password', 'AuthController@processForgotPassword');
$router->addRoute('GET',  '/reset-password', 'AuthController@showResetPasswordForm');
$router->addRoute('POST', '/reset-password', 'AuthController@processResetPassword');
$router->addRoute('POST', '/verify-user-data', 'AuthController@verifyUserData');

// --- DASHBOARD ---
$router->addRoute('GET', '/', 'AuthController@showLoginForm'); // Redireciona a raiz diretamente para o login
$router->addRoute('GET', '/dashboard', 'DashboardController@index'); // O dashboard continua acessível em /dashboard
$router->addRoute('GET', '/admin/dashboard/os-pendentes', 'DashboardController@osPendentes'); // Endpoint para OS pendentes
$router->addRoute('GET', '/funcionario/dashboard/os-pendentes', 'DashboardController@osPendentes'); // Endpoint para OS pendentes funcionário

// --- ROTAS ADMIN (USUÁRIOS, TÉCNICOS, PRODUTOS, CLIENTES, OS) ---
$router->addRoute('GET',  '/admin/users',              'Admin\UserController@index');
$router->addRoute('GET',  '/admin/users/register',     'Admin\UserController@mostrarFormularioCadastro');
$router->addRoute('POST', '/admin/users/create',       'Admin\UserController@processarCadastro');
$router->addRoute('GET',  '/admin/users/edit/{id}',    'Admin\UserController@showEditForm');
$router->addRoute('POST', '/admin/users/update/{id}', 'Admin\UserController@atualizar');
$router->addRoute('POST', '/admin/users/changeStatus','Admin\UserController@alterarStatus');
$router->addRoute('GET',  '/admin/users/search',      'Admin\UserController@pesquisar');

$router->addRoute('GET',  '/admin/tecnicos',              'Admin\TecnicoController@index');
$router->addRoute('GET',  '/admin/tecnicos/register',     'Admin\TecnicoController@showRegisterForm');
$router->addRoute('POST', '/admin/tecnicos/create',       'Admin\TecnicoController@processRegister');
$router->addRoute('GET',  '/admin/tecnicos/edit/{id}',    'Admin\TecnicoController@showEditForm');
$router->addRoute('POST', '/admin/tecnicos/update/{id}',  'Admin\TecnicoController@atualizar');
$router->addRoute('POST', '/admin/tecnicos/changeStatus', 'Admin\TecnicoController@alterarStatus');
$router->addRoute('GET',  '/admin/tecnicos/search',       'Admin\TecnicoController@pesquisar');

$router->addRoute('GET',  '/admin/produtos',              'Admin\ProdutoController@index');
$router->addRoute('GET',  '/admin/produtos/register',     'Admin\ProdutoController@mostrarFormularioCadastro');
$router->addRoute('POST', '/admin/produtos/create',       'Admin\ProdutoController@processarCadastro');
$router->addRoute('GET',  '/admin/produtos/edit/{id}',    'Admin\ProdutoController@showEditForm');
$router->addRoute('POST', '/admin/produtos/update/{id}',  'Admin\ProdutoController@atualizar');
$router->addRoute('POST', '/admin/produtos/changeStatus', 'Admin\ProdutoController@alterarStatus');
$router->addRoute('GET',  '/admin/produtos/search',       'Admin\ProdutoController@pesquisar');

$router->addRoute('GET',  '/admin/clientes',              'Admin\ClienteController@index');
$router->addRoute('GET',  '/admin/clientes/register',     'Admin\ClienteController@showRegisterForm');
$router->addRoute('POST', '/admin/clientes/create', 'Admin\ClienteController@processRegister');
$router->addRoute('GET',  '/admin/clientes/edit/{id}',    'Admin\ClienteController@showEditForm');
$router->addRoute('POST', '/admin/clientes/update/{id}',  'Admin\ClienteController@atualizar');
$router->addRoute('POST', '/admin/clientes/changeStatus', 'Admin\ClienteController@alterarStatus');
$router->addRoute('GET',  '/admin/clientes/search',       'Admin\ClienteController@pesquisar');
$router->addRoute('GET',  '/admin/clientes/buscar-cpf-cnpj', 'Admin\ClienteController@buscarPorCpfCnpj');
$router->addRoute('POST', '/admin/clientes/verificar-email', 'Admin\ClienteController@verificarEmail');
$router->addRoute('POST', '/admin/clientes/verificar-cpf', 'Admin\ClienteController@verificarCpf');
$router->addRoute('POST', '/admin/clientes/verificar-cnpj', 'Admin\ClienteController@verificarCnpj');
$router->addRoute('POST', '/admin/tecnicos/verificar-email', 'Admin\TecnicoController@verificarEmail');
$router->addRoute('POST', '/admin/tecnicos/verificar-cpf', 'Admin\TecnicoController@verificarCpf');
$router->addRoute('POST', '/admin/users/verificar-email', 'Admin\UserController@verificarEmail');
$router->addRoute('POST', '/admin/users/verificar-cpf', 'Admin\UserController@verificarCpf');

// --- ROTAS DE BUSCA AJAX ---
// Prefer Portuguese canonical actions; English-named methods remain as aliases in controllers
$router->addRoute('GET',  '/admin/search/clientes',       'Admin\SearchController@pesquisarClientes');
$router->addRoute('GET',  '/admin/search/produtos',       'Admin\SearchController@pesquisarProdutos');

$router->addRoute('GET',  '/admin/os',                'Admin\OrdemServicoController@index');
$router->addRoute('GET',  '/admin/os/register',       'Admin\OrdemServicoController@showRegisterForm');
$router->addRoute('POST', '/admin/os/create',         'Admin\OrdemServicoController@processRegister');
$router->addRoute('GET',  '/admin/os/edit/{id}',      'Admin\OrdemServicoController@showEditForm');
$router->addRoute('POST', '/admin/os/update/{id}', 'Admin\OrdemServicoController@atualizar');
$router->addRoute('POST', '/admin/os/changeStatus',   'Admin\OrdemServicoController@alterarStatus');
$router->addRoute('GET',  '/admin/os/search',         'Admin\OrdemServicoController@pesquisar');
$router->addRoute('GET',  '/admin/os/calendario',     'Admin\OrdemServicoController@calendario'); // Rota corrigida para a view do calendário
$router->addRoute('GET',  '/admin/os/details',        'Admin\OrdemServicoController@detalhes'); // Rota para detalhes via AJAX
// Rota para buscar avaliação (nota + comentário) via AJAX
$router->addRoute('GET',  '/admin/os/getEvaluation',  'Admin\OrdemServicoController@obterAvaliacao');

// --- ROTAS DE GERENCIAMENTO DE MATERIAIS ---
$router->addRoute('GET',  '/admin/os/add-material/{id}',        'Admin\OrdemServicoController@showAddMaterialForm');
$router->addRoute('POST', '/admin/os/add-material/{id}',        'Admin\OrdemServicoController@processAddMaterial');
$router->addRoute('POST', '/admin/os/remove-material/{id_os}/{id_prod}', 'Admin\OrdemServicoController@removerMaterial');
$router->addRoute('POST', '/admin/os/update-material/{id_os}/{id_prod}', 'Admin\OrdemServicoController@updateMaterialQuantity');
$router->addRoute('POST', '/admin/os/close/{id}',               'Admin\OrdemServicoController@closeOS');
$router->addRoute('POST', '/admin/os/cancel/{id}',              'Admin\OrdemServicoController@cancelOS');
$router->addRoute('GET',  '/admin/os/with-materials',           'Admin\OrdemServicoController@listWithMaterials');
$router->addRoute('GET',  '/admin/os/available-products',       'Admin\OrdemServicoController@getAvailableProducts');
$router->addRoute('GET',  '/admin/os/materials/{id}',           'Admin\OrdemServicoController@getMaterialsByOS');
$router->addRoute('GET',  '/admin/os/material-report',          'Admin\OrdemServicoController@materialReport');

// --- ROTAS TÉCNICO (PAINEL UNIFICADO) ---
$router->addRoute('GET',  '/tecnico/os',                  'Tecnico\OrdemServicoController@index');
$router->addRoute('POST', '/tecnico/os/alterarStatus',    'Tecnico\OrdemServicoController@alterarStatus');
$router->addRoute('POST', '/tecnico/os/registrarProdutos','Tecnico\OrdemServicoController@registrarProdutos');
$router->addRoute('GET',  '/tecnico/os/historico',        'Tecnico\OrdemServicoController@historico');
$router->addRoute('POST', '/tecnico/os/atualizar-material', 'Tecnico\OrdemServicoController@atualizarMaterial'); // Nova rota
$router->addRoute('GET',  '/tecnico/os/buscarOSParaEstorno', 'Tecnico\OrdemServicoController@buscarOSParaEstorno'); // Rota para estorno
// --- ROTAS funcionario
$router->addRoute('GET',  '/funcionario/clientes',              'Funcionario\ClienteController@index');
$router->addRoute('GET',  '/funcionario/clientes/register',     'Funcionario\ClienteController@showRegisterForm');
$router->addRoute('POST', '/funcionario/clientes/create',       'Funcionario\ClienteController@processRegister');
$router->addRoute('GET',  '/funcionario/clientes/edit/{id}',    'Funcionario\ClienteController@showEditForm');
$router->addRoute('POST', '/funcionario/clientes/update/{id}',  'Funcionario\ClienteController@atualizar');
$router->addRoute('POST', '/funcionario/clientes/changeStatus', 'Funcionario\ClienteController@alterarStatus');
$router->addRoute('GET',  '/funcionario/clientes/search',       'Funcionario\ClienteController@pesquisar');
$router->addRoute('GET',  '/funcionario/clientes/buscar-cpf-cnpj', 'Funcionario\ClienteController@buscarPorCpfCnpj');
$router->addRoute('POST', '/funcionario/clientes/verificar-email', 'Funcionario\ClienteController@verificarEmail');
$router->addRoute('POST', '/funcionario/clientes/verificar-cpf', 'Funcionario\ClienteController@verificarCpf');
$router->addRoute('POST', '/funcionario/clientes/verificar-cnpj', 'Funcionario\ClienteController@verificarCnpj');

$router->addRoute('GET',  '/funcionario/search/clientes',       'Funcionario\SearchController@buscarClientes');
$router->addRoute('GET',  '/funcionario/search/produtos',       'Funcionario\SearchController@buscarProdutos');

$router->addRoute('GET',  '/funcionario/os',                'Funcionario\OrdemServicoController@index');
$router->addRoute('GET',  '/funcionario/os/register',       'Funcionario\OrdemServicoController@showRegisterForm');
$router->addRoute('POST', '/funcionario/os/create',         'Funcionario\OrdemServicoController@processRegister');
$router->addRoute('GET',  '/funcionario/os/edit/{id}',      'Funcionario\OrdemServicoController@showEditForm');
$router->addRoute('POST', '/funcionario/os/update/{id}',    'Funcionario\OrdemServicoController@atualizar');
$router->addRoute('POST', '/funcionario/os/changeStatus',   'Funcionario\OrdemServicoController@alterarStatus');
$router->addRoute('GET',  '/funcionario/os/search',         'Funcionario\OrdemServicoController@pesquisar');
$router->addRoute('POST', '/funcionario/os/remove-material/{id_os}/{id_prod}', 'Funcionario\OrdemServicoController@removerMaterial');
$router->addRoute('GET',  '/funcionario/os/calendario',     'Funcionario\OrdemServicoController@calendario'); 
$router->addRoute('GET',  '/funcionario/os/details',        'Funcionario\OrdemServicoController@detalhes'); // Rota para detalhes via AJAX
$router->addRoute('GET',  '/funcionario/os/getEvaluation',  'Funcionario\OrdemServicoController@obterAvaliacao');
// --- ROTAS RELATÓRIOS ---
$router->addRoute('GET',  '/admin/relatorios',                    'Admin\RelatorioController@index');
$router->addRoute('GET',  '/admin/relatorios/resumo-geral',       'Admin\RelatorioController@resumoGeral');
$router->addRoute('GET',  '/admin/relatorios/performance-tecnicos', 'Admin\RelatorioController@performanceTecnicos');
$router->addRoute('GET',  '/admin/relatorios/buscar-os',          'Admin\RelatorioController@buscarOS');
$router->addRoute('GET',  '/admin/relatorios/os/{id}',            'Admin\RelatorioController@relatorioOS');
$router->addRoute('GET',  '/admin/relatorios/pdf/os/{id}',        'Admin\RelatorioController@pdfOS');
$router->addRoute('GET',  '/admin/relatorios/pdf/resumo-geral',   'Admin\RelatorioController@pdfResumoGeral');
$router->addRoute('GET',  '/admin/relatorios/excel/os/{id}',      'Admin\RelatorioController@excelOS');
$router->addRoute('GET',  '/admin/relatorios/excel/resumo-geral', 'Admin\RelatorioController@excelResumoGeral');
$router->addRoute('GET',  '/admin/relatorios/xml/os/{id}',        'Admin\RelatorioController@xmlOS');
$router->addRoute('GET',  '/admin/relatorios/xml/resumo-geral',   'Admin\RelatorioController@xmlResumoGeral');
$router->addRoute('GET',  '/admin/relatorios/produtos',           'Admin\RelatorioController@relatorioProdutos');
$router->addRoute('GET',  '/admin/relatorios/pdfProdutos',         'Admin\RelatorioController@pdfProdutos');
$router->addRoute('GET',  '/admin/relatorios/excelProdutos',       'Admin\RelatorioController@excelProdutos');
$router->addRoute('GET',  '/admin/relatorios/xmlProdutos',         'Admin\RelatorioController@xmlProdutos');

// --- ROTAS CLIENTE ---
$router->addRoute('GET',  '/cliente/portal', 'Cliente\PortalClienteController@index');
$router->addRoute('POST', '/cliente/portal/confirmarConclusao', 'Cliente\PortalClienteController@confirmarConclusao');
$router->addRoute('POST', '/cliente/portal/salvarAvaliacao', 'Cliente\PortalClienteController@salvarAvaliacao');
$router->addRoute('GET',  '/cliente/os',                'Cliente\OrdemServicoClienteController@index');
$router->addRoute('GET',  '/cliente/os/historico',      'Cliente\OrdemServicoClienteController@historico');
$router->addRoute('POST', '/cliente/os/confirmarConclusao/{id}', 'Cliente\OrdemServicoClienteController@confirmarConclusao');
$router->addRoute('GET',  '/cliente/avaliacoes/register/{id_os}', 'Cliente\AvaliacaoTecnicaController@showRegisterForm');
$router->addRoute('POST', '/cliente/avaliacoes/create/{id_os}',   'Cliente\AvaliacaoTecnicaController@processRegister');

// --- ROTAS API PARA ALERTAS ---
$router->addRoute('GET', '/api/os/delayed', 'ApiController@getDelayedOS');
$router->addRoute('GET', '/api/os/delayed-details', 'ApiController@getDelayedOSDetails');
$router->addRoute('GET', '/api/os/status-changes', 'ApiController@getStatusChanges');
$router->addRoute('GET', '/api/os/current-status', 'ApiController@getCurrentStatus');
$router->addRoute('GET', '/api/os/recent-changes', 'ApiController@getRecentChanges');
$router->addRoute('GET', '/api/os/details/{id}', 'ApiController@getOSDetails');
$router->addRoute('GET', '/api/test', 'ApiController@test');

// --- DISPATCH ---
$router->dispatch();
