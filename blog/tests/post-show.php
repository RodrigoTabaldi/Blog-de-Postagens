<?php

declare(strict_types=1);

require __DIR__ . '/../app/Models/Post.php';
require __DIR__ . '/../app/Repositories/PostRepository.php';
require __DIR__ . '/../app/Validators/PostValidator.php';
require __DIR__ . '/../app/Controllers/PostController.php';

// Substitui apenas a consulta para testar o controller sem alterar um banco real.
class PostShowRepository extends PostRepository
{
    public array $requestedIds = [];

    public function __construct(private Post $post)
    {
    }

    public function findById(int $id): ?Post
    {
        $this->requestedIds[] = $id;
        return $id === $this->post->id ? $this->post : null;
    }
}

function checkShow(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function renderShow(PostController $controller, mixed $id): array
{
    // Simula uma nova requisição e captura o status e o HTML produzidos.
    $_GET = $id === null ? [] : ['id' => $id];
    http_response_code(200);
    ob_start();
    $controller->show();
    return [http_response_code(), ob_get_clean()];
}

$content = str_repeat('Conteúdo acentuado ', 20) . "\n<script>alert(1)</script>";
$_SESSION = ['csrf_token' => 'token-de-teste'];
$repository = new PostShowRepository(new Post(7, '<Título>', $content, '1000-01-01'));
$controller = new PostController($repository, new PostValidator());

// Confere conteúdo integral, quebras de linha e escape de dados fornecidos pelo usuário.
[$status, $html] = renderShow($controller, '7');
checkShow($status === 200 && $repository->requestedIds === [7], 'Postagem válida não encontrada.');
checkShow(str_contains($html, '&lt;Título&gt;') && str_contains($html, '01/01/1000'), 'Título ou data incorretos.');
checkShow(str_contains($html, str_repeat('Conteúdo acentuado ', 20) . '<br />'), 'Conteúdo foi truncado ou perdeu a quebra de linha.');
checkShow(str_contains($html, '&lt;script&gt;alert(1)&lt;/script&gt;'), 'HTML do conteúdo não foi escapado.');
checkShow(str_contains($html, '?action=index'), 'Retorno à listagem ausente.');
checkShow(str_contains($html, '?action=edit&amp;id=7'), 'Link para edição ausente.');
checkShow(str_contains($html, '?action=delete&amp;id=7') && str_contains($html, 'data-post-title="&lt;Título&gt;"'), 'Formulário de exclusão ausente ou título não escapado.');

// IDs inválidos não devem chegar ao repositório; um ID inexistente deve retornar 404.
foreach ([null, '', '0', '-1', '1.5', '1x', ' 7', '7 ', '999999999999999999999999', ['7']] as $invalidId) {
    [$status, $html] = renderShow($controller, $invalidId);
    checkShow($status === 404 && str_contains($html, 'Página não encontrada'), 'ID inválido não retornou 404.');
}
checkShow($repository->requestedIds === [7], 'ID inválido chegou ao repositório.');
[$status] = renderShow($controller, '8');
checkShow($status === 404 && $repository->requestedIds === [7, 8], 'Postagem inexistente não retornou 404.');

echo "Testes de leitura aprovados.\n";
