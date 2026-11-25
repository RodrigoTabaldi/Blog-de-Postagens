<?php

declare(strict_types=1);

require __DIR__ . '/../app/Database/Connection.php';

// O servidor deve apontar para a configuração local de um banco exclusivo de testes.
if ($argc !== 2 || !preg_match('~^http://(127\.0\.0\.1|localhost):[0-9]+/?$~D', $argv[1])) {
    fwrite(STDERR, "Uso: php tests/aceitacao.php http://127.0.0.1:8000\n");
    exit(1);
}
$configPath = __DIR__ . '/../config/database.php';
if (!is_file($configPath)) {
    throw new RuntimeException('Configure um banco de teste em config/database.php.');
}
$config = require $configPath;
if (!is_array($config) || !isset($config['database']) || !is_string($config['database'])
    || !preg_match('/(?:_test|_teste)$/D', $config['database'])) {
    throw new RuntimeException('O nome do banco de teste deve terminar em _test ou _teste.');
}

$pdo = Connection::create($config);
$baseUrl = rtrim($argv[1], '/');
$cookie = '';
$marker = 'aceitacao_' . bin2hex(random_bytes(12));
$createdIds = [];

function assertAcceptance(bool $condition, string $case): void
{
    if (!$condition) {
        throw new RuntimeException('Falha: ' . $case);
    }
    echo "OK: $case\n";
}

function requestAcceptance(string $url, string $method = 'GET', array $data = []): array
{
    global $cookie;
    // Desabilita redirecionamentos para verificar explicitamente o status e o destino.
    $headers = "Connection: close\r\n";
    if ($cookie !== '') {
        $headers .= "Cookie: $cookie\r\n";
    }
    $options = ['method' => $method, 'header' => $headers, 'ignore_errors' => true, 'follow_location' => 0, 'timeout' => 5];
    if ($method === 'POST') {
        $options['header'] .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $options['content'] = http_build_query($data);
    }
    $body = file_get_contents($url, false, stream_context_create(['http' => $options]));
    if ($body === false || !isset($http_response_header[0]) || !preg_match('~^HTTP/\S+ (\d{3})~', $http_response_header[0], $matches)) {
        throw new RuntimeException('Não foi possível obter resposta HTTP do servidor de teste.');
    }
    foreach ($http_response_header as $header) {
        if (preg_match('/^Set-Cookie:\s*([^;]+)/i', $header, $match)) {
            $cookie = $match[1];
        }
    }
    return [(int) $matches[1], $body, $http_response_header];
}

function fetchAcceptancePost(PDO $pdo, int $id): array|false
{
    $query = $pdo->prepare('SELECT id, titulo, conteudo, data_publicacao FROM posts WHERE id = :id');
    $query->execute([':id' => $id]);
    return $query->fetch();
}

try {
    [$status, $form] = requestAcceptance($baseUrl . '/?action=create');
    assertAcceptance($status === 200 && preg_match('/name="csrf_token" value="([a-f0-9]{64})"/', $form, $tokenMatch) === 1, 'Formulário e token CSRF');
    $valid = ['csrf_token' => $tokenMatch[1], 'titulo' => "$marker título", 'conteudo' => "$marker conteúdo\n<script>alert(1)</script>", 'data_publicacao' => '2026-01-02'];

    // Envia os dados diretamente, como um navegador com JavaScript desativado.
    foreach ([['titulo' => '   '], ['conteudo' => ''], ['titulo' => str_repeat('a', 256)], ['data_publicacao' => '2026-02-30']] as $change) {
        [$status] = requestAcceptance($baseUrl . '/?action=store', 'POST', array_replace($valid, $change));
        assertAcceptance($status === 422, 'Cadastro inválido rejeitado');
    }
    [$status] = requestAcceptance($baseUrl . '/?action=store', 'POST', array_diff_key($valid, ['csrf_token' => true]));
    assertAcceptance($status === 403, 'Cadastro sem CSRF rejeitado');

    // Localiza apenas registros com o marcador único criado por esta execução.
    $find = $pdo->prepare('SELECT id FROM posts WHERE titulo = :titulo');
    $find->execute([':titulo' => $valid['titulo']]);
    assertAcceptance($find->fetchAll() === [], 'Nenhum cadastro inválido persistido');
    [$status, , $headers] = requestAcceptance($baseUrl . '/?action=store', 'POST', $valid);
    $find->execute([':titulo' => $valid['titulo']]);
    $rows = $find->fetchAll();
    foreach ($rows as $row) {
        $createdIds[] = (int) $row['id'];
    }
    assertAcceptance($status === 303 && count($rows) === 1 && preg_grep('/^Location:\s*\?action=index\s*$/i', $headers) !== [], 'Cadastro e redirecionamento');
    $id = $createdIds[0];
    $stored = fetchAcceptancePost($pdo, $id);
    assertAcceptance($stored !== false && $stored['conteudo'] === $valid['conteudo'] && $stored['data_publicacao'] === $valid['data_publicacao'], 'Conteúdo salvo no banco');

    // Confere a listagem e simula uma atualização da página após o redirecionamento.
    [$status, $list] = requestAcceptance($baseUrl . '/?action=index');
    assertAcceptance($status === 200 && str_contains($list, $valid['titulo']) && str_contains($list, '02/01/2026') && str_contains($list, "?action=show&amp;id=$id"), 'Listagem e link de leitura');
    requestAcceptance($baseUrl . '/?action=index');
    $find->execute([':titulo' => $valid['titulo']]);
    assertAcceptance(count($find->fetchAll()) === 1, 'Recarregar não duplica cadastro');
    [$status, $show] = requestAcceptance($baseUrl . '/?action=show&id=' . $id);
    assertAcceptance($status === 200 && str_contains($show, '&lt;script&gt;alert(1)&lt;/script&gt;') && !str_contains($show, '<script>alert(1)</script>'), 'Leitura integral e escape de HTML');

    // Compara o registro antes e depois das tentativas de edição.
    [$status] = requestAcceptance($baseUrl . '/?action=update&id=' . $id, 'POST', array_replace($valid, ['titulo' => '']));
    assertAcceptance($status === 422 && fetchAcceptancePost($pdo, $id)['titulo'] === $valid['titulo'], 'Edição inválida preserva dados');
    [$status] = requestAcceptance($baseUrl . '/?action=update&id=' . $id, 'POST', array_diff_key($valid, ['csrf_token' => true]));
    assertAcceptance($status === 403 && fetchAcceptancePost($pdo, $id)['titulo'] === $valid['titulo'], 'Edição sem CSRF preserva dados');
    $edited = array_replace($valid, ['titulo' => "$marker editado", 'conteudo' => "$marker texto editado", 'data_publicacao' => '2026-02-03']);
    [$status] = requestAcceptance($baseUrl . '/?action=update&id=' . $id, 'POST', $edited);
    $stored = fetchAcceptancePost($pdo, $id);
    assertAcceptance($status === 303 && $stored !== false && (int) $stored['id'] === $id && $stored['titulo'] === $edited['titulo'] && $stored['conteudo'] === $edited['conteudo'] && $stored['data_publicacao'] === $edited['data_publicacao'], 'Edição válida mantém ID e altera os campos');

    // Pesquisa partes presentes separadamente no título e no conteúdo.
    foreach (['editado', 'texto editado'] as $term) {
        [$status, $results] = requestAcceptance($baseUrl . '/?action=index&q=' . rawurlencode($marker . ' ' . $term));
        assertAcceptance($status === 200 && str_contains($results, $edited['titulo']), 'Busca por título ou conteúdo');
    }
    [$status, $results] = requestAcceptance($baseUrl . '/?action=index&q=' . rawurlencode($marker . ' inexistente'));
    assertAcceptance($status === 200 && str_contains($results, 'Nenhuma postagem encontrada'), 'Busca sem resultados');
    [$status, $results] = requestAcceptance($baseUrl . '/?action=index&q=');
    assertAcceptance($status === 200 && str_contains($results, $edited['titulo']), 'Busca vazia lista postagens');
    [$status, $results] = requestAcceptance($baseUrl . '/?action=index&q=' . rawurlencode("$marker ' OR 1=1 --"));
    assertAcceptance($status === 200 && str_contains($results, 'Nenhuma postagem encontrada'), 'Caracteres SQL são tratados como dados');
    [$status] = requestAcceptance($baseUrl . '/?action=show&id=0');
    assertAcceptance($status === 404, 'ID inválido retorna 404');
    // GET e POST sem token não devem excluir; somente o POST válido pode remover.
    [$status] = requestAcceptance($baseUrl . '/?action=delete&id=' . $id);
    assertAcceptance($status === 405 && fetchAcceptancePost($pdo, $id) !== false, 'Exclusão por GET bloqueada');
    [$status] = requestAcceptance($baseUrl . '/?action=delete&id=' . $id, 'POST', []);
    assertAcceptance($status === 403 && fetchAcceptancePost($pdo, $id) !== false, 'Exclusão sem CSRF bloqueada');
    [$status] = requestAcceptance($baseUrl . '/?action=delete&id=' . $id, 'POST', ['csrf_token' => $valid['csrf_token']]);
    assertAcceptance($status === 303 && fetchAcceptancePost($pdo, $id) === false, 'Exclusão confirmada remove o registro');
    [$status] = requestAcceptance($baseUrl . '/?action=show&id=' . $id);
    assertAcceptance($status === 404, 'Postagem excluída retorna 404');
    [$status, $results] = requestAcceptance($baseUrl . '/?action=index&q=' . rawurlencode($marker));
    assertAcceptance($status === 200 && !str_contains($results, $edited['titulo']), 'Postagem excluída não aparece na busca');
} finally {
    // Remove somente IDs identificados como criados por este teste, mesmo após falha.
    $cleanup = $pdo->prepare('DELETE FROM posts WHERE id = :id');
    foreach ($createdIds as $id) {
        $cleanup->execute([':id' => $id]);
    }
}
