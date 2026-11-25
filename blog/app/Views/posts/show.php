<?php
// Espera $post com os dados completos da postagem encontrada.
$pageTitle = $post->titulo;
require __DIR__ . '/../layouts/header.php';
?>
<article class="reading-width">
    <a class="back-link" href="?action=index">← Voltar às postagens</a>
    <header class="article-heading">
        <?php // Formata a data do banco sem depender do fuso horário. ?>
        <time datetime="<?= htmlspecialchars($post->dataPublicacao, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(implode('/', array_reverse(explode('-', $post->dataPublicacao))), ENT_QUOTES, 'UTF-8') ?></time>
        <h1><?= htmlspecialchars($post->titulo, ENT_QUOTES, 'UTF-8') ?></h1>
    </header>
    <?php // Escapa o texto antes de converter as quebras de linha em elementos HTML. ?>
    <div class="article-content"><?= nl2br(htmlspecialchars($post->conteudo, ENT_QUOTES, 'UTF-8')) ?></div>
    <div class="article-actions"><a class="btn btn-outline-primary" href="?action=edit&amp;id=<?= (int) $post->id ?>">Editar postagem</a></div>
</article>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
