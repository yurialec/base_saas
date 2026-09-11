# Regras de negócio

## Usuários

- Um usuário pertence a um tenant.
- Um usuário não pode acessar dados de outro tenant.

## Tenant

- Cada tenant possui seus próprios dados.

## Exclusão

- Dados críticos não devem ser excluídos fisicamente.

## Segurança

- Toda operação deve verificar autorização.
- Dados de outro tenant nunca podem ser retornados.

## Banco

- Não criar registros duplicados quando existir
  uma restrição lógica para isso.