# Blueprint: Google OAuth e Google Calendar no Laravel 8

Este documento descreve a arquitetura implementada neste Proof of Concept (PoC) para autenticação com Google e sincronização de agendamentos com o Google Calendar. Ele deve ser usado como base para a implementação no sistema definitivo, adaptando a regra de tenancy, os campos de domínio e as URIs do ambiente de produção.

> **Segurança:** nunca versione o `.env`, `APP_KEY`, Client Secret, access token ou refresh token. Se uma credencial for exposta, revogue/rotacione-a no Google Cloud Console.

## 1. Visão geral da arquitetura implementada

### Componentes e responsabilidades

| Camada | Arquivo/componente | Responsabilidade |
|---|---|---|
| Rotas web | `routes/web.php` | Inicia e recebe callbacks OAuth; expõe a rota web de conexão da agenda. |
| Rotas API | `routes/api.php` | Lista, cria e sincroniza agendamentos autenticados. |
| Autenticação | `app/Http/Controllers/Auth/GoogleAuthController.php` | Login/cadastro com Google Socialite e persistência temporária do retorno OAuth. |
| Cadastro SaaS | `app/Http/Controllers/Auth/RegisterController.php` | Cria tenant, perfil, permissões, usuário e conta social em uma única transação. |
| Conexão da agenda | `app/Http/Controllers/GoogleCalendarConnectionController.php` | Solicita consentimento específico para Calendar e valida `state`, usuário, tenant e validade de 10 minutos. |
| Persistência da conexão | `app/Services/GoogleCalendarConnectionService.php` | Atualiza os tokens e impede associação da mesma conta Google a usuários distintos. |
| Calendar API | `app/Services/GoogleCalendarService.php` | Renova token, cria/recupera evento remoto e registra falhas sanitizadas. |
| Regra de agenda | `app/Services/AgendaService.php` | Salva localmente primeiro; sincroniza depois sem perder o agendamento se o Google falhar. |
| Isolamento de dados | `app/Repositories/AgendaRepository.php` | Filtra explicitamente por `tenant_id` e `user_id`. |
| Front-end | `resources/js/pages/Agenda.vue` | Conecta/reconecta a conta e permite sincronizar itens pendentes. |

### Fluxos OAuth usados

Há dois fluxos propositalmente separados:

1. **Login Google (`intent=login`):** pede apenas `openid`, `profile` e `email`. Uma conta já presente em `social_accounts` autentica o usuário Laravel.
2. **Cadastro Google (`intent=registration`):** pede identidade e `https://www.googleapis.com/auth/calendar.events`, com `access_type=offline`, `prompt=consent` e `include_granted_scopes=true`. Após o callback, o usuário ainda informa dados da empresa para concluir a criação do tenant.
3. **Conexão/reconexão da agenda:** mesmo que o usuário tenha entrado por senha ou por Google, a tela Agenda pode solicitar em contexto o escopo `calendar.events`. Isso é a abordagem mais apropriada quando o recurso Calendar é usado pela primeira vez.

O `access_token` é curto; o `refresh_token` viabiliza a renovação sem presença do usuário. Para isso, `access_type=offline` é obrigatório. A URI de retorno precisa corresponder **exatamente** à URI cadastrada no cliente OAuth — incluindo esquema, host, porta, caminho e barra final. Consulte a [documentação OAuth para aplicações web](https://developers.google.com/identity/protocols/oauth2/web-server).

### Fluxo resumido

```mermaid
sequenceDiagram
    actor U as Usuário
    participant L as Laravel / Socialite
    participant G as Google OAuth
    participant DB as Banco de dados
    participant C as Google Calendar API

    U->>L: Login ou cadastro com Google
    L->>G: Redirect com scopes, state e consentimento
    G-->>L: code; Socialite obtém perfil e tokens
    alt cadastro novo
        L->>L: Guarda retorno OAuth cifrado na sessão
        U->>L: Informa dados da empresa
        L->>DB: Cria tenant, role, user e social_account em transação
    else login já vinculado
        L->>DB: Localiza social_account por provider + provider_id
        L->>L: Cria sessão Laravel e contexto do tenant
    end

    U->>L: Conectar/Reconectar Google Calendar
    L->>G: Redirect com calendar.events + offline
    G-->>L: Consentimento e tokens
    L->>DB: Atualiza access/refresh token cifrados

    U->>L: Criar/Sincronizar agendamento
    L->>DB: Salva agenda local
    alt token próximo da expiração
        L->>G: POST /token com refresh_token
        G-->>L: Novo access_token
        L->>DB: Atualiza token e expiração
    end
    L->>C: events.get / events.insert em primary
    C-->>L: Evento confirmado
    L->>DB: Grava google_event_id
```

### Rotas implementadas

| Método | URI | Nome | Uso |
|---|---|---|---|
| GET | `/auth/google/redirect?intent=login` | `google.redirect` | Inicia login Google. |
| GET | `/auth/google/redirect?intent=registration` | `google.redirect` | Inicia cadastro Google com escopo Calendar. |
| GET | `/auth/google/callback` | `google.callback` | Callback de login/cadastro. |
| POST | `/{tenant}/agenda/google/connect` | `google.calendar.connect` | Inicia conexão/reconexão Calendar. Requer `auth`, `tenant` e `clear_route`. |
| GET | `/auth/google/calendar/callback` | `google.calendar.callback` | Callback da conexão Calendar. Requer `auth`. |
| GET | `/api/{tenant}/agenda` | `agenda.index` | Lista agenda e status da conexão. |
| POST | `/api/{tenant}/agenda` | `agenda.store` | Salva localmente e tenta sincronizar. |
| POST | `/api/{tenant}/agenda/{id}/sync` | `agenda.sync` | Reprocessa um agendamento pendente. |

As APIs da agenda usam `auth:sanctum`, `tenant` e `clear_route`. No projeto definitivo, preserve a validação de que o slug de tenant na URL pertence ao usuário autenticado.

## 2. Google Cloud Console: configuração integrada

### 2.1 Criar projeto e ativar APIs

1. Acesse [Google Cloud Console](https://console.cloud.google.com/), crie um projeto para **desenvolvimento/staging** e outro para **produção**.
2. Em **APIs & Services > Library**, ative **Google Calendar API**.
3. Em **Google Auth Platform** (ou **APIs & Services > OAuth consent screen**, dependendo da interface), configure a tela de consentimento.

OAuth 2.0 não exige uma “Google OAuth2 API” separada. O Socialite usa o endpoint OAuth/OpenID Connect do Google para perfil e e-mail. A **People API não é usada pelo código atual**; ative-a somente se o sistema definitivo fizer chamadas próprias a dados adicionais de pessoas.

### 2.2 Configurar OAuth Consent Screen

Preencha nome do app, e-mail de suporte, contato do desenvolvedor, domínio autorizado, Política de Privacidade e Termos de Uso da aplicação definitiva.

Configure os escopos mínimos:

```text
openid
email
profile
https://www.googleapis.com/auth/calendar.events
```

`calendar.events` permite criar, consultar e alterar eventos da agenda do usuário. Não solicite o escopo amplo `calendar` sem necessidade. Solicitar a permissão no momento em que o usuário clica em “Conectar Google Calendar” reduz fricção e segue a recomendação de autorização incremental do Google.

#### Ponto crítico: Testing, Test Users e verificação

O aviso/erro “Acesso bloqueado: o app não concluiu o processo de verificação do Google” é esperado quando o app usa escopos sensíveis, como Calendar, e ainda não foi verificado.

- **Em Testing:** em **Audience > Test users**, adicione todos os e-mails de desenvolvedores, QA e testadores. Apenas esses usuários poderão autorizar. O limite é de 100 usuários de teste.
- **Em Testing, o refresh token expira em sete dias** para escopos como Calendar. Isso foi a causa do `invalid_grant` observado neste PoC após mais de sete dias. Não trate essa duração como comportamento de produção.
- **Em Production:** publique a aplicação. Usuários poderão ver o aviso de app não verificado enquanto a verificação não for concluída; para remover o aviso e operar publicamente com escopos sensíveis, envie o app para verificação e forneça justificativa de escopo, URLs públicas e credenciais/instruções de revisão quando solicitadas pelo Google.
- **Apps internos:** se o sistema for exclusivo de uma organização Google Workspace, avalie o tipo de público **Internal**.

As regras de usuários de teste, publicação e validade de consentimento estão descritas na [documentação de audiência OAuth do Google](https://support.google.com/cloud/answer/15549945). A documentação de troubleshooting do Calendar explica o aviso de aplicativo não verificado e o avanço em ambiente de desenvolvimento: [troubleshoot authentication and authorization](https://developers.google.com/workspace/calendar/api/troubleshoot-authentication-authorization).

### 2.3 Criar credenciais OAuth

1. Em **Google Auth Platform > Clients** ou **APIs & Services > Credentials**, escolha **Create credentials > OAuth client ID**.
2. Selecione **Web application**.
3. Informe um nome identificável, por exemplo `SaaS Produção`.
4. Cadastre as URIs de redirecionamento autorizadas:

```text
# Desenvolvimento local
http://localhost:8000/auth/google/callback
http://localhost:8000/auth/google/calendar/callback

# Produção - substitua pelo domínio real e use HTTPS
https://app.exemplo.com/auth/google/callback
https://app.exemplo.com/auth/google/calendar/callback
```

5. Copie o **Client ID** e o **Client Secret** para o gerenciador de segredos/variáveis do ambiente. Nunca os coloque no JavaScript ou em repositório.

## 3. Configurações do ambiente Laravel

### Dependências Composer

Versões presentes no PoC:

```json
{
  "laravel/framework": "^8.0",
  "laravel/socialite": "5.5.2",
  "google/apiclient": "2.13.2"
}
```

Instalação equivalente:

```bash
composer require laravel/socialite:5.5.2 google/apiclient:2.13.2
```

Socialite cuida do redirect, `state`, troca do código OAuth e dados do perfil. `google/apiclient` cria o cliente autenticado e chama `events.get`/`events.insert`.

### `.env`

Use valores reais somente fora do controle de versão:

```dotenv
APP_URL=https://app.exemplo.com

GOOGLE_CLIENT_ID=xxxxxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=segredo-gerenciado-fora-do-git
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
GOOGLE_CALENDAR_REDIRECT_URI="${APP_URL}/auth/google/calendar/callback"

GOOGLE_CALENDAR_TIMEZONE=America/Sao_Paulo
GOOGLE_CALENDAR_DURATION=30
GOOGLE_CALENDAR_LOG_LEVEL=debug

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=.exemplo.com
```

Para desenvolvimento local, `APP_URL=http://localhost:8000` e `SESSION_SECURE_COOKIE=false` são adequados. Em produção, use HTTPS, `APP_DEBUG=false`, cookie seguro e uma sessão compartilhada/adequada à topologia do ambiente.

Após alterar variáveis em ambiente com cache:

```bash
php artisan config:clear
php artisan config:cache
```

### `config/services.php`

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
    'calendar_redirect' => env('GOOGLE_CALENDAR_REDIRECT_URI'),
    'calendar_timezone' => env('GOOGLE_CALENDAR_TIMEZONE', 'America/Sao_Paulo'),
    'calendar_duration' => env('GOOGLE_CALENDAR_DURATION', 30),
],
```

### Log dedicado

O canal `google_calendar` em `config/logging.php` usa rotação diária por 30 dias:

```php
'google_calendar' => [
    'driver' => 'daily',
    'path' => storage_path('logs/google-calendar.log'),
    'level' => env('GOOGLE_CALENDAR_LOG_LEVEL', 'debug'),
    'days' => 30,
],
```

Os arquivos serão criados como `storage/logs/google-calendar-AAAA-MM-DD.log`. Garanta permissão de escrita para o usuário do PHP-FPM/Apache. Os logs registram IDs técnicos, classes, códigos e respostas de erro limitadas; **não** devem registrar tokens, Client Secret ou conteúdo privado do usuário.

## 4. Código-fonte e estrutura do banco de dados

### 4.1 Persistência: `users`, `social_accounts` e `agendas`

O projeto mantém dados de OAuth em `social_accounts`, e não em `users`. A equivalência com nomenclaturas comuns é:

| Nome usual | Coluna implementada | Motivo |
|---|---|---|
| `google_id` | `provider_id` com `provider = google` | Permite múltiplos provedores futuramente. |
| `avatar` | `avatar` | URL do avatar retornada pelo Google. |
| `google_token` | `access_token` | Token temporário de acesso à API. |
| `google_refresh_token` | `refresh_token` | Renovação offline do access token. |

Migration de `social_accounts`:

```php
Schema::create('social_accounts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('provider');
    $table->string('provider_id');
    $table->string('avatar')->nullable();
    $table->text('access_token');
    $table->text('refresh_token')->nullable();
    $table->timestamp('token_expires_at')->nullable();
    $table->timestamps();

    $table->unique(['provider', 'provider_id']);
    $table->unique(['user_id', 'provider']);
});
```

O model `SocialAccount` cifra `access_token` e `refresh_token` com `Crypt::encryptString()` ao salvar e decifra ao ler. Isto depende diretamente de uma `APP_KEY` estável; não a altere sem uma estratégia de rotação/reautorização.

Os eventos locais usam uma tabela de domínio:

```php
Schema::create('agendas', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->date('data');
    $table->time('hora');
    $table->text('comentario');
    $table->string('google_event_id')->nullable();
    $table->timestamps();
    $table->index(['tenant_id', 'user_id', 'data']);
});
```

`google_event_id = null` significa pendente; um ID preenchido significa que o evento foi confirmado remotamente.

### 4.2 OAuth: redirect e callback

O núcleo do redirect de cadastro solicita acesso offline:

```php
return Socialite::driver('google')
    ->scopes([
        'openid',
        'profile',
        'email',
        'https://www.googleapis.com/auth/calendar.events',
    ])
    ->with([
        'access_type' => 'offline',
        'prompt' => 'consent',
        'include_granted_scopes' => 'true',
    ])
    ->redirect();
```

No callback, o retorno OAuth de um usuário novo é mantido temporariamente e cifrado na sessão até o cadastro de empresa ser concluído:

```php
$request->session()->put('google_registration', [
    'provider_id' => $googleUser->getId(),
    'name' => $googleUser->getName() ?: $googleUser->getNickname(),
    'email' => $googleUser->getEmail(),
    'avatar' => $googleUser->getAvatar(),
    'access_token' => Crypt::encryptString($googleUser->token),
    'refresh_token' => $googleUser->refreshToken
        ? Crypt::encryptString($googleUser->refreshToken)
        : null,
    'token_expires_at' => Carbon::now()
        ->addSeconds((int) $googleUser->expiresIn)
        ->toDateTimeString(),
]);
```

O `RegisterController` cria o tenant e o administrador em `DB::transaction()`, e só então cria `SocialAccount`. Ele não permite trocar o e-mail que foi autorizado no Google. Para uma conta local com e-mail já existente, o PoC não faz vinculação automática apenas pelo e-mail: o proprietário deve autenticar-se e executar uma conexão autenticada. Essa medida evita sequestro de conta por associação indevida.

### 4.3 Conexão Calendar e validação de `state`

Na Agenda, a rota web retorna uma URL OAuth em JSON porque `axios` não deve seguir um redirect OAuth entre domínios. O front-end navega o browser com `window.location.assign(data.url)`.

O controller armazena um contexto de curta duração na sessão:

```php
$request->session()->put('google_calendar_connection', [
    'user_id' => $request->user()->id,
    'tenant_id' => $request->user()->tenant_id,
    'state' => $request->session()->get('state'),
    'expires_at' => time() + 600,
]);
```

No callback, ele compara o `state` com `hash_equals`, verifica expiração, usuário e tenant. Isso complementa a validação de `state` realizada pelo Socialite e protege contra CSRF/troca de contexto OAuth.

`GoogleCalendarConnectionService::connect()` exige que o escopo `calendar.events` esteja entre os escopos aprovados, bloqueia a mesma conta Google em usuários diferentes e preserva o refresh token existente quando o Google não reenviá-lo:

```php
$refreshToken = $googleUser->refreshToken ?: $account->refresh_token;

$account->fill([
    'provider_id' => $googleUser->getId(),
    'avatar' => $googleUser->getAvatar(),
    'access_token' => $googleUser->token,
    'refresh_token' => $refreshToken,
    'token_expires_at' => Carbon::now()
        ->addSeconds((int) ($googleUser->expiresIn ?: 3600)),
])->save();
```

### 4.4 Criação idempotente e renovação de tokens

`GoogleCalendarService` renova o token um minuto antes da expiração:

```php
if (! $account->token_expires_at || $account->token_expires_at->lte(Carbon::now()->addMinute())) {
    $token = $client->fetchAccessTokenWithRefreshToken($account->refresh_token);

    $account->access_token = $token['access_token'];
    $account->token_expires_at = Carbon::now()
        ->addSeconds((int) ($token['expires_in'] ?? 3600));
    $account->save();
}
```

Para evitar duplicidade após timeout, o serviço gera `eventId` determinístico com HMAC de tenant, usuário, agenda, data de criação e `APP_KEY`. Antes de inserir, chama `events.get('primary', $eventId)`. Se não existir (404), insere com o mesmo ID; se receber conflito 409, busca o evento e confirma seus metadados privados:

```php
$event = new Event([
    'id' => $eventId,
    'extendedProperties' => [
        'private' => ['agenda_reference' => $eventId],
    ],
    'summary' => 'Agendamento',
    'description' => $agenda->comentario,
    'start' => ['dateTime' => $start->toRfc3339String(), 'timeZone' => $timezone],
    'end' => ['dateTime' => $end->toRfc3339String(), 'timeZone' => $timezone],
]);

$created = $calendar->events->insert('primary', $event);
```

Após confirmação, `AgendaRepository::saveGoogleEventId()` grava o ID remoto. Se houver falha, `AgendaService` mantém o registro local e devolve status `pending`; o usuário pode reconectar e usar `POST /agenda/{id}/sync`.

### 4.5 Tratamento de `invalid_grant`

Refresh tokens podem expirar, ser revogados pelo usuário, ser invalidados por políticas ou pelo limite de tokens. Quando a resposta contém `invalid_grant`, o serviço:

1. registra a falha no canal `google_calendar`;
2. remove o `refresh_token` local e força `token_expires_at` para o passado;
3. informa que o usuário deve clicar em **Reconectar Google Calendar**;
4. preserva o agendamento local como pendente.

Esse comportamento é obrigatório em produção: refresh tokens podem deixar de funcionar a qualquer momento. Veja [boas práticas OAuth do Google](https://developers.google.com/identity/protocols/oauth2/resources/best-practices).

## 5. Checklist para replicação no novo sistema

- [ ] Criar projetos Google Cloud independentes para desenvolvimento/staging e produção.
- [ ] Ativar Google Calendar API no projeto correto.
- [ ] Configurar OAuth Consent Screen, dados de marca, domínios, Política de Privacidade e Termos de Uso.
- [ ] Definir `openid`, `email`, `profile` e `calendar.events` como escopos necessários.
- [ ] Em Testing, adicionar todos os desenvolvedores/QA em **Test users**.
- [ ] Para produção pública, publicar o app e concluir a verificação Google necessária para escopos sensíveis.
- [ ] Criar cliente OAuth do tipo **Web application**.
- [ ] Cadastrar as duas redirect URIs de cada ambiente, com correspondência exata.
- [ ] Instalar `laravel/socialite` e `google/apiclient` em versões compatíveis com a versão do Laravel/PHP do sistema definitivo.
- [ ] Adicionar as variáveis `GOOGLE_*` ao secret manager e ao `.env` de cada ambiente, sem commitar valores.
- [ ] Mapear `services.google` em `config/services.php`.
- [ ] Criar migration/model `social_accounts`, com unicidade por `provider + provider_id` e `user_id + provider`.
- [ ] Cifrar access e refresh tokens em repouso; planejar retenção e rotação de `APP_KEY`.
- [ ] Implementar o callback Socialite com `state`; não usar `stateless()` em aplicação web baseada em sessão.
- [ ] Solicitar `access_type=offline`, `prompt=consent` e preservar refresh token anterior se o provedor não enviar outro.
- [ ] Separar login básico da autorização Calendar incremental, ou documentar conscientemente a decisão de combiná-los.
- [ ] Criar tabela local de eventos/agendamentos com `google_event_id` e relação com usuário/tenant.
- [ ] Isolar todas as consultas por tenant e usuário autenticado.
- [ ] Criar serviço Google que renove token antes de expirar, trate `invalid_grant` e peça reconexão.
- [ ] Usar ID remoto determinístico/idempotente e confirmar evento antes de marcar sincronizado localmente.
- [ ] Salvar localmente antes da sincronização e oferecer reprocessamento dos itens pendentes.
- [ ] Configurar canal de logs específico, sem tokens ou conteúdo pessoal sensível.
- [ ] Garantir permissão de escrita em `storage/logs` e monitorar `google-calendar-*.log`.
- [ ] Executar `php artisan migrate --force` no deploy, sem `migrate:fresh` em produção.
- [ ] Executar `php artisan config:cache` após injetar as variáveis de ambiente em produção.
- [ ] Testar: cadastro Google, login Google, cadastro por senha + conexão Calendar, criação, sincronização manual, expiração/revogação e reconexão.
