<?php

declare(strict_types=1);

require __DIR__ . '/../app/Models/Post.php';
require __DIR__ . '/../app/Repositories/PostRepository.php';
require __DIR__ . '/../app/Validators/PostValidator.php';
require __DIR__ . '/../app/Controllers/PostController.php';

// Registra gravações para confirmar que requisições inválidas não persistem dados.
class PostStoreSecurityRepository extends PostRepository
{
    public int $creates = 0;

    public function __construct()
    {
    }

    public function create(Post $post): int
    {
        $this->creates++;
        return 1;
    }
}

function checkStoreSecurity(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function requestStore(PostController $controller, array $input): array
{
    // Simula o envio diretamente ao servidor, sem depender de JavaScript.
    $_POST = $input;
    http_response_code(200);
    ob_start();
    $controller->store();
    return [http_response_code(), ob_get_clean()];
}

$_SESSION = ['csrf_token' => 'token-de-teste'];
$repository = new PostStoreSecurityRepository();
$controller = new PostController($repository, new PostValidator());
$valid = ['titulo' => 'Título', 'conteudo' => 'Conteúdo', 'data_publicacao' => '2026-01-01'];

// Ausência, valor incorreto ou tipo inesperado para o token devem retornar 403.
foreach ([$valid, $valid + ['csrf_token' => 'errado'], $valid + ['csrf_token' => ['token-de-teste']]] as $input) {
    [$status] = requestStore($controller, $input);
    checkStoreSecurity($status === 403 && $repository->creates === 0, 'Token inválido permitiu gravação.');
}

// Mesmo com CSRF válido, entradas inválidas recebem 422 e não são gravadas.
$invalid = ['csrf_token' => 'token-de-teste', 'titulo' => '<script>', 'conteudo' => '   ', 'data_publicacao' => '2026-02-30'];
[$status, $html] = requestStore($controller, $invalid);
checkStoreSecurity($status === 422 && $repository->creates === 0, 'Dados inválidos permitiram gravação.');
checkStoreSecurity(str_contains($html, '&lt;script&gt;') && str_contains($html, 'error-conteudo'), 'Formulário não exibiu valores e erros com segurança.');

echo "Testes de segurança do cadastro aprovados.\n";
