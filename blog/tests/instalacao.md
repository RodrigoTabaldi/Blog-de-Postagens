# Verificação das instruções de instalação

Em 26/11/2026, as instruções foram testadas no Windows com PHP 8.4 e MySQL Community Server 26.10. Foi criado um clone limpo, em outro diretório, de uma cópia temporária da versão local com as alterações ainda não publicadas. O MySQL usado no teste era uma instância isolada, com banco vazio e uma conta de aplicação limitada à tabela `posts`.

O schema e o seed opcional foram importados, a configuração local foi criada a partir do exemplo e a aplicação foi iniciada com o servidor embutido do PHP. A listagem e o formulário responderam com HTTP 200; as três postagens de exemplo e seus acentos apareceram na listagem. Os testes `post-validator.php` e `post-edit.php` também passaram.

Durante a verificação, o comando inicial com `source` e `-e` falhou no cliente MySQL. A documentação foi corrigida para executar `source` no console do MySQL. Também foi necessário definir `utf8mb4` no cliente para importar corretamente os acentos no Windows. O procedimento corrigido foi repetido com sucesso.

O endereço remoto do repositório não foi alterado nem publicado nesta verificação. Depois da publicação, é necessário conferir um clone obtido diretamente do GitHub.

## Verificação com Docker

Em 26/11/2026, a imagem PHP foi construída e o MySQL 8.4 foi iniciado com um volume novo. A aplicação exibiu as três postagens de exemplo, e os fluxos HTTP de cadastro, pesquisa e exclusão passaram. Após reiniciar os containers, os exemplos permaneceram sem duplicação. As extensões `pdo_mysql` e `mbstring` estavam habilitadas na imagem.

Na primeira tentativa, o cliente MySQL importou acentos incorretamente. Foi adicionada a declaração de codificação UTF-8 ao seed e a inicialização foi repetida com um volume novo; as postagens foram exibidas corretamente.
