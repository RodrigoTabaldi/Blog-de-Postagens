<?php
// As views recebem $pageTitle e, opcionalmente, $flashMessage e $flashType.
$pageTitle = $pageTitle ?? 'Blog de Postagens';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> | Blog de Postagens</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<a class="skip-link" href="#conteudo">Pular para o conteúdo</a>
<header class="site-header">
    <nav class="container site-nav" aria-label="Navegação principal">
        <a class="site-brand" href="?action=index">Blog de Postagens</a>
        <div class="nav-actions">
            <a class="nav-link-custom" href="?action=index">Início</a>
            <a class="btn btn-primary" href="?action=create">Nova postagem</a>
        </div>
    </nav>
</header>
<main id="conteudo" class="container site-main" tabindex="-1">
    <?php if (!empty($flashMessage)): ?>
        <div class="alert <?= ($flashType ?? '') === 'error' ? 'alert-danger' : 'alert-success' ?>" role="<?= ($flashType ?? '') === 'error' ? 'alert' : 'status' ?>">
            <?= htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>
