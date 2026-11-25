<?php

declare(strict_types=1);

// Coordena a apresentação, criação e edição de postagens.
class PostController
{
    // Recebe as dependências que acessam o banco e validam os dados.
    public function __construct(private PostRepository $repository, private PostValidator $validator)
    {
    }

    public function index(): void
    {
        // Busca todas as postagens na ordem definida pelo repositório.
        $posts = $this->repository->findAll();
        // A mensagem de sucesso é exibida apenas uma vez após o redirecionamento.
        $flashMessage = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        require __DIR__ . '/../Views/posts/index.php';
    }

    public function create(): void
    {
        // O formulário recebe o token da sessão para proteger o envio.
        $csrfToken = $_SESSION['csrf_token'];
        require __DIR__ . '/../Views/posts/create.php';
    }

    public function show(): void
    {
        $post = $this->findRequestedPost();
        if ($post !== null) {
            require __DIR__ . '/../Views/posts/show.php';
        }
    }

    public function edit(): void
    {
        $post = $this->findRequestedPost();
        if ($post !== null) {
            // O formulário compartilhado recebe os dados atuais e o token da sessão.
            $csrfToken = $_SESSION['csrf_token'];
            require __DIR__ . '/../Views/posts/edit.php';
        }
    }

    public function update(): void
    {
        // O token deve ser válido antes de consultar ou alterar a postagem.
        $token = $_POST['csrf_token'] ?? null;
        if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            echo 'Não foi possível validar o formulário.';
            return;
        }

        $post = $this->findRequestedPost();
        if ($post === null) {
            return;
        }

        // Reutiliza as regras do cadastro e não grava dados inválidos.
        $errors = $this->validator->validate($_POST);
        if ($errors !== []) {
            // Preserva apenas strings, evitando exibir tipos inesperados no formulário.
            $values = [];
            foreach (['titulo', 'conteudo', 'data_publicacao'] as $field) {
                $values[$field] = isset($_POST[$field]) && is_string($_POST[$field]) ? $_POST[$field] : '';
            }
            $csrfToken = $_SESSION['csrf_token'];
            http_response_code(422);
            require __DIR__ . '/../Views/posts/edit.php';
            return;
        }

        // Mantém o ID original, inclusive quando nenhum campo foi alterado.
        $updatedPost = new Post($post->id, trim($_POST['titulo']), trim($_POST['conteudo']), $_POST['data_publicacao']);
        $this->repository->update($updatedPost);
        $_SESSION['flash_message'] = 'Postagem atualizada com sucesso.';
        // Redireciona após a gravação para evitar o reenvio ao atualizar a página.
        header('Location: ?action=index', true, 303);
    }

    private function findRequestedPost(): ?Post
    {
        // Aceita apenas um identificador inteiro positivo dentro do intervalo do PHP.
        $rawId = $_GET['id'] ?? null;
        $id = is_string($rawId) && preg_match('/^[0-9]+$/D', $rawId)
            ? filter_var($rawId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            : false;

        // IDs inválidos e registros ausentes recebem a mesma resposta.
        $post = $id === false ? null : $this->repository->findById($id);
        if ($post === null) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return null;
        }

        return $post;
    }

    public function store(): void
    {
        // Recusa o envio quando o token não corresponde ao da sessão.
        $token = $_POST['csrf_token'] ?? null;
        if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            echo 'Não foi possível validar o formulário.';
            return;
        }

        // Os dados são validados antes de qualquer gravação no banco.
        $errors = $this->validator->validate($_POST);
        if ($errors !== []) {
            // Preserva apenas valores que podem ser exibidos com segurança no formulário.
            $values = [];
            foreach (['titulo', 'conteudo', 'data_publicacao'] as $field) {
                $values[$field] = isset($_POST[$field]) && is_string($_POST[$field]) ? $_POST[$field] : '';
            }
            $csrfToken = $_SESSION['csrf_token'];
            http_response_code(422);
            require __DIR__ . '/../Views/posts/create.php';
            return;
        }

        // Remove espaços nas extremidades e salva a postagem validada.
        $post = new Post(null, trim($_POST['titulo']), trim($_POST['conteudo']), $_POST['data_publicacao']);
        $this->repository->create($post);
        $_SESSION['flash_message'] = 'Postagem criada com sucesso.';
        // Redireciona após o POST para evitar reenvio ao atualizar a página.
        header('Location: ?action=index', true, 303);
    }
}
