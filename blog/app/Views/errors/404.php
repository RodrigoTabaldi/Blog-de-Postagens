<?php
// Informa que o recurso solicitado não está disponível.
$pageTitle = 'Página não encontrada';
require __DIR__ . '/../layouts/header.php';
?>
<section class="empty-state">
    <h1>Página não encontrada</h1>
    <p>A postagem solicitada não existe ou não está disponível.</p>
    <a class="btn btn-primary" href="?action=index">Voltar às postagens</a>
</section>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
