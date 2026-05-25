<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Quota;
use App\Models\QuotaTransaction;
use App\Models\DigitalContract;
use App\Models\Hotel;
use App\Models\RentalOffer;
use App\Services\EmailService;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get transactions where user is either owner or renter
        $transactions = QuotaTransaction::where('owner_id', $user->id)
            ->orWhere('renter_id', $user->id)
            ->with(['quota', 'owner', 'renter'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('transactions.index', compact('transactions'));
    }

    /**
     * Show the specified transaction.
     */
    public function show(QuotaTransaction $transaction)
    {
        $user = Auth::user();
        
        // Check if user is involved in this transaction
        if ($transaction->owner_id !== $user->id && $transaction->renter_id !== $user->id) {
            return redirect()->route('transactions.index')
                ->with('error', 'Você não tem permissão para visualizar esta transação.');
        }

        $transaction->load(['quota', 'owner', 'renter', 'digitalContract']);

        return view('transactions.show', compact('transaction'));
    }

    /**
     * Show negotiation start page (rent or buy).
     */
    public function startNegotiation(Request $request, Quota $quota)
    {
        $user = Auth::user();
        $transactionType = $request->get('type', 'rent'); // 'rent' or 'buy'
        
        // Check if user can rent/buy
        if ($quota->user_id === $user->id) {
            return redirect()->back()
                ->with('error', 'Você não pode alugar/comprar sua própria cota.');
        }

        if ($quota->status !== Quota::STATUS_AVAILABLE) {
            return redirect()->back()
                ->with('error', 'Esta cota não está mais disponível.');
        }

        // Block rental if linked hotel is not functioning
        $hotel = null;
        if (!empty($quota->hotel_id)) {
            $hotel = Hotel::find($quota->hotel_id);
        }
        if (!$hotel && !empty($quota->hotel_name)) {
            $hotel = Hotel::where('name', $quota->hotel_name)->first();
        }
        if ($hotel && !$hotel->is_functioning) {
            return redirect()->back()->with('error', 'Hotel inoperante: aluguel indisponível.');
        }

        return view('transactions.start-negotiation', compact('quota', 'transactionType'));
    }

    /**
     * Rent a quota - Start negotiation.
     */
    public function rent(Request $request, Quota $quota)
    {
        $user = Auth::user();
        
        // Check if user can rent
        if ($quota->user_id === $user->id) {
            return redirect()->back()
                ->with('error', 'Você não pode alugar sua própria cota.');
        }

        if ($quota->status !== Quota::STATUS_AVAILABLE && $quota->status !== Quota::STATUS_NEGOTIATING) {
            return redirect()->back()
                ->with('error', 'Esta cota não está mais disponível.');
        }

        // Se já está em negociação, verificar se é a mesma pessoa
        if ($quota->status === Quota::STATUS_NEGOTIATING) {
            if ($quota->current_transaction_id) {
                $currentTransaction = QuotaTransaction::find($quota->current_transaction_id);
                if ($currentTransaction && $currentTransaction->renter_id !== $user->id) {
                    return redirect()->back()
                        ->with('error', 'Esta cota já está em negociação com outro usuário.');
                }
            }
        }

        // Prazo de 60h para o proprietário enviar o documento
        $documentDeadlineHours = 60;
        $negotiationDeadline = now()->addHours($documentDeadlineHours);

        // Determine total amount (allow override for fraction period)
        $totalAmount = $request->input('total_amount', $quota->rental_price);

        $guestNames = $this->parseGuestNamesFromRequest($request);

        // Create transaction (fluxo: aguardar doc do proprietário -> interessado assina -> proprietário finaliza -> pagar taxa de êxito -> comprovante)
        $transaction = QuotaTransaction::create([
            'quota_id' => $quota->id,
            'renter_id' => $user->id,
            'owner_id' => $quota->user_id,
            'transaction_type' => 'rental',
            'total_amount' => $totalAmount,
            'owner_amount' => $totalAmount * 0.95,
            'platform_fee' => $totalAmount * 0.05,
            'status' => QuotaTransaction::STATUS_NEGOTIATING,
            'payment_status' => QuotaTransaction::PAYMENT_PENDING,
            'transaction_date' => now(),
            'negotiation_started_at' => now(),
            'negotiation_deadline' => $negotiationDeadline,
            'document_upload_deadline' => now()->addHours($documentDeadlineHours),
            'payment_deadline_hours' => 24,
            'document_deadline_hours' => $documentDeadlineHours,
            'workflow_step' => QuotaTransaction::WORKFLOW_AWAITING_OWNER_DOC,
            'guest_names' => $guestNames,
        ]);

        $quota->update([
            'status' => Quota::STATUS_NEGOTIATING,
            'negotiation_deadline' => $negotiationDeadline,
            'current_transaction_id' => $transaction->id,
        ]);

        DigitalContract::create([
            'transaction_id' => $transaction->id,
            'contract_type' => 'rental_agreement',
            'contract_content' => $this->generateRentalContract($transaction),
            'is_completed' => false,
        ]);

        return redirect()->route('transactions.waiting-document', $transaction)
            ->with('success', 'Interesse registrado! Aguarde o proprietário enviar o documento em até 60 horas.');
    }

    /**
     * Buy a quota - Start negotiation.
     */
    public function buy(Request $request, Quota $quota)
    {
        $user = Auth::user();
        
        // Check if user can buy
        if ($quota->user_id === $user->id) {
            return redirect()->back()
                ->with('error', 'Você não pode comprar sua própria cota.');
        }

        if ($quota->status !== Quota::STATUS_AVAILABLE && $quota->status !== Quota::STATUS_NEGOTIATING) {
            return redirect()->back()
                ->with('error', 'Esta cota não está mais disponível.');
        }

        // Se já está em negociação, verificar se é a mesma pessoa
        if ($quota->status === Quota::STATUS_NEGOTIATING) {
            if ($quota->current_transaction_id) {
                $currentTransaction = QuotaTransaction::find($quota->current_transaction_id);
                if ($currentTransaction && $currentTransaction->renter_id !== $user->id) {
                    return redirect()->back()
                        ->with('error', 'Esta cota já está em negociação com outro usuário.');
                }
            }
        }

        $totalAmount = $request->input('total_amount', $quota->rental_price ?? 0);
        $documentDeadlineHours = 60;
        $negotiationDeadline = now()->addHours($documentDeadlineHours);

        $guestNames = $this->parseGuestNamesFromRequest($request);

        $transaction = QuotaTransaction::create([
            'quota_id' => $quota->id,
            'renter_id' => $user->id,
            'owner_id' => $quota->user_id,
            'transaction_type' => 'rental',
            'total_amount' => $totalAmount,
            'owner_amount' => $totalAmount * 0.95,
            'platform_fee' => $totalAmount * 0.05,
            'status' => QuotaTransaction::STATUS_NEGOTIATING,
            'payment_status' => QuotaTransaction::PAYMENT_PENDING,
            'transaction_date' => now(),
            'negotiation_started_at' => now(),
            'negotiation_deadline' => $negotiationDeadline,
            'document_upload_deadline' => now()->addHours($documentDeadlineHours),
            'payment_deadline_hours' => 24,
            'document_deadline_hours' => $documentDeadlineHours,
            'workflow_step' => QuotaTransaction::WORKFLOW_AWAITING_OWNER_DOC,
            'guest_names' => $guestNames,
        ]);

        $quota->update([
            'status' => Quota::STATUS_NEGOTIATING,
            'negotiation_deadline' => $negotiationDeadline,
            'current_transaction_id' => $transaction->id,
        ]);

        DigitalContract::create([
            'transaction_id' => $transaction->id,
            'contract_type' => 'rental_agreement',
            'contract_content' => $this->generateRentalContract($transaction),
            'is_completed' => false,
        ]);

        return redirect()->route('transactions.waiting-document', $transaction)
            ->with('success', 'Interesse registrado! Aguarde o proprietário enviar o documento em até 60 horas.');
    }

    /**
     * Parse and normalize guest names/CPF from request (guests[0][name], guests[0][cpf], ...).
     *
     * @return array<int, array{name: string, cpf: string}>
     */
    private function parseGuestNamesFromRequest(Request $request): array
    {
        $guests = $request->input('guests', []);
        if (!is_array($guests)) {
            return [];
        }
        $result = [];
        foreach ($guests as $row) {
            $name = is_array($row) ? trim((string) ($row['name'] ?? '')) : '';
            $cpf = is_array($row) ? preg_replace('/\D/', '', (string) ($row['cpf'] ?? '')) : '';
            if ($name !== '' || $cpf !== '') {
                $result[] = [
                    'name' => $name,
                    'cpf' => $cpf,
                ];
            }
        }
        return $result;
    }

    /**
     * Exchange a quota.
     */
    public function exchange(Request $request, Quota $quota)
    {
        $user = Auth::user();
        
        // Check if user can exchange
        if ($quota->user_id === $user->id) {
            return redirect()->back()
                ->with('error', 'Você não pode trocar sua própria cota.');
        }

        if ($quota->status !== Quota::STATUS_AVAILABLE) {
            return redirect()->back()
                ->with('error', 'Esta cota não está mais disponível.');
        }

        // If hotel not functioning, permitir apenas troca de titularidade (não troca de hospedagem)
        $hotel = null;
        if (!empty($quota->hotel_id)) {
            $hotel = Hotel::find($quota->hotel_id);
        }
        if (!$hotel && !empty($quota->hotel_name)) {
            $hotel = Hotel::where('name', $quota->hotel_name)->first();
        }
        if ($hotel && !$hotel->is_functioning) {
            return redirect()->back()->with('error', 'Hotel inoperante: trocas de hospedagem indisponíveis (apenas titularidade).');
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'exchange_quota_id' => 'required|exists:quotas,id',
        ], [
            'exchange_quota_id.required' => 'Selecione uma cota para troca.',
            'exchange_quota_id.exists' => 'Cota selecionada não existe.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $exchangeQuota = Quota::findOrFail($request->exchange_quota_id);

        // Check if exchange quota belongs to user
        if ($exchangeQuota->user_id !== $user->id) {
            return redirect()->back()
                ->with('error', 'Você só pode trocar suas próprias cotas.');
        }

        if ($exchangeQuota->status !== Quota::STATUS_AVAILABLE) {
            return redirect()->back()
                ->with('error', 'Sua cota para troca não está disponível.');
        }

        // Create transaction
        $transaction = QuotaTransaction::create([
            'quota_id' => $quota->id,
            'renter_id' => $user->id,
            'owner_id' => $quota->user_id,
            'transaction_type' => 'exchange',
            'total_amount' => 0,
            'owner_amount' => 0,
            'platform_fee' => 0,
            'status' => 'pending',
            'payment_method' => 'exchange',
            'payment_status' => 'completed',
            'transaction_date' => now(),
        ]);

        // Update quota statuses
        $quota->update(['status' => Quota::STATUS_RENTED]);
        $exchangeQuota->update(['status' => Quota::STATUS_RENTED]);

        // Create digital contract
        DigitalContract::create([
            'transaction_id' => $transaction->id,
            'contract_type' => 'exchange_agreement',
            'contract_content' => $this->generateExchangeContract($transaction, $exchangeQuota),
            'is_completed' => false,
        ]);

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Solicitação de troca criada com sucesso!');
    }

    /**
     * Sign a digital contract.
     */
    public function signContract(Request $request, QuotaTransaction $transaction)
    {
        $user = Auth::user();
        
        // Check if user is involved in this transaction
        if ($transaction->owner_id !== $user->id && $transaction->renter_id !== $user->id) {
            return redirect()->route('transactions.index')
                ->with('error', 'Você não tem permissão para assinar este contrato.');
        }

        $digitalContract = $transaction->digitalContract;
        
        if (!$digitalContract) {
            return redirect()->back()
                ->with('error', 'Contrato digital não encontrado.');
        }

        // Validate signature
        $validator = Validator::make($request->all(), [
            'signature' => 'required|string|min:10',
        ], [
            'signature.required' => 'Assinatura é obrigatória.',
            'signature.min' => 'Assinatura deve ter pelo menos 10 caracteres.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        // Update signature
        if ($transaction->owner_id === $user->id) {
            $digitalContract->update([
                'owner_signature' => $request->signature,
                'owner_signed_at' => now(),
            ]);
        } else {
            $digitalContract->update([
                'renter_signature' => $request->signature,
                'renter_signed_at' => now(),
            ]);
        }

        // Check if both parties signed
        if ($digitalContract->owner_signature && $digitalContract->renter_signature) {
            $digitalContract->update(['is_completed' => true]);
            $transaction->update(['status' => 'completed']);
        }

        return redirect()->back()
            ->with('success', 'Contrato assinado com sucesso!');
    }

    /**
     * Tela do interessado: aguardar documento do proprietário (60h). Atualização via AJAX.
     */
    public function waitingDocument(QuotaTransaction $transaction)
    {
        $user = Auth::user();
        if ($transaction->renter_id !== $user->id) {
            return redirect()->route('transactions.index')->with('error', 'Acesso negado.');
        }
        $transaction->load(['quota', 'owner', 'renter']);
        return view('transactions.waiting-document', compact('transaction'));
    }

    /**
     * Download do documento enviado pelo proprietário (interessado ou proprietário autenticado).
     */
    public function downloadDocument(QuotaTransaction $transaction)
    {
        $user = Auth::user();
        if ($transaction->renter_id !== $user->id && $transaction->owner_id !== $user->id) {
            abort(403, 'Acesso negado.');
        }
        if (empty($transaction->document_path)) {
            abort(404, 'Documento não disponível.');
        }
        $path = $transaction->document_path;
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'Arquivo não encontrado.');
        }
        $name = 'termo-autorizacao-hospedagem.' . (pathinfo($path, PATHINFO_EXTENSION) ?: 'pdf');
        return Storage::disk('public')->download($path, $name);
    }

    /**
     * Download do documento assinado pelo interessado (gov.br) — proprietário ou interessado.
     */
    public function downloadRenterSignedDocument(QuotaTransaction $transaction)
    {
        $user = Auth::user();
        if ($transaction->renter_id !== $user->id && $transaction->owner_id !== $user->id) {
            abort(403, 'Acesso negado.');
        }
        $path = $transaction->renter_signed_document_path ?? '';
        if (empty($path) || !Storage::disk('public')->exists($path)) {
            abort(404, 'Documento não encontrado.');
        }
        $name = 'documento-assinado-interessado.' . (pathinfo($path, PATHINFO_EXTENSION) ?: 'pdf');
        return Storage::disk('public')->download($path, $name);
    }

    /**
     * Download do documento assinado pelo proprietário — proprietário ou interessado.
     */
    public function downloadOwnerSignedDocument(QuotaTransaction $transaction)
    {
        $user = Auth::user();
        if ($transaction->renter_id !== $user->id && $transaction->owner_id !== $user->id) {
            abort(403, 'Acesso negado.');
        }
        $path = $transaction->owner_signed_document_path ?? '';
        if (empty($path) || !Storage::disk('public')->exists($path)) {
            abort(404, 'Documento não encontrado.');
        }
        $name = 'documento-assinado-proprietario.' . (pathinfo($path, PATHINFO_EXTENSION) ?: 'pdf');
        return Storage::disk('public')->download($path, $name);
    }

    /**
     * Download do comprovante de pagamento — proprietário ou interessado.
     */
    public function downloadPaymentReceipt(QuotaTransaction $transaction)
    {
        $user = Auth::user();
        if ($transaction->renter_id !== $user->id && $transaction->owner_id !== $user->id) {
            abort(403, 'Acesso negado.');
        }
        $path = $transaction->payment_receipt_path ?? '';
        if (empty($path) || !Storage::disk('public')->exists($path)) {
            abort(404, 'Comprovante não encontrado.');
        }
        $name = 'comprovante-pagamento.' . (pathinfo($path, PATHINFO_EXTENSION) ?: 'pdf');
        return Storage::disk('public')->download($path, $name);
    }

    /**
     * API: status da transação para polling (interessado).
     */
    public function statusPoll(QuotaTransaction $transaction)
    {
        $user = Auth::user();
        if ($transaction->renter_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json([
            'workflow_step' => $transaction->workflow_step,
            'document_path' => $transaction->document_path ? route('transactions.download-document', $transaction) : null,
            'document_uploaded_at' => $transaction->document_uploaded_at?->toIso8601String(),
            'owner_pix' => $transaction->owner_pix,
        ]);
    }

    /**
     * Lista de negociações em andamento (como proprietário e/ou como interessado).
     */
    public function ownerPending()
    {
        $user = Auth::user();
        $pendingAsOwner = QuotaTransaction::forOwnerInProgress($user->id)
            ->with(['quota', 'renter'])
            ->orderBy('created_at', 'desc')
            ->get();
        $pendingAsRenter = QuotaTransaction::forRenterInProgress($user->id)
            ->with(['quota', 'owner'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('transactions.owner-pending', compact('pendingAsOwner', 'pendingAsRenter'));
    }

    /**
     * Tela do proprietário: ver dados do interessado, anexar documento, finalizar.
     */
    public function ownerManage(QuotaTransaction $transaction)
    {
        $user = Auth::user();
        if ($transaction->owner_id !== $user->id) {
            return redirect()->route('transactions.index')->with('error', 'Acesso negado.');
        }
        $transaction->load(['quota', 'renter', 'owner']);
        return view('transactions.owner-manage', compact('transaction'));
    }

    /**
     * Proprietário anexa o documento para o interessado assinar (gov.br).
     */
    public function uploadOwnerDocument(Request $request, QuotaTransaction $transaction)
    {
        $user = Auth::user();
        if ($transaction->owner_id !== $user->id) {
            return redirect()->route('transactions.index')->with('error', 'Acesso negado.');
        }
        $request->validate([
            'owner_pix' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png',
        ], [
            'owner_pix.required' => 'Informe seu PIX para receber o valor do aluguel.',
        ]);
        $path = $request->file('document')->store('transaction-documents', 'public');
        $transaction->update([
            'owner_pix' => trim($request->input('owner_pix')),
            'document_path' => $path,
            'document_uploaded_at' => now(),
            'workflow_step' => QuotaTransaction::WORKFLOW_DOC_AVAILABLE,
        ]);
        return redirect()->route('transactions.owner-manage', $transaction)->with('success', 'Documento e PIX enviados. O interessado poderá assinar com gov.br.');
    }

    /**
     * Interessado envia documento assinado com gov.br.
     */
    public function uploadRenterSignedDocument(Request $request, QuotaTransaction $transaction)
    {
        $user = Auth::user();
        if ($transaction->renter_id !== $user->id) {
            return redirect()->route('transactions.index')->with('error', 'Acesso negado.');
        }
        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'payment_receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'payment_receipt.required' => 'Anexe o comprovante de pagamento do valor do aluguel (PIX ao proprietário).',
        ]);
        $docPath = $request->file('document')->store('transaction-documents', 'public');
        $receiptPath = $request->file('payment_receipt')->store('transaction-documents', 'public');
        $transaction->update([
            'renter_signed_document_path' => $docPath,
            'payment_receipt_path' => $receiptPath,
            'workflow_step' => QuotaTransaction::WORKFLOW_RENTER_SIGNED,
        ]);
        return redirect()->route('transactions.waiting-document', $transaction)->with('success', 'Documento assinado e comprovante enviados. Aguarde o proprietário finalizar.');
    }

    /**
     * Proprietário anexa documento assinado e finaliza (interessado segue para pagar taxa de êxito).
     */
    public function ownerFinalize(Request $request, QuotaTransaction $transaction)
    {
        $user = Auth::user();
        if ($transaction->owner_id !== $user->id) {
            return redirect()->route('transactions.index')->with('error', 'Acesso negado.');
        }
        $request->validate(['owner_signed_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120']);
        $path = $request->file('owner_signed_document')->store('transaction-documents', 'public');
        $transaction->update([
            'owner_signed_document_path' => $path,
            'workflow_step' => QuotaTransaction::WORKFLOW_AWAITING_TAX_PAYMENT,
        ]);
        return redirect()->route('transactions.owner-manage', $transaction)->with('success', 'Processo finalizado. O interessado foi liberado para pagar a taxa de êxito.');
    }

    /**
     * Tela do interessado: anexar comprovante de pagamento (obrigatório após pagar taxa de êxito).
     */
    public function showReceipt(QuotaTransaction $transaction)
    {
        $user = Auth::user();
        if ($transaction->renter_id !== $user->id) {
            return redirect()->route('transactions.index')->with('error', 'Acesso negado.');
        }
        if (($transaction->workflow_step ?? '') === QuotaTransaction::WORKFLOW_COMPLETED) {
            return redirect()->route('transactions.show', $transaction);
        }
        $transaction->load(['quota', 'owner', 'renter']);
        return view('transactions.receipt', compact('transaction'));
    }

    /**
     * Interessado envia comprovante de pagamento; conclui processo e retira cota da publicação.
     */
    public function uploadReceipt(Request $request, QuotaTransaction $transaction)
    {
        $user = Auth::user();
        if ($transaction->renter_id !== $user->id) {
            return redirect()->route('transactions.index')->with('error', 'Acesso negado.');
        }
        $request->validate(['receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120']);
        $path = $request->file('receipt')->store('transaction-documents', 'public');
        $transaction->update([
            'payment_receipt_path' => $path,
            'workflow_step' => QuotaTransaction::WORKFLOW_COMPLETED,
            'status' => QuotaTransaction::STATUS_COMPLETED,
        ]);
        $transaction->quota->update([
            'status' => Quota::STATUS_RENTED,
            'negotiation_deadline' => null,
            'current_transaction_id' => null,
        ]);
        if ($transaction->digitalContract) {
            $transaction->digitalContract->update(['is_completed' => true]);
        }
        return redirect()->route('transactions.show', $transaction)->with('success', 'Comprovante enviado. Processo concluído; a cota saiu da listagem de publicadas.');
    }

    /**
     * Show payment page.
     */
    public function showPayment(QuotaTransaction $transaction)
    {
        $user = Auth::user();
        
        if ($transaction->renter_id !== $user->id) {
            return redirect()->route('transactions.index')->with('error', 'Você não tem permissão para acessar este pagamento.');
        }

        $step = $transaction->workflow_step ?? null;
        if ($step === QuotaTransaction::WORKFLOW_AWAITING_OWNER_DOC || $step === QuotaTransaction::WORKFLOW_DOC_AVAILABLE || $step === QuotaTransaction::WORKFLOW_RENTER_SIGNED) {
            return redirect()->route('transactions.waiting-document', $transaction);
        }
        if ($step === QuotaTransaction::WORKFLOW_COMPLETED) {
            return redirect()->route('transactions.show', $transaction);
        }
        if ($transaction->payment_status === QuotaTransaction::PAYMENT_COMPLETED && $step === QuotaTransaction::WORKFLOW_TAX_PAID) {
            return redirect()->route('transactions.receipt', $transaction);
        }

        if ($transaction->isNegotiationExpired()) {
            $this->cancelExpiredNegotiation($transaction);
            return redirect()->route('quotas.index')->with('error', 'O prazo para pagamento expirou. A negociação foi cancelada.');
        }

        $transaction->load(['quota', 'owner', 'renter']);
        return view('transactions.payment', compact('transaction'));
    }

    /**
     * Process payment for a transaction.
     */
    public function processPayment(Request $request, QuotaTransaction $transaction)
    {
        $user = Auth::user();
        
        // Check if user is the renter
        if ($transaction->renter_id !== $user->id) {
            return redirect()->route('transactions.index')
                ->with('error', 'Você não tem permissão para processar este pagamento.');
        }

        // Verificar se a negociação expirou
        if ($transaction->isNegotiationExpired()) {
            $this->cancelExpiredNegotiation($transaction);
            return redirect()->route('quotas.index')
                ->with('error', 'O prazo para pagamento expirou. A negociação foi cancelada.');
        }

        if ($transaction->payment_status === QuotaTransaction::PAYMENT_COMPLETED) {
            return redirect()->back()
                ->with('error', 'Pagamento já foi processado.');
        }

        // Validate payment method
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string|in:credit_card,debit_card,pix,bank_transfer',
        ], [
            'payment_method.required' => 'Método de pagamento é obrigatório.',
            'payment_method.in' => 'Método de pagamento inválido.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // TODO: Integrar com gateway de pagamento real (Mercado Pago, PagSeguro, etc)
        // Por enquanto, simular processamento
        $paymentReference = 'PAY-' . strtoupper(uniqid());

        $transaction->update([
            'payment_method' => $request->payment_method,
            'payment_status' => QuotaTransaction::PAYMENT_COMPLETED,
            'status' => QuotaTransaction::STATUS_COMPLETED,
            'payment_id' => $paymentReference,
            'workflow_step' => QuotaTransaction::WORKFLOW_COMPLETED,
        ]);

        // Retirar do ar ofertas de aluguel desta cota (inteira ou fração)
        RentalOffer::where('quota_id', $transaction->quota_id)
            ->where('status', 'active')
            ->update([
                'status' => 'negotiated',
                'negotiated_at' => now(),
                'negotiated_with' => $transaction->renter_id,
            ]);

        $transaction->quota->update([
            'status' => Quota::STATUS_RENTED,
            'negotiation_deadline' => null,
            'current_transaction_id' => null,
        ]);

        if ($transaction->digitalContract) {
            $transaction->digitalContract->update(['is_completed' => true]);
        }

        try {
            (new EmailService())->sendRentalSuccessEmails($transaction);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Erro ao enviar e-mails de conclusão do aluguel: ' . $e->getMessage());
        }

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Pagamento da taxa de êxito concluído! O aluguel da cota foi finalizado. Você e o proprietário receberão o termo por e-mail.');
    }

    /**
     * Show document upload page.
     */
    public function showDocument(QuotaTransaction $transaction)
    {
        $user = Auth::user();
        
        // Check if user is the owner
        if ($transaction->owner_id !== $user->id) {
            return redirect()->route('transactions.index')
                ->with('error', 'Você não tem permissão para acessar esta página.');
        }

        // Verificar se o pagamento foi feito
        if ($transaction->payment_status !== QuotaTransaction::PAYMENT_COMPLETED) {
            return redirect()->back()
                ->with('error', 'O pagamento ainda não foi realizado.');
        }

        // Verificar se o documento já foi enviado
        if ($transaction->document_uploaded_at) {
            return redirect()->route('transactions.show', $transaction)
                ->with('info', 'Documento já foi enviado.');
        }

        $transaction->load(['quota', 'owner', 'renter']);

        return view('transactions.document', compact('transaction'));
    }

    /**
     * Upload document after payment.
     */
    public function uploadDocument(Request $request, QuotaTransaction $transaction)
    {
        $user = Auth::user();
        
        // Check if user is the owner
        if ($transaction->owner_id !== $user->id) {
            return redirect()->route('transactions.index')
                ->with('error', 'Você não tem permissão para fazer upload do documento.');
        }

        // Verificar se o pagamento foi feito
        if ($transaction->payment_status !== QuotaTransaction::PAYMENT_COMPLETED) {
            return redirect()->back()
                ->with('error', 'O pagamento ainda não foi realizado.');
        }

        // Validar upload
        $validator = Validator::make($request->all(), [
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ], [
            'document.required' => 'O documento é obrigatório.',
            'document.file' => 'O arquivo enviado é inválido.',
            'document.mimes' => 'O documento deve ser PDF, JPG, JPEG ou PNG.',
            'document.max' => 'O documento não pode ter mais de 5MB.',
        ]);

        if ($validator->fails()) {
        return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Upload do documento
        $documentPath = $request->file('document')->store('transaction-documents', 'public');

        // Verificar se foi enviado dentro do prazo
        $isOnTime = $transaction->document_upload_deadline && 
                   now() <= $transaction->document_upload_deadline;

        // Update transaction
        $transaction->update([
            'document_path' => $documentPath,
            'document_uploaded_at' => now(),
            'status' => QuotaTransaction::STATUS_COMPLETED,
        ]);

        // Update quota status
        $transaction->quota->update([
            'status' => Quota::STATUS_RENTED,
            'negotiation_deadline' => null,
            'current_transaction_id' => null,
        ]);

        $message = $isOnTime 
            ? 'Documento enviado com sucesso dentro do prazo!'
            : 'Documento enviado, mas fora do prazo estabelecido.';

        return redirect()->route('transactions.show', $transaction)
            ->with('success', $message);
    }

    /**
     * Cancel expired negotiation and return quota to available.
     */
    private function cancelExpiredNegotiation(QuotaTransaction $transaction)
    {
        $transaction->update([
            'status' => QuotaTransaction::STATUS_EXPIRED,
            'payment_status' => QuotaTransaction::PAYMENT_FAILED,
        ]);

        if ($transaction->quota) {
            $transaction->quota->update([
                'status' => Quota::STATUS_AVAILABLE,
                'negotiation_deadline' => null,
                'current_transaction_id' => null,
            ]);
        }
    }

    /**
     * Generate rental contract content.
     */
    private function generateRentalContract(QuotaTransaction $transaction)
    {
        return "CONTRATO DE ALUGUEL DE COTA HOTELEIRA\n\n" .
               "Contratante (Locador): {$transaction->owner->name}\n" .
               "Contratado (Locatário): {$transaction->renter->name}\n" .
               "Hotel: {$transaction->quota->hotel_name}\n" .
               "Localização: {$transaction->quota->location}\n" .
               "Período: {$transaction->quota->start_date} a {$transaction->quota->end_date}\n" .
               "Valor: R$ " . number_format($transaction->total_amount, 2, ',', '.') . "\n" .
               "Data do Contrato: " . now()->format('d/m/Y') . "\n\n" .
               "Termos e Condições:\n" .
               "1. O locatário se compromete a utilizar a cota conforme especificado.\n" .
               "2. O pagamento deve ser realizado conforme acordado.\n" .
               "3. Qualquer alteração deve ser comunicada com antecedência.\n\n" .
               "Este contrato é válido e vinculante para ambas as partes.";
    }

    /**
     * Generate exchange contract content.
     */
    private function generateExchangeContract(QuotaTransaction $transaction, Quota $exchangeQuota)
    {
        return "CONTRATO DE TROCA DE COTAS HOTELEIRAS\n\n" .
               "Parte 1: {$transaction->owner->name}\n" .
               "Cota oferecida: {$transaction->quota->hotel_name} - {$transaction->quota->location}\n" .
               "Período: {$transaction->quota->start_date} a {$transaction->quota->end_date}\n\n" .
               "Parte 2: {$transaction->renter->name}\n" .
               "Cota oferecida: {$exchangeQuota->hotel_name} - {$exchangeQuota->location}\n" .
               "Período: {$exchangeQuota->start_date} a {$exchangeQuota->end_date}\n\n" .
               "Data do Contrato: " . now()->format('d/m/Y') . "\n\n" .
               "Termos e Condições:\n" .
               "1. Ambas as partes concordam com a troca das cotas especificadas.\n" .
               "2. Cada parte se responsabiliza por sua cota original.\n" .
               "3. Qualquer alteração deve ser comunicada com antecedência.\n\n" .
               "Este contrato é válido e vinculante para ambas as partes.";
    }
}
