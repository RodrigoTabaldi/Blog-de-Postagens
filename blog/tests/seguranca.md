# Verificação de segurança com MySQL

Estas verificações precisam de um servidor MySQL configurado e de um banco de teste separado. Não use dados de produção nem inclua senhas nos comandos ou no repositório.

1. Aplique `database/schema.sql` ao banco de teste e configure um arquivo PHP local fora do controle de versão com os dados de conexão desse banco.
2. Com as extensões `pdo_mysql` e `mbstring` habilitadas, execute `php tests/database.php caminho/para/configuracao-de-teste.php` a partir da pasta `blog`. O teste usa uma transação e desfaz as alterações; confirme que a tabela é InnoDB antes de executá-lo.
3. Execute os fluxos HTTP de cadastro, edição e exclusão com e sem token CSRF válido. Confirme diretamente no banco que envios inválidos não alteram registros e que os códigos 403, 404, 405, 422 e 500 correspondem aos casos previstos no planejamento.
4. Conecte-se ao MySQL com a conta usada pela aplicação e execute `SELECT CURRENT_USER();` e `SHOW GRANTS FOR CURRENT_USER;`. Revise também eventuais papéis ativos e concessões herdadas. A conta deve ter apenas `SELECT`, `INSERT`, `UPDATE` e `DELETE` necessários na tabela `posts`, sem privilégios administrativos ou permissões adicionais em outros bancos. A criação do schema deve ser feita por uma conta administrativa separada.
5. Revise o histórico Git com uma ferramenta de detecção de segredos apropriada. A busca pontual por padrões comuns já realizada não substitui uma análise completa; trate qualquer valor encontrado como sensível e não o imprima nos registros de teste.

Registre o resultado de cada verificação e marque os itens correspondentes do planejamento apenas após confirmá-los no ambiente efetivo.
