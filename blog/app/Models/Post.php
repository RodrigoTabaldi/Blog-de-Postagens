<?php

declare(strict_types=1);

// Representa os dados de uma postagem; o ID é nulo antes da criação.
class Post
{
    public function __construct(
        public ?int $id,
        public string $titulo,
        public string $conteudo,
        public string $dataPublicacao,
    ) {
    }
}
