<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\PaymentTransaction;
use App\Models\QuotaTransaction;
use App\Models\HospitalityAuthorization;
use App\Services\NotificationService;
use App\Services\FileUploadService;

class PaymentController extends Controller
{
    protected $notificationService;
    protected $fileUploadService;

    public function __construct(NotificationService $notificationService, FileUploadService $fileUploadService)
    {
        $this->notificationService = $notificationService;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Show payment page after user clicks "QUERO"
     */
    public function show(QuotaTransaction $transaction)
    {
        $user = Auth::user();
        
        // Verificar se usuário tem permissão
        if ($transaction->renter_id !== $user->id) {
            return redirect()->back()->with('error', 'Você não tem permissão para acessar este pagamento.');
        }

        $transaction->load(['quota', 'owner', 'renter']);
        
        // Buscar ou criar payment transaction
        $paymentTransaction = PaymentTransaction::firstOrCreate(
            ['transaction_id' => $transaction->id],
            [
                'user_id' => $user->id,
                'amount' => $transaction->total_amount,
                'fees' => $transaction->platform_fee,
                'total_amount' => $transaction->total_amount + $transaction->platform_fee,
                'payment_due_at' => now()->addHours(12), // Prazo de 12h
            ]
        );

        // Notificar ofertante
        $this->notificationService->notifyOfferOwner(
            $transaction->owner,
            $transaction->quota,
            $user
        );

        return view('payments.show', compact('transaction', 'paymentTransaction'));
    }

    /**
     * Process payment (fictício)
     */
    public function process(Request $request, QuotaTransaction $transaction)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:credit_card,debit_card,pix,bank_transfer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $paymentTransaction = PaymentTransaction::where('transaction_id', $transaction->id)->first();
        
        if (!$paymentTransaction) {
            return redirect()->back()->with('error', 'Transação de pagamento não encontrada.');
        }

        // Simular processamento de pagamento
        $paymentTransaction->update([
            'payment_method' => $request->payment_method,
            'status' => 'completed',
            'payment_completed_at' => now(),
            'payment_reference' => 'PAY-' . strtoupper(uniqid()),
        ]);

        // Atualizar transação
        $transaction->update([
            'payment_status' => 'completed',
            'status' => 'completed',
        ]);

        // Criar autorização de hospedagem
        $authorization = HospitalityAuthorization::create([
            'quota_id' => $transaction->quota_id,
            'guest_user_id' => $user->id,
            'authorization_code' => HospitalityAuthorization::generateAuthorizationCode(),
            'guest_name' => $user->name,
            'guest_email' => $user->email,
            'check_in_date' => $transaction->quota->start_date,
            'check_out_date' => $transaction->quota->end_date,
            'number_of_guests' => $transaction->quota->number_of_guests,
            'status' => 'approved',
            'approved_at' => now(),
            'expires_at' => $transaction->quota->end_date->addDays(1),
        ]);

        // Enviar documentos para hotel (simulado)
        // Em produção, integrar com API do hotel

        return redirect()->route('payments.success', $transaction)
            ->with('success', 'Pagamento processado com sucesso!');
    }

    /**
     * Show authorization upload page (for offer owner)
     */
    public function showAuthorization(QuotaTransaction $transaction)
    {
        $user = Auth::user();
        
        if ($transaction->owner_id !== $user->id) {
            return redirect()->back()->with('error', 'Você não tem permissão.');
        }

        $paymentTransaction = PaymentTransaction::where('transaction_id', $transaction->id)->first();
        
        return view('payments.authorization', compact('transaction', 'paymentTransaction'));
    }

    /**
     * Upload authorization document and video
     */
    public function uploadAuthorization(Request $request, QuotaTransaction $transaction)
    {
        $user = Auth::user();
        
        if ($transaction->owner_id !== $user->id) {
            return redirect()->back()->with('error', 'Você não tem permissão.');
        }

        $validator = Validator::make($request->all(), [
            'authorization_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'video' => 'required|file|mimes:mp4,avi,mov|max:10240',
            'sent_at_hour' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $paymentTransaction = PaymentTransaction::where('transaction_id', $transaction->id)->first();
        
        // Upload documento
        $documentPath = $request->file('authorization_document')->store('authorizations', 'public');
        
        // Upload vídeo
        $videoPath = $request->file('video')->store('videos', 'public');

        $paymentTransaction->update([
            'authorization_document_path' => $documentPath,
            'video_path' => $videoPath,
            'sent_at_hour' => $request->has('sent_at_hour'),
        ]);

        // Verificar se foi enviado "NA HORA" (12h)
        $hoursElapsed = now()->diffInHours($paymentTransaction->created_at);
        if ($hoursElapsed > 12) {
            // Aplicar bloqueio de 24h
            $paymentTransaction->applyBlock('Autorização enviada após prazo de 12h', 24);
        }

        // Notificar interessado que pode pagar
        $this->notificationService->notifyPaymentDue(
            $transaction->renter,
            $transaction,
            12
        );

        return redirect()->route('payments.authorization', $transaction)
            ->with('success', 'Autorização e vídeo enviados com sucesso!');
    }

    /**
     * Show success page
     */
    public function success(QuotaTransaction $transaction)
    {
        $user = Auth::user();
        
        if ($transaction->renter_id !== $user->id) {
            return redirect()->back();
        }

        $paymentTransaction = PaymentTransaction::where('transaction_id', $transaction->id)->first();
        $authorization = HospitalityAuthorization::where('quota_id', $transaction->quota_id)
            ->where('guest_user_id', $user->id)
            ->first();

        return view('payments.success', compact('transaction', 'paymentTransaction', 'authorization'));
    }
}
