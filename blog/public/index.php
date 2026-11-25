<?php

declare(strict_types=1);

// Carrega as classes necessárias para atender às rotas disponíveis.
require __DIR__ . '/../app/Database/Connection.php';
require __DIR__ . '/../app/Models/Post.php';
require __DIR__ . '/../app/Repositories/PostRepository.php';
require __DIR__ . '/../app/Validators/PostValidator.php';
require __DIR__ . '/../app/Controllers/PostController.php';

// Mantém o token CSRF na sessão para validar os formulários enviados.
session_start();
$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));

// Associa cada ação ao método HTTP permitido antes de acessar o banco.
$action = $_GET['action'] ?? 'index';
$routes = ['index' => 'GET', 'create' => 'GET', 'store' => 'POST'];
if (!is_string($action) || !isset($routes[$action])) {
    http_response_code(404);
    echo 'Página não encontrada.';
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== $routes[$action]) {
    header('Allow: ' . $routes[$action]);
    http_response_code(405);
    echo 'Método não permitido.';
    exit;
}

// Carrega a configuração local, cria as dependências e executa a ação.
try {
    $configPath = __DIR__ . '/../config/database.php';
    if (!is_file($configPath)) {
        throw new RuntimeException('Configuração do banco ausente.');
    }
    $config = require $configPath;
    $controller = new PostController(new PostRepository(Connection::create($config)), new PostValidator());
    $controller->$action();
} catch (Throwable $exception) {
    // Detalhes técnicos e credenciais não devem aparecer na resposta HTTP.
    http_response_code(500);
    echo 'Não foi possível concluir a solicitação. Tente novamente mais tarde.';
}
