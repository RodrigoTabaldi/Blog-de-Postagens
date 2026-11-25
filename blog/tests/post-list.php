<?php

declare(strict_types=1);

require __DIR__ . '/../app/Models/Post.php';

function renderPostList(array $posts): string
{
    // Captura o HTML para verificar a view sem iniciar um servidor web.
    ob_start();
    require __DIR__ . '/../app/Views/posts/index.php';
    return ob_get_clean();
}

function checkList(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

// Confere o estado vazio e a apresentação de textos nos limites do trecho.
$empty = renderPostList([]);
checkList(str_contains($empty, 'Ainda não há postagens'), 'Estado vazio ausente.');
checkList(str_contains($empty, '?action=create'), 'Acesso à criação ausente.');

$single = renderPostList([new Post(3, str_repeat('Título ', 40), '<b>Conteúdo</b>', '2026-01-01')]);
checkList(substr_count($single, 'class="post-card"') === 1, 'Listagem de uma postagem incorreta.');
checkList(str_contains($single, '?action=show&amp;id=3'), 'Link para leitura ausente.');
checkList(str_contains($single, str_repeat('Título ', 40)), 'Título longo não foi apresentado integralmente.');
checkList(str_contains($single, '&lt;b&gt;Conteúdo&lt;/b&gt;'), 'Conteúdo não foi escapado.');

$html = renderPostList([
    new Post(1, '<script>alert(1)</script>', str_repeat('á', 160), '1000-01-01'),
    new Post(2, 'Segunda postagem', str_repeat('ç', 161), '2026-11-25'),
]);
// Exercita caracteres multibyte, escape de HTML e datas nos limites suportados.
checkList(substr_count($html, 'class="post-card"') === 2, 'Quantidade de postagens incorreta.');
checkList(str_contains($html, '&lt;script&gt;alert(1)&lt;/script&gt;'), 'Título não foi escapado.');
checkList(str_contains($html, str_repeat('á', 160) . '</p>'), 'Trecho no limite foi truncado.');
checkList(str_contains($html, str_repeat('ç', 160) . '…</p>'), 'Trecho longo não foi truncado corretamente.');
checkList(str_contains($html, '01/01/1000') && str_contains($html, '25/11/2026'), 'Data apresentada incorretamente.');

echo "Testes da listagem aprovados.\n";
