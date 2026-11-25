# Planejamento do blog

Aplicação web para criação, edição, exclusão, listagem e busca de postagens. O acesso será direto, sem cadastro ou autenticação.

**Stack:** PHP 8.4, MySQL, PDO, HTML, CSS, Bootstrap 5 e JavaScript.

Os itens marcados indicam implementação ou revisão concluída no código. As validações com MySQL real e os testes de aceitação continuam pendentes onde indicados.

## 01. Estrutura do projeto

- [x] Organizar a aplicação em MVC, com o acesso ao banco separado dos controllers e das páginas.
- [x] Centralizar as requisições em `public/index.php`.
- [x] Configurar o carregamento das classes e o roteamento por `action`.
- [x] Reutilizar cabeçalho, rodapé e formulário de postagem.
- [x] Manter as configurações do banco fora de `public/` e ignorar credenciais locais no Git.
- [x] Tratar rotas inexistentes e métodos HTTP não permitidos.

Estrutura prevista:

```text
blog/
├── app/
│   ├── Controllers/PostController.php
│   ├── Models/Post.php
│   ├── Repositories/PostRepository.php
│   ├── Validators/PostValidator.php
│   ├── Database/Connection.php
│   └── Views/
│       ├── layouts/
│       │   ├── header.php
│       │   └── footer.php
│       ├── posts/
│       │   ├── index.php
│       │   ├── show.php
│       │   ├── create.php
│       │   ├── edit.php
│       │   └── form.php
│       └── errors/
│           ├── 404.php
│           └── 500.php
├── config/database.example.php
├── database/
│   ├── schema.sql
│   └── seed.sql
├── public/
│   ├── index.php
│   └── assets/
│       ├── css/style.css
│       └── js/main.js
├── tests/
├── .gitignore
├── README.md
└── planejamento.md
```

O controller recebe as requisições, o validator verifica os dados e o repository executa as consultas. As views ficam responsáveis apenas pela apresentação.

### Rotas

| Método | Endereço | Ação do controller |
|---|---|---|
| GET | `/` ou `/?action=index` | `index()` |
| GET | `/?action=create` | `create()` |
| POST | `/?action=store` | `store()` |
| GET | `/?action=show&id={id}` | `show()` |
| GET | `/?action=edit&id={id}` | `edit()` |
| POST | `/?action=update&id={id}` | `update()` |
| POST | `/?action=delete&id={id}` | `delete()` |
| GET | `/?action=index&q={termo}` | `index()` |

## 02. Banco de dados

- [x] Criar o banco `blog` e a tabela `posts` em `database/schema.sql`.
- [x] Utilizar InnoDB e `utf8mb4` para armazenamento de textos acentuados.
- [x] Criar `database/seed.sql` com algumas postagens para demonstração, de importação opcional.
- [ ] Verificar a execução do schema em um banco vazio.

### Tabela `posts`

| Coluna | Tipo | Restrição |
|---|---|---|
| `id` | INT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT |
| `titulo` | VARCHAR(255) | NOT NULL |
| `conteudo` | TEXT | NOT NULL |
| `data_publicacao` | DATE | NOT NULL |

```sql
CREATE DATABASE IF NOT EXISTS blog
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE blog;

CREATE TABLE IF NOT EXISTS posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    conteudo TEXT NOT NULL,
    data_publicacao DATE NOT NULL
) ENGINE=InnoDB;
```

A data será armazenada em `YYYY-MM-DD` e exibida em `DD/MM/YYYY`. O identificador será gerado pelo banco, sem intervenção do usuário. Não há necessidade de outras tabelas.

## 03. Conexão e acesso aos dados

Arquivos: `Connection.php`, `Post.php`, `PostRepository.php` e `config/database.example.php`.

- [x] Implementar conexão PDO com host, porta, banco, usuário, senha e charset configuráveis.
- [x] Configurar `PDO::ATTR_ERRMODE` como `PDO::ERRMODE_EXCEPTION`, `PDO::ATTR_DEFAULT_FETCH_MODE` como `PDO::FETCH_ASSOC` e desabilitar emulação de prepared statements.
- [x] Definir o modelo `Post` com os quatro campos da tabela.
- [x] Preparar o repository, implementando os métodos conforme as respectivas funcionalidades.
- [x] Utilizar parâmetros vinculados em todas as consultas que recebem entradas externas.
- [ ] Validar a conexão e tratar indisponibilidade do banco sem exibir credenciais ou detalhes internos.

Métodos previstos no repository: `findAll()`, `findById(int $id)`, `create()`, `update()`, `delete(int $id)` e `search(string $term)`.

## 04. Layout do blog

- [x] Criar um cabeçalho com o nome do blog e acesso à página inicial e à nova postagem.
- [x] Definir a identidade visual em `public/assets/css/style.css`, usando fundo claro, texto escuro, bordas discretas e tipografia legível.
- [x] Usar Bootstrap como base de responsividade, com estilos próprios para evitar uma aparência genérica.
- [x] Organizar a listagem em cards com título, data, trecho e ações.
- [x] Definir uma largura confortável para a leitura dos artigos e para os formulários.
- [x] Padronizar botões, mensagens de erro e sucesso, estados vazios e confirmação de exclusão.
- [ ] Garantir contraste, foco visível, labels nos campos e funcionamento em telas menores.

A interface terá quatro páginas principais: listagem, leitura, criação e edição. A busca será integrada à listagem.

## 05. Criação de postagens

Rotas: `GET /?action=create` e `POST /?action=store`.

- [x] Criar `create.php` e o formulário reutilizável `form.php`.
- [x] Implementar os campos `titulo` (input text), `conteudo` (textarea) e `data_publicacao` (input date).
- [x] Implementar `PostController::create()`, `PostController::store()` e `PostRepository::create()`.
- [x] Centralizar as regras de validação em `PostValidator.php`.
- [x] Validar título e conteúdo no JavaScript antes de enviar o formulário; repetir as validações no PHP.
- [x] Rejeitar campos vazios ou compostos apenas por espaços, título acima de 255 caracteres, conteúdo acima de 65.535 bytes e data inválida ou fora do intervalo suportado pelo MySQL.
- [x] Rejeitar parâmetros com tipos inesperados.
- [x] Gerar e verificar token CSRF no formulário POST.
- [x] Preservar os valores preenchidos quando houver erro e indicar o campo que precisa ser corrigido.
- [x] Salvar a postagem e redirecionar para a listagem após sucesso (Post/Redirect/Get), com mensagem de confirmação.

```sql
INSERT INTO posts (titulo, conteudo, data_publicacao)
VALUES (:titulo, :conteudo, :data_publicacao);
```

Validação: cadastrar uma postagem válida, repetir com título e conteúdo vazios e testar o envio com JavaScript desabilitado. Recarregar a página após o cadastro não deve duplicar o registro.

## 06. Listagem de postagens

Rota: `GET /?action=index` (também disponível na raiz).

- [x] Implementar `PostController::index()` e `PostRepository::findAll()`.
- [x] Criar `app/Views/posts/index.php` e exibir todas as postagens.
- [x] Apresentar título, data e trecho de até 160 caracteres, sem cortar caracteres multibyte; adicionar reticências somente quando necessário.
- [x] Ordenar pela data de publicação decrescente e pelo ID em caso de empate.
- [x] Vincular o título à página individual da postagem (task 07).
- [x] Integrar as ações Editar e Excluir em cada card (tasks 08 e 09).
- [x] Exibir uma mensagem e acesso à criação quando não houver postagens.
- [x] Escapar dados dinâmicos antes da renderização HTML.

```sql
SELECT id, titulo, conteudo, data_publicacao
FROM posts
ORDER BY data_publicacao DESC, id DESC;
```

Validação: conferir a listagem com zero, um e vários registros, incluindo títulos longos e conteúdo acentuado.

## 07. Leitura da postagem

Rota: `GET /?action=show&id={id}`.

- [x] Implementar `PostController::show()` e `PostRepository::findById(int $id)`.
- [x] Validar o ID como inteiro positivo e retornar HTTP 404 se a postagem não existir ou o identificador for inválido.
- [x] Criar `app/Views/posts/show.php`.
- [x] Exibir título, data e conteúdo completo, sem truncamento.
- [x] Preservar as quebras de linha e apresentar o conteúdo como texto, sem interpretar HTML fornecido pelo usuário.
- [x] Adicionar retorno à listagem e integrar as ações de edição e exclusão quando disponíveis.

```sql
SELECT id, titulo, conteudo, data_publicacao
FROM posts
WHERE id = :id;
```

Validação: abrir a postagem pelo título na listagem, comparar o conteúdo com o registro no banco e acessar um ID inexistente.

## 08. Edição de postagens

Rotas: `GET /?action=edit&id={id}` e `POST /?action=update&id={id}`.

- [x] Implementar `PostController::edit()`, `PostController::update()` e `PostRepository::update()`.
- [x] Criar `edit.php`, reutilizando `form.php` com os dados atuais da postagem.
- [x] Permitir alteração de título, conteúdo e data de publicação, mantendo o ID original.
- [x] Reutilizar as validações PHP e JavaScript da criação.
- [x] Verificar a existência do registro e validar o token CSRF antes de atualizar.
- [x] Tratar entradas inválidas sem sobrescrever os dados existentes.
- [x] Permitir salvar sem alterações e cancelar a edição.
- [x] Adicionar a opção Editar em cada postagem da listagem.
- [x] Redirecionar após sucesso e apresentar mensagem de confirmação.

```sql
UPDATE posts
SET titulo = :titulo,
    conteudo = :conteudo,
    data_publicacao = :data_publicacao
WHERE id = :id;
```

Validação: alterar os três campos, cancelar uma edição e enviar dados inválidos. Conferir se o registro mantém o mesmo ID e se a quantidade de postagens não muda.

## 09. Exclusão de postagens

Rota: `POST /?action=delete&id={id}`.

- [x] Implementar `PostController::delete()` e `PostRepository::delete(int $id)`.
- [x] Disponibilizar a opção Excluir em cada postagem da listagem.
- [x] Implementar confirmação em JavaScript, identificando a postagem e permitindo cancelar a operação.
- [x] Validar ID, existência da postagem e token CSRF.
- [x] Impedir exclusões via GET.
- [x] Remover o registro do MySQL e redirecionar para a listagem com feedback.
- [x] Tratar a tentativa de excluir um registro inexistente.

```sql
DELETE FROM posts WHERE id = :id;
```

Validação: cancelar uma exclusão e confirmar que o registro permanece; confirmar a exclusão e verificar que o registro não aparece mais na listagem nem na busca.

## 10. Busca de postagens

Rota: `GET /?action=index&q={termo}`.

- [x] Criar formulário de pesquisa na página inicial.
- [x] Receber o parâmetro `q` no controller, validar seu tipo e remover espaços das extremidades.
- [x] Implementar `PostRepository::search(string $term)` com pesquisa parcial em `titulo` ou `conteudo`.
- [x] Vincular os dois parâmetros separadamente e escapar `%`, `_` e o próprio caractere de escape para que sejam pesquisados como texto literal.
- [x] Manter ordenação, título clicável e ações de edição e exclusão nos resultados.
- [x] Preservar o termo pesquisado e informar quando não houver correspondências.
- [x] Permitir limpar a pesquisa; um termo vazio deve retornar todas as postagens.

```sql
SELECT id, titulo, conteudo, data_publicacao
FROM posts
WHERE titulo LIKE :titulo ESCAPE '!'
   OR conteudo LIKE :conteudo ESCAPE '!'
ORDER BY data_publicacao DESC, id DESC;
```

Os parâmetros recebem o termo escapado, envolvido por `%` para permitir correspondências parciais. A busca será feita no MySQL, sem depender do JavaScript.

Validação: pesquisar uma palavra que apareça apenas no título, outra apenas no conteúdo, termos parciais, acentuados, inexistentes e caracteres especiais.

## 11. Revisão de segurança e validações

- [x] Conferir o uso de prepared statements nas operações de leitura e escrita.
- [x] Revisar o escape de conteúdo, atributos HTML, valores dos formulários e termos de pesquisa com `htmlspecialchars()` e UTF-8.
- [x] Conferir a geração de tokens CSRF com `random_bytes()`, armazenamento em sessão e comparação com `hash_equals()`.
- [ ] Confirmar que toda operação POST exige token válido e que dados inválidos não são persistidos.
- [ ] Revisar os métodos HTTP e as respostas de erro: 403 para CSRF inválido, 404 para recurso inexistente, 405 para método não permitido, 422 para formulário inválido e 500 para erro interno.
- [ ] Verificar se arquivos de configuração e credenciais estão protegidos, inclusive no histórico do Git.
- [ ] Utilizar um usuário MySQL com os privilégios necessários à aplicação, sem permissões administrativas.

Como não há autenticação, qualquer pessoa com acesso à aplicação pode gerenciar as postagens. As proteções acima não substituem um controle de acesso; a aplicação será destinada à execução local e avaliação do desafio.

Revisão parcial: os testes de controller e HTTP disponíveis passaram, e os 13 commits acessíveis foram examinados por padrões comuns de credenciais, sem correspondências. Essa pesquisa não garante a ausência de todos os tipos de segredo. Permanecem pendentes a validação com MySQL, a conferência dos privilégios efetivos e a revisão completa do histórico. O procedimento está em `tests/seguranca.md`.

## 12. Testes de aceitação

Registrar os resultados dos testes em `tests/`, incluindo falhas encontradas e correções. Usar um banco de teste separado dos dados de demonstração.

| Caso | Resultado esperado |
|---|---|
| Cadastro válido | Registro criado com título, conteúdo e data |
| Título ou conteúdo vazio | Envio bloqueado no JavaScript e no PHP |
| Campos apenas com espaços | Registro não criado |
| Título acima do limite ou data inválida | Mensagem de validação, sem persistência |
| JavaScript desabilitado | PHP continua rejeitando dados inválidos |
| Recarregar após cadastro | Nenhum registro duplicado |
| Listagem de registros | Todos os posts exibem título, data e trecho |
| Clicar no título | Conteúdo integral da postagem correta |
| Edição válida | Registro atualizado mantendo o ID |
| Edição inválida | Dados originais preservados |
| Cancelar exclusão | Registro preservado |
| Confirmar exclusão | Registro removido do banco |
| Pesquisa por título ou conteúdo | Correspondências apresentadas |
| Pesquisa vazia ou sem resultados | Listagem completa ou estado vazio, respectivamente |
| ID inexistente | Resposta 404 |
| Exclusão por GET | Resposta 405, sem exclusão |
| POST sem token CSRF válido | Resposta 403, sem alteração |
| Entrada com caracteres de SQL | Tratada como dado, sem alterar a consulta |
| Conteúdo com HTML ou script | Apresentado como texto, sem execução |
| Reiniciar aplicação | Dados permanecem no MySQL |

- [ ] Executar todos os casos e conferir os registros diretamente no MySQL.
- [ ] Corrigir as falhas identificadas e repetir os testes afetados.

## 13. Revisão do código e da interface

- [x] Verificar sintaxe dos arquivos PHP com `php -l`.
- [ ] Revisar nomes, tipagem e responsabilidades das classes e métodos.
- [ ] Remover duplicações, código não utilizado e logs de depuração.
- [x] Verificar se não existem consultas SQL nas views.
- [ ] Revisar formulários, mensagens, links e ações de gerenciamento.
- [ ] Testar a interface em celular, tablet e desktop, incluindo navegação por teclado.
- [ ] Conferir o console do navegador e executar novamente os fluxos principais após as correções.

## 14. Documentação e entrega

- [ ] Finalizar `README.md` com descrição, funcionalidades, stack, pré-requisitos e instruções de execução local.
- [ ] Documentar a criação do banco, importação do schema e configuração das credenciais locais; indicar que o seed é opcional.
- [ ] Documentar as extensões PHP necessárias, incluindo `pdo_mysql` e `mbstring`.
- [ ] Adicionar capturas reais da aplicação e uma breve descrição da arquitetura.
- [ ] Testar a execução em outro diretório a partir de um clone limpo, seguindo apenas o README.
- [ ] Conferir os requisitos do desafio e a ausência de credenciais no repositório.
- [ ] Publicar a versão final no GitHub e verificar o acesso do avaliador.
- [ ] Enviar o link do repositório por e-mail.

Execução local prevista:

```bash
php -S localhost:8000 -t public
```

Acesso: `http://localhost:8000`.

## Versionamento

Realizar commits ao longo da implementação, de acordo com as mudanças efetivamente concluídas. Exemplos: `feat: add post creation`, `feat: implement keyword search`, `fix: validate post input`, `docs: update local setup`.

O projeto estará pronto para entrega quando o CRUD e a busca estiverem funcionando, as validações no PHP e no JavaScript estiverem verificadas, a tabela `posts` corresponder ao desafio e a execução local puder ser reproduzida pelo README.
