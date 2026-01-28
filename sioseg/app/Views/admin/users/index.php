<div class="list-container">
    <h2>Lista de Usuários</h2>

    <!-- Mensagens de sucesso ou erro -->
    <?php if (!empty($sucesso)): ?>
        <div class="success-message"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>

    <?php if (!empty($erro)): ?>
        <div class="error-message"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <!-- Tabela de usuários -->
    <?php if (!empty($users)): ?>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>RG</th>
                    <th>Órgão Emissor RG</th>
                    <th>Data Expedição RG</th>
                    <th>Data Nascimento</th>
                    <th>Telefone 1</th>
                    <th>Telefone 2</th>
                    <th>Telefone 3</th>
                    <th>Email</th>
                    <th>Perfil</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr<?= ($user->status ?? '') === 'inativo' ? ' class="status-inativo"' : '' ?>>
                        <td><?= htmlspecialchars($user->nome_usu ?? '-') ?></td>
                        <td><?= htmlspecialchars($user->cpf_usu ?? '-') ?></td>
                        <td><?= htmlspecialchars($user->rg_usu ?? '-') ?></td>
                        <td><?= htmlspecialchars($user->rg_emissor_usu ?? '-') ?></td>
                        <td><?= !empty($user->data_expedicao_rg_usu) ? htmlspecialchars(date('d/m/Y', strtotime($user->data_expedicao_rg_usu))) : '-' ?></td>
                        <td><?= !empty($user->data_nascimento_usu) ? htmlspecialchars(date('d/m/Y', strtotime($user->data_nascimento_usu))) : '-' ?></td>
                        <td><?= htmlspecialchars($user->tel1_usu ?? '-') ?></td>
                        <td><?= htmlspecialchars($user->tel2_usu ?? '-') ?></td>
                        <td><?= htmlspecialchars($user->tel3_usu ?? '-') ?></td>
                        <td><?= htmlspecialchars($user->email_usu ?? '-') ?></td>
                        <td><?= htmlspecialchars($user->perfil ?? '-') ?></td>
                        <td><?= ($user->status ?? '') === 'ativo' ? 'Ativo' : 'Inativo' ?></td>
                        <td class="action-cell">
                            <div class="actions-wrapper">
                                <!-- Link para edição -->
                                <a href="<?= BASE_URL ?>admin/users/edit/<?= $user->id_usu ?>" class="action-button edit-button">Editar</a>

                                <!-- Formulário para alterar status -->
                                <form action="<?= BASE_URL ?>admin/users/changeStatus" method="post" class="status-form">
                                    <input type="hidden" name="id" value="<?= $user->id_usu ?>">
                                    <input type="hidden" name="status" value="<?= $user->status === 'ativo' ? 'inativo' : 'ativo' ?>">
                                    <button type="submit" class="action-button"><?= $user->status === 'ativo' ? 'Desativar' : 'Ativar' ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nenhum usuário encontrado.</p>
    <?php endif; ?>
</div>
