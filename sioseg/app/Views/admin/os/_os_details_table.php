<?php if (!empty($os_list)): ?>
    <div class="os-details-table">
        <table>
            <thead>
                <tr>
                    <th>OS</th>
                    <th>Cliente</th>
                    <th>Técnico</th>
                    <th>Status</th>
                    <th>Serviço</th>
                    <th>Data de Agendamento</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($os_list as $os): ?>
                <tr>
                    <?php $idOs = isset($os->id_os) ? $os->id_os : ''; ?>
                    <td><strong>#<?= htmlspecialchars((string)$idOs) ?></strong></td>
                    <td>
                        <?php
                            // Lógica consistente com os formulários de cadastro/edição
                            // Preferir o nome do cliente quando disponível; usar razão social apenas como fallback
                            $nomeCliente = '';
                            $rawNomeCli = isset($os->nome_cli) ? trim((string)$os->nome_cli) : '';
                            $rawClienteNome = isset($os->cliente_nome) ? trim((string)$os->cliente_nome) : '';
                            $rawRazao = isset($os->razao_social) ? trim((string)$os->razao_social) : '';

                            if ($rawNomeCli !== '') {
                                $nomeCliente = $rawNomeCli;
                            } elseif ($rawClienteNome !== '') {
                                $nomeCliente = $rawClienteNome;
                            } elseif ($rawRazao !== '') {
                                $nomeCliente = $rawRazao;
                            }

                            echo htmlspecialchars((string)$nomeCliente);
                        ?>
                    </td>
                    <td><?= htmlspecialchars((string)(isset($os->nome_tec) ? $os->nome_tec : 'Não atribuído')) ?></td>
                    <td>
                        <?php
                            // Mapeia o status do banco para a classe CSS correta
                            $status = isset($os->status) ? $os->status : '';
                            switch($status) {
                                case 'aberta':
                                    $statusClass = 'open';
                                    break;
                                case 'em andamento':
                                    $statusClass = 'in-progress';
                                    break;
                                case 'concluida':
                                    $statusClass = 'completed';
                                    break;
                                case 'encerrada':
                                    $statusClass = 'closed';
                                    break;
                                default:
                                    $statusClass = strtolower(str_replace(' ', '-', $status));
                            }
                        ?>
                        <span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars(ucwords((string)$status)) ?></span>
                    </td>
                    <td><?= htmlspecialchars((string)(isset($os->servico_prestado) ? $os->servico_prestado : '')) ?></td>
                    <td>
                        <?php
                            // Mostrar data de agendamento formatada quando disponível (detalhes do calendário)
                            $agendamento = '-';
                            if (isset($os->data_agendamento) && trim((string)$os->data_agendamento) !== '') {
                                try {
                                    $dt = new DateTime((string)$os->data_agendamento);
                                    // Formato dia/mês/ano e hora se existir
                                    $agendamento = $dt->format('d/m/Y H:i');
                                } catch (Exception $e) {
                                    // Se parsing falhar, mostrar o valor cru, escapado
                                    $agendamento = htmlspecialchars((string)$os->data_agendamento);
                                }
                            }
                            echo htmlspecialchars((string)$agendamento);
                        ?>
                    </td>
                    <td>
                        <a href="<?= BASE_URL ?>admin/os/edit/<?= htmlspecialchars((string)$idOs) ?>" class="action-btn edit-btn" title="Editar OS">
                            <i class="fas fa-edit"></i>
                        </a>
                        <?php if ($status === 'concluida'): ?>
                            <button type="button" class="action-btn btn-view-evaluation" data-os-id="<?= htmlspecialchars((string)$idOs) ?>" title="Ver Avaliação">
                                <i class="fas fa-star"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="no-data">
        <i class="fas fa-calendar-times"></i>
        <p>Nenhuma ordem de serviço encontrada para esta data.</p>
    </div>
<?php endif; ?>
<!-- modal agora é global no footer -->
