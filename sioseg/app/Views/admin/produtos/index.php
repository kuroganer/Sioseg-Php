<div class="list-container">
    <h2>Lista de Produtos</h2>

    <!-- Mensagens de sucesso ou erro -->
    <?php if (!empty($sucesso)): ?>
        <div class="success-message"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>

    <?php if (!empty($erro)): ?>
        <div class="error-message"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <!-- Tabela de produtos -->
    <?php if (!empty($produtos)): ?>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Descrição</th>
                    <th>Quantidade</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                    <?php 
                        $qtde = (int)($produto->qtde ?? 0);
                        $classRow = ($produto->status ?? '') === 'inativo' ? 'status-inativo' : '';
                        if ($qtde <= 20 && $qtde > 0) {
                            $classRow .= ' estoque-baixo';
                        } elseif ($qtde === 0) {
                            $classRow .= ' estoque-zero';
                        }
                    ?>
                    <tr<?= !empty($classRow) ? ' class="' . trim($classRow) . '"' : '' ?>>
                        <td><?= htmlspecialchars($produto->nome ?? '-') ?></td>
                        <td><?= htmlspecialchars($produto->marca ?? '-') ?></td>
                        <td><?= htmlspecialchars($produto->modelo ?? '-') ?></td>
                        <td><?= htmlspecialchars($produto->descricao ?? '-') ?></td>
                        <td>
                            <?php if ($qtde <= 20 && $qtde > 0): ?>
                                <span class="estoque-baixo-badge" title="Estoque baixo - Reabastecer em breve">
                                    ⚠️ <?= htmlspecialchars($qtde) ?>
                                </span>
                            <?php elseif ($qtde === 0): ?>
                                <span class="estoque-zero-badge" title="Produto sem estoque">
                                    ❌ <?= htmlspecialchars($qtde) ?>
                                </span>
                            <?php else: ?>
                                <?= htmlspecialchars($qtde) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= ($produto->status ?? '') === 'ativo' ? 'Ativo' : 'Inativo' ?></td>
                        <td class="action-cell">
                            <div class="actions-wrapper">
                                <!-- Link para edição -->
                                <a href="<?= BASE_URL ?>admin/produtos/edit/<?= $produto->id_prod ?>" class="action-button edit-button">Editar</a>

                                <!-- Formulário para alterar status -->
                                <form action="<?= BASE_URL ?>admin/produtos/changeStatus" method="post" class="status-form">
                                    <input type="hidden" name="id" value="<?= $produto->id_prod ?>">
                                    <input type="hidden" name="status" value="<?= $produto->status === 'ativo' ? 'inativo' : 'ativo' ?>">
                                    <button type="submit" class="action-button"><?= $produto->status === 'ativo' ? 'Desativar' : 'Ativar' ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nenhum produto encontrado.</p>
    <?php endif; ?>
    
    <?php if (isset($pagination)): ?>
        <?= $pagination->renderPaginationControls() ?>
    <?php endif; ?>
</div>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/pagination.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/estoque-alerts.css">

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Alerta automático para produtos com estoque baixo ao carregar a página
    const produtosBaixoEstoque = document.querySelectorAll('.estoque-baixo');
    const produtosSemEstoque = document.querySelectorAll('.estoque-zero');
    
    if (produtosBaixoEstoque.length > 0 || produtosSemEstoque.length > 0) {
        let mensagem = '⚠️ ALERTA DE ESTOQUE\n\n';
        
        if (produtosSemEstoque.length > 0) {
            mensagem += '❌ ' + produtosSemEstoque.length + ' produto(s) SEM ESTOQUE\n';
        }
        
        if (produtosBaixoEstoque.length > 0) {
            mensagem += '⚠️ ' + produtosBaixoEstoque.length + ' produto(s) com ESTOQUE BAIXO (≤20 unidades)\n';
        }
        
        mensagem += '\nVerifique os produtos destacados e considere reabastecer.';
        
        // Mostra alerta após um pequeno delay para não interferir no carregamento
        setTimeout(function() {
            alert(mensagem);
        }, 500);
    }
});
</script>
