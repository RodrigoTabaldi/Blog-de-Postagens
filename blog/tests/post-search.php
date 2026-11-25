<?php

declare(strict_types=1);

require __DIR__ . '/../app/Models/Post.php';
require __DIR__ . '/../app/Repositories/PostRepository.php';
require __DIR__ . '/../app/Validators/PostValidator.php';
require __DIR__ . '/../app/Controllers/PostController.php';

// Registra qual consulta o controller solicitou sem depender de um banco real.
class PostSearchRepository extends PostRepository
{
    public array $calls = [];
    public array $results = [];

    public function __construct()
    {
    }

    public function findAll(): array
    {
        $this->calls[] = ['all'];
        return $this->results;
    }

    public function search(string $term): array
    {
        $this->calls[] = ['search', $term];
        return $this->results;
    }
}

function checkSearch(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function requestSearch(PostController $controller, array $query): array
{
    // Captura o HTML e o status como se fosse uma nova requisição.
    $_GET = $query;
    http_response_code(200);
    ob_start();
    $controller->index();
    return [http_response_code(), ob_get_clean()];
}

$_SESSION = ['csrf_token' => 'token-de-teste'];
$repository = new PostSearchRepository();
$controller = new PostController($repository, new PostValidator());

// A busca ausente ou só com espaços usa a listagem completa.
foreach ([[], ['q' => '   ']] as $query) {
    [$status, $html] = requestSearch($controller, $query);
    checkSearch($status === 200 && end($repository->calls) === ['all'], 'Busca vazia não listou tudo.');
    checkSearch(str_contains($html, 'Ainda não há postagens'), 'Estado vazio da listagem ausente.');
}

// Tipos inválidos e bytes que não são UTF-8 não chegam ao repositório.
$previousCalls = count($repository->calls);
foreach ([['q' => ['texto']], ['q' => "\xFF"]] as $query) {
    [$status] = requestSearch($controller, $query);
    checkSearch($status === 400 && count($repository->calls) === $previousCalls, 'Busca inválida não foi rejeitada.');
}

// O termo é aparado, preservado com escape no formulário e pode ser limpo.
[$status, $html] = requestSearch($controller, ['q' => '  <script>á%_!</script>  ']);
checkSearch($status === 200 && end($repository->calls) === ['search', '<script>á%_!</script>'], 'Termo não foi normalizado.');
checkSearch(str_contains($html, 'value="&lt;script&gt;á%_!&lt;/script&gt;"') && str_contains($html, 'Limpar'), 'Termo ou opção de limpeza ausente.');
checkSearch(str_contains($html, 'Nenhuma postagem encontrada'), 'Estado sem resultados ausente.');

// Resultados continuam oferecendo leitura, edição e exclusão.
$repository->results = [new Post(7, 'Título', 'Conteúdo', '2026-01-01')];
[$status, $html] = requestSearch($controller, ['q' => 'Título']);
checkSearch($status === 200 && str_contains($html, '?action=show&amp;id=7') && str_contains($html, '?action=edit&amp;id=7') && str_contains($html, '?action=delete&amp;id=7'), 'Ações ausentes nos resultados.');

echo "Testes de busca aprovados.\n";
