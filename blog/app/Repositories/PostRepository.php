<?php

declare(strict_types=1);

// Concentra as consultas de postagens e converte os resultados em objetos Post.
class PostRepository
{
    public function __construct(private PDO $connection)
    {
    }

    /** @return Post[] */
    public function findAll(): array
    {
        // Publicações mais recentes aparecem primeiro; o ID desempata datas iguais.
        $statement = $this->connection->query(
            'SELECT id, titulo, conteudo, data_publicacao FROM posts ORDER BY data_publicacao DESC, id DESC'
        );

        return array_map([$this, 'toPost'], $statement->fetchAll());
    }

    public function findById(int $id): ?Post
    {
        $statement = $this->connection->prepare(
            'SELECT id, titulo, conteudo, data_publicacao FROM posts WHERE id = :id'
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetch();

        return $row === false ? null : $this->toPost($row);
    }

    public function create(Post $post): int
    {
        $statement = $this->connection->prepare(
            'INSERT INTO posts (titulo, conteudo, data_publicacao) VALUES (:titulo, :conteudo, :data_publicacao)'
        );
        $statement->execute([
            ':titulo' => $post->titulo,
            ':conteudo' => $post->conteudo,
            ':data_publicacao' => $post->dataPublicacao,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function update(Post $post): void
    {
        if ($post->id === null) {
            throw new InvalidArgumentException('A postagem precisa de um ID para ser atualizada.');
        }

        $statement = $this->connection->prepare(
            'UPDATE posts SET titulo = :titulo, conteudo = :conteudo, data_publicacao = :data_publicacao WHERE id = :id'
        );
        $statement->bindValue(':titulo', $post->titulo);
        $statement->bindValue(':conteudo', $post->conteudo);
        $statement->bindValue(':data_publicacao', $post->dataPublicacao);
        $statement->bindValue(':id', $post->id, PDO::PARAM_INT);
        $statement->execute();
    }

    public function delete(int $id): bool
    {
        $statement = $this->connection->prepare('DELETE FROM posts WHERE id = :id');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount() > 0;
    }

    /** @return Post[] */
    public function search(string $term): array
    {
        $term = trim($term);
        if ($term === '') {
            return $this->findAll();
        }

        // Trata os curingas do LIKE como texto digitado, mantendo a busca parcial.
        $pattern = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term) . '%';
        $statement = $this->connection->prepare(
            "SELECT id, titulo, conteudo, data_publicacao FROM posts WHERE titulo LIKE :titulo ESCAPE '!' OR conteudo LIKE :conteudo ESCAPE '!' ORDER BY data_publicacao DESC, id DESC"
        );
        $statement->execute([':titulo' => $pattern, ':conteudo' => $pattern]);

        return array_map([$this, 'toPost'], $statement->fetchAll());
    }

    private function toPost(array $row): Post
    {
        return new Post((int) $row['id'], $row['titulo'], $row['conteudo'], $row['data_publicacao']);
    }
}
