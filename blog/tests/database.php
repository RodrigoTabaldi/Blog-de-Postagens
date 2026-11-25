<?php

declare(strict_types=1);

require __DIR__ . '/../app/Database/Connection.php';
require __DIR__ . '/../app/Models/Post.php';
require __DIR__ . '/../app/Repositories/PostRepository.php';

if ($argc !== 2 || !is_file($argv[1])) {
    fwrite(STDERR, "Uso: php tests/database.php caminho/para/configuracao.php\n");
    exit(1);
}

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$config = require $argv[1];
$pdo = Connection::create($config);
check($pdo->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION, 'Modo de erro incorreto.');
check($pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE) === PDO::FETCH_ASSOC, 'Modo de leitura incorreto.');
check(!$pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES), 'Prepared statements emulados.');

$repository = new PostRepository($pdo);
$marker = bin2hex(random_bytes(12));
$pdo->beginTransaction();
try {
    $id = $repository->create(new Post(null, "$marker 100%_!", 'Conteúdo acentuado', '2026-01-01'));
    $repository->create(new Post(null, "$marker 100abcX!", 'Outra postagem', '2026-01-02'));
    $post = $repository->findById($id);
    check($post !== null && $post->titulo === "$marker 100%_!" && $post->conteudo === 'Conteúdo acentuado', 'Criação ou leitura falhou.');
    check(count($repository->search("$marker 100%_!")) === 1, 'Busca literal falhou.');
    check(count($repository->search(' ')) === count($repository->findAll()), 'Busca vazia falhou.');

    $post->titulo = "$marker atualizado";
    $post->dataPublicacao = '2026-02-02';
    $repository->update($post);
    $updated = $repository->findById($id);
    check($updated !== null && $updated->titulo === $post->titulo && $updated->dataPublicacao === '2026-02-02', 'Atualização falhou.');
    $repository->update($updated);
    check($repository->delete($id), 'Exclusão falhou.');
    check($repository->findById($id) === null && !$repository->delete($id), 'Registro inexistente não foi tratado.');
} finally {
    $pdo->rollBack();
}

try {
    Connection::create(array_replace($config, ['port' => 0]));
    throw new RuntimeException('Configuração inválida aceita.');
} catch (InvalidArgumentException $exception) {
    check($exception->getMessage() === 'Configuração de banco de dados inválida.', 'Erro de configuração inesperado.');
}

try {
    Connection::create(array_replace($config, ['database' => 'banco_inexistente_' . $marker]));
    throw new RuntimeException('Conexão inválida aceita.');
} catch (RuntimeException $exception) {
    check($exception->getMessage() === 'Não foi possível conectar ao banco de dados.', 'Erro de conexão expõe detalhes.');
}

try {
    Connection::create(array_replace($config, ['host' => '127.0.0.1', 'port' => 1]));
    throw new RuntimeException('Conexão com servidor indisponível aceita.');
} catch (RuntimeException $exception) {
    check($exception->getMessage() === 'Não foi possível conectar ao banco de dados.', 'Erro de indisponibilidade expõe detalhes.');
}

echo "Testes de banco concluídos.\n";
