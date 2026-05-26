<?php

namespace App\Services\Asaas;

use App\Models\AsaasSubaccount;
use App\Models\AsaasWalletTransfer;
use App\Models\QuotaTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AsaasSubaccountService
{
    public function __construct(
        protected AsaasClient $client
    ) {}

    /**
     * Garante subconta Asaas para o cotista (idempotente por usuário).
     */
    public function ensureForUser(User $user, ?QuotaTransaction $transaction = null): AsaasSubaccount
    {
        $existing = AsaasSubaccount::where('user_id', $user->id)->first();
        if ($existing?->isActive()) {
            return $existing;
        }

        if (!$this->client->isConfigured()) {
            return $this->storePlaceholder($user, $transaction, 'Integração Asaas não configurada (ASAAS_API_KEY).');
        }

        $profile = $user->profile;
        if (!$profile || empty($profile->cpf)) {
            return $this->storePlaceholder($user, $transaction, 'Complete o CPF no perfil para ativar a carteira digital.');
        }

        $payload = $this->buildSubaccountPayload($user);
        if ($payload === null) {
            return $this->storePlaceholder($user, $transaction, 'Dados de endereço incompletos no perfil para criar a subconta.');
        }

        $response = $this->client->post('/v3/accounts', $payload);

        if (!$response->successful()) {
            $message = $this->extractErrorMessage($response->json()) ?? 'Erro ao criar subconta no Asaas.';
            Log::error('Asaas: falha ao criar subconta', ['user_id' => $user->id, 'response' => $response->json()]);

            return $this->upsertFailed($user, $transaction, $message);
        }

        $data = $response->json();

        return DB::transaction(function () use ($user, $transaction, $data) {
            return AsaasSubaccount::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'asaas_account_id' => $data['id'] ?? null,
                    'wallet_id' => $data['walletId'] ?? null,
                    'api_key' => $data['apiKey'] ?? null,
                    'status' => !empty($data['walletId']) ? AsaasSubaccount::STATUS_ACTIVE : AsaasSubaccount::STATUS_PENDING,
                    'last_error' => null,
                    'created_from_transaction_id' => $transaction?->id,
                ]
            );
        });
    }

    public function getBalance(AsaasSubaccount $subaccount, bool $refresh = true): float
    {
        if (!$subaccount->isActive()) {
            return (float) ($subaccount->cached_balance ?? 0);
        }

        if (!$refresh && $subaccount->cached_balance !== null && $subaccount->balance_synced_at?->gt(now()->subMinutes(2))) {
            return (float) $subaccount->cached_balance;
        }

        $response = $this->client->get('/v3/finance/balance', [], $subaccount->api_key);

        if (!$response->successful()) {
            return (float) ($subaccount->cached_balance ?? 0);
        }

        $balance = (float) ($response->json('balance') ?? 0);
        $subaccount->update([
            'cached_balance' => $balance,
            'balance_synced_at' => now(),
        ]);

        return $balance;
    }

    public function executeTransfer(AsaasWalletTransfer $transfer): AsaasWalletTransfer
    {
        $masterWallet = config('asaas.master_wallet_id');
        if (empty($masterWallet)) {
            throw new \RuntimeException('Carteira principal não configurada (ASAAS_MASTER_WALLET_ID).');
        }

        $subaccount = $transfer->subaccount;
        if (!$subaccount?->isActive()) {
            throw new \RuntimeException('Sua carteira digital ainda não está ativa.');
        }

        $transfer->update([
            'status' => AsaasWalletTransfer::STATUS_PROCESSING,
            'destination_wallet_id' => $masterWallet,
        ]);

        $response = $this->client->post('/v3/transfers', [
            'value' => round((float) $transfer->amount, 2),
            'walletId' => $masterWallet,
            'description' => 'Transferência Cota Brasilis #'.$transfer->id,
        ], $subaccount->api_key);

        if ($response->successful()) {
            $data = $response->json();
            $transfer->update([
                'status' => AsaasWalletTransfer::STATUS_COMPLETED,
                'asaas_transfer_id' => $data['id'] ?? null,
                'asaas_response' => $data,
                'completed_at' => now(),
            ]);
            $this->getBalance($subaccount, true);

            return $transfer->fresh();
        }

        $message = $this->extractErrorMessage($response->json()) ?? 'Não foi possível concluir a transferência.';
        $transfer->update([
            'status' => AsaasWalletTransfer::STATUS_FAILED,
            'error_message' => $message,
            'asaas_response' => $response->json(),
        ]);

        throw new \RuntimeException($message);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function buildSubaccountPayload(User $user): ?array
    {
        $profile = $user->profile;
        if (!$profile) {
            return null;
        }

        $cpfCnpj = preg_replace('/\D/', '', (string) $profile->cpf);
        $cep = preg_replace('/\D/', '', (string) $profile->cep);
        $phone = preg_replace('/\D/', '', (string) ($user->whatsapp ?: $profile->phone));

        if (strlen($cpfCnpj) < 11 || empty($profile->street) || empty($profile->city) || empty($cep)) {
            return null;
        }

        $payload = [
            'name' => $profile->full_name ?: $user->name,
            'email' => $user->email,
            'cpfCnpj' => $cpfCnpj,
            'mobilePhone' => $this->formatPhone($phone),
            'incomeValue' => config('asaas.default_income_value'),
            'address' => $profile->street,
            'addressNumber' => $profile->house_number ?: 'S/N',
            'province' => $profile->neighborhood ?: 'Centro',
            'postalCode' => $this->formatCep($cep),
            'complement' => $profile->complement,
            'city' => $profile->city,
            'state' => strtoupper(substr((string) $profile->state, 0, 2)),
        ];

        if (strlen($cpfCnpj) === 11) {
            $payload['birthDate'] = config('asaas.default_birth_date');
        }

        if (strlen($cpfCnpj) === 14) {
            $payload['companyType'] = 'LIMITED';
        }

        return array_filter($payload, fn ($v) => $v !== null && $v !== '');
    }

    protected function formatPhone(string $digits): string
    {
        if (strlen($digits) < 10) {
            return '11999999999';
        }
        if (!str_starts_with($digits, '55') && strlen($digits) <= 11) {
            return $digits;
        }

        return $digits;
    }

    protected function formatCep(string $cep): string
    {
        if (strlen($cep) === 8) {
            return substr($cep, 0, 5).'-'.substr($cep, 5);
        }

        return $cep;
    }

    /**
     * @param  array<string, mixed>|null  $json
     */
    protected function extractErrorMessage(?array $json): ?string
    {
        $errors = $json['errors'] ?? null;
        if (!is_array($errors) || $errors === []) {
            return null;
        }

        return collect($errors)->pluck('description')->filter()->implode(' ');
    }

    protected function storePlaceholder(User $user, ?QuotaTransaction $transaction, string $message): AsaasSubaccount
    {
        return AsaasSubaccount::updateOrCreate(
            ['user_id' => $user->id],
            [
                'status' => AsaasSubaccount::STATUS_FAILED,
                'last_error' => $message,
                'created_from_transaction_id' => $transaction?->id,
            ]
        );
    }

    protected function upsertFailed(User $user, ?QuotaTransaction $transaction, string $message): AsaasSubaccount
    {
        return AsaasSubaccount::updateOrCreate(
            ['user_id' => $user->id],
            [
                'status' => AsaasSubaccount::STATUS_FAILED,
                'last_error' => $message,
                'created_from_transaction_id' => $transaction?->id,
            ]
        );
    }
}
