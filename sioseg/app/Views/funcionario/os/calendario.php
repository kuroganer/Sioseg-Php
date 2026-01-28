<?php
$rootUrl = defined('SUBDIRECTORY') ? rtrim(SUBDIRECTORY, '/') : '';
$today = date('Y-m-d');
?>

<div class="calendar-container">
    <header class="calendar-header">
        <div class="nav-section">
            <a href="<?= $rootUrl ?>/funcionario/os/calendario?year=<?= $nav['prev']['year'] ?>&month=<?= $nav['prev']['month'] ?>" class="nav-arrow" title="Mês anterior">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h1 class="calendar-title"><?= htmlspecialchars($monthName) ?> de <?= htmlspecialchars($year) ?></h1>
            <a href="<?= $rootUrl ?>/funcionario/os/calendario?year=<?= $nav['next']['year'] ?>&month=<?= $nav['next']['month'] ?>" class="nav-arrow" title="Próximo mês">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>
        <div class="calendar-actions">
            <button id="today-btn" class="action-btn" title="Ir para hoje">
                <i class="fas fa-calendar-day"></i> Hoje
            </button>
            <div class="filter-section">
                <label class="filter-label">Filtrar:</label>
                <div class="status-filters">
                    <button class="filter-btn active" data-status="all" data-role="filter">Todas</button>
                    <button class="filter-btn" data-status="aberta" data-role="filter">Abertas</button>
                    <button class="filter-btn" data-status="em andamento" data-role="filter">Em Andamento</button>
                    <button class="filter-btn" data-status="concluida" data-role="filter">Concluídas</button>
                    <button class="filter-btn" data-status="encerrada" data-role="filter">Encerradas</button>
                </div>
            </div>
        </div>
    </header>

    <div class="calendar-grid">
        <?php
        $daysOfWeek = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
        foreach ($daysOfWeek as $dayName) {
            echo "<div class='day-name'>{$dayName}</div>";
        }

        // Células vazias no início
        for ($i = 0; $i < $startDayOfWeek; $i++) {
            echo '<div class="day-cell empty"></div>';
        }

        // Dias do mês
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $stats = $osStats[$day] ?? [];
            $abertas = $stats['aberta'] ?? 0;
            $em_andamento = $stats['em andamento'] ?? 0;
            $concluidas = $stats['concluida'] ?? 0;
            $encerradas = $stats['encerrada'] ?? 0;
            $totalOS = $abertas + $em_andamento + $concluidas + $encerradas;
            $fullDate = sprintf('%d-%02d-%02d', $year, $month, $day);
            $isToday = $fullDate === $today;
            $hasOS = $totalOS > 0;
            
            $cellClasses = ['day-cell'];
            if ($isToday) $cellClasses[] = 'today';
            if ($hasOS) $cellClasses[] = 'has-os';
            
            echo "<div class='" . implode(' ', $cellClasses) . "' data-date='{$fullDate}' data-total-os='{$totalOS}' data-abertas='{$abertas}' data-andamento='{$em_andamento}' data-concluidas='{$concluidas}' data-encerradas='{$encerradas}'>";
            echo "<div class='day-number'>{$day}</div>";
            
            if ($isToday) {
                echo "<div class='today-indicator'></div>";
            }

            if ($totalOS > 0) {
                echo "<div class='os-stats'>";
                echo "<div class='total-count'>{$totalOS} OS</div>";
                if ($abertas > 0) echo "<span class='status-badge open' data-status='aberta'>{$abertas}</span>";
                if ($em_andamento > 0) echo "<span class='status-badge in-progress' data-status='em andamento'>{$em_andamento}</span>";
                if ($concluidas > 0) echo "<span class='status-badge completed' data-status='concluida'>{$concluidas}</span>";
                if ($encerradas > 0) echo "<span class='status-badge closed' data-status='encerrada'>{$encerradas}</span>";
                echo "</div>";
            }

            echo "</div>";
        }
        ?>
    </div>

    <div id="os-details-container" class="details-container" style="display: none;">
        <div class="details-header">
            <h3 id="details-title"></h3>
            <button id="close-details" class="close-button" title="Fechar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="details-content" class="details-content">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i> Carregando...
            </div>
        </div>
    </div>
    
    <div class="calendar-legend">
        <h4>Legenda:</h4>
        <div class="legend-items">
            <div class="legend-item">
                <span class="status-badge open">N</span>
                <span>Abertas</span>
            </div>
            <div class="legend-item">
                <span class="status-badge in-progress">N</span>
                <span>Em Andamento</span>
            </div>
            <div class="legend-item">
                <span class="status-badge completed">N</span>
                <span>Concluídas</span>
            </div>
            <div class="legend-item">
                <span class="status-badge closed">N</span>
                <span>Encerradas</span>
            </div>
            <div class="legend-item">
                <span class="today-sample"></span>
                <span>Hoje</span>
            </div>
        </div>
    </div>
</div>