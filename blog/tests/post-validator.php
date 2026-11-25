<?php

declare(strict_types=1);

require __DIR__ . '/../app/Validators/PostValidator.php';

// Exercita entradas válidas, limites e tipos inesperados sem acessar o banco.
$validator = new PostValidator();
$valid = ['titulo' => 'Título', 'conteudo' => 'Conteúdo', 'data_publicacao' => '2024-02-29'];
$cases = [
    [$valid, []],
    [array_replace($valid, ['titulo' => '   ', 'conteudo' => "\t\n"]), ['titulo', 'conteudo']],
    [array_replace($valid, ['titulo' => str_repeat('á', 256)]), ['titulo']],
    [array_replace($valid, ['conteudo' => str_repeat('a', 65536)]), ['conteudo']],
    [array_replace($valid, ['data_publicacao' => '2023-02-29']), ['data_publicacao']],
    [array_replace($valid, ['data_publicacao' => '0999-12-31']), ['data_publicacao']],
    [array_replace($valid, ['data_publicacao' => "2024-01-01\0"]), ['data_publicacao']],
    [array_replace($valid, ['titulo' => ['inválido']]), ['titulo']],
    [array_replace($valid, ['titulo' => str_repeat('á', 255), 'conteudo' => str_repeat('a', 65535), 'data_publicacao' => '1000-01-01']), []],
];

// Compara os campos rejeitados com o resultado esperado em cada caso.
foreach ($cases as $index => [$input, $expected]) {
    $actual = array_keys($validator->validate($input));
    if ($actual !== $expected) {
        throw new RuntimeException('Falha no caso de validação ' . ($index + 1));
    }
}
echo "Validações de postagem aprovadas.\n";
