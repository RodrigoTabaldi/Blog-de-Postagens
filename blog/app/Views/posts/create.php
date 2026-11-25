<?php
// Define os textos e o destino usados pelo formulário compartilhado.
$pageTitle = 'Nova postagem';
$formAction = '?action=store';
$submitLabel = 'Publicar postagem';
require __DIR__ . '/../layouts/header.php';
?>
<div class="reading-width">
    <a class="back-link" href="?action=index">← Voltar às postagens</a>
    <div class="page-heading"><p class="eyebrow">Compartilhe uma ideia</p><h1>Nova postagem</h1><p class="lead-text">Preencha os campos para criar uma publicação.</p></div>
    <?php require __DIR__ . '/form.php'; ?>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
