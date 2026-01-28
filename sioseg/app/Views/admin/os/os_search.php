 <div class="searcher-container"> 
    <h2>Consultar Ordens de Serviço</h2>

    <form action="<?= BASE_URL ?>admin/os/search" method="GET">
        <div class="form-group">
            <label for="clienteName">Consultar pelo nome do cliente:</label>
            <input type="text" id="clienteName" name="nome" placeholder="Digite o nome do cliente..." value="<?= htmlspecialchars($searchTerm); ?>">
        </div>
        <button type="submit">Consultar</button>
    </form>

    <div class="results-grid">
        <?php if (!empty($osList)): ?>
            <?php foreach ($osList as $os): ?>
                <div class="result-card">
                    <div class="card-header">
                        <h3>OS #<?= htmlspecialchars($os->id_os) ?></h3>
                        <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $os->status ?? '')) ?>">
                            <?= htmlspecialchars(ucwords($os->status) ?? 'N/A') ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <p><i class="fa-solid fa-user"></i><strong>Cliente:</strong> <?= htmlspecialchars($os->nome_cli ?? '-') ?></p>
                        <p><i class="fa-solid fa-tools"></i><strong>Serviço:</strong> <?= htmlspecialchars(ucfirst($os->tipo_servico) ?? '-') ?></p>
                        <p><i class="fa-solid fa-user-tie"></i><strong>Técnico:</strong> <?= htmlspecialchars($os->nome_tec ?? '-') ?></p>
                        <p><i class="fa-solid fa-calendar-alt"></i><strong>Abertura:</strong> <?= !empty($os->data_abertura) ? htmlspecialchars(date('d/m/Y H:i', strtotime($os->data_abertura))) : '-' ?></p>
                        <p><i class="fa-solid fa-calendar-day"></i><strong>Agendamento:</strong> <?= !empty($os->data_agendamento) ? htmlspecialchars(date('d/m/Y H:i', strtotime($os->data_agendamento))) : '-' ?></p>
                        <?php if (!empty($os->data_encerramento)): ?>
                            <p><i class="fa-solid fa-calendar-check"></i><strong>Encerramento:</strong> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($os->data_encerramento))) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <a href="<?= BASE_URL ?>admin/os/edit/<?= $os->id_os ?>">Editar</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">
                <?= $searchTerm ? 'Nenhuma OS encontrada com esse cliente.' : 'Digite o nome do cliente e clique em Consultar.'; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
