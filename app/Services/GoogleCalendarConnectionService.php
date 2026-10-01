<?php

namespace App\Services;

use App\Models\SocialAccount;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoogleCalendarConnectionService
{
    public const SCOPE = 'https://www.googleapis.com/auth/calendar.events';

    public function status(User $user): array
    {
        $account = SocialAccount::where('user_id', $user->id)->where('provider', 'google')->first();

        // O vinculo local nao garante que a autorizacao nao tenha sido revogada no Google.
        return [
            'linked' => (bool) $account,
            'ready' => $account && ($account->refresh_token || ($account->token_expires_at && $account->token_expires_at->isFuture())),
        ];
    }

    public function connect(User $user, $googleUser): void
    {
        if (!$googleUser->getId() || !$googleUser->token
            || !in_array(self::SCOPE, $googleUser->approvedScopes ?? [], true)) {
            throw ValidationException::withMessages(['google' => 'Autorize o acesso aos eventos do Google Calendar para conectar a agenda.']);
        }

        DB::transaction(function () use ($user, $googleUser) {
            // Serializa conexoes do mesmo usuario; as constraints UNIQUE protegem o provider_id.
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $owner = SocialAccount::where('provider', 'google')->where('provider_id', $googleUser->getId())->first();
            if ($owner && (string) $owner->user_id !== (string) $user->id) {
                throw ValidationException::withMessages(['google' => 'Esta conta Google já está vinculada a outro usuário.']);
            }

            $account = SocialAccount::where('provider', 'google')->where('user_id', $user->id)->first();
            if ($account && (string) $account->provider_id !== (string) $googleUser->getId()) {
                throw ValidationException::withMessages(['google' => 'Reconecte a mesma conta Google já vinculada ao seu usuário.']);
            }

            $account = $account ?: new SocialAccount(['provider' => 'google', 'user_id' => $user->id]);
            $refreshToken = $googleUser->refreshToken ?: $account->refresh_token;
            if (!$refreshToken) {
                throw ValidationException::withMessages(['google' => 'Não foi concedido acesso offline. Tente conectar novamente e autorize o calendário.']);
            }

            $account->fill([
                'provider_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'access_token' => $googleUser->token,
                'refresh_token' => $refreshToken,
                'token_expires_at' => Carbon::now()->addSeconds((int) ($googleUser->expiresIn ?: 3600)),
            ])->save();
        });
    }
}
