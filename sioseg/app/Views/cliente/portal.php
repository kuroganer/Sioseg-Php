<?php
use App\Core\Session;

// Adiciona um estilo para o destaque do formulário de avaliação
echo '<style>.highlight { border: 2px solid var(--accent-color); border-radius: 8px; box-shadow: 0 0 15px rgba(var(--accent-color-rgb), 0.5); transition: all 0.3s ease-in-out; }</style>';

extract($dados);
$base_url = defined('BASE_URL') ? BASE_URL : '/';
?>

<!-- CSS específico do portal do cliente -->
<link rel="stylesheet" href="<?= $base_url ?>assets/css/cliente-portal.css">

<div class="portal-container">
    <h1 class="page-title"><i class="fas fa-user-circle"></i> Portal do Cliente</h1>

    <?php if (Session::tem('sucesso')): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= Session::obter('sucesso'); ?>
        </div>
        <?php Session::remover('sucesso'); ?>
    <?php endif; ?>

    <?php if (Session::tem('erro')): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?= Session::obter('erro'); ?>
        </div>
        <?php Session::remover('erro'); ?>
    <?php endif; ?>

    <div class="tabs">
        <button class="tab-button active" data-tab="servicos" onclick="showTab('servicos', this)">
            <i class="fas fa-tools"></i> Serviços Ativos
        </button>
        <button class="tab-button" data-tab="historico" onclick="showTab('historico', this)">
            <i class="fas fa-history"></i> Histórico
        </button>
        <button class="tab-button" data-tab="avaliacoes" onclick="showTab('avaliacoes', this)">
            <i class="fas fa-star"></i> Avaliações
        </button>
    </div>

    <!-- Serviços Ativos -->
    <div id="servicos" class="tab-content active">
        
        <?php if (empty($osAtivas)): ?>
            <div class="service-card">
                <p><i class="fas fa-info-circle"></i> Nenhum serviço ativo no momento.</p>
            </div>
        <?php else: ?>
            <?php foreach ($osAtivas as $os): ?>
                <div class="service-card">
                    <h3><i class="fas fa-wrench"></i> OS #<?= $os->id_os; ?> - <?= htmlspecialchars($os->servico_prestado); ?></h3>
                    <p><i class="fas fa-user-tie"></i> <strong>Técnico:</strong> <?= htmlspecialchars($os->nome_tec); ?></p>
                    <p><i class="fas fa-calendar-alt"></i> <strong>Data de Agendamento:</strong> <?= date('d/m/Y H:i', strtotime($os->data_agendamento)); ?></p>
                    <p><strong>Status:</strong> 
                        <span class="status status-<?= strtolower(str_replace([' ', 'Í'], ['-', 'i'], $os->status)); ?>">
                            <i class="fas fa-<?= $os->status === 'aberta' ? 'clock' : 'cog fa-spin'; ?>"></i>
                            <?= ucwords($os->status); ?>
                        </span>
                    </p>
                    
                    <div class="progresso-bar">
                        <div class="progresso" style="width: <?= $os->status === 'aberta' ? '25%' : '75%'; ?>"></div>
                    </div>
                    <p style="font-size: 0.9em; color: var(--text-muted); margin: 5px 0 15px 0; text-align: center;">
                        <?= $os->status === 'aberta' ? 'Aguardando início' : 'Em execução'; ?>
                    </p>
                    
                    <?php if ($os->conclusao_tecnico === 'concluida' && $os->conclusao_cliente === 'pendente'): ?>
                        <form id="form-confirm-<?= $os->id_os; ?>" data-os-id="<?= $os->id_os; ?>" method="POST" action="<?= $base_url; ?>cliente/os/confirmarConclusao/<?= $os->id_os; ?>" style="display: inline;">
                            <input type="hidden" name="os_id" value="<?= $os->id_os; ?>">
                            <button type="submit" class="confirm-button">
                                <i class="fas fa-check-circle"></i> Confirmar Conclusão
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Histórico -->
    <div id="historico" class="tab-content">
        <?php if (empty($historico)): ?>
            <div class="service-card">
                <p><i class="fas fa-info-circle"></i> Nenhum histórico de serviços.</p>
            </div>
        <?php else: ?>
            <?php foreach ($historico as $os): ?>
                <div class="service-card">
                    <h3><i class="fas fa-check-circle"></i> OS #<?= $os->id_os; ?> - <?= htmlspecialchars($os->servico_prestado); ?></h3>
                    <p><i class="fas fa-user-tie"></i> <strong>Técnico:</strong> <?= htmlspecialchars($os->nome_tec); ?></p>
                    <p><i class="fas fa-calendar-alt"></i> <strong>Data de Agendamento:</strong> <?= date('d/m/Y H:i', strtotime($os->data_agendamento)); ?></p>
                    <?php if ($os->data_encerramento): ?>
                        <p><i class="fas fa-calendar-check"></i> <strong>Data de Encerramento:</strong> <?= date('d/m/Y H:i', strtotime($os->data_encerramento)); ?></p>
                    <?php endif; ?>
                    <p><strong>Status:</strong> 
                        <span class="status status-encerrada">
                            <i class="fas fa-flag-checkered"></i>
                            <?= ucwords($os->status); ?>
                        </span>
                    </p>
                    
                    <div class="progresso-bar">
                        <div class="progresso" style="width: 100%"></div>
                    </div>
                    <p style="font-size: 0.9em; color: var(--text-muted); margin: 5px 0 15px 0; text-align: center;">
                        Serviço concluído
                    </p>
                    <?php if (in_array($os->status, ['concluida', 'encerrada'])): ?>
                        <?php 
                        $jaAvaliado = false;
                        foreach ($avaliacoes as $avaliacao) {
                            if ($avaliacao->id_os_fk == $os->id_os) {
                                $jaAvaliado = true;
                                break;
                            }
                        }
                        ?>
                        
                        <?php if (!$jaAvaliado): ?>
                            <div id="avaliacao-form-<?= $os->id_os; ?>" class="avaliacao-form">
                                <h3><i class="fas fa-star"></i> Avalie este Serviço</h3>
                                <form method="POST" action="<?= $base_url; ?>cliente/portal/salvarAvaliacao">
                                    <input type="hidden" name="os_id" value="<?= $os->id_os; ?>">
                                    
                                    <div class="estrelas" data-rating="0">
                                        <span data-value="1">★</span>
                                        <span data-value="2">★</span>
                                        <span data-value="3">★</span>
                                        <span data-value="4">★</span>
                                        <span data-value="5">★</span>
                                    </div>
                                    <input type="hidden" name="nota" value="0">
                                    
                                    <textarea name="comentario" placeholder="Deixe seu comentário sobre o serviço..." maxlength="5000"></textarea>
                                    
                                    <button type="submit">
                                        <i class="fas fa-paper-plane"></i> Enviar Avaliação
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Avaliações -->
    <div id="avaliacoes" class="tab-content">
        <?php if (empty($avaliacoes)): ?>
            <div class="service-card">
                <p><i class="fas fa-info-circle"></i> Nenhuma avaliação realizada.</p>
            </div>
        <?php else: ?>
            <?php foreach ($avaliacoes as $avaliacao): ?>
                <div class="service-card">
                    <h3><i class="fas fa-star"></i> Avaliação - OS #<?= $avaliacao->id_os_fk; ?></h3>
                    <p><i class="fas fa-cogs"></i> <strong>Serviço:</strong> <?= htmlspecialchars($avaliacao->servico_prestado); ?></p>
                    <p><strong>Nota:</strong> 
                        <span class="estrelas">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="<?= $i <= $avaliacao->nota ? 'preenchida' : ''; ?>">★</span>
                            <?php endfor; ?>
                        </span>
                    </p>                    
                    <?php if ($avaliacao->comentario): ?>
                        <div class="avaliacao-feita">
                            <p><i class="fas fa-comment"></i> <strong>Comentário:</strong></p>
                            <div class="comentario-texto">
                                <?= nl2br(htmlspecialchars($avaliacao->comentario)); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript específico do portal do cliente -->
<script src="<?= $base_url ?>assets/js/cliente-portal.js"></script>

<?php
if (Session::tem('os_confirmada_id')) {
    $osId = Session::obter('os_confirmada_id');
    echo "<script>window.osConfirmadaId = " . json_encode($osId) . ";</script>";
    Session::remover('os_confirmada_id'); // Limpa a sessão para não perguntar novamente
}
?>
<!-- Ajuste dinâmico do padding-bottom e scroll seguro para evitar footer cobrindo conteúdo -->
<script>
(function(){
    var container = document.querySelector('main') || document.querySelector('.portal-container');
    if (!container) return;

    function getFooterHeight(footer) {
        var r = footer.getBoundingClientRect();
        return Math.ceil(r.height) || footer.offsetHeight || 0;
    }

    function initFooterSpacing(footer) {
        if (!footer) return;

        function updateFooterSpace() {
            var h = getFooterHeight(footer);
            document.documentElement.style.setProperty('--footer-height', h + 'px');
            container.style.paddingBottom = h + 'px';
            document.body.style.paddingBottom = h + 'px';
            // Compatibilidade com scroll nativo
            try { document.documentElement.style.scrollPaddingBottom = h + 'px'; } catch(e) {}
        }

        // Observadores
        if (window.ResizeObserver) {
            try {
                var ro = new ResizeObserver(updateFooterSpace);
                ro.observe(footer);
            } catch (e) {
                // fallback se RO falhar
                var mo = new MutationObserver(updateFooterSpace);
                mo.observe(footer, { childList: true, subtree: true, attributes: true });
            }
        } else {
            var mo2 = new MutationObserver(updateFooterSpace);
            mo2.observe(footer, { childList: true, subtree: true, attributes: true });
        }

        window.addEventListener('load', updateFooterSpace);
        window.addEventListener('resize', updateFooterSpace);
        window.addEventListener('orientationchange', updateFooterSpace);

        // chamada inicial
        setTimeout(updateFooterSpace, 120);
    }

    // procura footer com retries (caso o layout insira depois)
    function waitForFooter(retries) {
        var footer = document.querySelector('.footer') || document.querySelector('footer') || document.querySelector('.site-footer');
        if (footer) {
            initFooterSpacing(footer);
            return;
        }
        if (retries > 0) {
            setTimeout(function(){ waitForFooter(retries - 1); }, 100);
        } else {
            // última tentativa ao carregar completamente
            window.addEventListener('load', function(){
                var f = document.querySelector('.footer') || document.querySelector('footer') || document.querySelector('.site-footer');
                if (f) initFooterSpacing(f);
            });
        }
    }

    waitForFooter(50); // tenta por ~5s (50 * 100ms)
})();
</script>