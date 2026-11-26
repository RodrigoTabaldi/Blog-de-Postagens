<?php

declare(strict_types=1);

require __DIR__ . '/../app/Models/Post.php';
require __DIR__ . '/../app/Repositories/PostRepository.php';
require __DIR__ . '/../app/Validators/PostValidator.php';
require __DIR__ . '/../app/Controllers/PostController.php';

// Mantém uma cópia dos dados para verificar gravações sem acessar um banco real.
class PostEditRepository extends PostRepository
{
    public int $updates = 0;
    public int $lookups = 0;

    public function __construct(public Post $post)
    {
    }

    public function findById(int $id): ?Post
    {
        $this->lookups++;
        return $id === $this->post->id ? clone $this->post : null;
    }

    public function update(Post $post): void
    {
        $this->updates++;
        $this->post = clone $post;
    }
}

function checkEdit(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function requestEdit(PostController $controller, string $action, mixed $id, array $input = []): array
{
    $_GET = $id === null ? [] : ['id' => $id];
    $_POST = $input;
    http_response_code(200);
    ob_start();
    $controller->$action();
    return [http_response_code(), ob_get_clean()];
}

$_SESSION = ['csrf_token' => 'token-de-teste'];
$repository = new PostEditRepository(new Post(7, 'Original', 'Conteúdo original', '2026-01-01'));
$controller = new PostController($repository, new PostValidator());
$valid = ['csrf_token' => 'token-de-teste', 'titulo' => ' Novo título ', 'conteudo' => ' Novo conteúdo ', 'data_publicacao' => '2026-02-02'];

// O formulário mostra os dados atuais, o token e a opção de cancelar.
[$status, $html] = requestEdit($controller, 'edit', '7');
checkEdit($status === 200 && str_contains($html, 'value="Original"') && str_contains($html, 'Conteúdo original'), 'Dados atuais ausentes.');
checkEdit(str_contains($html, '?action=update&amp;id=7') && str_contains($html, 'token-de-teste') && str_contains($html, 'Cancelar'), 'Formulário de edição incompleto.');
// O rótulo deve apontar para o textarea, sem outro elemento com o mesmo ID.
checkEdit(substr_count($html, 'id="conteudo"') === 1 && str_contains($html, 'for="conteudo"'), 'Rótulo de conteúdo não identifica um campo único.');

// Uma falha de CSRF não pode consultar nem alterar a postagem.
$lookups = $repository->lookups;
foreach ([[], array_replace($valid, ['csrf_token' => 'incorreto']), array_replace($valid, ['csrf_token' => ['token-de-teste']])] as $input) {
    [$status] = requestEdit($controller, 'update', '7', $input);
    checkEdit($status === 403 && $repository->updates === 0 && $repository->lookups === $lookups, 'CSRF inválido não foi bloqueado.');
}

// IDs inválidos e inexistentes retornam 404 sem gravar alterações.
foreach ([null, '0', '-1', ['7'], '8'] as $id) {
    [$status] = requestEdit($controller, 'edit', $id);
    checkEdit($status === 404, 'Edição de ID inválido ou inexistente não retornou 404.');
    [$status] = requestEdit($controller, 'update', $id, $valid);
    checkEdit($status === 404 && $repository->updates === 0, 'Atualização de ID inválido ou inexistente não retornou 404.');
}

// Erros preservam os valores enviados e os dados originais armazenados.
$invalid = array_replace($valid, ['titulo' => '<script>', 'conteudo' => '   ', 'data_publicacao' => '2026-02-30']);
[$status, $html] = requestEdit($controller, 'update', '7', $invalid);
checkEdit($status === 422 && $repository->updates === 0 && $repository->post->titulo === 'Original', 'Dados inválidos foram gravados.');
checkEdit(str_contains($html, '&lt;script&gt;') && str_contains($html, 'error-conteudo') && str_contains($html, 'error-data_publicacao'), 'Valores ou erros não foram exibidos.');

// Tipos inesperados e limites excedidos também são rejeitados no servidor.
foreach ([
    array_replace($valid, ['titulo' => ['inválido']]),
    array_replace($valid, ['titulo' => str_repeat('á', 256)]),
    array_replace($valid, ['conteudo' => str_repeat('a', 65536)]),
] as $input) {
    [$status] = requestEdit($controller, 'update', '7', $input);
    checkEdit($status === 422 && $repository->updates === 0 && $repository->post->titulo === 'Original', 'Entrada inválida foi gravada.');
}

// A atualização preserva o ID e aceita uma segunda gravação sem alterações.
[$status] = requestEdit($controller, 'update', '7', $valid);
checkEdit($status === 303 && $repository->updates === 1 && $repository->post->id === 7, 'Atualização válida falhou.');
checkEdit($repository->post->titulo === 'Novo título' && $repository->post->conteudo === 'Novo conteúdo' && $repository->post->dataPublicacao === '2026-02-02', 'Campos não foram atualizados.');
checkEdit(($_SESSION['flash_message'] ?? '') === 'Postagem atualizada com sucesso.', 'Mensagem de sucesso ausente.');
[$status] = requestEdit($controller, 'update', '7', $valid);
checkEdit($status === 303 && $repository->updates === 2 && $repository->post->id === 7, 'Salvar sem alterações falhou.');

echo "Testes de edição aprovados.\n";
