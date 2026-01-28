<?php
// ProjetoGES_MVC/app/Views/dashboard/index.php

// A variável $data virá do Controller
$userEmail = $data['userEmail'] ?? 'Usuário';
$userProfile = $data['userProfile'] ?? 'Convidado';
?>

<style>
.content {
    max-width: 800px;
    margin: 40px auto;
    padding: 30px 25px;
    background: #ffffff;
    border-radius: 15px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    font-family: 'Poppins', Arial, sans-serif;
    color: #333;
    line-height: 1.6;
    transition: transform 0.3s, box-shadow 0.3s;
}

.content:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
}

.content h1 {
    font-size: 1.8em;
    margin-bottom: 15px;
    color: #2575fc;
}

.content p {
    font-size: 1em;
    margin-bottom: 12px;
    color: #555;
}

@media (max-width: 768px) {
    .content {
        padding: 20px 15px;
        margin: 20px;
    }

    .content h1 {
        font-size: 1.5em;
    }
}
</style>

<div class="content">
    <h1>Bem-vindo ao Sistema Integrado de Ordem de Serviço e Gestão, <?= htmlspecialchars(ucfirst($userName ?? 'Usuário')); ?>!</h1>
    <p>Seu perfil de acesso é: <strong><?= htmlspecialchars(ucfirst($userProfile ?? 'Convidado')); ?></strong>.</p>
    <p>Esta é a sua página inicial. As funcionalidades disponíveis dependerão do seu perfil.</p>

    <?php if ($userProfile === 'admin'): ?>
        <p>Acesso total como Administrador.</p>
    <?php elseif ($userProfile === 'cliente'): ?>
        <p>Aqui você pode gerenciar suas Ordens de Serviço.</p>
    <?php endif; ?>
</div>
