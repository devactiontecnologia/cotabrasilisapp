<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\QuotaController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminHotelController;
use App\Http\Controllers\AdminSuccessFeeController;
use App\Http\Controllers\AdminProfileApprovalController;
use App\Http\Controllers\RentalOfferController;
use App\Http\Controllers\AuctionController;
use App\Http\Controllers\QuotaManagementController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\OwnerOnboardingController;
use App\Http\Controllers\ClientPreferenceController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ExchangeController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\EducationalController;
use App\Http\Controllers\HotelOptionsController;
use App\Http\Controllers\BoraLaController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\AsaasWebhookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminEducationalHubController;
use App\Http\Controllers\AdminEducationalContentController;
use App\Http\Controllers\AdminEducationalVideoController;
use App\Http\Controllers\ClientAuthorizationTermsController;
use App\Http\Controllers\SitePageController;
use App\Http\Controllers\AdminSiteInformationController;
use App\Http\Controllers\AdminPlatformDocumentsController;
use App\Http\Controllers\AdminDataExchangeController;
use App\Http\Controllers\AdminFaqController;
use App\Http\Controllers\AdminBoraLaPostController;
use App\Http\Controllers\PublicMediaController;

// Evita que segmentos literais (ex.: owner-pending) coincidam com /transactions/{transaction}
Route::pattern('transaction', '[0-9]+');

// Public routes
Route::get('/media/{path}', [PublicMediaController::class, 'show'])
    ->where('path', '.*')
    ->name('storage.public');

Route::get('/', [WelcomeController::class, 'index']);
Route::get('/perguntas-frequentes', [WelcomeController::class, 'faq'])->name('faq');
Route::get('/institucional/{slug}', [SitePageController::class, 'show'])->name('site.page');

// Asaas Webhook (público, sem autenticação)
Route::post('/webhooks/asaas', [AsaasWebhookController::class, 'handle'])->name('webhooks.asaas');

// Public quotas search (for non-authenticated users)
Route::get('/cotas-disponiveis', [QuotaController::class, 'publicIndex'])->name('public.quotas.index');
Route::get('/cotas-em-destaque', [QuotaController::class, 'featured'])->name('public.quotas.featured');
Route::get('/cotas/{quota}', [QuotaController::class, 'publicShow'])->name('public.quotas.show');

// Public API endpoints
Route::get('/api/hotels/search', [HotelController::class, 'search'])->name('api.hotels.search');
Route::get('/api/hotels/{hotel}', [HotelController::class, 'show'])->name('api.hotels.show');
// Mesmo handler, rota fora de /api para evitar conflito com routes/api.php em alguns ambientes
Route::get('/web-autocomplete/hotels', [HotelController::class, 'search'])->name('web.autocomplete.hotels');

// Authentication routes
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/registration/success', [AuthController::class, 'showRegistrationSuccess'])->name('registration.success');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password reset routes
Route::get('/password/forgot', [PasswordResetController::class, 'showForgotPassword'])->name('password.forgot');
Route::post('/password/email', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/password/reset', [PasswordResetController::class, 'showResetPassword'])->name('password.reset');
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword'])->name('password.update');

// Multi-step registration - individual step routes
Route::get('/register/step1', [AuthController::class, 'showRegisterStep1'])->name('register.step1');
Route::post('/register/step1', [AuthController::class, 'postRegisterStep1'])->name('register.step1.post');

Route::get('/register/step2', [AuthController::class, 'showRegisterStep2'])->name('register.step2');
Route::post('/register/step2', [AuthController::class, 'postRegisterStep2'])->name('register.step2.post');

Route::get('/register/step3', [AuthController::class, 'showRegisterStep3'])->name('register.step3');
Route::post('/register/step3', [AuthController::class, 'postRegisterStep3'])->name('register.step3.post');

Route::get('/register/step4', [AuthController::class, 'showRegisterStep4'])->name('register.step4');
Route::post('/register/step4', [AuthController::class, 'postRegisterStep4'])->name('register.step4.post');

Route::get('/register/step5', [AuthController::class, 'showRegisterStep5'])->name('register.step5');
Route::post('/register/step5', [AuthController::class, 'postRegisterStep5'])->name('register.step5.post');

Route::get('/register/step6', [AuthController::class, 'showRegisterStep6'])->name('register.step6');
Route::post('/register/step6', [AuthController::class, 'postRegisterStep6'])->name('register.step6.post');
// AJAX helper to validate CPF during multi-step client flow
Route::post('/register/check-cpf', [AuthController::class, 'checkCpf'])->name('register.check_cpf');
// AJAX helper to validate Email uniqueness during multi-step client flow
Route::post('/register/check-email', [AuthController::class, 'checkEmail'])->name('register.check_email');
Route::post('/register/check-delegated-gestor', [AuthController::class, 'checkDelegatedGestor'])
    ->middleware('throttle:30,1')
    ->name('register.check_delegated_gestor');
Route::post('/register/download-delegated-gestor-document', [AuthController::class, 'downloadDelegatedGestorDocument'])
    ->middleware('throttle:30,1')
    ->name('register.download_delegated_gestor_document');
Route::post('/register/finalize', [AuthController::class, 'finalizeRegistration'])->name('register.finalize');
Route::get('/register/success', [AuthController::class, 'registerSuccess'])->name('register.success');

// Protected routes
Route::middleware(['auth', 'valid.registration'])->group(function () {
    // Profile completion routes (no profile check middleware)
    Route::get('/profile/complete', [UserProfileController::class, 'showComplete'])->name('profile.complete');
    Route::post('/profile/complete', [UserProfileController::class, 'complete']);
    
    // Dashboard (acessível mesmo antes de completar o perfil; não aprovados veem tela de aguardando aprovação)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/approval-status', [DashboardController::class, 'approvalStatus'])->name('dashboard.approval-status');
    Route::post('/dashboard/clear-approval-modal', [DashboardController::class, 'clearApprovalModal'])->name('dashboard.clear-approval-modal');

    // Interesses e negociações em andamento (proprietário e interessado); fora de profile.complete para acesso amplo
    Route::get('/transactions/owner-pending', [TransactionController::class, 'ownerPending'])->name('transactions.owner-pending');
    
    // All other protected routes (with profile check middleware)
    Route::middleware('profile.complete')->group(function () {

        // Client area pages
        Route::view('/cliente/reservas', 'client.reservations')->name('client.reservations');
        Route::view('/cliente/minhas-ofertas', 'client.offers')->name('client.offers');
        Route::view('/cliente/pesquisados', 'client.searched')->name('client.searched');
        Route::get('/cliente/favoritos', [ClientPreferenceController::class, 'favorites'])->name('client.favorites');
        Route::get('/cliente/desejados', [ClientPreferenceController::class, 'wishlist'])->name('client.wishlist');
        Route::post('/cliente/desejados/salvar-busca', [ClientPreferenceController::class, 'saveWishlistSearch'])->name('client.wishlist.save');
        Route::delete('/cliente/desejados/{wishlistSearch}', [ClientPreferenceController::class, 'removeWishlistSearch'])->name('client.wishlist.remove');
        // Bora lá! Cota Brasilis routes
        Route::get('/cliente/bora-la', [BoraLaController::class, 'index'])->name('bora-la.index');
        Route::get('/cliente/bora-la/oferta-unica', [BoraLaController::class, 'ofertaUnica'])->name('bora-la.oferta-unica');
        Route::post('/cliente/bora-la/oferta-unica', [BoraLaController::class, 'storeOfertaUnica'])->name('bora-la.oferta-unica.store');
        Route::get('/cliente/bora-la/atualizacoes', [BoraLaController::class, 'atualizacoes'])->name('bora-la.atualizacoes');
        Route::post('/cliente/bora-la/atualizacoes', [BoraLaController::class, 'storeAtualizacao'])->name('bora-la.atualizacoes.store');
        Route::get('/cliente/bora-la/avisos', [BoraLaController::class, 'avisos'])->name('bora-la.avisos');
        Route::post('/cliente/bora-la/avisos', [BoraLaController::class, 'storeAviso'])->name('bora-la.avisos.store');
        Route::get('/cliente/bora-la/enquetes', [BoraLaController::class, 'enquetes'])->name('bora-la.enquetes');
        Route::post('/cliente/bora-la/enquetes', [BoraLaController::class, 'storeEnquete'])->name('bora-la.enquetes.store');
        Route::get('/cliente/bora-la/dicas', [BoraLaController::class, 'dicas'])->name('bora-la.dicas');
        Route::post('/cliente/bora-la/dicas', [BoraLaController::class, 'storeDica'])->name('bora-la.dicas.store');
        
        // Mantém a rota antiga para compatibilidade
        Route::get('/cliente/ofertas-cota-brasilis', [BoraLaController::class, 'index'])->name('client.cota-offers');
        Route::get('/cliente/termos-autorizacao', [ClientAuthorizationTermsController::class, 'show'])->name('client.authorization-terms');

        // Profile routes
        Route::get('/profile', [UserProfileController::class, 'show'])->name('profile.show');
        Route::post('/profile/tipo-perfil', [UserProfileController::class, 'updateProfileType'])->name('profile.update-type');
        Route::get('/profile/edit', [UserProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');
        Route::get('/profile/config', [UserProfileController::class, 'getProfileConfig'])->name('profile.config');

        // Owner onboarding
        Route::get('/owner/onboarding', [OwnerOnboardingController::class, 'show'])->name('owner.onboarding');
        Route::post('/owner/onboarding', [OwnerOnboardingController::class, 'submit'])->name('owner.onboarding.submit');
        Route::delete('/owner/onboarding/photo', [OwnerOnboardingController::class, 'deletePhoto'])->name('owner.onboarding.photo.delete');

        // Quota routes
        Route::resource('quotas', QuotaController::class);
        Route::get('/my-quotas', [QuotaController::class, 'myQuotas'])->name('quotas.my');
        Route::get('/quotas/{quota}/transfer', [QuotaController::class, 'showTransferOwnership'])->name('quotas.transfer');
        Route::post('/quotas/{quota}/transfer', [QuotaController::class, 'transferOwnershipAction'])->name('quotas.transfer.submit');

        // Client preferences
        Route::post('/favorites/{quota}', [ClientPreferenceController::class, 'toggleFavorite'])->name('client.favorites.toggle');
        Route::post('/wishlist/{quota}', [ClientPreferenceController::class, 'toggleWishlist'])->name('client.wishlist.toggle');

        // Transaction routes
        Route::get('/quotas/{quota}/negotiate', [TransactionController::class, 'startNegotiation'])->name('quotas.negotiate');
        Route::post('/quotas/{quota}/rent', [TransactionController::class, 'rent'])->name('quotas.rent');
        Route::post('/quotas/{quota}/buy', [TransactionController::class, 'buy'])->name('quotas.buy');
        Route::post('/quotas/{quota}/exchange', [TransactionController::class, 'exchange'])->name('quotas.exchange');
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::get('/transactions/{transaction}/waiting-document', [TransactionController::class, 'waitingDocument'])->name('transactions.waiting-document');
        Route::get('/transactions/{transaction}/download-document', [TransactionController::class, 'downloadDocument'])->name('transactions.download-document');
        Route::get('/transactions/{transaction}/document/renter-signed', [TransactionController::class, 'downloadRenterSignedDocument'])->name('transactions.download-renter-signed');
        Route::get('/transactions/{transaction}/document/owner-signed', [TransactionController::class, 'downloadOwnerSignedDocument'])->name('transactions.download-owner-signed');
        Route::get('/transactions/{transaction}/document/payment-receipt', [TransactionController::class, 'downloadPaymentReceipt'])->name('transactions.download-payment-receipt');
        Route::get('/transactions/{transaction}/status', [TransactionController::class, 'statusPoll'])->name('transactions.status');
        Route::get('/transactions/{transaction}/owner-manage', [TransactionController::class, 'ownerManage'])->name('transactions.owner-manage');
        Route::post('/transactions/{transaction}/owner-document', [TransactionController::class, 'uploadOwnerDocument'])->name('transactions.owner-document.upload');
        Route::post('/transactions/{transaction}/renter-signed-document', [TransactionController::class, 'uploadRenterSignedDocument'])->name('transactions.renter-signed.upload');
        Route::post('/transactions/{transaction}/owner-finalize', [TransactionController::class, 'ownerFinalize'])->name('transactions.owner-finalize');
        Route::get('/transactions/{transaction}/receipt', [TransactionController::class, 'showReceipt'])->name('transactions.receipt');
        Route::post('/transactions/{transaction}/receipt', [TransactionController::class, 'uploadReceipt'])->name('transactions.receipt.upload');
        Route::get('/transactions/{transaction}/payment', [TransactionController::class, 'showPayment'])->name('transactions.payment');
        Route::post('/transactions/{transaction}/payment', [TransactionController::class, 'processPayment'])->name('transactions.payment.process');
        Route::get('/transactions/{transaction}/document', [TransactionController::class, 'showDocument'])->name('transactions.document');
        Route::post('/transactions/{transaction}/document', [TransactionController::class, 'uploadDocument'])->name('transactions.document.upload');
        Route::post('/transactions/{transaction}/sign', [TransactionController::class, 'signContract'])->name('transactions.sign');

        // Payment routes (fictício)
        Route::get('/payments/{transaction}', [PaymentController::class, 'show'])->name('payments.show');
        Route::post('/payments/{transaction}/process', [PaymentController::class, 'process'])->name('payments.process');
        Route::get('/payments/{transaction}/authorization', [PaymentController::class, 'showAuthorization'])->name('payments.authorization');
        Route::post('/payments/{transaction}/authorization', [PaymentController::class, 'uploadAuthorization'])->name('payments.upload-authorization');
        Route::get('/payments/{transaction}/success', [PaymentController::class, 'success'])->name('payments.success');

        // Exchange routes (Trocar)
        Route::get('/exchanges', [ExchangeController::class, 'index'])->name('exchanges.index');
        Route::get('/exchanges/create', [ExchangeController::class, 'create'])->name('exchanges.create');
        Route::post('/exchanges', [ExchangeController::class, 'store'])->name('exchanges.store');
        Route::get('/exchanges/{exchangeOffer}', [ExchangeController::class, 'show'])->name('exchanges.show');
        Route::get('/exchanges/{exchangeOffer}/edit', [ExchangeController::class, 'edit'])->name('exchanges.edit');
        Route::put('/exchanges/{exchangeOffer}', [ExchangeController::class, 'update'])->name('exchanges.update');
        Route::delete('/exchanges/{exchangeOffer}', [ExchangeController::class, 'destroy'])->name('exchanges.destroy');

        // Sale routes (Vender)
        Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
        Route::get('/sales/quota-data/{quotaId}', [SaleController::class, 'getQuotaData'])->name('sales.quota.data');
        Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
        Route::get('/sales/{saleOffer}/edit', [SaleController::class, 'edit'])->name('sales.edit');
        Route::put('/sales/{saleOffer}', [SaleController::class, 'update'])->name('sales.update');
        Route::post('/sales/{saleOffer}/negotiate', [SaleController::class, 'negotiate'])->name('sales.negotiate');
        Route::get('/sales/{saleOffer}', [SaleController::class, 'show'])->name('sales.show');

        // Purchase routes (Comprar)
        Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
        Route::get('/purchases/{purchaseRequest}', [PurchaseController::class, 'show'])->name('purchases.show');
        Route::post('/purchases/{purchaseRequest}/delegate', [PurchaseController::class, 'delegate'])->name('purchases.delegate');

        // Offer Optimization routes (Otimização de Êxito)
        Route::get('/offer-optimization', [\App\Http\Controllers\OfferOptimizationController::class, 'index'])->name('offer-optimization.index');

        // Educational routes (Camada Educativa)
        Route::get('/educational', [EducationalController::class, 'index'])->name('educational.index');
        Route::get('/educational/conteudo/{content}', [EducationalController::class, 'showContent'])->name('educational.content.show');
        Route::get('/educational/videos', [EducationalController::class, 'videos'])->name('educational.videos');
        Route::get('/educational/videos/{video}', [EducationalController::class, 'show'])->name('educational.video.show');
        Route::post('/educational/videos/{video}/comment', [EducationalController::class, 'comment'])->name('educational.video.comment');
        Route::post('/educational/videos/{video}/view', [EducationalController::class, 'recordView'])->name('educational.video.view');

        // Hotel Options routes
        Route::get('/hotel-options', [HotelOptionsController::class, 'index'])->name('hotel-options.index');

        // Rental Offers routes
        Route::get('/offers/my', [RentalOfferController::class, 'myOffers'])->name('rental-offers.my');
        Route::get('/offers', [RentalOfferController::class, 'index'])->name('rental-offers.index');
        Route::get('/offers/request', [RentalOfferController::class, 'request'])->name('rental-offers.request');
        Route::get('/offers/search', [RentalOfferController::class, 'search'])->name('rental-offers.search');
        Route::get('/offers/create', [RentalOfferController::class, 'create'])->name('rental-offers.create');
        Route::post('/offers', [RentalOfferController::class, 'store'])->name('rental-offers.store');
        Route::get('/offers/{rentalOffer}', [RentalOfferController::class, 'show'])->name('rental-offers.show');
        Route::get('/offers/{rentalOffer}/edit', [RentalOfferController::class, 'edit'])->name('rental-offers.edit');
        Route::put('/offers/{rentalOffer}', [RentalOfferController::class, 'update'])->name('rental-offers.update');
        Route::delete('/offers/{rentalOffer}', [RentalOfferController::class, 'destroy'])->name('rental-offers.destroy');

        // Auction routes
        Route::get('/auctions', [AuctionController::class, 'index'])->name('auctions.index');
        Route::get('/auctions/{rentalOffer}', [AuctionController::class, 'show'])->name('auctions.show');
        Route::post('/auctions/{rentalOffer}/bid', [AuctionController::class, 'placeBid'])->name('auctions.place-bid');
        Route::post('/auctions/{rentalOffer}/end', [AuctionController::class, 'endAuction'])->name('auctions.end');
        Route::get('/auctions/{rentalOffer}/bids', [AuctionController::class, 'getBids'])->name('auctions.bids');

        // Quota Management routes
        Route::prefix('quota-management')->name('quota-management.')->group(function () {
            Route::get('/', [QuotaManagementController::class, 'index'])->name('index');
            Route::get('/create', [QuotaManagementController::class, 'create'])->name('create');
            Route::post('/', [QuotaManagementController::class, 'store'])->name('store');
            Route::get('/search', [QuotaManagementController::class, 'search'])->name('search');
            Route::get('/{quota}', [QuotaManagementController::class, 'show'])->name('show');
            Route::get('/{quota}/edit', [QuotaManagementController::class, 'edit'])->name('edit');
            Route::put('/{quota}', [QuotaManagementController::class, 'update'])->name('update');
            Route::post('/{quota}/publish', [QuotaManagementController::class, 'publish'])->name('publish');
            Route::post('/{quota}/unpublish', [QuotaManagementController::class, 'unpublish'])->name('unpublish');
            Route::post('/{quota}/transfer', [QuotaManagementController::class, 'transferOwnership'])->name('transfer');
            Route::post('/{quota}/fractioned-offer', [QuotaManagementController::class, 'createFractionedOffer'])->name('fractioned-offer');
            Route::post('/{quota}/mark-paid', [QuotaManagementController::class, 'markAsPaid'])->name('mark-paid');
            Route::post('/{quota}/mark-unpaid', [QuotaManagementController::class, 'markAsUnpaid'])->name('mark-unpaid');
            Route::post('/{quota}/create-offer', [QuotaManagementController::class, 'createOffer'])->name('create-offer');
            Route::post('/{quota}/delegate-purchase', [QuotaManagementController::class, 'delegatePurchase'])->name('delegate-purchase');
            Route::get('/stats/overview', [QuotaManagementController::class, 'getStats'])->name('stats');
        });
    });
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Users management
    Route::resource('users', AdminUserController::class);
    Route::post('/users/{user}/toggle-block', [AdminUserController::class, 'toggleBlock'])->name('users.toggle-block');
    Route::post('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('users.toggle-active');

    // Profile approvals (Pendentes / Aprovados / Reprovados)
    Route::get('/profile-approvals', [AdminProfileApprovalController::class, 'index'])->name('profile-approvals.index');
    Route::post('/profile-approvals/{user}/approve', [AdminProfileApprovalController::class, 'approve'])->name('profile-approvals.approve');
    Route::post('/profile-approvals/{user}/reject', [AdminProfileApprovalController::class, 'reject'])->name('profile-approvals.reject');
    
    // Hotels management
    Route::resource('hotels', AdminHotelController::class);
    Route::post('/hotels/{hotel}/toggle-active', [AdminHotelController::class, 'toggleActive'])->name('hotels.toggle-active');
    
    // Success Fees management
    Route::resource('success-fees', AdminSuccessFeeController::class);
    Route::post('/success-fees/{successFee}/toggle-active', [AdminSuccessFeeController::class, 'toggleActive'])->name('success-fees.toggle-active');

    // Conteúdo educativo (textos e vídeos)
    Route::prefix('educational')->name('educational.')->group(function () {
        Route::get('/', [AdminEducationalHubController::class, 'index'])->name('index');
        Route::resource('contents', AdminEducationalContentController::class)->parameters(['contents' => 'content'])->except(['show']);
        Route::resource('videos', AdminEducationalVideoController::class)->except(['show']);
    });
    
    // Quotas management
    Route::get('/quotas', [AdminController::class, 'quotas'])->name('quotas.index');
    Route::get('/quotas/{quota}', [AdminController::class, 'showQuota'])->name('quotas.show');
    
    // Transactions management
    Route::get('/transactions', [AdminController::class, 'transactions'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [AdminController::class, 'showTransaction'])->name('transactions.show');
    
    // Logs
    Route::get('/logs', [AdminController::class, 'logs'])->name('logs.index');
    
    // Notifications
    Route::get('/notifications', [AdminController::class, 'notifications'])->name('notifications.index');

    Route::get('/site-information', [AdminSiteInformationController::class, 'index'])->name('site-information.index');
    Route::get('/site-information/{sitePage}/edit', [AdminSiteInformationController::class, 'edit'])->name('site-information.edit');
    Route::put('/site-information/{sitePage}', [AdminSiteInformationController::class, 'update'])->name('site-information.update');

    Route::get('/platform-documents', [AdminPlatformDocumentsController::class, 'edit'])->name('platform-documents.edit');
    Route::put('/platform-documents', [AdminPlatformDocumentsController::class, 'update'])->name('platform-documents.update');

    Route::get('/data-exchange', [AdminDataExchangeController::class, 'index'])->name('data-exchange.index');
    Route::post('/data-exchange/export', [AdminDataExchangeController::class, 'export'])->name('data-exchange.export');
    Route::post('/data-exchange/import', [AdminDataExchangeController::class, 'import'])->name('data-exchange.import');

    Route::resource('faqs', AdminFaqController::class)->except(['show']);
    Route::resource('bora-la-posts', AdminBoraLaPostController::class)->parameters(['bora-la-posts' => 'boraLaPost'])->except(['show']);
});
