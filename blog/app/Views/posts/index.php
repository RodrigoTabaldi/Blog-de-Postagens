<?php
// Espera $posts (lista de Post), $searchTerm e $csrfToken.
$pageTitle = 'Postagens';
$searchTerm = $searchTerm ?? '';
require __DIR__ . '/../layouts/header.php';
?>
<section class="page-heading">
    <p class="eyebrow">Ideias e histórias</p>
    <h1>Postagens</h1>
    <p class="lead-text">Explore as publicações e compartilhe suas ideias.</p>
</section>
<form class="search-form" action="" method="get" role="search">
    <input type="hidden" name="action" value="index">
    <label class="form-label" for="q">Buscar postagens</label>
    <div class="search-controls">
        <input class="form-control" id="q" name="q" type="search" value="<?= htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8') ?>" placeholder="Pesquise por título ou conteúdo">
        <button class="btn btn-primary" type="submit">Buscar</button>
        <?php if ($searchTerm !== ''): ?><a class="btn btn-outline-secondary" href="?action=index">Limpar</a><?php endif; ?>
    </div>
</form>
<?php if ($posts === []): ?>
    <div class="empty-state">
        <h2><?= $searchTerm !== '' ? 'Nenhuma postagem encontrada' : 'Ainda não há postagens' ?></h2>
        <p><?= $searchTerm !== '' ? 'Tente pesquisar por outro termo.' : 'Comece compartilhando sua primeira ideia.' ?></p>
        <a class="btn btn-primary" href="?action=create">Criar postagem</a>
    </div>
<?php else: ?>
    <div class="post-grid">
        <?php foreach ($posts as $post): ?>
            <?php // Limita o trecho sem cortar caracteres acentuados. ?>
            <?php $excerpt = mb_strlen($post->conteudo, 'UTF-8') > 160 ? mb_substr($post->conteudo, 0, 160, 'UTF-8') . '…' : $post->conteudo; ?>
            <article class="post-card">
                <time datetime="<?= htmlspecialchars($post->dataPublicacao, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(date('d/m/Y', strtotime($post->dataPublicacao)), ENT_QUOTES, 'UTF-8') ?></time>
                <h2><a href="?action=show&amp;id=<?= (int) $post->id ?>"><?= htmlspecialchars($post->titulo, ENT_QUOTES, 'UTF-8') ?></a></h2>
                <p><?= htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8') ?></p>
                <div class="card-actions">
                    <a href="?action=show&amp;id=<?= (int) $post->id ?>">Ler postagem <span aria-hidden="true">→</span></a>
                    <a href="?action=edit&amp;id=<?= (int) $post->id ?>">Editar</a>
                    <?php if (isset($csrfToken)): ?>
                        <form action="?action=delete&amp;id=<?= (int) $post->id ?>" method="post" data-confirm-delete data-post-title="<?= htmlspecialchars($post->titulo, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <button class="button-link danger-link" type="submit" aria-label="Excluir postagem: <?= htmlspecialchars($post->titulo, ENT_QUOTES, 'UTF-8') ?>">Excluir</button>
                        </form>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
