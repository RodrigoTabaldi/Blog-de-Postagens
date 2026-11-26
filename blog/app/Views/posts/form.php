<?php
// Espera $csrfToken; $values e $errors são opcionais e usam os nomes dos campos.
$values = $values ?? [
    'titulo' => $post->titulo ?? '',
    'conteudo' => $post->conteudo ?? '',
    'data_publicacao' => $post->dataPublicacao ?? '',
];
$errors = $errors ?? [];
?>
<?php // Sem o token fornecido pelo controller, o formulário não deve ser enviado. ?>
<?php if (!isset($csrfToken)): ?>
    <div class="alert alert-danger" role="alert">O formulário está indisponível. Tente novamente mais tarde.</div>
<?php else: ?>
<form class="post-form" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>" method="post" data-post-form>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <?php foreach (['titulo' => 'Título', 'conteudo' => 'Conteúdo', 'data_publicacao' => 'Data de publicação'] as $field => $label): ?>
        <div class="form-field">
            <label class="form-label" for="<?= $field ?>"><?= $label ?></label>
            <?php $attributes = !empty($errors[$field]) ? ' aria-invalid="true" aria-describedby="error-' . $field . '"' : ''; ?>
            <?php if ($field === 'conteudo'): ?>
                <textarea class="form-control" id="conteudo" name="conteudo" rows="10" required<?= $attributes ?>><?= htmlspecialchars($values[$field] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            <?php else: ?>
                <input class="form-control" id="<?= $field ?>" name="<?= $field ?>" type="<?= $field === 'titulo' ? 'text' : 'date' ?>" value="<?= htmlspecialchars($values[$field] ?? '', ENT_QUOTES, 'UTF-8') ?>" required<?= $attributes ?>>
            <?php endif; ?>
            <?php if (!empty($errors[$field])): ?><p class="field-error" id="error-<?= $field ?>"><?= htmlspecialchars($errors[$field], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
        </div>
    <?php endforeach; ?>
    <div class="form-actions"><button class="btn btn-primary" type="submit"><?= htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8') ?></button><a class="btn btn-outline-secondary" href="?action=index">Cancelar</a></div>
</form>
<?php endif; ?>
