<?php
// Reutiliza o formulário de criação com os dados da postagem recebida.
$pageTitle = 'Editar postagem';
$formAction = '?action=update&id=' . (int) $post->id;
$submitLabel = 'Salvar alterações';
require __DIR__ . '/../layouts/header.php';
?>
<div class="reading-width">
    <a class="back-link" href="?action=index">← Voltar às postagens</a>
    <div class="page-heading"><p class="eyebrow">Sua publicação</p><h1>Editar postagem</h1><p class="lead-text">Atualize as informações da postagem.</p></div>
    <?php require __DIR__ . '/form.php'; ?>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
