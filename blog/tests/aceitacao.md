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

Na revisão posterior, o roteiro passou a procurar o marcador no título e no conteúdo após cada envio inválido, para detectar também uma possível gravação com título alterado. O teste `node tests/post-form-validation.js` verificou, em ambiente simulado, o bloqueio de campos com apenas espaços e a limpeza da mensagem ao editar o campo.

## Execução em MySQL — 25/11/2026

- MySQL Community Server 26.10.0, instância temporária isolada; schema original aplicado ao banco vazio `blog`, com tabela InnoDB vazia. O roteiro HTTP usou o banco separado `blog_test` e uma cópia temporária da aplicação.
- `tests/database.php` e `tests/aceitacao.php` passaram, inclusive a verificação direta de cadastro e edição inválidos, CSRF, CRUD, busca, SQL como dado e HTML escapado. Os testes locais de controller, views, validação, SQLite e JavaScript também passaram.
- No navegador, foram confirmados criação, leitura, cancelamento da exclusão e persistência após reiniciar o servidor PHP. Listagem, leitura e edição não apresentaram rolagem horizontal em 320 px; criação foi conferida em 320 px antes da correção abaixo.
- Falha encontrada: o rótulo de conteúdo não era associado ao campo, pois o elemento principal e o textarea compartilhavam o ID `conteudo`. O ID da área principal foi corrigido; o navegador confirmou a associação na criação e na edição, e `tests/post-edit.php` passou com uma verificação de regressão.
- A verificação de contraste, foco por teclado e todos os fluxos em diferentes tamanhos de tela ainda não é completa. Os testes automatizados de JavaScript simulam o navegador e não substituem todos os testes manuais. Por isso, os itens gerais de aceitação continuam abertos.
