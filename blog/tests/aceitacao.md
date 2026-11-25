# Testes de aceitação

O roteiro HTTP em `aceitacao.php` exercita cadastro, listagem, leitura, edição, busca e exclusão. Ele consulta os dados diretamente no MySQL e remove, ao final, os registros que identificou como criados pela execução.

## Preparação

1. Crie um banco vazio e exclusivo para testes, com nome terminado em `_test` ou `_teste`, e crie nele a tabela `posts` conforme `database/schema.sql`. O arquivo original contém `CREATE DATABASE blog` e `USE blog`: adapte essas instruções ao banco de teste antes de executá-lo.
2. Configure `config/database.php` para esse banco. Esse arquivo é local e ignorado pelo Git. Confirme que o usuário tem apenas os privilégios necessários no banco de teste.
3. Habilite as extensões PHP `pdo_mysql` e `mbstring`. Na pasta `blog`, inicie `php -S 127.0.0.1:8000 -t public` usando esta mesma cópia do projeto e a configuração de teste. Não aponte o roteiro para outro servidor ou banco.
4. Em outro terminal, na pasta `blog`, execute `php tests/aceitacao.php http://127.0.0.1:8000` e depois `php tests/database.php config/database.php`.

O roteiro usa dados identificados por um marcador aleatório e remove apenas os IDs encontrados após o cadastro. Se a execução for interrompida abruptamente ou a resposta do servidor falhar após a gravação, pode ser necessário remover manualmente registros de teste com prefixo `aceitacao_`. Não execute em bancos compartilhados ou com dados importantes.

## Cobertura e resultados

Em 25/11/2026, passaram os testes locais de controller, validação, views, busca SQL em SQLite e confirmação de exclusão em JavaScript. O roteiro HTTP e o teste de repositório também passaram em MariaDB 11.4.9 isolado, em uma cópia temporária do projeto e com banco `blog_test`. A persistência foi confirmada após reiniciar o servidor PHP.

O envio HTTP direto confirmou as validações do servidor sem JavaScript. A confirmação e o cancelamento da exclusão foram testados com o JavaScript real em um ambiente simulado. Ainda devem ser conferidos os fluxos em navegador e a execução no MySQL especificado pelo projeto; MariaDB não substitui essa verificação. Não houve falhas nos testes executados. Registre aqui eventuais falhas e correções antes de marcar a task como concluída.
