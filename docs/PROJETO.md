# BASE SAAS

## 1. Objetivo

O sistema tem como objetivo fornecer uma base SaaS reutilizável para o desenvolvimento de novos sistemas SaaS.

A proposta é disponibilizar uma estrutura inicial pronta, contendo recursos comuns a esse tipo de aplicação, reduzindo o tempo necessário para desenvolver funcionalidades administrativas e estruturais que normalmente se repetem entre diferentes projetos.

## 2. Público-alvo

Desenvolvedores e equipes de desenvolvimento que desejam criar sistemas SaaS a partir de uma estrutura previamente preparada.

## 3. Problema resolvido

A Base SaaS busca eliminar a necessidade de desenvolver repetidamente funcionalidades comuns a sistemas SaaS, como:

gerenciamento de tenants;
usuários;
perfis e permissões;
ACL;
módulos;
limites de usuários;
configurações do sistema;
estrutura administrativa;
recursos financeiros básicos;
agenda;
autenticação e controle de acesso.

Dessa forma, o desenvolvedor poderá utilizar uma estrutura pronta e concentrar seus esforços nas regras de negócio específicas de cada novo sistema.

## 4. Stack

Linguagem: PHP 7.4
Framework Backend: Laravel 8
Banco de dados: MySql
Frontend: Vue.js
Autenticação: Web, podendo ser alterada conforme a necessidade do projeto
Arquitetura Backend:
Controller
Service
Repository

## 5. Conceito do sistema

O conceito da Base SaaS é fornecer uma estrutura robusta e reutilizável para servir como ponto de partida para o desenvolvimento de outros sistemas SaaS.

Em vez de iniciar cada projeto desenvolvendo novamente CRUDs, controle de acesso, cadastro de usuários, perfis, permissões, configurações de frontend, autenticação e demais recursos estruturais, a Base SaaS disponibilizará essas funcionalidades previamente implementadas.

Com isso, o desenvolvedor poderá concentrar seu trabalho nas funcionalidades específicas do sistema que está sendo criado.

Por exemplo, para desenvolver um sistema destinado a uma barbearia, a estrutura administrativa, usuários, permissões, tenants e demais funcionalidades básicas já estarão disponíveis. O desenvolvimento poderá se concentrar nas regras específicas do negócio, como agenda, atendimentos, profissionais, serviços e clientes.

## 6. Multi-tenancy

Inicialmente, o sistema utilizará uma arquitetura multi-tenant com banco de dados único.

Os registros deverão possuir identificação do tenant responsável, garantindo o isolamento lógico das informações entre diferentes empresas cadastradas na plataforma.

A arquitetura deverá ser preparada de forma que outras estratégias de multi-tenancy possam ser avaliadas futuramente, caso necessário.

## 7. Usuários

O sistema possuirá usuários vinculados aos respectivos tenants.

Também existirá um usuário Master, responsável pela administração geral da plataforma.

O usuário Master terá acesso a funcionalidades específicas que não estarão disponíveis para usuários comuns dos tenants, como gerenciamento global de empresas, planos, módulos, assinaturas e configurações administrativas da plataforma.

## 8. Funcionalidades

A Base SaaS deverá possuir inicialmente as seguintes funcionalidades:

cadastro e gerenciamento de tenants;
cadastro de usuários;
cadastro de perfis;
cadastro de permissões;
controle de acesso por ACL;
cadastro de clientes;
agenda;
registro de pagamentos;
contas a pagar;
contas a receber;
gestão financeira básica;
histórico de pagamentos;
relatórios básicos;
período de teste gratuito de 10 dias;
controle de limite de usuários;
gerenciamento de módulos;
controle de acesso por tenant;
bloqueio do tenant em caso de atraso ou inadimplência.

## 9. Funcionalidades planejadas

Além das funcionalidades iniciais, poderão ser adicionados novos recursos conforme a evolução da Base SaaS, incluindo:

gerenciamento de planos;
controle de assinaturas;
cobrança recorrente;
integração com gateways de pagamento;
configuração de módulos por plano;
definição de limites de uso;
notificações;
logs de atividades;
auditoria de ações dos usuários;
configurações personalizadas por tenant;
dashboards administrativos;
relatórios avançados;
APIs para integração com sistemas externos.

As funcionalidades dessa seção poderão ser priorizadas de acordo com as necessidades dos projetos que utilizarem a Base SaaS.

## 10. Restrições

As seguintes regras deverão ser consideradas como princípios da arquitetura:

todas as informações relacionadas a uma empresa deverão respeitar o isolamento por tenant;
um usuário não poderá acessar informações pertencentes a outro tenant;
permissões deverão ser validadas no backend, independentemente das restrições existentes no frontend;
funcionalidades administrativas globais deverão ser acessíveis somente pelo usuário Master;
novas funcionalidades deverão seguir a arquitetura definida pelo projeto;
regras específicas de negócio não deverão ser acopladas ao núcleo da Base SaaS quando puderem ser implementadas como módulos;
a estrutura deverá permitir a criação de diferentes sistemas SaaS sem necessidade de alterações significativas no núcleo da aplicação.

## 11. Estado atual

Atualmente, o projeto possui as seguintes funcionalidades implementadas:

cadastro de tenants;
cadastro de usuários;
cadastro de perfis;
cadastro de permissões.
controle de acesso.
isolamento dos dados por tenant.