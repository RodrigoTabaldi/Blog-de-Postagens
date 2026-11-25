<?php

declare(strict_types=1);

// Reúne as regras de validação usadas no cadastro de postagens.
class PostValidator
{
    // Valida os dados no servidor, mesmo quando o JavaScript está desativado.
    public function validate(array $input): array
    {
        $errors = [];
        // Rejeita campos ausentes ou recebidos em formatos inesperados, como arrays.
        foreach (['titulo', 'conteudo', 'data_publicacao'] as $field) {
            if (!isset($input[$field]) || !is_string($input[$field])) {
                $errors[$field] = 'Informe um valor válido.';
            }
        }

        // O limite do título é medido em caracteres, incluindo os acentuados.
        if (!isset($errors['titulo'])) {
            $title = $input['titulo'];
            if (!mb_check_encoding($title, 'UTF-8') || preg_match('/^\s*$/u', $title)) {
                $errors['titulo'] = 'Informe um título válido.';
            } elseif (mb_strlen($title, 'UTF-8') > 255) {
                $errors['titulo'] = 'O título deve ter no máximo 255 caracteres.';
            }
        }

        // O limite do conteúdo é medido em bytes, conforme o tipo TEXT do MySQL.
        if (!isset($errors['conteudo'])) {
            $content = $input['conteudo'];
            if (!mb_check_encoding($content, 'UTF-8') || preg_match('/^\s*$/u', $content)) {
                $errors['conteudo'] = 'Informe um conteúdo válido.';
            } elseif (strlen($content) > 65535) {
                $errors['conteudo'] = 'O conteúdo deve ter no máximo 65.535 bytes.';
            }
        }

        // Confere formato, existência da data e intervalo aceito pelo MySQL.
        if (!isset($errors['data_publicacao'])) {
            $value = $input['data_publicacao'];
            $date = preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)
                ? DateTimeImmutable::createFromFormat('!Y-m-d', $value) : false;
            if ($date === false || $date->format('Y-m-d') !== $value
                || $value < '1000-01-01' || $value > '9999-12-31') {
                $errors['data_publicacao'] = 'Informe uma data válida entre 1000-01-01 e 9999-12-31.';
            }
        }

        return $errors;
    }
}
