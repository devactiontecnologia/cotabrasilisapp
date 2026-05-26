<?php

namespace App\Http\Controllers;

use App\Models\AsaasWalletTransfer;
use App\Services\Asaas\AsaasSubaccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AsaasWalletController extends Controller
{
    public function __construct(
        protected AsaasSubaccountService $subaccountService
    ) {}

    public function showTransferForm()
    {
        $user = Auth::user();
        $subaccount = $user->asaasSubaccount;
        $walletActive = $subaccount?->isActive() ?? false;
        $balance = $walletActive
            ? $this->subaccountService->getBalance($subaccount)
            : (float) ($subaccount?->cached_balance ?? 0);
        $walletMessage = $walletActive
            ? null
            : ($subaccount?->last_error ?? 'Ative sua carteira iniciando um aluguel, troca ou compra (Quero alugar / trocar / comprar).');

        return view('wallet.transfer-form', compact('subaccount', 'balance', 'walletActive', 'walletMessage'));
    }

    public function storeTransfer(Request $request)
    {
        $user = Auth::user();
        $subaccount = $user->asaasSubaccount;

        if (!$subaccount?->isActive()) {
            throw ValidationException::withMessages([
                'amount' => 'Carteira digital indisponível.',
            ]);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
        ], [
            'amount.required' => 'Informe o valor da transferência.',
            'amount.min' => 'O valor mínimo é R$ 0,01.',
        ]);

        $amount = round((float) $validated['amount'], 2);
        $balance = $this->subaccountService->getBalance($subaccount);

        if ($amount > $balance) {
            throw ValidationException::withMessages([
                'amount' => 'Saldo insuficiente para esta transferência.',
            ]);
        }

        $transfer = AsaasWalletTransfer::create([
            'user_id' => $user->id,
            'asaas_subaccount_id' => $subaccount->id,
            'amount' => $amount,
            'destination_wallet_id' => config('asaas.master_wallet_id', ''),
            'status' => AsaasWalletTransfer::STATUS_PENDING,
        ]);

        return redirect()->route('wallet.transfer.processing', $transfer);
    }

    public function processing(AsaasWalletTransfer $transfer)
    {
        $this->authorizeTransfer($transfer);
        $transfer->load('subaccount');

        if ($transfer->status === AsaasWalletTransfer::STATUS_COMPLETED) {
            return redirect()->route('wallet.transfer.confirmation', $transfer);
        }

        if ($transfer->status === AsaasWalletTransfer::STATUS_FAILED) {
            return redirect()->route('wallet.transfer.form')
                ->with('error', $transfer->error_message ?? 'Transferência não concluída.');
        }

        if ($transfer->status === AsaasWalletTransfer::STATUS_PENDING) {
            try {
                $this->subaccountService->executeTransfer($transfer);
            } catch (\RuntimeException $e) {
                return redirect()->route('wallet.transfer.form')
                    ->with('error', $e->getMessage());
            }

            return redirect()->route('wallet.transfer.confirmation', $transfer->fresh());
        }

        return view('wallet.transfer-processing', compact('transfer'));
    }

    public function confirmation(AsaasWalletTransfer $transfer)
    {
        $this->authorizeTransfer($transfer);

        if ($transfer->status !== AsaasWalletTransfer::STATUS_COMPLETED) {
            return redirect()->route('wallet.transfer.processing', $transfer);
        }

        $balance = $transfer->subaccount
            ? $this->subaccountService->getBalance($transfer->subaccount)
            : 0;

        return view('wallet.transfer-confirmation', compact('transfer', 'balance'));
    }

    protected function authorizeTransfer(AsaasWalletTransfer $transfer): void
    {
        if ($transfer->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
