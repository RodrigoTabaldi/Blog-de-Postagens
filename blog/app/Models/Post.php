<?php

declare(strict_types=1);

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
