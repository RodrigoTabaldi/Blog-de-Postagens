<?php

declare(strict_types=1);

require __DIR__ . '/../app/Models/Post.php';
require __DIR__ . '/../app/Repositories/PostRepository.php';

function checkSearchSqlite(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

// Um banco em memória exercita a consulta sem alterar dados locais.
$pdo = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pdo->exec('CREATE TABLE posts (id INTEGER PRIMARY KEY, titulo TEXT NOT NULL, conteudo TEXT NOT NULL, data_publicacao TEXT NOT NULL)');
$pdo->exec("INSERT INTO posts VALUES (1, 'Título acentuado', 'Palavra exclusiva', '2026-01-01'), (2, '100%_! especial', 'Outro texto', '2026-02-01'), (3, '100abcX! especial', 'Outro texto', '2026-02-01')");
$repository = new PostRepository($pdo);

// Verifica correspondências parciais em cada campo e caracteres literais do LIKE.
checkSearchSqlite($repository->search('acentuado')[0]->id === 1, 'Busca parcial no título falhou.');
checkSearchSqlite($repository->search('exclusiva')[0]->id === 1, 'Busca no conteúdo falhou.');
$literal = $repository->search('100%_!');
checkSearchSqlite(count($literal) === 1 && $literal[0]->id === 2, 'Caracteres especiais não foram tratados literalmente.');
checkSearchSqlite($repository->search('inexistente') === [], 'Busca inexistente encontrou registros.');
$all = $repository->search(' ');
checkSearchSqlite(array_map(fn (Post $post): ?int => $post->id, $all) === [3, 2, 1], 'Busca vazia ou ordenação falhou.');

echo "Testes SQL de busca em SQLite aprovados.\n";
