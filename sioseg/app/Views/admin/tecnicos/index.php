<div class="list-container">
    <h2>Lista de Técnicos</h2>

    <!-- Mensagens de sucesso ou erro -->
    <?php if (!empty($sucesso)): ?>
        <div class="success-message"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>

    <?php if (!empty($erro)): ?>
        <div class="error-message"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <!-- Tabela de técnicos -->
    <?php if (!empty($tecnicos)): ?>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>RG</th>
                    <th>Órgão Emissor RG</th>
                    <th>Data Expedição RG</th>
                    <th>Data Nascimento</th>
                    <th>Telefone Pessoal</th>
                    <th>Telefone Empresa</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tecnicos as $tecnico): ?>
                    <tr<?= ($tecnico->status ?? '') === 'inativo' ? ' class="status-inativo"' : '' ?>>
                        <td><?= htmlspecialchars($tecnico->nome_tec ?? '-') ?></td>
                        <td><?= htmlspecialchars($tecnico->cpf_tec ?? '-') ?></td>
                        <td><?= htmlspecialchars($tecnico->rg_tec ?? '-') ?></td>
                        <td><?= htmlspecialchars($tecnico->rg_emissor_tec ?? '-') ?></td>
                        <td><?= !empty($tecnico->data_expedicao_rg_tec) ? htmlspecialchars(date('d/m/Y', strtotime($tecnico->data_expedicao_rg_tec))) : '-' ?></td>
                        <td><?= !empty($tecnico->data_nascimento_tec) ? htmlspecialchars(date('d/m/Y', strtotime($tecnico->data_nascimento_tec))) : '-' ?></td>
                        <td><?= htmlspecialchars($tecnico->tel_pessoal ?? '-') ?></td>
                        <td><?= htmlspecialchars($tecnico->tel_empresa ?? '-') ?></td>
                        <td><?= htmlspecialchars($tecnico->email_tec ?? '-') ?></td>
                        <td><?= ($tecnico->status ?? '') === 'ativo' ? 'Ativo' : 'Inativo' ?></td>
                        <td class="action-cell">
                            <div class="actions-wrapper">
                                <!-- Link para edição -->
                                <a href="<?= BASE_URL ?>admin/tecnicos/edit/<?= $tecnico->id_tec ?>" class="action-button edit-button">Editar</a>

                                <!-- Formulário para alterar status -->
                                <form action="<?= BASE_URL ?>admin/tecnicos/changeStatus" method="post" class="status-form">
                                    <input type="hidden" name="id" value="<?= $tecnico->id_tec ?>">
                                    <input type="hidden" name="status" value="<?= $tecnico->status === 'ativo' ? 'inativo' : 'ativo' ?>">
                                    <button type="submit" class="action-button"><?= $tecnico->status === 'ativo' ? 'Desativar' : 'Ativar' ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nenhum técnico encontrado.</p>
    <?php endif; ?>
    
    <?php if (isset($pagination)): ?>
        <?= $pagination->renderPaginationControls() ?>
    <?php endif; ?>
</div>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/pagination.css">
