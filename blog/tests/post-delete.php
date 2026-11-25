<?php

declare(strict_types=1);

require __DIR__ . '/../app/Models/Post.php';
require __DIR__ . '/../app/Repositories/PostRepository.php';
require __DIR__ . '/../app/Validators/PostValidator.php';
require __DIR__ . '/../app/Controllers/PostController.php';

// Simula o repositório, inclusive a remoção por outra requisição após a consulta.
class PostDeleteRepository extends PostRepository
{
    public int $lookups = 0;
    public int $deletions = 0;
    public bool $exists = true;
    public bool $deleteResult = true;

    public function __construct()
    {
    }

    public function findById(int $id): ?Post
    {
        $this->lookups++;
        return $this->exists && $id === 7 ? new Post(7, 'Título', 'Conteúdo', '2026-01-01') : null;
    }

    public function delete(int $id): bool
    {
        $this->deletions++;
        if ($this->deleteResult) {
            $this->exists = false;
        }
        return $this->deleteResult;
    }
}

function checkDelete(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function requestDelete(PostController $controller, mixed $id, array $input): array
{
    // Simula os parâmetros de uma requisição e captura a resposta do controller.
    $_GET = $id === null ? [] : ['id' => $id];
    $_POST = $input;
    http_response_code(200);
    ob_start();
    $controller->delete();
    return [http_response_code(), ob_get_clean()];
}

$_SESSION = ['csrf_token' => 'token-de-teste'];
$repository = new PostDeleteRepository();
$controller = new PostController($repository, new PostValidator());
$valid = ['csrf_token' => 'token-de-teste'];

// Sem token válido não ocorre consulta ou exclusão.
foreach ([[], ['csrf_token' => 'incorreto'], ['csrf_token' => ['token-de-teste']]] as $input) {
    [$status] = requestDelete($controller, '7', $input);
    checkDelete($status === 403 && $repository->lookups === 0 && $repository->deletions === 0, 'CSRF inválido não foi bloqueado.');
}

// Um ID inválido ou uma postagem inexistente retorna 404 sem exclusão.
foreach ([null, '0', '-1', ['7'], '8'] as $id) {
    [$status] = requestDelete($controller, $id, $valid);
    checkDelete($status === 404 && $repository->deletions === 0 && $repository->exists, 'ID inválido ou inexistente não foi tratado.');
}

// Uma remoção concorrente não deve produzir uma mensagem falsa de sucesso.
$repository->deleteResult = false;
[$status] = requestDelete($controller, '7', $valid);
checkDelete($status === 404 && !isset($_SESSION['flash_message']), 'Remoção concorrente não foi tratada.');

// A exclusão confirmada redireciona e uma nova tentativa recebe 404.
$repository->deleteResult = true;
[$status] = requestDelete($controller, '7', $valid);
checkDelete($status === 303 && !$repository->exists, 'Exclusão válida falhou.');
checkDelete(($_SESSION['flash_message'] ?? '') === 'Postagem excluída com sucesso.', 'Mensagem de sucesso ausente.');
[$status] = requestDelete($controller, '7', $valid);
checkDelete($status === 404, 'Postagem já excluída não retornou 404.');

echo "Testes de exclusão aprovados.\n";
