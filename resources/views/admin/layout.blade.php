<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <meta name="theme-color" content="#f8fafc">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Painel Administrativo') - Cota Brasilis</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Paginação e utilitários compartilhados com o site (modern-pagination em vendor/pagination) -->
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        html {
            color-scheme: light;
        }

        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
            --light-bg: #f8fafc;
            --sidebar-bg: #ffffff;
            --sidebar-hover: #ecfdf5;
            --sidebar-border: #e2e8f0;
            --sidebar-text: #1e293b;
            --sidebar-muted: #64748b;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --gradient-primary: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            --gradient-success: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --gradient-warning: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --gradient-danger: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background-color: var(--light-bg);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .admin-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 280px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
            box-shadow: var(--shadow-md);
        }

        .admin-sidebar.collapsed {
            width: 80px;
        }

        .admin-sidebar .sidebar-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid var(--sidebar-border);
            background: linear-gradient(135deg, #ecfdf5, #f8fafc);
        }

        .admin-sidebar .sidebar-header .logo {
            display: flex;
            align-items: center;
            color: var(--sidebar-text);
            text-decoration: none;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .admin-sidebar .sidebar-header .logo i {
            font-size: 1.5rem;
            margin-right: 0.75rem;
        }

        .admin-sidebar .sidebar-header .logo-text {
            transition: opacity 0.3s ease;
        }

        .admin-sidebar.collapsed .sidebar-header .logo-text {
            opacity: 0;
        }

        .admin-sidebar .nav {
            padding: 1rem 0;
        }

        .admin-sidebar .nav-item {
            margin: 0.25rem 0.75rem;
        }

        .admin-sidebar .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--sidebar-muted);
            text-decoration: none;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .admin-sidebar .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.12), transparent);
            transition: left 0.5s;
        }

        .admin-sidebar .nav-link:hover::before {
            left: 100%;
        }

        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            color: #047857;
            background: var(--sidebar-hover);
            transform: translateX(4px);
        }

        .admin-sidebar .nav-link i {
            font-size: 1.1rem;
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }

        .admin-sidebar .nav-link .nav-text {
            transition: opacity 0.3s ease;
        }

        .admin-sidebar.collapsed .nav-link .nav-text {
            opacity: 0;
        }

        .admin-sidebar .nav-divider {
            height: 1px;
            background: var(--sidebar-border);
            margin: 1rem 0;
        }

        /* Main Content */
        .admin-main {
            margin-left: 280px;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .admin-main.sidebar-collapsed {
            margin-left: 80px;
        }

        .admin-header {
            background: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .admin-header .header-left {
            display: flex;
            align-items: center;
        }

        .admin-header .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--text-secondary);
            margin-right: 1rem;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            display: none;
        }

        .admin-header .sidebar-toggle:hover {
            background: var(--light-bg);
            color: var(--text-primary);
        }

        @media (max-width: 1199.98px) {
            .admin-header .sidebar-toggle {
                display: block;
            }
        }

        .admin-header .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .admin-header .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .admin-header .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: var(--light-bg);
            border-radius: 2rem;
            text-decoration: none;
            color: var(--text-primary);
            transition: all 0.3s ease;
        }

        .admin-header .user-info:hover {
            background: var(--border-color);
            color: var(--text-primary);
        }

        .admin-header .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .admin-content {
            padding: 2rem;
        }

        /* Cards */
        .admin-card {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--shadow-md);
            border: none;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .admin-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .admin-card .card-header {
            background: white;
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .admin-card .card-body {
            padding: 1.5rem;
        }

        /* Stats Cards */
        .stats-card {
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 1rem;
            overflow: hidden;
            position: relative;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }

        .stats-card.success {
            background: var(--gradient-success);
        }

        .stats-card.warning {
            background: var(--gradient-warning);
        }

        .stats-card.danger {
            background: var(--gradient-danger);
        }

        .stats-card .card-body {
            position: relative;
            z-index: 1;
        }

        .stats-card .stats-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }

        .stats-card .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }

        .stats-card .stats-label {
            font-size: 0.875rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Tables */
        .admin-table {
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        .admin-table .table {
            margin: 0;
        }

        .admin-table .table th {
            background: var(--light-bg);
            border: none;
            font-weight: 600;
            color: var(--text-primary);
            padding: 1rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .admin-table .table td {
            border: none;
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }

        .admin-table .table tbody tr:hover {
            background: var(--light-bg);
        }

        /* Buttons */
        .btn-admin {
            border-radius: 0.75rem;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn-admin::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-admin:hover::before {
            left: 100%;
        }

        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-admin-primary {
            background: var(--gradient-primary);
            color: white;
        }

        .btn-admin-success {
            background: var(--gradient-success);
            color: white;
        }

        .btn-admin-warning {
            background: var(--gradient-warning);
            color: white;
        }

        .btn-admin-danger {
            background: var(--gradient-danger);
            color: white;
        }

        /* Badges */
        .badge-admin {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 500;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Alerts */
        .alert-admin {
            border: none;
            border-radius: 1rem;
            padding: 1rem 1.5rem;
            font-weight: 500;
            box-shadow: var(--shadow-md);
        }

        .alert-admin-success {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
        }

        .alert-admin-danger {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
        }

        .alert-admin-warning {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            color: #92400e;
        }

        /* Sidebar Overlay */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
            transition: opacity 0.3s ease;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* Responsive */
        @media (max-width: 1199.98px) {
            .admin-sidebar {
                transform: translateX(-100%);
                width: 280px;
            }

            .admin-sidebar.show {
                transform: translateX(0);
            }

            .admin-main {
                margin-left: 0 !important;
            }

            .sidebar-toggle {
                display: block !important;
            }

            .sidebar-overlay.show {
                display: block;
            }
        }

        @media (max-width: 992px) {
            .admin-content {
                padding: 1.5rem;
            }

            .admin-header {
                padding: 1rem 1.5rem;
            }

            .admin-header .page-title {
                font-size: 1.5rem;
            }

            .admin-header .header-right {
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .user-info {
                display: none;
            }

            .user-avatar {
                width: 28px;
                height: 28px;
                font-size: 0.875rem;
            }
        }

        @media (max-width: 768px) {
            .admin-content {
                padding: 1rem;
            }

            .admin-header {
                padding: 1rem;
            }

            .admin-header .page-title {
                font-size: 1.25rem;
            }

            .admin-header .user-avatar {
                width: 24px;
                height: 24px;
                font-size: 0.75rem;
            }

            .admin-card {
                border-radius: 8px;
            }

            .stats-card {
                margin-bottom: 1rem;
            }

            /* Cards stack on mobile */
            .row > [class*="col-"] {
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 576px) {
            .admin-sidebar {
                width: 100%;
                max-width: 280px;
            }

            .admin-header {
                padding: 0.75rem;
            }

            .admin-header .page-title {
                font-size: 1.1rem;
            }

            .admin-content {
                padding: 0.75rem;
            }

            .page-header {
                padding: 1.5rem;
                flex-direction: column;
                gap: 1rem;
            }

            .btn-back,
            .btn-submit,
            .btn-cancel {
                width: 100%;
                text-align: center;
            }
        }

        /* Tablets */
        @media (min-width: 768px) and (max-width: 1199.98px) {
            .admin-sidebar {
                width: 280px;
            }

            .admin-content {
                padding: 2rem 1.5rem;
            }
        }

        /* Loading Animation */
        .loading {
            position: relative;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Custom Scrollbar */
        .admin-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .admin-sidebar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .admin-sidebar::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, 0.35);
            border-radius: 3px;
        }

        .admin-sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.55);
        }

    </style>
    
    @stack('styles')
</head>
<body class="@yield('body-class')">
    <script>
        (function () {
            document.documentElement.setAttribute('data-bs-theme', 'light');
            document.documentElement.style.colorScheme = 'light';
            if (document.body) {
                document.body.setAttribute('data-bs-theme', 'light');
                document.body.style.colorScheme = 'light';
            }
            try {
                ['theme', 'color-theme', 'bs-theme', 'colorMode', 'darkMode'].forEach(function (k) {
                    localStorage.removeItem(k);
                });
            } catch (e) {}
        })();
    </script>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar -->
    <nav class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="logo">
                <i class="bi bi-shield-check"></i>
                <span class="logo-text">Admin Panel</span>
            </a>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-speedometer2"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                    <i class="bi bi-people"></i>
                    <span class="nav-text">Usuários</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.profile-approvals.*') ? 'active' : '' }}" href="{{ route('admin.profile-approvals.index') }}">
                    <i class="bi bi-person-check"></i>
                    <span class="nav-text">Aprovação de perfis</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.hotels.*') ? 'active' : '' }}" href="{{ route('admin.hotels.index') }}">
                    <i class="bi bi-building"></i>
                    <span class="nav-text">Hotéis</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.success-fees.*') ? 'active' : '' }}" href="{{ route('admin.success-fees.index') }}">
                    <i class="bi bi-currency-dollar"></i>
                    <span class="nav-text">Taxas de Êxito</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.educational.*') ? 'active' : '' }}" href="{{ route('admin.educational.index') }}">
                    <i class="bi bi-journal-bookmark"></i>
                    <span class="nav-text">Conteúdo educativo</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.bora-la-posts.*') ? 'active' : '' }}" href="{{ route('admin.bora-la-posts.index') }}">
                    <i class="bi bi-megaphone"></i>
                    <span class="nav-text">Bora lá (publicações)</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.site-information.*') ? 'active' : '' }}" href="{{ route('admin.site-information.index') }}">
                    <i class="bi bi-info-square"></i>
                    <span class="nav-text">Informações do site</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.platform-documents.*') ? 'active' : '' }}" href="{{ route('admin.platform-documents.edit') }}">
                    <i class="bi bi-file-earmark-text"></i>
                    <span class="nav-text">Documentos (termos)</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.data-exchange.*') ? 'active' : '' }}" href="{{ route('admin.data-exchange.index') }}">
                    <i class="bi bi-database-arrow-down-up"></i>
                    <span class="nav-text">Exportar / importar dados</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}" href="{{ route('admin.faqs.index') }}">
                    <i class="bi bi-question-circle"></i>
                    <span class="nav-text">Perguntas frequentes</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.quotas.*') ? 'active' : '' }}" href="{{ route('admin.quotas.index') }}">
                    <i class="bi bi-calendar-check"></i>
                    <span class="nav-text">Cotas</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}" href="{{ route('admin.transactions.index') }}">
                    <i class="bi bi-arrow-left-right"></i>
                    <span class="nav-text">Transações</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}" href="{{ route('admin.notifications.index') }}">
                    <i class="bi bi-bell"></i>
                    <span class="nav-text">Notificações</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}" href="{{ route('admin.logs.index') }}">
                    <i class="bi bi-journal-text"></i>
                    <span class="nav-text">Logs</span>
                </a>
            </li>
        </ul>
        
        <div class="nav-divider"></div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="bi bi-arrow-left"></i>
                    <span class="nav-text">Voltar ao Sistema</span>
                </a>
            </li>
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}" class="d-inline w-100">
                    @csrf
                    <button type="submit" class="nav-link btn btn-link text-start w-100">
                        <i class="bi bi-box-arrow-right"></i>
                        <span class="nav-text">Sair</span>
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="admin-main" id="adminMain">
        <!-- Header -->
        <header class="admin-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                </div>
                
                <div class="header-right">
                    <a href="#" class="user-info" data-bs-toggle="dropdown">
                        <div class="user-avatar">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="fw-semibold">{{ auth()->user()->name }}</div>
                            <small class="text-muted">{{ ucfirst(auth()->user()->role) }}</small>
                        </div>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('profile.show') }}">
                            <i class="bi bi-person me-2"></i>Meu Perfil
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="bi bi-gear me-2"></i>Configurações
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right me-2"></i>Sair
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="admin-content">
            @if(session('success'))
                <div class="alert alert-admin alert-admin-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-admin alert-admin-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Sidebar Toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('adminSidebar');
        const main = document.getElementById('adminMain');
        const overlay = document.getElementById('sidebarOverlay');

        // Desktop sidebar toggle (collapse/expand)
        function handleDesktopToggle() {
            if (window.innerWidth > 1199) {
                sidebar.classList.toggle('collapsed');
                main.classList.toggle('sidebar-collapsed');
            }
        }

        // Mobile/Tablet sidebar toggle (show/hide)
        function handleMobileToggle() {
            if (window.innerWidth <= 1199) {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            }
        }

        // Main toggle handler
        sidebarToggle.addEventListener('click', function() {
            if (window.innerWidth > 1199) {
                handleDesktopToggle();
            } else {
                handleMobileToggle();
            }
        });

        // Close sidebar when clicking overlay
        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        }

        // Close sidebar when clicking a nav link on mobile
        document.querySelectorAll('.admin-sidebar .nav-link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 1199) {
                    sidebar.classList.remove('show');
                    if (overlay) {
                        overlay.classList.remove('show');
                    }
                }
            });
        });

        // Handle window resize
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth > 1199) {
                    sidebar.classList.remove('show');
                    if (overlay) {
                        overlay.classList.remove('show');
                    }
                } else {
                    sidebar.classList.remove('collapsed');
                    main.classList.remove('sidebar-collapsed');
                }
            }, 250);
        });

        // Auto-hide alerts
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });

        // Loading states for forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }
            });
        });

        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add ripple effect to buttons
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple');
                
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });
    </script>
    
    @stack('scripts')
    @yield('scripts')
</body>
</html>