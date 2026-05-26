@extends('layouts.app')

@section('title', 'Dashboard - Cota Brasilis')

@section('content')
@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Schema;
    use App\Models\Quota;
    use App\Models\QuotaTransaction;
    use App\Models\RentalOffer;
    use App\Models\Notification;

    $user = auth()->user();
    $profile = $profile ?? $user->profile;
    $isOwnerDelegatorOnly = $profile
        && (string) ($profile->getRawOriginal('has_quota') ?? $profile->has_quota) === '3';
    $user->loadCount(['quotas', 'ownedTransactions', 'rentalTransactions']);

    // Banner "Ver interesses": permanece até status final (concluído, cancelado ou expirado)
    $pendingOwnerInterestsCount = QuotaTransaction::forOwnerInProgress($user->id)->count();
    $pendingRenterInterestsCount = QuotaTransaction::forRenterInProgress($user->id)->count();
    $pendingInterestsCount = $pendingOwnerInterestsCount + $pendingRenterInterestsCount;

    $totalQuotas = $user->quotas_count;
    $activeQuotas = $user->quotas()->where('status', Quota::STATUS_AVAILABLE)->count();
    $totalTransactions = $user->owned_transactions_count + $user->rental_transactions_count;
    $leilaoDisponivel = data_get($profile?->getProfileConfig(), 'max_auctions_per_month') ?? data_get($profile?->getProfileConfig(), 'max_auctions');
    $profileLabel = $profile ? Str::title($profile->profile_type) : 'Visitante';
    $now = now();

    $activeOffersCount = RentalOffer::where('user_id', $user->id)->active()->count();
    $recentOffers = RentalOffer::where('user_id', $user->id)
        ->with(['quota', 'hotel'])
        ->orderByDesc('updated_at')
        ->take(5)
        ->get();

    $unreadNotificationsCount = 0;
    $recentNotifications = collect();
    if (Schema::hasTable('notifications')) {
        $unreadNotificationsCount = Notification::where('user_id', $user->id)->where('is_read', false)->count();
        $recentNotifications = Notification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(6)
            ->get();
    }

    $interestOwnerRows = QuotaTransaction::forOwnerInProgress($user->id)
        ->with(['quota', 'renter'])
        ->orderByDesc('updated_at')
        ->take(3)
        ->get();
    $interestRenterRows = QuotaTransaction::forRenterInProgress($user->id)
        ->with(['quota', 'owner'])
        ->orderByDesc('updated_at')
        ->take(3)
        ->get();
@endphp

<style>
    :root {
        --dashboard-green: #009739;
        --dashboard-deep: #014034;
        --dashboard-sand: #fceab5;
        --dashboard-ink: #0f172a;
        --dashboard-muted: #64748b;
        --dashboard-surface: #ffffff;
        --dashboard-ghost: #f8fafc;
    }

    .dashboard-shell {
        max-width: 100%;
        margin: 0 auto 4rem;
        display: flex;
        flex-direction: column;
        gap: 2.5rem;
        padding: 0 1rem;
    }

    .dashboard-hero {
        border-radius: 28px;
        padding: 2.8rem 2.6rem;
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(0, 151, 57, 0.92), rgba(10, 82, 52, 0.88));
        color: #fff;
        box-shadow: 0 32px 80px rgba(5, 74, 40, 0.35);
    }

    .dashboard-hero::after {
        content: '';
        position: absolute;
        inset: -160px auto auto 60%;
        width: 420px;
        height: 420px;
        background: radial-gradient(circle at center, rgba(255, 255, 255, 0.22), transparent 68%);
        transform: rotate(18deg);
    }

    .dashboard-hero__welcome {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .dashboard-hero__badge {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.45rem 1.2rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.18);
        font-weight: 600;
        letter-spacing: 0.02em;
    }

    .dashboard-hero__title {
        font-size: 2.35rem;
        font-weight: 800;
        margin: 0;
    }

    .dashboard-hero__subtitle {
        font-size: 1.05rem;
        max-width: 520px;
        margin: 0;
        color: rgba(255, 255, 255, 0.85);
        line-height: 1.6;
    }

    .dashboard-hero__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1.25rem;
        margin-top: 1rem;
    }

    .dashboard-hero__meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.45rem 0.85rem;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.12);
        font-weight: 600;
    }

    .dashboard-hero__profile {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dashboard-hero__avatar {
        width: 140px;
        height: 140px;
        border-radius: 38px;
        background: rgba(255, 255, 255, 0.16);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 18px 45px rgba(0, 0, 0, 0.24);
        border: 2px solid rgba(255, 255, 255, 0.4);
        overflow: hidden;
    }

    .dashboard-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.5rem;
    }

    .metric-card {
        background: var(--dashboard-surface);
        border-radius: 22px;
        padding: 1.8rem;
        border: 1px solid rgba(15, 23, 42, 0.06);
        display: flex;
        gap: 1rem;
        align-items: center;
        box-shadow: 0 24px 65px rgba(15, 23, 42, 0.08);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .metric-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 32px 85px rgba(15, 23, 42, 0.14);
    }

    .metric-card__icon {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: var(--dashboard-green);
        background: rgba(0, 151, 57, 0.12);
    }

    .metric-card__body span {
        display: block;
        font-size: 0.9rem;
        color: var(--dashboard-muted);
        font-weight: 600;
    }

    .metric-card__body strong {
        display: block;
        font-size: 1.65rem;
        font-weight: 800;
        color: var(--dashboard-ink);
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 0.95fr);
        gap: 1.75rem;
    }

    .profile-card,
    .journey-card,
    .actions-card,
    .insights-card {
        background: var(--dashboard-surface);
        border-radius: 24px;
        border: 1px solid rgba(15, 23, 42, 0.05);
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .card-header-modern {
        padding: 1.6rem 1.9rem;
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        background: linear-gradient(135deg, rgba(0, 151, 57, 0.08), rgba(4, 64, 52, 0.08));
    }

    .card-header-modern h5 {
        margin: 0;
        font-weight: 800;
        color: var(--dashboard-ink);
    }

    .card-header-modern span {
        font-size: 0.9rem;
        color: var(--dashboard-muted);
    }

    .profile-card .card-body,
    .actions-card .card-body,
    .journey-card .card-body,
    .insights-card .card-body {
        padding: 1.9rem;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .profile-summary {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }

    .profile-summary__identity {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }

    .profile-summary__avatar {
        width: 60px;
        height: 60px;
        border-radius: 18px;
        background: rgba(0, 151, 57, 0.16);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--dashboard-deep);
    }

    .profile-summary__info strong {
        font-size: 1.15rem;
        color: var(--dashboard-ink);
    }

    .profile-summary__info span {
        font-size: 0.85rem;
        color: var(--dashboard-muted);
    }

    .profile-data {
        display: grid;
        gap: 0.9rem;
    }

    .profile-data__item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.92rem;
        color: var(--dashboard-muted);
    }

    .profile-data__item strong {
        color: var(--dashboard-ink);
        font-size: 0.95rem;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.4rem 0.85rem;
        border-radius: 999px;
        font-weight: 600;
        font-size: 0.82rem;
    }

    .status-pill.success {
        background: rgba(34, 197, 94, 0.16);
        color: #047857;
    }

    .status-pill.warning {
        background: rgba(250, 204, 21, 0.22);
        color: #b45309;
    }

    .profile-actions {
        display: grid;
        gap: 0.75rem;
    }

    .profile-actions .btn {
        border-radius: 14px;
        font-weight: 600;
        padding: 0.75rem 1.1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .actions-card {
        width: 100% !important;
        max-width: 100% !important;
    }

    .hotel-options-card {
        width: 100%;
        max-width: 100%;
        background: linear-gradient(135deg, rgba(0, 151, 57, 0.95), rgba(10, 82, 52, 0.95));
        border-radius: 24px;
        padding: 2.5rem 2.8rem;
        box-shadow: 0 24px 65px rgba(5, 74, 40, 0.25);
        position: relative;
        overflow: hidden;
    }

    .hotel-options-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.15), transparent 70%);
        border-radius: 50%;
    }

    .hotel-options-content {
        position: relative;
        z-index: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 2rem;
    }

    .hotel-options-text {
        flex: 1;
    }

    .hotel-options-title {
        font-size: 2rem;
        font-weight: 800;
        color: #ffffff !important;
        margin: 0 0 0.75rem 0;
        line-height: 1.2;
    }

    .hotel-options-subtitle {
        font-size: 1.1rem;
        color: rgba(255, 255, 255, 0.9);
        margin: 0;
        line-height: 1.6;
        max-width: 600px;
    }

    .hotel-options-button {
        background: #fbbf24;
        color: #000000;
        padding: 1rem 2.5rem;
        border-radius: 14px;
        font-weight: 700;
        font-size: 1.05rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        box-shadow: 0 8px 24px rgba(251, 191, 36, 0.4);
        white-space: nowrap;
    }

    .hotel-options-button:hover {
        background: #f59e0b;
        transform: translateY(-2px);
        box-shadow: 0 12px 32px rgba(251, 191, 36, 0.5);
        color: #000000;
    }

    @media (max-width: 768px) {
        .hotel-options-card {
            padding: 2rem 1.8rem;
        }

        .hotel-options-content {
            flex-direction: column;
            align-items: flex-start;
            gap: 1.5rem;
        }

        .hotel-options-title {
            font-size: 1.65rem;
        }

        .hotel-options-subtitle {
            font-size: 1rem;
        }

        .hotel-options-button {
            width: 100%;
            padding: 0.9rem 2rem;
        }
    }

    @media (max-width: 576px) {
        .hotel-options-card {
            padding: 1.8rem 1.5rem;
            border-radius: 20px;
        }

        .hotel-options-title {
            font-size: 1.5rem;
        }

        .hotel-options-subtitle {
            font-size: 0.95rem;
        }
    }

    .action-tile {
        border-radius: 20px;
        padding: 1.4rem 1.5rem;
        background: var(--dashboard-ghost);
        border: 1px solid rgba(15, 23, 42, 0.05);
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        min-height: 190px;
    }

    .action-tile:hover {
        transform: translateY(-6px);
        box-shadow: 0 24px 65px rgba(15, 23, 42, 0.12);
    }

    .action-tile__icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        color: var(--dashboard-green);
        background: rgba(0, 151, 57, 0.12);
    }

    .action-tile h6 {
        margin: 0;
        font-weight: 700;
        color: var(--dashboard-ink);
    }

    .action-tile p {
        margin: 0;
        color: var(--dashboard-muted);
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .action-tile .btn {
        align-self: flex-start;
        border-radius: 12px;
        padding: 0.65rem 1.2rem;
    }

    .journey-timeline {
        display: grid;
        gap: 1.1rem;
    }

    .journey-step {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
    }

    .journey-step__marker {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: rgba(15, 23, 42, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--dashboard-green);
        font-weight: 700;
    }

    .journey-step.completed .journey-step__marker {
        background: rgba(34, 197, 94, 0.14);
        color: #047857;
    }

    /* Banner de interesse: permanece visível no scroll até o processo concluir (dados no banco) */
    .interest-banner {
        background: #fff3cd;
        border: 1px solid #ffeeba;
        border-radius: 16px;
        padding: 0.85rem 1.25rem;
        color: #856404;
        position: sticky;
        top: 0.75rem;
        z-index: 20;
        box-shadow: 0 6px 20px rgba(133, 100, 4, 0.12);
    }

    .journey-step header {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        margin-bottom: 0.35rem;
    }

    .journey-step header strong {
        color: var(--dashboard-ink);
        font-size: 1rem;
    }

    .journey-step header span {
        font-size: 0.8rem;
        color: var(--dashboard-muted);
    }

    .journey-step p {
        margin: 0;
        color: var(--dashboard-muted);
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .insights-grid {
        display: grid;
        gap: 1rem;
    }

    .insight-item {
        padding: 1.2rem 1.4rem;
        border-radius: 18px;
        background: rgba(15, 23, 42, 0.04);
        display: flex;
        align-items: flex-start;
        gap: 0.9rem;
    }

    .insight-item i {
        font-size: 1.25rem;
        color: var(--dashboard-green);
    }

    .insight-item strong {
        display: block;
        color: var(--dashboard-ink);
        font-size: 0.95rem;
    }

    .insight-item span {
        display: block;
        color: var(--dashboard-muted);
        font-size: 0.85rem;
        line-height: 1.5;
    }

    .alert-modern {
        border-radius: 18px;
        padding: 1.25rem 1.5rem;
        border: 1px solid rgba(234, 179, 8, 0.32);
        background: rgba(250, 204, 21, 0.12);
        display: flex;
        gap: 1rem;
        align-items: center;
        color: #b45309;
    }

    .alert-modern i {
        font-size: 1.4rem;
    }

    /* Painel visão geral — ofertas, notificações, interesses */
    .dashboard-hub-card {
        background: var(--dashboard-surface);
        border-radius: 24px;
        border: 1px solid rgba(15, 23, 42, 0.06);
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.07);
        overflow: hidden;
        width: 100% !important;
        max-width: 100% !important;
    }

    .dashboard-hub-card .card-body {
        padding: 1.75rem 1.9rem 2rem;
    }

    .hub-stats-row {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1.1rem;
        margin-bottom: 1.75rem;
    }

    .hub-stat {
        border-radius: 18px;
        padding: 1.25rem 1.35rem;
        background: linear-gradient(145deg, rgba(0, 151, 57, 0.06), rgba(1, 64, 52, 0.05));
        border: 1px solid rgba(0, 151, 57, 0.12);
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        min-height: 108px;
    }

    .hub-stat__label {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--dashboard-muted);
    }

    .hub-stat__value {
        font-size: 1.85rem;
        font-weight: 800;
        color: var(--dashboard-ink);
        line-height: 1.1;
    }

    .hub-stat__hint {
        font-size: 0.82rem;
        color: var(--dashboard-muted);
    }

    .hub-stat--amber {
        background: linear-gradient(145deg, rgba(250, 204, 21, 0.14), rgba(180, 83, 9, 0.06));
        border-color: rgba(234, 179, 8, 0.35);
    }

    .hub-stat--sky {
        background: linear-gradient(145deg, rgba(14, 165, 233, 0.1), rgba(3, 105, 161, 0.05));
        border-color: rgba(14, 165, 233, 0.22);
    }

    .hub-columns {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1.35rem;
        align-items: stretch;
    }

    .hub-panel {
        border-radius: 18px;
        border: 1px solid rgba(15, 23, 42, 0.07);
        background: var(--dashboard-ghost);
        padding: 1.25rem 1.3rem;
        display: flex;
        flex-direction: column;
        min-height: 280px;
    }

    .hub-panel__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
    }

    .hub-panel__title {
        font-weight: 800;
        font-size: 1.02rem;
        color: var(--dashboard-ink);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .hub-panel__title i {
        color: var(--dashboard-green);
    }

    .hub-panel__link {
        font-size: 0.82rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .hub-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        flex: 1;
    }

    .hub-list__item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.65rem;
        padding: 0.65rem 0.75rem;
        border-radius: 12px;
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.05);
        font-size: 0.88rem;
    }

    .hub-list__item strong {
        display: block;
        color: var(--dashboard-ink);
        font-weight: 600;
        font-size: 0.9rem;
    }

    .hub-list__meta {
        font-size: 0.75rem;
        color: var(--dashboard-muted);
    }

    .hub-empty {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: var(--dashboard-muted);
        font-size: 0.9rem;
        padding: 1.5rem 0.5rem;
    }

    /* Carteira digital Asaas */
    .wallet-card {
        background: linear-gradient(135deg, rgba(0, 151, 57, 0.95), rgba(1, 64, 52, 0.92));
        border-radius: 24px;
        color: #fff;
        box-shadow: 0 24px 60px rgba(5, 74, 40, 0.28);
        overflow: hidden;
        width: 100%;
    }

    .wallet-card .card-body {
        padding: 1.85rem 2rem;
    }

    .wallet-card__title {
        font-size: 1.15rem;
        font-weight: 800;
        text-transform: capitalize;
        margin: 0 0 0.35rem;
        letter-spacing: 0.01em;
    }

    .wallet-card__subtitle {
        font-size: 0.88rem;
        opacity: 0.88;
        margin: 0;
    }

    .wallet-card__balance-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        opacity: 0.85;
        margin-top: 1.5rem;
    }

    .wallet-card__balance {
        font-size: 2.35rem;
        font-weight: 800;
        line-height: 1.1;
        margin: 0.25rem 0 0;
    }

    .wallet-card__actions {
        margin-top: 1.5rem;
    }

    .wallet-card a.wallet-card__btn {
        background: var(--dashboard-green) !important;
        color: #fff !important;
        font-weight: 700;
        border: none;
        border-radius: 14px;
        padding: 0.75rem 1.35rem;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        transition: background 0.2s ease, box-shadow 0.2s ease;
    }

    .wallet-card a.wallet-card__btn:hover,
    .wallet-card a.wallet-card__btn:focus {
        background: #007a2f !important;
        color: #fff !important;
        box-shadow: 0 8px 20px rgba(0, 122, 47, 0.35);
    }

    .wallet-card a.wallet-card__btn i {
        color: #fff !important;
    }

    .wallet-card__alert {
        background: rgba(255, 255, 255, 0.15);
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 0.85rem;
        margin-top: 1rem;
    }

    @media (max-width: 1050px) {
        .hub-stats-row,
        .hub-columns {
            grid-template-columns: 1fr;
        }

        .hub-stat {
            min-height: auto;
        }

        .dashboard-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .actions-grid {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .dashboard-hero {
            border-radius: 24px;
            padding: 2.4rem 1.9rem;
        }

        .dashboard-hero__title {
            font-size: 2rem;
        }

        .dashboard-hero__profile {
            margin-top: 1.6rem;
        }

        .dashboard-metrics {
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        }
    }

    @media (max-width: 576px) {
        .dashboard-shell {
            gap: 2rem;
            padding: 0 0.75rem;
        }

        .dashboard-hero {
            padding: 2.2rem 1.6rem;
        }

        .dashboard-hero__meta span {
            width: 100%;
        }

        .metric-card {
            padding: 1.4rem;
        }

        .profile-actions {
            grid-template-columns: 1fr;
        }

        .action-tile {
            min-height: auto;
        }

        .actions-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@if($isOwnerDelegatorOnly)
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-lg-5">
            <h2 class="h4 fw-bold text-success mb-3">
                <i class="fas fa-check-circle me-2"></i>Conta criada com sucesso
            </h2>
            <p class="mb-2">
                Voce agora possue uma conta no cota brasilis, oriente o gestor que deseja autorizar a administrar sua cota, em caso de duvidas consulte nossas perguntas frequentes ou fale com nosso suporte.
            </p>
            <div class="mt-4 d-flex flex-wrap gap-2">
                <a href="{{ route('faq') }}" class="btn btn-outline-primary">
                    <i class="fas fa-question-circle me-2"></i>Perguntas frequentes
                </a>
            </div>
        </div>
    </div>
@else
<div class="dashboard-shell">
    @if($profile && !$profile->terms_accepted)
        <div class="alert-modern" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Complete seu cadastro</strong>
                <div>Você precisa aceitar os termos para liberar 100% das funcionalidades. Leva menos de 2 minutos.</div>
            </div>
            <a href="{{ route('profile.complete') }}" class="btn btn-warning btn-sm ms-auto">
                <i class="fas fa-user-edit me-1"></i> Completar Perfil
            </a>
        </div>
    @endif

    <!-- Ações Rápidas - Ocupa toda a largura -->
    <article class="actions-card" style="width: 100%; max-width: 100%;">
        <div class="card-header-modern">
            <h5>Ações rápidas</h5>
            <span>Continue a sua experiência com poucos cliques.</span>
        </div>
        <div class="card-body">
            <div class="actions-grid">
                <div class="action-tile">
                    <div class="action-tile__icon"><i class="fas fa-search"></i></div>
                    <h6>Buscar cotas ou frações</h6>
                    <p>Explore destinos em todo o Brasil com disponibilidade atualizada em tempo real.</p>
                    <a href="{{ route('quotas.index') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-arrow-right"></i> Buscar agora
                    </a>
                </div>

                @if($profile && data_get($profile->getProfileConfig(), 'can_publish'))
                    <div class="action-tile">
                        <div class="action-tile__icon" style="background: rgba(251, 191, 36, 0.2); color: #b45309;"><i class="fas fa-plus"></i></div>
                        <h6>Publicar cota ou fração</h6>
                        <p>Usufrua de sua cota ou frações, e ainda as transforme em receita caso queiras. Publique com fotos e condições especiais.</p>
                        <a href="{{ route('rental-offers.create') }}" class="btn btn-warning btn-sm text-white">
                            <i class="fas fa-bolt"></i> Começar anúncio
                        </a>
                    </div>
                @endif

                <div class="action-tile">
                    <div class="action-tile__icon"><i class="fas fa-list"></i></div>
                    <h6>Minhas cotas</h6>
                    <p>Gerencie status, valores e documentações dos seus ativos hoteleiros.</p>
                    <a href="{{ route('quotas.my') }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-layer-group"></i> Organizar cotas
                    </a>
                </div>

                <div class="action-tile">
                    <div class="action-tile__icon" style="background: rgba(14, 165, 233, 0.18); color: #0369a1;"><i class="fas fa-filter"></i></div>
                    <h6>Filtros para Otimização do Êxito</h6>
                    <p>Configure filtros avançados para otimizar o sucesso das suas ofertas e buscas.</p>
                    <a href="{{ route('offer-optimization.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-sliders-h"></i> Configurar filtros
                    </a>
                </div>
            </div>

            @if($pendingInterestsCount > 0)
                <div class="interest-banner d-flex align-items-center justify-content-between flex-wrap gap-2 mt-4 pt-4 mb-0 border-top border-warning border-opacity-25">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-bell me-2 fa-lg"></i>
                        <div>
                            @if($pendingOwnerInterestsCount > 0 && $pendingRenterInterestsCount > 0)
                                <strong>Negociações em andamento</strong>
                                <span class="d-block small">Você tem interesses na sua cota e também negociações como interessado. Abra a lista para acompanhar ou enviar documentos.</span>
                            @elseif($pendingOwnerInterestsCount > 0)
                                <strong>Interesse na sua cota</strong>
                                <span class="d-block small">Alguém clicou em Quero alugar, Quero trocar ou Quero comprar. Veja os dados do interessado e anexe o documento.</span>
                            @else
                                <strong>Sua negociação em andamento</strong>
                                <span class="d-block small">Há aluguel, troca ou compra em curso. Abra a lista para ver o status e os próximos passos.</span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('transactions.owner-pending') }}" class="btn btn-warning">
                        <i class="fas fa-arrow-right me-1"></i>Ver interesses ({{ $pendingInterestsCount }})
                    </a>
                </div>
            @endif
        </div>
    </article>

    <!-- Carteira digital (subconta Asaas) -->
    <article class="wallet-card">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                <div>
                    <h2 class="wallet-card__title">Sua carteira digital Cota Brasilis</h2>
                    <p class="wallet-card__subtitle">Valores das suas negociações de aluguel, troca e compra</p>
                </div>
                <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 52px; height: 52px;">
                    <i class="fas fa-wallet fa-lg"></i>
                </div>
            </div>

            <p class="wallet-card__balance-label mb-0">Saldo</p>
            <p class="wallet-card__balance">
                R$ {{ number_format(($walletAvailable ?? false) ? ($walletBalance ?? 0) : ($asaasSubaccount->cached_balance ?? 0), 2, ',', '.') }}
            </p>

            @if(!($walletAvailable ?? false))
                <div class="wallet-card__alert">
                    @if($asaasSubaccount ?? null)
                        @if(!$asaasSubaccount->isActive())
                            <i class="fas fa-info-circle me-1"></i>
                            {{ $asaasSubaccount->last_error ?? 'Sua carteira será ativada ao iniciar aluguel, troca ou compra (Quero alugar / trocar / comprar).' }}
                        @endif
                    @else
                        <i class="fas fa-info-circle me-1"></i>
                        Sua carteira será criada automaticamente quando você iniciar um aluguel, troca ou compra (Quero alugar / trocar / comprar).
                    @endif
                </div>
            @endif

            <div class="wallet-card__actions">
                <a href="{{ route('wallet.transfer.form') }}" class="btn btn-success wallet-card__btn">
                    <i class="fas fa-exchange-alt me-2"></i>Fazer transferência de valor
                </a>
            </div>
        </div>
    </article>

    <!-- Visão geral: ofertas, notificações e interesses -->
    <article class="dashboard-hub-card">
        <div class="card-header-modern">
            <h5>Visão geral</h5>
            <span>Ofertas publicadas, alertas e negociações em andamento — tudo em um painel único.</span>
        </div>
        <div class="card-body">
            <div class="hub-stats-row">
                <div class="hub-stat">
                    <span class="hub-stat__label">Ofertas ativas</span>
                    <span class="hub-stat__value">{{ $activeOffersCount }}</span>
                    <span class="hub-stat__hint">Minhas ofetas publicadas e disponível até hoje.</span>
                </div>
                <div class="hub-stat hub-stat--sky">
                    <span class="hub-stat__label">Notificações não lidas</span>
                    <span class="hub-stat__value">{{ $unreadNotificationsCount }}</span>
                    <span class="hub-stat__hint">Mensagens e avisos da plataforma.</span>
                </div>
                <div class="hub-stat hub-stat--amber">
                    <span class="hub-stat__label">Interesses / negociações</span>
                    <span class="hub-stat__value">{{ $pendingInterestsCount }}</span>
                    <span class="hub-stat__hint">Meus interesses e os interesses dos outros pelas minhas ofertas.</span>
                </div>
            </div>

            <div class="hub-columns">
                <div class="hub-panel">
                    <div class="hub-panel__head">
                        <h3 class="hub-panel__title"><i class="fas fa-bullhorn"></i> Ofertas</h3>
                        <a href="{{ route('rental-offers.my') }}" class="hub-panel__link text-success text-decoration-none">Minhas ofertas <i class="fas fa-arrow-right ms-1 small"></i></a>
                    </div>
                    @if($recentOffers->isEmpty())
                        <div class="hub-empty">Você ainda não possui ofertas de aluguel cadastradas. Publique a partir de <strong>Ações rápidas</strong>.</div>
                    @else
                        <ul class="hub-list">
                            @foreach($recentOffers as $offer)
                                <li class="hub-list__item">
                                    <div>
                                        <strong>{{ Str::limit($offer->title ?? 'Oferta', 42) }}</strong>
                                        <span class="hub-list__meta d-block mt-1">
                                            {{ ucfirst($offer->status ?? '—') }}
                                            @if($offer->updated_at)
                                                · atualizado {{ $offer->updated_at->diffForHumans() }}
                                            @endif
                                        </span>
                                    </div>
                                    <a href="{{ route('rental-offers.my') }}" class="btn btn-sm btn-outline-success flex-shrink-0">Ver</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="hub-panel">
                    <div class="hub-panel__head">
                        <h3 class="hub-panel__title"><i class="fas fa-bell"></i> Notificações</h3>
                        @if($unreadNotificationsCount > 0)
                            <span class="badge rounded-pill bg-primary">{{ $unreadNotificationsCount }} nova(s)</span>
                        @endif
                    </div>
                    @if($recentNotifications->isEmpty())
                        <div class="hub-empty">Alertas recentes de usuários, novidades, atualizações, enquetes, dicas e etc., da equipe do Cota Brasilis serão comunicadas por aqui.</div>
                    @else
                        <ul class="hub-list">
                            @foreach($recentNotifications as $note)
                                <li class="hub-list__item">
                                    <div>
                                        <strong>{{ Str::limit($note->title ?? 'Aviso', 48) }}</strong>
                                        <span class="hub-list__meta d-block mt-1">
                                            {{ Str::limit(strip_tags($note->message ?? ''), 72) }}
                                            @if($note->created_at)
                                                · {{ $note->created_at->diffForHumans() }}
                                            @endif
                                        </span>
                                    </div>
                                    @if(!($note->is_read ?? false))
                                        <span class="badge bg-warning text-dark flex-shrink-0">Novo</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="hub-panel">
                    <div class="hub-panel__head">
                        <h3 class="hub-panel__title"><i class="fas fa-handshake"></i> Interesses</h3>
                        @if($pendingInterestsCount > 0)
                            <a href="{{ route('transactions.owner-pending') }}" class="hub-panel__link text-success text-decoration-none">Abrir lista <i class="fas fa-arrow-right ms-1 small"></i></a>
                        @endif
                    </div>
                    @if($pendingInterestsCount === 0)
                        <div class="hub-empty">Sem negociações em aberto. Quando houver interesse na sua cota ou quando você iniciar um fluxo, os detalhes aparecerão aqui.</div>
                    @else
                        <ul class="hub-list">
                            @foreach($interestOwnerRows as $t)
                                <li class="hub-list__item">
                                    <div>
                                        <strong>{{ Str::limit($t->quota->hotel_name ?? 'Cota', 32) }}</strong>
                                        <span class="hub-list__meta d-block mt-1">Você é o proprietário · {{ $t->renter->name ?? 'Interessado' }}</span>
                                    </div>
                                    <a href="{{ route('transactions.owner-manage', $t) }}" class="btn btn-sm btn-outline-success flex-shrink-0">Gerir</a>
                                </li>
                            @endforeach
                            @foreach($interestRenterRows as $t)
                                <li class="hub-list__item">
                                    <div>
                                        <strong>{{ Str::limit($t->quota->hotel_name ?? 'Cota', 32) }}</strong>
                                        <span class="hub-list__meta d-block mt-1">Você é o interessado · {{ $t->owner->name ?? 'Proprietário' }}</span>
                                    </div>
                                    <a href="{{ route('transactions.show', $t) }}" class="btn btn-sm btn-outline-primary flex-shrink-0">Abrir</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </article>

    <!-- Card Opções no meu hotel -->
    <article class="hotel-options-card">
        <div class="hotel-options-content">
            <div class="hotel-options-text">
                <h3 class="hotel-options-title text-white" style="font-size: 2.8rem;">Opções no meu hotel</h3>
                <p class="hotel-options-subtitle" style="font-size: 1.2rem; color:white;">Acesse e veja as melhores opções para 
                    <span style="background:#fbbf24; color:#000000; width:350px;  height:30px; border-radius: 10px; "><b>Aluguel</b>,<b>Troca</b>  e  <b>Compra</b> em seu hotel.</span></p>
            </div>
            <a href="{{ route('hotel-options.index') }}" class="hotel-options-button">
                Visualizar
            </a>
        </div>
    </article>

    <div class="dashboard-grid">
</div>
@endif

@if($user->needsApprovalSuccessModal())
<!-- Modal exibido uma única vez após aprovação da conta -->
<div class="modal fade" id="approvalSuccessModal" tabindex="-1" aria-labelledby="approvalSuccessModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <div class="rounded-circle bg-success bg-opacity-25 d-inline-flex align-items-center justify-content-center" style="width: 90px; height: 90px;">
                        <i class="fas fa-check-circle fa-4x text-success"></i>
                    </div>
                </div>
                <h5 class="modal-title fw-bold text-dark mb-3" id="approvalSuccessModalLabel">Parabéns</h5>
                <p class="text-muted mb-0">
                    Sua conta foi aprovada pelo Cota Brasilis. Venha desfrutar dessa maravilhosa oportunidade Turística.
                </p>
                <div class="mt-4">
                    <button type="button" class="btn btn-primary btn-lg px-4" data-bs-dismiss="modal" id="approvalSuccessModalClose">Entendi</button>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
(function() {
    var modalEl = document.getElementById('approvalSuccessModal');
    if (!modalEl) return;
    var modal = new bootstrap.Modal(modalEl);
    modal.show();
    function clearFlag() {
        fetch('{{ route("dashboard.clear-approval-modal") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
    }
    modalEl.addEventListener('hidden.bs.modal', clearFlag);
})();
</script>
@endpush
@endif
@endsection