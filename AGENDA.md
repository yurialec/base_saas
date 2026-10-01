# Agenda

## Ativacao

A migration `2026_09_22_000001_create_agendas_table.php` precisa ser aplicada pelo responsavel pelo ambiente. Ela cria as FKs durante a criacao da tabela, inclusive no SQLite. Nenhuma migration foi executada durante a implementacao.

A pagina fica em `/{tenant}/agenda`. O router Vue ja registra essa pagina; o menu lateral continua usando os registros de menu existentes no banco.

## Configuracao Google

Reutiliza `services.google.client_id`, `client_secret` e os tokens criptografados em `social_accounts`. A Google Calendar API deve estar habilitada no projeto Google Cloud. Usuarios cadastrados com senha podem usar **Conectar Google Calendar** na Agenda, sem trocar sua sessao de login. A conexao pede o escopo `https://www.googleapis.com/auth/calendar.events` e acesso offline.

Adicione ao cliente OAuth do Google Cloud uma URI de redirecionamento autorizada com a origem real da aplicacao seguida de `/auth/google/calendar/callback`. Exemplo local: `http://localhost:8000/auth/google/calendar/callback`. Essa URI e adicional a `/auth/google/callback`, usada pelo login/cadastro.

Configure `GOOGLE_CALENDAR_REDIRECT_URI` com essa mesma URL se necessario. Sem essa variavel, a aplicacao usa a URL gerada para a rota `google.calendar.callback`. A origem deve preservar a sessao do usuario que iniciou a conexao.

Variaveis opcionais no ambiente:

```dotenv
GOOGLE_CALENDAR_TIMEZONE=America/Sao_Paulo
GOOGLE_CALENDAR_DURATION=30
GOOGLE_CALENDAR_REDIRECT_URI="${APP_URL}/auth/google/calendar/callback"
```

Os eventos sao criados no calendario `primary` do usuario. Data e hora sao interpretadas no fuso acima, exibido na tela. O servico renova tokens expirados usando o refresh token existente, preservando-o quando o Google nao retorna um novo.

Referencia: https://developers.google.com/workspace/calendar/api/v3/reference/events/insert

## API

O prefixo multi-tenant existente foi mantido. O Axios usa `/agenda` relativo a `/api/{tenant}`.

- `GET /api/{tenant}/agenda`: lista somente os registros do usuario autenticado nesse tenant. Aceita `?data=2026-09-22` como filtro opcional.
- `POST /api/{tenant}/agenda`: recebe `data` (`YYYY-MM-DD`), `hora` (`HH:mm`) e `comentario` (obrigatorio, ate 5000 caracteres). IDs de usuario e tenant sao determinados no servidor.
- `POST /{tenant}/agenda/google/connect`: inicia a conexao via sessao web e CSRF; retorna a URL de autorizacao Google.
- `GET /auth/google/calendar/callback`: exige login, verifica state OAuth, prazo de 10 minutos, usuario e tenant que iniciaram a conexao. Nao autentica outro usuario nem cria uma conta local.
- `POST /api/{tenant}/agenda/{id}/sync`: sincroniza somente um agendamento pertencente ao usuario e tenant atuais.
- A listagem inclui `google_calendar` (vinculo local e disponibilidade de token), `connection_url` e o resultado da ultima conexao. Vinculo local nao garante que o acesso nao tenha sido revogado no Google; nesse caso, use Reconectar.
- A resposta de criacao usa HTTP 201 com `data` (agendamento) e `integration` (`status` e `message`).
- `synced`: evento criado e ID salvo localmente. `pending`: registro local salvo, mas sincronizacao nao confirmada.

Agendamentos antigos nao sao enviados automaticamente ao conectar. Use **Sincronizar** individualmente nos pendentes. Uma conta Google vinculada a outro usuario e recusada; para um usuario ja vinculado, so e permitida a reconexao da mesma conta. Os tokens continuam criptografados e o refresh token anterior e preservado quando nao houver outro na resposta.

Novas sincronizacoes usam ID Google deterministico (HMAC com APP_KEY, tenant, usuario, ID local e created_at). Antes de inserir, o servico procura esse ID; em conflito 409, recupera o evento e verifica sua referencia privada. Repetir a solicitacao de um registro ja sincronizado nao cria outro evento. Preserve APP_KEY e os identificadores locais para manter a identidade dos eventos em retentativas.

Eventos criados pela versao anterior com ID aleatorio e cuja gravacao local falhou nao podem ser recuperados por esse novo ID: confira esses casos antigos no Google antes de sincronizar. Eventos remotos cancelados nao sao recriados automaticamente. Nao ha retentativa automatica, edicao ou exclusao de eventos nesta etapa.

O modulo nao cria permissoes ACL nem registros de menu no banco. Usa Controller -> Service -> Repository, com binding de `AgendaRepositoryInterface` em `AppServiceProvider`.
