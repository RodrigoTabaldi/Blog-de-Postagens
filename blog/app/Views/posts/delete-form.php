<?php
// Recebe a postagem, o token da sessão e a classe visual da página que inclui o formulário.
// O título é escapado nos atributos HTML e usado para identificar a postagem na confirmação.
?>
<form action="?action=delete&amp;id=<?= (int) $post->id ?>" method="post" data-confirm-delete data-post-title="<?= htmlspecialchars($post->titulo, ENT_QUOTES, 'UTF-8') ?>">
    <?php // O servidor compara este token com o armazenado na sessão antes de excluir. ?>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <button class="<?= htmlspecialchars($deleteButtonClass, ENT_QUOTES, 'UTF-8') ?>" type="submit" aria-label="Excluir postagem: <?= htmlspecialchars($post->titulo, ENT_QUOTES, 'UTF-8') ?>">Excluir</button>
</form>
