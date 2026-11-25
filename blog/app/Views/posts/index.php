<?php
// Espera $posts como uma lista de objetos Post.
$pageTitle = 'Postagens';
require __DIR__ . '/../layouts/header.php';
?>
<section class="page-heading">
    <p class="eyebrow">Ideias e histórias</p>
    <h1>Postagens</h1>
    <p class="lead-text">Explore as publicações e compartilhe suas ideias.</p>
</section>
<?php // Apresenta um convite à criação quando o banco não possui postagens. ?>
<?php if ($posts === []): ?>
    <div class="empty-state">
        <h2>Ainda não há postagens</h2>
        <p>Comece compartilhando sua primeira ideia.</p>
        <a class="btn btn-primary" href="?action=create">Criar postagem</a>
    </div>
<?php else: ?>
    <div class="post-grid">
        <?php foreach ($posts as $post): ?>
            <?php // Limita o trecho sem cortar caracteres acentuados. ?>
            <?php $excerpt = mb_strlen($post->conteudo, 'UTF-8') > 160 ? mb_substr($post->conteudo, 0, 160, 'UTF-8') . '…' : $post->conteudo; ?>
            <article class="post-card">
                <?php // A data do banco já está no formato ISO, sem conversão de fuso horário. ?>
                <time datetime="<?= htmlspecialchars($post->dataPublicacao, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(implode('/', array_reverse(explode('-', $post->dataPublicacao))), ENT_QUOTES, 'UTF-8') ?></time>
                <?php // O conteúdo cadastrado é exibido como texto, sem interpretar HTML. ?>
                <h2><a href="?action=show&amp;id=<?= (int) $post->id ?>"><?= htmlspecialchars($post->titulo, ENT_QUOTES, 'UTF-8') ?></a></h2>
                <p><?= htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8') ?></p>
                <div class="card-actions">
                    <a href="?action=edit&amp;id=<?= (int) $post->id ?>">Editar</a>
                    <?php $deleteButtonClass = 'button-link danger-link'; require __DIR__ . '/delete-form.php'; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
