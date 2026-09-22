# Agenda

## Ativacao

A migration `2026_09_22_000001_create_agendas_table.php` precisa ser aplicada pelo responsavel pelo ambiente. Ela cria as FKs durante a criacao da tabela, inclusive no SQLite. Nenhuma migration foi executada durante a implementacao.

A pagina fica em `/{tenant}/agenda`. O router Vue ja registra essa pagina; o menu lateral continua usando os registros de menu existentes no banco.

## Configuracao Google

Reutiliza `services.google.client_id`, `client_secret` e os tokens criptografados em `social_accounts`. O usuario deve ter autorizado o escopo `https://www.googleapis.com/auth/calendar.events` no cadastro e a Google Calendar API deve estar habilitada no projeto Google Cloud. O login com senha, sozinho, nao cria esse vinculo.

Variaveis opcionais no ambiente:

```dotenv
GOOGLE_CALENDAR_TIMEZONE=America/Sao_Paulo
GOOGLE_CALENDAR_DURATION=30
```

Os eventos sao criados no calendario `primary` do usuario. Data e hora sao interpretadas no fuso acima, exibido na tela. O servico renova tokens expirados usando o refresh token existente, preservando-o quando o Google nao retorna um novo.

Referencia: https://developers.google.com/workspace/calendar/api/v3/reference/events/insert

## API

O prefixo multi-tenant existente foi mantido. O Axios usa `/agenda` relativo a `/api/{tenant}`.

- `GET /api/{tenant}/agenda`: lista somente os registros do usuario autenticado nesse tenant. Aceita `?data=2026-09-22` como filtro opcional.
- `POST /api/{tenant}/agenda`: recebe `data` (`YYYY-MM-DD`), `hora` (`HH:mm`) e `comentario` (obrigatorio, ate 5000 caracteres). IDs de usuario e tenant sao determinados no servidor.
- A resposta de criacao usa HTTP 201 com `data` (agendamento) e `integration` (`status` e `message`).
- `synced`: evento criado e ID salvo localmente. `pending`: registro local salvo, mas sincronizacao nao confirmada.

Nao ha retentativa automatica, edicao ou exclusao de eventos nesta etapa. Se o Google aceitar o evento e a resposta ou persistencia local falhar, o evento pode existir remotamente sem confirmacao local; nao recadastre o horario para tentar sincronizar. A lista destaca como confirmados apenas os registros com `google_event_id`.

O modulo nao cria permissoes ACL nem registros de menu no banco. Usa Controller -> Service -> Repository, com binding de `AgendaRepositoryInterface` em `AppServiceProvider`.
