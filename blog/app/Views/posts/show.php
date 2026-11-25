<?php
// Espera $post (Post) e $csrfToken.
$pageTitle = $post->titulo;
require __DIR__ . '/../layouts/header.php';
?>
<article class="reading-width">
    <a class="back-link" href="?action=index">← Voltar às postagens</a>
    <header class="article-heading">
        <time datetime="<?= htmlspecialchars($post->dataPublicacao, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(date('d/m/Y', strtotime($post->dataPublicacao)), ENT_QUOTES, 'UTF-8') ?></time>
        <h1><?= htmlspecialchars($post->titulo, ENT_QUOTES, 'UTF-8') ?></h1>
    </header>
    <!-- Exibe o conteúdo como texto e mantém as quebras de linha. -->
    <div class="article-content"><?= nl2br(htmlspecialchars($post->conteudo, ENT_QUOTES, 'UTF-8')) ?></div>
    <div class="article-actions">
        <a class="btn btn-outline-primary" href="?action=edit&amp;id=<?= (int) $post->id ?>">Editar postagem</a>
        <?php if (isset($csrfToken)): ?>
            <form action="?action=delete&amp;id=<?= (int) $post->id ?>" method="post" data-confirm-delete data-post-title="<?= htmlspecialchars($post->titulo, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <button class="btn btn-outline-danger" type="submit">Excluir postagem</button>
            </form>
        <?php endif; ?>
    </div>
</article>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
