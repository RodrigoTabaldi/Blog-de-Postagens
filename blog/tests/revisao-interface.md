# Revisão do código e da interface

Em 25/11/2026, a revisão das classes, rotas e views confirmou a separação entre validação, acesso a dados e apresentação. A validação de CSRF e a recuperação dos valores do formulário foram centralizadas no controller para evitar regras divergentes entre criação, edição e exclusão. Não foram encontrados logs de depuração no código da aplicação.

O limite HTML anterior contava unidades UTF-16 e impedia títulos válidos com alguns caracteres, como emojis. O JavaScript passou a contar pontos de código, como o PHP; ambos foram testados com 255 e 256 emojis. O PHP continua validando os dados quando o JavaScript está desabilitado. As transições de cards e botões são aplicadas apenas quando há suporte a hover e a pessoa não pediu movimento reduzido.

Os testes PHP de validação, controllers, views e SQLite, os testes JavaScript e a verificação de sintaxe passaram. Em uma cópia isolada usando MySQL 26.10, os testes de repositório e aceitação HTTP passaram; o roteiro HTTP foi repetido após a centralização do controller.

No navegador do Orca, foram executados criação, leitura, edição, pesquisa e exclusão, incluindo cancelamento e confirmação. Um título com 256 emojis foi rejeitado e outro com 255 foi salvo. Listagem e formulário de criação foram conferidos em larguras de 320, 768 e 1440 pixels sem rolagem horizontal; os campos tinham labels associados. O console não apresentou erros nos fluxos observados.

Na verificação posterior em uma aba ativa, a tecla Tab revelou que o Bootstrap sobrescrevia o contorno dos botões e campos. Após o ajuste de especificidade no CSS, a inspeção visual confirmou um contorno sólido de 3 px no link de acesso ao conteúdo, no botão de nova postagem e no campo de pesquisa. Os testes em larguras simuladas não substituem dispositivos físicos ou uma auditoria completa de contraste.
