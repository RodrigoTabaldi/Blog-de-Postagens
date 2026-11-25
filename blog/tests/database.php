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

// Execute com uma configuração apontando para um banco de teste separado.
$config = require $argv[1];
$pdo = Connection::create($config);
check($pdo->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION, 'Modo de erro incorreto.');
check($pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE) === PDO::FETCH_ASSOC, 'Modo de leitura incorreto.');
check(!$pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES), 'Prepared statements emulados.');

$repository = new PostRepository($pdo);
$marker = bin2hex(random_bytes(12));
// Desfaz as alterações mesmo se alguma verificação falhar.
$pdo->beginTransaction();
try {
    $id = $repository->create(new Post(null, "$marker 100%_!", 'Conteúdo acentuado', '2026-01-01'));
    $repository->create(new Post(null, "$marker 100abcX!", 'Outra postagem', '2026-01-02'));
    $post = $repository->findById($id);
    check($post !== null && $post->titulo === "$marker 100%_!" && $post->conteudo === 'Conteúdo acentuado', 'Criação ou leitura falhou.');
    check(count($repository->search("$marker 100%_!")) === 1, 'Busca literal falhou.');
    check(count($repository->search(' ')) === count($repository->findAll()), 'Busca vazia falhou.');

    // Usa marcadores únicos para verificar título, conteúdo, curingas literais e ordenação.
    $firstId = $repository->create(new Post(null, "$marker título único", "$marker conteúdo especial", '2026-03-01'));
    $secondId = $repository->create(new Post(null, "$marker título único", "$marker outra descrição", '2026-03-01'));
    $titleResults = $repository->search("$marker título ún");
    check(count($titleResults) === 2 && $titleResults[0]->id === $secondId && $titleResults[1]->id === $firstId, 'Busca no título ou ordenação por ID falhou.');
    check(count($repository->search("$marker conteúdo especial")) === 1, 'Busca no conteúdo falhou.');
    check(count($repository->search("$marker inexistente")) === 0, 'Busca sem resultados falhou.');

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

// As falhas de configuração e de conexão devem retornar mensagens seguras.
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
