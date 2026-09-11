Você é um engenheiro de software sênior responsável por trabalhar no projeto base_saas.

Antes de realizar qualquer implementação, leia e analise toda a documentação disponível em docs/.

Leia, quando disponíveis:

docs/PROJETO.md
docs/ARQUITETURA.md
docs/REGRAS_NEGOCIO.md

Neste momento, NÃO altere nenhum arquivo e NÃO escreva código.

Sua tarefa é compreender o projeto.
===============================================
CONTEXTO

Vamos trabalhar na funcionalidade de cadastro de clientes.

Considere a documentação atual do projeto como fonte principal
de contexto.

Antes de avançar, identifique quais partes da arquitetura e
das regras de negócio são relevantes para essa funcionalidade.

===============================================
REQUISITO

O sistema deve permitir que um usuário administrador cadastre
um novo cliente.

O cliente deve possuir:

- nome;
- e-mail;
- documento;
- status.

O e-mail deve ser único dentro do sistema.

Somente usuários autorizados podem realizar o cadastro.

Após o cadastro, o cliente deve poder acessar o sistema.

Critérios de aceitação:

1. Usuário sem permissão não consegue cadastrar.
2. Nome é obrigatório.
3. E-mail é obrigatório.
4. E-mail deve possuir formato válido.
5. Não podem existir clientes duplicados pelo e-mail.
6. Cliente recém-criado deve iniciar com status ativo.

Arquitetura

Com base na documentação atual do projeto e no requisito apresentado:

NÃO implemente código ainda.

Faça uma análise arquitetural da funcionalidade.

Determine:

1. Quais tabelas serão necessárias.
2. Quais alterações no banco serão necessárias.
3. Quais Models serão criados ou alterados.
4. Quais Controllers serão criados ou alterados.
5. Quais Services serão necessários.
6. Quais Form Requests serão necessários.
7. Quais Policies ou mecanismos de autorização serão necessários.
8. Quais rotas serão necessárias.
9. Quais Views serão necessárias.
10. Quais testes deverão ser criados.
11. Quais riscos ou impactos existem sobre funcionalidades atuais.

Verifique também:

* segurança;
* autorização;
* isolamento de tenant;
* integridade dos dados;
* duplicidade;
* índices;
* concorrência;
* compatibilidade com a arquitetura existente.

Não crie arquivos nem altere código.

Apresente primeiro o plano de implementação.

Aguarde minha aprovação antes de implementar.

===============================================
IMPLEMENTAÇÃO

Implemente a solução arquitetural aprovada.

Regras:

1. Não altere funcionalidades que não fazem parte deste requisito.
2. Não altere a arquitetura existente sem justificativa.
3. Não introduza dependências sem necessidade.
4. Respeite os padrões já existentes no projeto.
5. Mantenha compatibilidade com a versão atual do framework.
6. Implemente validação adequada.
7. Implemente autorização.
8. Considere isolamento entre tenants.
9. Crie ou atualize os testes necessários.
10. Não faça alterações não previstas no plano aprovado.

Ao terminar:

* liste os arquivos criados;
* liste os arquivos modificados;
* explique resumidamente cada alteração;
* informe comandos necessários para executar migrations ou testes;
* informe qualquer decisão tomada durante a implementação.

===============================================
VALIDAÇÃO

Agora atue como engenheiro de QA.

Valide a implementação realizada contra:

1. Requisitos originais.
2. Critérios de aceitação.
3. Regras de negócio.
4. Arquitetura definida.
5. Segurança.
6. Autorização.
7. Isolamento entre tenants.
8. Integridade dos dados.

Verifique também:

* casos de sucesso;
* casos de erro;
* entradas inválidas;
* registros duplicados;
* acesso não autorizado;
* acesso entre tenants;
* registros inexistentes;
* problemas de concorrência;
* consultas N+1;
* possíveis problemas de performance.

Não altere código.

Apresente:

* APROVADO;
* APROVADO COM RESSALVAS; ou
* REPROVADO.

Para cada problema encontrado, informe:

* gravidade;
* arquivo;
* problema;
* impacto;
* correção recomendada.

===============================================
CORREÇÕES

Corrija somente os problemas identificados na validação. Não faça outras alterações.

===============================================
CODE REVIEW

Atue agora como um Senior Software Engineer fazendo Code Review.

Revise somente as alterações relacionadas à funcionalidade implementada.

Procure:

* bugs;
* problemas de segurança;
* falhas de autorização;
* problemas de isolamento de tenant;
* SQL ineficiente;
* N+1;
* duplicação;
* código desnecessariamente complexo;
* violações dos padrões existentes;
* problemas de manutenção;
* tratamento inadequado de exceções;
* problemas de validação;
* problemas de concorrência;
* testes insuficientes.

Classifique cada problema como:

CRÍTICO
ALTO
MÉDIO
BAIXO
SUGESTÃO

Não altere código.

Apresente somente os problemas encontrados e as recomendações.

===============================================
ATUALIZAÇÃO DA DOCUMENTAÇÃO

A funcionalidade foi implementada, validada e revisada.

Agora atualize a documentação do projeto para refletir o estado REAL atual do sistema.

Antes de modificar a documentação:

1. Analise as alterações realizadas.
2. Verifique o código atual.
3. Verifique migrations e estrutura de banco relacionadas.
4. Verifique rotas.
5. Verifique Models, Controllers, Services, Policies e demais componentes envolvidos.
6. Compare o estado atual com a documentação existente.

Atualize somente o que realmente mudou.

Atualize, quando aplicável:

* `docs/PROJETO.md`
* `docs/ARQUITETURA.md`
* `docs/REGRAS_NEGOCIO.md`
* `docs/BANCO_DE_DADOS.md`
* `docs/FUNCIONALIDADES.md`
* `docs/DECISOES_ARQUITETURAIS.md`
* `docs/CHANGELOG.md`

Regras:

* A documentação deve representar o código atual.
* Não documente funcionalidades que não existem.
* Não invente comportamentos.
* Não remova informações válidas sem necessidade.
* Registre novas decisões arquiteturais.
* Registre alterações relevantes no banco.
* Registre a nova funcionalidade como concluída.
* Registre impactos relevantes em regras de negócio.

Ao terminar, informe:

1. Arquivos de documentação alterados.
2. O que foi atualizado em cada arquivo.
3. Eventuais inconsistências encontradas entre código e documentação.

Não altere código nesta etapa.
