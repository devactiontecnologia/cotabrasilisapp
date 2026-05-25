@extends('layouts.app')

@section('title', 'Cotas Disponíveis - Cota Brasilis')

@section('content')
@php
    use Illuminate\Support\Str;

    $userProfile = auth()->check() ? auth()->user()->profile : null;
    $userConfig = $userProfile ? $userProfile->getProfileConfig() : [];
    $totalQuotas = $quotas->total();
@endphp

<style>
    :root {
        --brand-green: #009739;
        --brand-green-dark: #046143;
        --brand-ink: #0f172a;
        --brand-muted: #64748b;
        --surface: #ffffff;
        --surface-alt: #f8fafc;
    }

    .quotas-page {
        max-width: 1120px;
        margin: 0 auto 4rem;
        display: flex;
        flex-direction: column;
        gap: 2.5rem;
    }

    .page-hero {
        background: linear-gradient(135deg, rgba(0, 151, 57, 0.95), rgba(10, 82, 52, 0.95));
        border-radius: 28px;
        padding: 2.75rem 3rem;
        position: relative;
        overflow: hidden;
    }

    .page-hero::after {
        content: "";
        position: absolute;
        width: 320px;
        height: 320px;
        top: -140px;
        right: -140px;
        background: radial-gradient(circle at center, rgba(0, 151, 57, 0.28), transparent 68%);
        z-index: 0;
    }

    .page-hero__badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.2);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 0.4rem 1.1rem;
        border-radius: 999px;
        font-weight: 700;
        letter-spacing: 0.02em;
        margin-bottom: 1.25rem;
        position: relative;
        z-index: 1;
    }

    .page-hero h1 {
        font-size: 2.5rem;
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 0.35rem;
        position: relative;
        z-index: 1;
    }

    .page-hero p {
        color: rgba(255, 255, 255, 0.9);
        font-size: 1.05rem;
        max-width: 540px;
        margin-bottom: 0;
        position: relative;
        z-index: 1;
    }

    .page-hero__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        margin-top: 1.75rem;
        position: relative;
        z-index: 1;
    }

    .page-hero__stat {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: #fff;
        border-radius: 16px;
        padding: 0.85rem 1.35rem;
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.08);
    }

    .page-hero__stat i {
        font-size: 1.4rem;
        color: var(--brand-green);
    }

    .page-hero__stat strong {
        display: block;
        font-size: 1.15rem;
        color: var(--brand-ink);
    }

    .page-hero__stat span {
        display: block;
        font-size: 0.85rem;
        color: var(--brand-muted);
        font-weight: 500;
    }

    .filters-card {
        background: var(--surface);
        border-radius: 26px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.06);
        padding: 2.25rem;
    }

    .filters-card__headline {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    .filters-card__headline h2 {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--brand-ink);
        margin: 0;
    }

    .filters-card__headline p {
        margin: 0;
        color: var(--brand-muted);
        font-size: 0.95rem;
    }

    .filters-card__icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: rgba(0, 151, 57, 0.15);
        color: var(--brand-green-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        box-shadow: 0 14px 30px rgba(0, 151, 57, 0.18);
    }

    .filters-card label {
        font-weight: 700;
        font-size: 0.95rem;
        color: #334155;
        margin-bottom: 0.55rem;
    }

    .filter-icon-wrapper {
        position: relative;
    }

    .filter-icon-wrapper i {
        position: absolute;
        top: 50%;
        left: 1rem;
        transform: translateY(-50%);
        color: var(--brand-green);
        font-size: 1.1rem;
        opacity: 0.7;
    }

    .filter-control {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 0.85rem 1rem 0.85rem 3rem;
        font-size: 0.95rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .filter-control:focus {
        border-color: var(--brand-green);
        box-shadow: 0 0 0 4px rgba(0, 151, 57, 0.12);
        background: #fff;
    }

    .filters-card__footer {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
        margin-top: 2rem;
    }

    .btn-filter-primary {
        background: var(--brand-green);
        color: #fff;
        font-weight: 700;
        padding: 0.9rem 1.75rem;
        border: none;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        width: 100%;
        justify-content: center;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .btn-filter-primary:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 22px 38px rgba(0, 151, 57, 0.28);
        color: #fff;
    }

    .btn-filter-primary:disabled {
        opacity: 0.52;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .btn-filter-secondary {
        background: transparent;
        border: none;
        color: #0f766e;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.65rem 0.5rem;
        width: 100%;
    }

    .btn-filter-secondary:hover {
        color: #0d9488;
        text-decoration: underline;
    }

    .results-summary {
        background: var(--surface-alt);
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 1.5rem 2rem;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 1.25rem;
        box-shadow: 0 18px 35px rgba(15, 23, 42, 0.05);
    }

    .results-summary__title {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .results-summary__title h3 {
        margin: 0;
        font-weight: 700;
        color: var(--brand-ink);
        font-size: 1.35rem;
    }

    .results-summary__title span {
        color: var(--brand-muted);
        font-size: 0.95rem;
    }

    .results-summary__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .results-summary__actions .btn {
        border-radius: 12px;
        padding: 0.65rem 1.4rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .quota-list {
        display: flex;
        flex-direction: column;
        gap: 1.75rem;
    }

    .quota-card {
        background: #fff;
        border-radius: 22px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
        display: flex;
        flex-wrap: wrap;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .quota-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 30px 85px rgba(15, 23, 42, 0.12);
    }

    .quota-card__media {
        position: relative;
        flex: 1 1 320px;
        min-height: 240px;
        max-height: 320px;
        overflow: hidden;
    }

    .quota-card__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .media-placeholder {
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, #d1fae5, #bbf7d0);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--brand-green-dark);
        font-size: 3rem;
    }

    .media-badge {
        position: absolute;
        top: 18px;
        left: 18px;
        background: rgba(255, 255, 255, 0.9);
        color: #047857;
        padding: 0.35rem 0.9rem;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        box-shadow: 0 14px 28px rgba(4, 120, 87, 0.16);
    }

    .media-counter {
        position: absolute;
        bottom: 18px;
        right: 18px;
        background: rgba(15, 23, 42, 0.78);
        color: #fff;
        font-size: 0.85rem;
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .quota-card__body {
        flex: 1 1 380px;
        padding: 2.25rem;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .quota-card__body header {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }

    .quota-card__body h3 {
        margin: 0;
        font-weight: 800;
        font-size: 1.5rem;
        color: var(--brand-ink);
    }

    .location-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(0, 151, 57, 0.12);
        color: var(--brand-green-dark);
        padding: 0.35rem 0.9rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .season-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: rgba(14, 165, 233, 0.12);
        color: #0284c7;
        padding: 0.3rem 0.75rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1rem;
    }

    .meta-item {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        color: #1f2937;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .meta-item i {
        color: #0ea5e9;
        font-size: 1.05rem;
        margin-top: 0.15rem;
    }

    .meta-item span {
        display: block;
        font-weight: 500;
        font-size: 0.82rem;
        color: var(--brand-muted);
    }

    .meta-item--period .period-meta__label {
        display: block;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.25rem;
        line-height: 1.3;
    }

    .meta-item--period .period-meta__nights {
        display: block;
        font-size: 0.82rem;
        font-weight: 500;
        color: #64748b;
        margin-top: 0.2rem;
    }

    .meta-item--period strong {
        font-weight: 700;
        font-size: 0.95rem;
        color: #0f172a;
        line-height: 1.35;
    }

    .meta-item--period > i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.45rem;
        height: 2.45rem;
        flex-shrink: 0;
        border-radius: 0.65rem;
        background: rgba(16, 185, 129, 0.16);
        color: #059669;
        font-size: 1rem;
        margin-top: 0.1rem;
    }

    .amenities-meta-item {
        width: 100%;
        margin-top: 0.5rem;
        flex-direction: column;
    }

    .amenities-meta-item > div {
        flex: 1;
    }

    .amenities-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .amenities-group span {
        background: #f1f5f9;
        color: #475569;
        padding: 0.35rem 0.75rem;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .notes {
        color: #475569;
        font-size: 0.95rem;
        line-height: 1.6;
        margin: 0;
    }

    .owner-chip {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        background: #f8fafc;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
    }

    .owner-avatar {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(0, 151, 57, 0.18);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: var(--brand-green-dark);
    }

    .owner-chip small {
        display: block;
        font-size: 0.75rem;
        color: var(--brand-muted);
        font-weight: 500;
        margin-bottom: 0.1rem;
    }

    .owner-chip strong {
        display: block;
        color: var(--brand-ink);
        font-size: 0.9rem;
    }

    .owner-chip span {
        display: block;
        font-size: 0.78rem;
        color: #94a3b8;
        font-weight: 500;
    }

    .quota-card__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .quota-card__actions .btn {
        border-radius: 12px;
        padding: 0.75rem 1.4rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .quota-card__price {
        flex: 0 0 260px;
        background: linear-gradient(160deg, var(--brand-green) 0%, #006d3d 100%);
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 2.25rem 2rem;
        gap: 1.5rem;
    }

    .quota-card__price .price-label {
        font-size: 0.95rem;
        opacity: 0.85;
    }

    .quota-card__price .price-amount {
        font-size: 2rem;
        font-weight: 800;
        margin: 0;
    }

    .quota-card__price .price-meta {
        font-size: 0.85rem;
        opacity: 0.9;
    }

    .price-badge {
        background: rgba(255, 255, 255, 0.18);
        border-radius: 999px;
        padding: 0.35rem 0.9rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.85rem;
    }

    .quota-card__price .btn {
        align-self: flex-start;
        border-radius: 12px;
        padding: 0.75rem 1.45rem;
        font-weight: 700;
        color: var(--brand-green);
        background: #fff;
        border: none;
        box-shadow: 0 16px 40px rgba(255, 255, 255, 0.25);
    }

    .quota-card__price .btn:hover {
        color: var(--brand-green-dark);
    }

    .exchange-banner {
        background: rgba(255, 255, 255, 0.18);
        border-radius: 18px;
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        font-size: 1.05rem;
        text-align: center;
    }

    .pagination-wrapper {
        display: flex;
        justify-content: center;
        margin: 3rem 0 1rem;
    }

    .empty-state {
        text-align: center;
        padding: 4.5rem 1.5rem;
        background: #fff;
        border-radius: 24px;
        border: 1px dashed #cbd5f5;
        color: var(--brand-ink);
        box-shadow: 0 24px 55px rgba(15, 23, 42, 0.06);
    }

    .empty-state .icon {
        width: 110px;
        height: 110px;
        margin: 0 auto 1.5rem;
        border-radius: 28px;
        background: rgba(0, 151, 57, 0.16);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--brand-green);
        font-size: 2.75rem;
        box-shadow: 0 18px 42px rgba(13, 148, 94, 0.2);
    }

    .empty-state p {
        color: var(--brand-muted);
        max-width: 520px;
        margin: 0 auto 1.5rem;
        font-size: 0.98rem;
    }

    .trust-panel {
        background: #f1f5f9;
        border-radius: 28px;
        padding: 3rem 1.5rem;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.04);
        margin: 4rem auto 0;
    }

    .trust-grid {
        max-width: 1120px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.5rem;
    }

    .trust-card {
        background: #fff;
        border-radius: 20px;
        padding: 1.75rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
    }

    .trust-card i {
        font-size: 1.8rem;
        color: var(--brand-green);
    }

    .trust-card strong {
        font-size: 1.05rem;
        color: var(--brand-ink);
    }

    .trust-card span {
        color: var(--brand-muted);
        font-size: 0.9rem;
        line-height: 1.5;
    }

    @media (max-width: 1199px) {
        .quota-card__price {
            flex: 1 1 100%;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            border-left: none;
            border-radius: 0 0 22px 22px;
            flex-direction: row;
            align-items: center;
            gap: 1.25rem;
        }

        .quota-card__price > div {
            flex: 1;
        }
    }

    @media (max-width: 992px) {
        .quotas-page {
            padding: 0 0.75rem;
        }

        .filters-card {
            padding: 1.75rem;
        }

        .page-hero {
            padding: 2.25rem;
        }

        .page-hero__stat {
            width: 100%;
            justify-content: space-between;
        }

        .results-summary {
            padding: 1.25rem 1.5rem;
        }

        .quota-card__body {
            padding: 1.75rem;
        }
    }

    @media (max-width: 768px) {
        .filters-card__headline {
            flex-direction: column;
            align-items: flex-start;
        }

        .filters-card__footer {
            flex-direction: column;
            align-items: stretch;
        }

        .btn-filter-secondary {
            justify-content: center;
        }

        .results-summary__actions {
            width: 100%;
            justify-content: stretch;
        }

        .results-summary__actions .btn {
            flex: 1;
            justify-content: center;
        }
    }

    @media (max-width: 576px) {
        .page-hero {
            border-radius: 22px;
        }

        .quota-card {
            border-radius: 18px;
        }

        .quota-card__media {
            min-height: 200px;
        }

        .quota-card__price {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="quotas-page">
    <section class="page-hero">
        <span class="page-hero__badge">
            <i class="fas fa-compass"></i>
            Cotas Disponíveis
        </span>
        <h1>Hospedagem </h1>
        <p>A melhor curadoria de cotas hoteleiras verificadas. 
            Filtre por hotel, cidade, estado, número de hóspedes e período para encontrar a experiência perfeita.</p>
        <div class="page-hero__meta">
            <div class="page-hero__stat">
                <i class="fas fa-building"></i>
                <div>
                    <strong>{{ $totalQuotas }}</strong>
                    <span>Cotas ativas neste momento</span>
                </div>
            </div>
            <div class="page-hero__stat">
                <i class="fas fa-map-marked-alt"></i>
                <div>
                    <strong>Brasil inteiro</strong>
                    <span>Destinos auditados pela Cota Brasilis</span>
                </div>
            </div>
            <div class="page-hero__stat">
                <i class="fas fa-shield-check"></i>
                <div>
                    <strong>Garantia digital</strong>
                    <span>Contratos e pagamentos seguros</span>
                </div>
            </div>
        </div>
    </section>

    <section class="filters-card">
        <div class="filters-card__headline">
            <div>
                <h2>Filtre Hospedagens por Estado</h2>
                <p>Use os filtros para refinar sua busca e descobrir a Cota ou Fração ideal para sua Hospedagem.</p>
            </div>
            <div class="filters-card__icon">
                <i class="fas fa-sliders-h"></i>
            </div>
        </div>

        <form method="GET" action="{{ route('public.quotas.index') }}">
            <input type="hidden" name="search" value="1">
            <div class="row g-3 g-lg-4">
                <div class="col-12">
                    <label for="hotel_name" class="form-label">Nome do Hotel</label>
                    <div class="hotel-autocomplete-wrapper position-relative">
                    <div class="filter-icon-wrapper">
                        <i class="fas fa-hotel"></i>
                        <input type="text" id="hotel_name" name="hotel_name" class="form-control filter-control"
                                   value="{{ request('hotel_name') }}" placeholder="Ex: Resort Paradise" autocomplete="off">
                        </div>
                        <div class="hotel-autocomplete-list"></div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label for="city" class="form-label">Cidade</label>
                    <div class="filter-icon-wrapper" style="position: relative;">
                        <i class="fas fa-city"></i>
                        <input type="text" id="city" name="city" class="form-control filter-control"
                               value="{{ request('city') }}" placeholder="Ex: Rio de Janeiro" autocomplete="off">
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label for="state" class="form-label">Estado</label>
                    <div class="filter-icon-wrapper">
                        <i class="fas fa-map-marker-alt"></i>
                        <select id="state" name="state" class="form-select filter-control">
                            <option value="">Selecione</option>
                            @foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf)
                                <option value="{{ $uf }}" {{ request('state') === $uf ? 'selected' : '' }}>{{ $uf }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label for="guests" class="form-label">Hóspedes</label>
                    <div class="filter-icon-wrapper">
                        <i class="fas fa-users"></i>
                        <select id="guests" name="guests" class="form-select filter-control">
                            <option value="">Qualquer quantidade</option>
                            @for($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}" {{ request('guests') == $i ? 'selected' : '' }}>
                                    {{ $i }} {{ Str::plural('pessoa', $i) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="start_date" class="form-label">Data de Entrada</label>
                    <div class="filter-icon-wrapper">
                        <i class="fas fa-calendar-check"></i>
                        <input type="date" id="start_date" name="start_date" class="form-control filter-control"
                               value="{{ request('start_date') }}" min="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="end_date" class="form-label">Data de Saída</label>
                    <div class="filter-icon-wrapper">
                        <i class="fas fa-calendar-alt"></i>
                        <input type="date" id="end_date" name="end_date" class="form-control filter-control"
                               value="{{ request('end_date') }}" min="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="month" class="form-label">Mês de Disponibilidade</label>
                    <div class="filter-icon-wrapper">
                        <i class="fas fa-calendar"></i>
                        <select id="month" name="month" class="form-select filter-control">
                            <option value="">Selecione um mês</option>
                            <option value="1" {{ request('month') == '1' ? 'selected' : '' }}>Janeiro</option>
                            <option value="2" {{ request('month') == '2' ? 'selected' : '' }}>Fevereiro</option>
                            <option value="3" {{ request('month') == '3' ? 'selected' : '' }}>Março</option>
                            <option value="4" {{ request('month') == '4' ? 'selected' : '' }}>Abril</option>
                            <option value="5" {{ request('month') == '5' ? 'selected' : '' }}>Maio</option>
                            <option value="6" {{ request('month') == '6' ? 'selected' : '' }}>Junho</option>
                            <option value="7" {{ request('month') == '7' ? 'selected' : '' }}>Julho</option>
                            <option value="8" {{ request('month') == '8' ? 'selected' : '' }}>Agosto</option>
                            <option value="9" {{ request('month') == '9' ? 'selected' : '' }}>Setembro</option>
                            <option value="10" {{ request('month') == '10' ? 'selected' : '' }}>Outubro</option>
                            <option value="11" {{ request('month') == '11' ? 'selected' : '' }}>Novembro</option>
                            <option value="12" {{ request('month') == '12' ? 'selected' : '' }}>Dezembro</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="filters-card__footer">
                <button type="submit" id="quota-public-search-submit" class="btn-filter-primary" disabled>
                    <i class="fas fa-search"></i>
                    Buscar Cotas ou Frações ideais
                </button>
                <a href="{{ route('public.quotas.index', ['search' => 1]) }}" class="btn-filter-secondary">
                    <i class="fas fa-sync"></i>
                    Limpar filtros
                </a>
            </div>
        </form>
    </section>

    <div class="results-summary">
        <div class="results-summary__title">
            <h3>Resultados da busca</h3>
            <span>
                <i class="fas fa-check-circle text-success me-1"></i>
                {{ $totalQuotas }} {{ Str::plural('cota disponível', $totalQuotas) }} encontradas para sua pesquisa
            </span>
        </div>
        <div class="results-summary__actions">
            @auth
                @if($userConfig['can_publish'] ?? false)
                    <a href="{{ route('quotas.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i>
                        Publicar cota
                    </a>
                @endif
                <a href="{{ route('quotas.my') }}" class="btn btn-outline-success">
                    <i class="fas fa-suitcase-rolling"></i>
                    Minhas cotas
                </a>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-sign-in-alt"></i>
                    Entrar
                </a>
                <a href="{{ route('register') }}" class="btn btn-success">
                    <i class="fas fa-user-plus"></i>
                    Criar conta gratuita
                </a>
            @endauth
        </div>
    </div>

    @if($quotas->count() > 0)
        <div class="quota-list">
            @foreach($quotas as $quota)
                @php
                    $images = collect($quota->hotel_images ?? []);
                    $firstImage = $images->first();
                    $imageCount = $images->count();
                    $periodBreakdown = $quota->getPeriodNightsBreakdown();
                    $checkIn = $quota->start_date?->format('d/m/Y');
                    $checkOut = $quota->end_date?->format('d/m/Y');
                    // Número de pernoites = número de dias da cota (inteira ou fracionada) - 1
                    // Sempre mostrar 1 a menos: 8 dias = 7 pernoites, 3 dias = 2 pernoites
                    $duration = $quota->getNightsCount();
                    if ($duration > 0) {
                        $duration = $duration - 1;
                    }
                    // Usar comodidades da cota ao invés do hotel
                    $amenities = collect($quota->quota_amenities ?? [])->filter()->take(6);
                    $owner = $quota->user;
                    $ownerName = $owner->name ?? 'Anunciante verificado';
                    $initials = collect(explode(' ', $ownerName))
                        ->filter(fn($part) => filled($part))
                        ->map(fn($part) => Str::upper(Str::substr($part, 0, 1)))
                        ->take(2)
                        ->implode('');
                    $initials = $initials ?: 'CB';
                    $publishedAgo = $quota->updated_at?->diffForHumans();
                    $txPublic = request('transaction_type', 'rental');
                    if ($txPublic === 'rental') {
                        $txPublic = 'rent';
                    }
                    $isExchange = request('transaction_type') === 'exchange';
                    $price = $quota->getMarketplaceListPrice($isExchange ? 'exchange' : 'rent');
                    $statusBadge = $isExchange ? 'Troca disponível' : 'Pronto para alugar';
                    $guestsText = $quota->number_of_guests ? $quota->number_of_guests . ' ' . Str::plural('hóspede', $quota->number_of_guests) : null;
                    $location = $quota->location ?: 'Destino confidencial';
                    $seasonality = $quota->seasonality ? Str::title($quota->seasonality) : null;
                @endphp

                <article class="quota-card">
                    <div class="quota-card__media">
                        @if($firstImage)
                            <img src="{{ asset('storage/' . $firstImage) }}" alt="{{ $quota->hotel_name }}">
                        @else
                            <div class="media-placeholder">
                                <i class="fas fa-hotel"></i>
                            </div>
                        @endif

                        <span class="media-badge">
                            <i class="fas fa-check"></i>
                            {{ $statusBadge }}
                        </span>

                        @if($imageCount > 1)
                            <span class="media-counter">
                                <i class="fas fa-images"></i>
                                {{ $imageCount }}
                            </span>
                        @endif
                    </div>

                    <div class="quota-card__body">
                        <header>
                            <div class="location-chip">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $location }}
                            </div>
                            <h3>{{ $quota->hotel_name }}</h3>
                        </header>

                        <div class="meta-grid">
                            @foreach($periodBreakdown as $periodItem)
                                <div class="meta-item meta-item--period">
                                    <i class="fas fa-calendar-alt"></i>
                                    <div>
                                        <span class="period-meta__label">{{ trim($periodItem['label']) }}</span>
                                        <strong>{{ $periodItem['formatted'] }}</strong>
                                        @if(isset($periodItem['nights']))
                                            <span class="period-meta__nights">{{ $periodItem['nights'] }} {{ $periodItem['nights'] == 1 ? 'pernoite' : 'pernoites' }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            @if(empty($periodBreakdown) && ($checkIn || $checkOut))
                                @if($checkIn)
                                <div class="meta-item">
                                    <i class="fas fa-calendar-check"></i>
                                    <div>
                                        <strong>{{ $checkIn }}</strong>
                                        <span>Check-in</span>
                                    </div>
                                </div>
                                @endif
                                @if($checkOut)
                                <div class="meta-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <div>
                                        <strong>{{ $checkOut }}</strong>
                                        <span>Check-out</span>
                                    </div>
                                </div>
                                @endif
                            @endif
                            @if($duration && empty($periodBreakdown))
                                <div class="meta-item">
                                    <i class="fas fa-moon"></i>
                                    <div>
                                        <strong>{{ $duration }} {{ Str::plural('pernoite', $duration) }}</strong>
                                        <span>Tempo de estada</span>
                                    </div>
                                </div>
                            @endif
                            @if($guestsText)
                                <div class="meta-item">
                                    <i class="fas fa-user-friends"></i>
                                    <div>
                                        <strong>{{ $guestsText }}</strong>
                                        <span>Capacidade</span>
                                    </div>
                                </div>
                            @endif
                            
                            @if($amenities->isNotEmpty())
                                @php
                                    $amenitiesTranslations = [
                                        'wifi' => 'Wi-Fi',
                                        'hidromassagem' => 'Hidromassagem',
                                        'cafe_da_manha' => 'Café da Manhã',
                                        'sofa_mais' => 'Sofá mais',
                                        'estacionamento_gratuito' => 'Estacionamento Grátis',
                                        'cozinha' => 'Cozinha',
                                        'area_kids' => 'Área Kids',
                                        'academia' => 'Academia',
                                        'piscina' => 'Piscina',
                                        'spa' => 'Spa',
                                        'restaurante' => 'Restaurante',
                                        'bar' => 'Bar',
                                        'centro_negocios' => 'Centro de Negócios',
                                        'business_center' => 'Centro de Negócios',
                                        'area_trabalho' => 'Área de Trabalho',
                                        'lareira' => 'Lareira',
                                        'adega' => 'Adega',
                                        'vista_mar' => 'Vista para o Mar',
                                        'room_service' => 'Serviço de Quarto',
                                        'heated_pool' => 'Piscina Coberta Aquecida',
                                        'gym' => 'Academia',
                                        'bowling' => 'Boliche',
                                        'concierge' => 'Concierge',
                                        'pool' => 'Piscina',
                                        'parking' => 'Estacionamento',
                                        'wet_bar' => 'Bar Molhado',
                                        'pet_friendly' => 'Espaço Pet',
                                        'wine_cellar' => 'Adega',
                                        'fireplace' => 'Lareira',
                                        'bike_rack' => 'Bicicletário',
                                        'sports_court' => 'Quadra Poliesportiva',
                                        'rooftop' => 'Rooftop',
                                    ];
                                @endphp
                                <div class="meta-item amenities-meta-item">
                                    <i class="fas fa-star"></i>
                                    <div style="width: 100%;">
                                        <strong style="display: block; margin-bottom: 0.5rem;">Comodidades</strong>
                                        <div class="amenities-group">
                                            @foreach($amenities as $amenity)
                                                @php
                                                    $amenityKey = str_replace(' ', '_', strtolower($amenity));
                                                    $amenityLabel = $amenitiesTranslations[$amenityKey] ?? Str::title(str_replace('_', ' ', $amenity));
                                                @endphp
                                                <span>{{ $amenityLabel }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($quota->observations)
                            <p class="notes">{{ Str::limit($quota->observations, 160) }}</p>
                        @endif

                        <div class="owner-chip">
                            <div class="owner-avatar p-0 overflow-hidden border-0 bg-transparent flex-shrink-0">
                                <x-user-avatar :user="$owner" :size="48" rounded="circle" />
                            </div>
                            <div>
                                <small>Publicado por</small>
                                <strong>{{ Str::limit($ownerName, 28) }}</strong>
                                @if($publishedAgo)
                                    <span>{{ $publishedAgo }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="quota-card__actions">
                            @if(Route::has('public.quotas.show'))
                                <a href="{{ route('public.quotas.show', array_merge([$quota], request()->only('transaction_type'))) }}" class="btn btn-success">
                                    <i class="fas fa-eye"></i>
                                    Ver detalhes
                                </a>
                            @endif
                        </div>
                    </div>

                    <aside class="quota-card__price">
                        @if($isExchange)
                            <div>
                                <span class="price-label">Troca</span>
                                <p class="price-amount">R$ {{ number_format(0, 2, ',', '.') }}</p>
                                @if($duration)
                                    <p class="price-meta">Período de {{ $duration }} {{ Str::plural('pernoite', $duration) }} — oferta publicada</p>
                                @endif
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="price-badge"><i class="fas fa-exchange-alt"></i>Negociação direta</span>
                            </div>
                            @if(Route::has('public.quotas.show'))
                                <a href="{{ route('public.quotas.show', array_merge([$quota], request()->only('transaction_type'))) }}" class="btn">
                                    <i class="fas fa-paper-plane"></i>
                                    Tenho interesse
                                </a>
                            @endif
                        @elseif($price !== null)
                            <div>
                                <span class="price-label">Investimento total</span>
                                <p class="price-amount">R$ {{ number_format($price, 2, ',', '.') }}</p>
                                @if($duration)
                                    <p class="price-meta">Período de {{ $duration }} {{ Str::plural('pernoite', $duration) }} para {{ $guestsText ?? 'seus convidados' }}</p>
                                @endif
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="price-badge"><i class="fas fa-shield-alt"></i>Proteção Cota Brasilis</span>
                                <span class="price-badge"><i class="fas fa-leaf"></i>Taxas reduzidas</span>
                            </div>
                            @if(Route::has('public.quotas.show'))
                                <a href="{{ route('public.quotas.show', array_merge([$quota], request()->only('transaction_type'))) }}" class="btn">
                                    <i class="fas fa-paper-plane"></i>
                                    Tenho interesse
                                </a>
                            @endif
                        @else
                            <div class="exchange-banner">
                                <i class="fas fa-info-circle me-2"></i>
                                Consulte o anunciante para valores
                            </div>
                        @endif
                    </aside>
                </article>
            @endforeach
        </div>

        @if($quotas->hasPages())
            <div class="mt-4">
                {{ $quotas->links('vendor.pagination.modern') }}
            </div>
        @endif
    @else
        <div class="empty-state">
            <div class="icon">
                <i class="fas fa-search"></i>
            </div>
            <h3 class="fw-bold mb-2">Nenhuma cota encontrada</h3>
            <p>Ajuste os filtros ou explore outros destinos para encontrar a cota perfeita para sua viagem.</p>
            <a href="{{ route('public.quotas.index', ['search' => 1]) }}" class="btn-filter-primary" style="max-width: 280px; margin: 0 auto;">
                <i class="fas fa-redo"></i>
                Ver todas as cotas
            </a>
        </div>
    @endif
</div>

<section class="trust-panel">
    <div class="trust-grid">
        <div class="trust-card">
            <i class="fas fa-file-signature"></i>
            <strong>Contratos digitais verificados</strong>
            <span>Documentos aprovados pela nossa equipe jurídica garantem segurança e transparência para todas as partes.</span>
        </div>
        <div class="trust-card">
            <i class="fas fa-headset"></i>
            <strong>Suporte concierge 7 dias</strong>
            <span>Especialistas do Turismo e Multipropriedade Hoteleira disponíveis para ajudá-lo</span>
        </div>
        <div class="trust-card">
            <i class="fas fa-credit-card"></i>
            <strong>Pagamento seguro</strong>
            <span><i style="font-size: 0.8rem;">Gateway</i> próprio, antifraude ativo e repasse somente após validação de <i style="font-size: 0.8rem;">	check-in</i> do hóspede.</span>
        </div>
    </div>
</section>

@push('styles')
<style>
/* Estilos para campos de seleção (select) */
.form-select {
    color: #212529;
    background-color: #ffffff;
    border: 1px solid #dee2e6;
    transition: all 0.3s ease;
    font-weight: 500;
}

.form-select:focus {
    border-color: #009739;
    box-shadow: 0 0 0 0.2rem rgba(0, 151, 57, 0.25);
    outline: none;
}

/* Quando uma opção está selecionada, aplicar estilo especial */
.form-select.has-selection {
    color: #ffffff !important;
    background-color: #009739 !important;
    font-weight: 600;
    border-color: #009739;
}

.form-select.has-selection:focus {
    color: #ffffff !important;
    background-color: #007a2e !important;
    border-color: #007a2e;
}

/* Estilo para as opções no dropdown */
.form-select option {
    color: #212529;
    background-color: #ffffff;
    padding: 0.5rem;
}

.form-select option:checked,
.form-select option[selected] {
    color: #ffffff !important;
    background-color: #009739 !important;
    font-weight: 600;
}

/* Melhorar aparência dos inputs de texto */
.form-control {
    color: #212529;
    transition: all 0.3s ease;
    font-weight: 500;
}

.form-control:focus {
    border-color: #009739;
    box-shadow: 0 0 0 0.2rem rgba(0, 151, 57, 0.25);
    color: #212529;
    outline: none;
}

.form-control::placeholder {
    color: #6c757d;
    opacity: 0.7;
}

.hotel-autocomplete-wrapper {
    width: 100%;
    position: relative; /* garante o dropdown posicionado corretamente */
}

.hotel-autocomplete-list {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
    margin-top: 4px;
}

.hotel-autocomplete-wrapper .filter-icon-wrapper {
    position: relative;
}

.hotel-autocomplete-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid #f1f3f5;
    transition: background-color 0.2s ease;
    display: flex;
    align-items: center;
}

.hotel-autocomplete-item:last-child {
    border-bottom: none;
}

.hotel-autocomplete-item:hover,
.hotel-autocomplete-item.active {
    background-color: rgba(0, 151, 57, 0.1);
    color: #009739;
}

.hotel-autocomplete-item i {
    color: #009739;
    width: 20px;
}

.hotel-name {
    font-weight: 600;
    color: #212529;
}

.hotel-location {
    color: #6c757d;
    font-size: 0.9rem;
    margin-left: 0.5rem;
}

.hotel-autocomplete-no-results {
    color: #6c757d;
    font-style: italic;
    cursor: default;
}

.hotel-autocomplete-no-results:hover {
    background-color: transparent;
    color: #6c757d;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function syncQuotaPublicSearchButtonState() {
        const btn = document.getElementById('quota-public-search-submit');
        if (!btn) return;
        const hotel = (document.getElementById('hotel_name')?.value || '').trim();
        const city = (document.getElementById('city')?.value || '').trim();
        const state = document.getElementById('state')?.value || '';
        const guests = document.getElementById('guests')?.value || '';
        const start = document.getElementById('start_date')?.value || '';
        const end = document.getElementById('end_date')?.value || '';
        const month = document.getElementById('month')?.value || '';
        const hasFilter = !!(hotel || city || state || guests || start || end || month);
        btn.disabled = !hasFilter;
    }

    // Melhorar aparência dos campos select quando uma opção está selecionada
    function updateSelectStyles() {
        const selects = document.querySelectorAll('.form-select');
        selects.forEach(select => {
            if (select.value && select.value !== '') {
                select.classList.add('has-selection');
            } else {
                select.classList.remove('has-selection');
            }
        });
    }

    // Aplicar estilos inicialmente
    updateSelectStyles();

    // Adicionar event listeners para todos os selects
    document.querySelectorAll('.form-select').forEach(select => {
        select.addEventListener('change', function() {
            updateSelectStyles();
            syncQuotaPublicSearchButtonState();
        });
    });

    document.querySelectorAll('#hotel_name, #city, #start_date, #end_date').forEach(el => {
        if (!el) return;
        el.addEventListener('input', syncQuotaPublicSearchButtonState);
        el.addEventListener('change', syncQuotaPublicSearchButtonState);
    });

    syncQuotaPublicSearchButtonState();

    // Autocomplete para campo de hotel
    function initHotelAutocomplete() {
        const hotelInput = document.getElementById('hotel_name');
        if (!hotelInput) return;
        
        const wrapper = hotelInput.closest('.hotel-autocomplete-wrapper');
        if (!wrapper) return;
        
        let autocompleteList = wrapper.querySelector('.hotel-autocomplete-list');
        if (!autocompleteList) {
            autocompleteList = document.createElement('div');
            autocompleteList.className = 'hotel-autocomplete-list';
            wrapper.appendChild(autocompleteList);
        }
        
        let searchTimeout;
        let selectedIndex = -1;
        
        // Monta a URL da API no prefixo correto do projeto.
        // Quando o site é acessado via "/cotabrasilis/public/...", removemos "/public"
        // para chamar o endpoint em "/cotabrasilis/api/...".
        function buildHotelsApiUrl(query, type) {
            const pathname = window.location.pathname || '';
            const base = pathname.includes('/public/')
                ? pathname.split('/public/')[0]
                : (pathname.includes('/public') ? pathname.split('/public')[0] : '');

            const prefix = base ? base.replace(/\/$/, '') : '';
            const typePart = type ? `&type=${encodeURIComponent(type)}` : '';
            return `${prefix}/api/hotels/search?query=${encodeURIComponent(query)}${typePart}`;
        }

        // Função para buscar hotéis
        function searchHotels(query) {
            if (query.length < 1) {
                autocompleteList.innerHTML = '';
                autocompleteList.style.display = 'none';
                return;
            }

            // Mostra o dropdown enquanto aguarda a resposta da API
            autocompleteList.style.display = 'block';
            autocompleteList.innerHTML = '<div class="hotel-autocomplete-item hotel-autocomplete-no-results">Buscando...</div>';

            // Obter o estado selecionado (se houver)
            const stateSelect = document.getElementById('state');
            const state = stateSelect ? stateSelect.value : '';
            
            // Endpoint de hotéis (usando prefixo correto)
            let url = buildHotelsApiUrl(query);
            if (state) url += `&state=${encodeURIComponent(state)}`;

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.data && data.data.length > 0) {
                        autocompleteList.innerHTML = '';
                        data.data.forEach((hotel, index) => {
                            const item = document.createElement('div');
                            item.className = 'hotel-autocomplete-item';
                            item.dataset.index = index;
                            item.innerHTML = `
                                <i class="fas fa-hotel me-2"></i>
                                <span class="hotel-name">${hotel.name}</span>
                                ${hotel.city || hotel.state ? `<span class="hotel-location"> - ${[hotel.city, hotel.state].filter(Boolean).join(', ')}</span>` : ''}
                            `;
                            item.addEventListener('click', function() {
                                hotelInput.value = hotel.name;
                                autocompleteList.innerHTML = '';
                                autocompleteList.style.display = 'none';
                                selectedIndex = -1;
                                syncQuotaPublicSearchButtonState();
                            });
                            item.addEventListener('mouseenter', function() {
                                document.querySelectorAll('.hotel-autocomplete-item').forEach(i => i.classList.remove('active'));
                                this.classList.add('active');
                                selectedIndex = parseInt(this.dataset.index);
                            });
                            autocompleteList.appendChild(item);
                        });
                        autocompleteList.style.display = 'block';
                    } else {
                        autocompleteList.innerHTML = '<div class="hotel-autocomplete-item hotel-autocomplete-no-results">Nenhum hotel encontrado</div>';
                        autocompleteList.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar hotéis:', error);
                    autocompleteList.innerHTML = '<div class="hotel-autocomplete-item hotel-autocomplete-no-results">Erro ao buscar hotéis</div>';
                    autocompleteList.style.display = 'block';
                });
        }

        // Event listener para input - busca a cada letra digitada
        hotelInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            // Busca imediatamente se tiver pelo menos 1 caractere
            if (query.length >= 1) {
                searchTimeout = setTimeout(() => {
                    searchHotels(query);
                }, 100); // Pequeno delay para evitar muitas requisições
            } else {
                autocompleteList.innerHTML = '';
                autocompleteList.style.display = 'none';
            }
        });

        // Se a pessoa clicar no campo já com texto, buscar e exibir sugestões
        hotelInput.addEventListener('focus', function() {
            const query = this.value.trim();
            if (query.length >= 1) {
                searchHotels(query);
            }
        });

        // Fechar autocomplete ao clicar fora
        document.addEventListener('click', function(e) {
            if (!hotelInput.contains(e.target) && !autocompleteList.contains(e.target)) {
                autocompleteList.style.display = 'none';
            }
        });

        // Navegação com teclado
        hotelInput.addEventListener('keydown', function(e) {
            const items = autocompleteList.querySelectorAll('.hotel-autocomplete-item:not(.hotel-autocomplete-no-results)');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (items.length > 0) {
                    selectedIndex = (selectedIndex + 1) % items.length;
                    items.forEach((item, index) => {
                        item.classList.toggle('active', index === selectedIndex);
                    });
                    if (items[selectedIndex]) {
                        items[selectedIndex].scrollIntoView({ block: 'nearest' });
                    }
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (items.length > 0) {
                    selectedIndex = selectedIndex <= 0 ? items.length - 1 : selectedIndex - 1;
                    items.forEach((item, index) => {
                        item.classList.toggle('active', index === selectedIndex);
                    });
                    if (items[selectedIndex]) {
                        items[selectedIndex].scrollIntoView({ block: 'nearest' });
                    }
                }
            } else if (e.key === 'Enter' && selectedIndex >= 0 && items[selectedIndex]) {
                e.preventDefault();
                items[selectedIndex].click();
            } else if (e.key === 'Escape') {
                autocompleteList.style.display = 'none';
                selectedIndex = -1;
            }
        });

        // Focar no campo ao clicar no autocomplete
        hotelInput.addEventListener('focus', function() {
            if (this.value.trim().length >= 1) {
                searchHotels(this.value.trim());
            }
        });

        // Recarregar hotéis quando o estado mudar
        const stateSelect = document.getElementById('state');
        if (stateSelect) {
            stateSelect.addEventListener('change', function() {
                if (hotelInput.value.trim().length >= 1) {
                    searchHotels(hotelInput.value.trim());
                }
                syncQuotaPublicSearchButtonState();
            });
        }
    }

    // Autocomplete para campo de cidade
    function initCityAutocomplete() {
        const cityInput = document.getElementById('city');
        if (!cityInput) return;
        
        let searchTimeout;
        let selectedIndex = -1;
        let autocompleteList = null;

        function buildHotelsApiUrl(query, type) {
            const pathname = window.location.pathname || '';
            const base = pathname.includes('/public/')
                ? pathname.split('/public/')[0]
                : (pathname.includes('/public') ? pathname.split('/public')[0] : '');

            const prefix = base ? base.replace(/\/$/, '') : '';
            const typePart = type ? `&type=${encodeURIComponent(type)}` : '';
            return `${prefix}/api/hotels/search?query=${encodeURIComponent(query)}${typePart}`;
        }
        
        // Criar container de autocomplete para cidade
        const wrapper = cityInput.closest('.filter-icon-wrapper');
        if (!wrapper) return;
        
        // Verificar se já existe uma lista de autocomplete
        if (!autocompleteList) {
            autocompleteList = document.createElement('div');
            autocompleteList.className = 'hotel-autocomplete-list';
            autocompleteList.style.position = 'absolute';
            autocompleteList.style.top = '100%';
            autocompleteList.style.left = '0';
            autocompleteList.style.right = '0';
            autocompleteList.style.zIndex = '1000';
            wrapper.style.position = 'relative';
            wrapper.appendChild(autocompleteList);
        }
        
        // Função para buscar cidades
        function searchCities(query) {
            if (query.length < 1) {
                if (autocompleteList) {
                    autocompleteList.innerHTML = '';
                    autocompleteList.style.display = 'none';
                }
                return;
            }

            // Obter o estado selecionado (se houver)
            const stateSelect = document.getElementById('state');
            const state = stateSelect ? stateSelect.value : '';
            
            // Construir URL da busca
            let url = buildHotelsApiUrl(query, 'city');
            if (state) url += `&state=${encodeURIComponent(state)}`;

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (autocompleteList && data.data && data.data.length > 0) {
                        autocompleteList.innerHTML = '';
                        data.data.forEach((item, index) => {
                            const listItem = document.createElement('div');
                            listItem.className = 'hotel-autocomplete-item';
                            listItem.dataset.index = index;
                            listItem.innerHTML = `
                                <i class="fas fa-city me-2"></i>
                                <span class="hotel-name">${item.city}</span>
                                ${item.state ? `<span class="hotel-location"> - ${item.state}</span>` : ''}
                            `;
                            listItem.addEventListener('click', function() {
                                cityInput.value = item.city;
                                if (autocompleteList) {
                                    autocompleteList.innerHTML = '';
                                    autocompleteList.style.display = 'none';
                                }
                                selectedIndex = -1;
                                syncQuotaPublicSearchButtonState();
                            });
                            listItem.addEventListener('mouseenter', function() {
                                if (autocompleteList) {
                                    autocompleteList.querySelectorAll('.hotel-autocomplete-item').forEach(i => i.classList.remove('active'));
                                }
                                this.classList.add('active');
                                selectedIndex = parseInt(this.dataset.index);
                            });
                            autocompleteList.appendChild(listItem);
                        });
                        autocompleteList.style.display = 'block';
                    } else if (autocompleteList) {
                        autocompleteList.innerHTML = '<div class="hotel-autocomplete-item hotel-autocomplete-no-results">Nenhuma cidade encontrada</div>';
                        autocompleteList.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar cidades:', error);
                });
        }

        // Event listener para input - busca a cada letra digitada
        cityInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            // Busca imediatamente se tiver pelo menos 1 caractere
            if (query.length >= 1) {
                searchTimeout = setTimeout(() => {
                    searchCities(query);
                }, 100);
            } else {
                if (autocompleteList) {
                    autocompleteList.innerHTML = '';
                    autocompleteList.style.display = 'none';
                }
            }
        });

        // Fechar autocomplete ao clicar fora
        document.addEventListener('click', function(e) {
            if (autocompleteList && !cityInput.contains(e.target) && !autocompleteList.contains(e.target)) {
                autocompleteList.style.display = 'none';
            }
        });

        // Navegação com teclado
        cityInput.addEventListener('keydown', function(e) {
            if (!autocompleteList) return;
            
            const items = autocompleteList.querySelectorAll('.hotel-autocomplete-item:not(.hotel-autocomplete-no-results)');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (items.length > 0) {
                    selectedIndex = (selectedIndex + 1) % items.length;
                    items.forEach((item, index) => {
                        item.classList.toggle('active', index === selectedIndex);
                    });
                    if (items[selectedIndex]) {
                        items[selectedIndex].scrollIntoView({ block: 'nearest' });
                    }
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (items.length > 0) {
                    selectedIndex = selectedIndex <= 0 ? items.length - 1 : selectedIndex - 1;
                    items.forEach((item, index) => {
                        item.classList.toggle('active', index === selectedIndex);
                    });
                    if (items[selectedIndex]) {
                        items[selectedIndex].scrollIntoView({ block: 'nearest' });
                    }
                }
            } else if (e.key === 'Enter' && selectedIndex >= 0 && items[selectedIndex]) {
                e.preventDefault();
                items[selectedIndex].click();
            } else if (e.key === 'Escape') {
                if (autocompleteList) {
                    autocompleteList.style.display = 'none';
                }
                selectedIndex = -1;
            }
        });

        // Focar no campo ao clicar no autocomplete
        cityInput.addEventListener('focus', function() {
            if (this.value.trim().length >= 1) {
                searchCities(this.value.trim());
            }
        });

        // Recarregar cidades quando o estado mudar
        const stateSelect = document.getElementById('state');
        if (stateSelect) {
            stateSelect.addEventListener('change', function() {
                // Limpar campo de cidade quando estado mudar
                if (cityInput.value.trim()) {
                    cityInput.value = '';
                    if (autocompleteList) {
                        autocompleteList.innerHTML = '';
                        autocompleteList.style.display = 'none';
                    }
                }
            });
        }
    }

    // Inicializar autocomplete quando a página carregar
    initHotelAutocomplete();
    initCityAutocomplete();
});
</script>
@endpush
@endsection
