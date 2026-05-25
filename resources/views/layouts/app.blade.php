<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <meta name="theme-color" content="#f8fafc">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Cota Brasilis')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <script>
        function highlight_map_states() {
            if ($(".states_section").length > 0) {
                $(".states_section .list_states .item .link").hover(function() {
                    var a = "#state_" + $(this).text().toLowerCase();
                    $(a).attr("class", "state hover")
                }, function() {
                    var a = "#state_" + $(this).text().toLowerCase();
                    $(a).attr("class", "state")
                })
            }
        };
    </script>
    <style>
        #map {
            display: none;
        }

        .hero-section .hero-map-frame #map {
            display: block !important;
        }

        #map .state {
            cursor: pointer;
        }

        #map .state .shape {
            cursor: pointer;
            -width: 0;
        }

        #map .state .label_icon_state {
            fill: #fff;
            font-family: Arial;
            font-size: 11px;
            line-height: 12px;
            font-weight: normal;
        }

        #map .state .label_state {
            display: none;
            font-family: Arial;
            font-size: 14px;
            line-height: 16px;
            font-weight: bold;
        }

        #map .state:hover .label_state,
        #map .state.hover .label_state {
            display: block;
        }

        #map .model-green .state .shape {
            fill: #6cb361;
        }

        #map .model-green .state .icon_state {
            fill: #10592f;
        }

        #map .model-green .state .label_icon_state {
            fill: #fff;
        }

        #map .model-green .state .label_state {
            fill: #666;
        }

        #map .model-green .state:hover .shape,
        #map .model-green .state.hover .shape {
            fill: #2d68b2;
        }

        #map .model-green .state:hover .icon_state,
        #map .model-green .state.hover .icon_state {
            fill: #5a95ce;
        }

        #map .model-orange .state .shape {
            fill: #fd7132;
        }

        #map .model-orange .state .icon_state {
            fill: #6cb361;
        }

        #map .model-orange .state .label_icon_state {
            fill: #fff;
        }

        #map .model-orange .state .label_state {
            fill: #666;
        }

        #map .model-orange .state:hover .shape,
        #map .model-orange .state.hover .shape {
            fill: #c93f04;
        }

        #map .model-orange .state:hover .icon_state,
        #map .model-orange .state.hover .icon_state {
            fill: #10592f;
        }

        #map .model-darkgreen .state .shape {
            fill: #366823;
        }

        #map .model-darkgreen .state .icon_state {
            fill: #2779c6;
        }

        #map .model-darkgreen .state .label_icon_state {
            fill: #fff;
        }

        #map .model-darkgreen .state .label_state {
            fill: #666;
        }

        #map .model-darkgreen .state:hover .shape,
        #map .model-darkgreen .state.hover .shape {
            fill: #4a8c31;
        }

        #map .model-darkgreen .state:hover .icon_state,
        #map .model-darkgreen .state.hover .icon_state {
            fill: #5a95ce;
        }
    </style>
    <style>
        :root {
            --primary-color: #009739;
            --primary-dark: #007A2F;
            --primary-yellow: #E1AD01;
            --secondary-color: #64748b;
            --success-color: #009739;
            --warning-color: #E1AD01;
            --danger-color: #ef4444;
            --info-color: #009739;
            --light-bg: #f8fafc;
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        /* Títulos legíveis em cards com fundo verde */
        .card-header.bg-success,
        .card-header.bg-success h1,
        .card-header.bg-success h2,
        .card-header.bg-success h3,
        .card-header.bg-success h4,
        .card-header.bg-success h5,
        .card-header.bg-success h6,
        .card-header.bg-success .fw-bold,
        .card-header.bg-success p,
        .card-header.bg-success small,
        .card-header.bg-success i,
        .card-header.bg-success a,
        .card[style*="linear-gradient(135deg, #009739"] .card-body > h1,
        .card[style*="linear-gradient(135deg, #009739"] .card-body > h2,
        .card[style*="linear-gradient(135deg, #009739"] .card-body > h3,
        .card[style*="linear-gradient(135deg, #009739"] .card-body > h4,
        .card[style*="linear-gradient(135deg, #009739"] .card-body > h5,
        .card[style*="linear-gradient(135deg, #009739"] .card-body > h6,
        .card[style*="linear-gradient(135deg, #009739"] .card-body > .fw-bold:first-child,
        .card[style*="background: linear-gradient(135deg, #009739"] .card-body > h1,
        .card[style*="background: linear-gradient(135deg, #009739"] .card-body > h2,
        .card[style*="background: linear-gradient(135deg, #009739"] .card-body > h3,
        .card[style*="background: linear-gradient(135deg, #009739"] .card-body > h4,
        .card[style*="background: linear-gradient(135deg, #009739"] .card-body > h5,
        .card[style*="background: linear-gradient(135deg, #009739"] .card-body > h6,
        .card-header[style*="linear-gradient"][class*="text-white"],
        .card-header[style*="background: #009739"],
        .card-header[style*="background:#009739"],
        .card-header[style*="background: linear-gradient(135deg, #0a8f3f"],
        .card-header[style*="background: linear-gradient(135deg, #009739"] {
            color: #ffffff !important;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--light-bg);
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* Navigation */
        .navbar {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.75rem;
            color: var(--primary-color) !important;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .navbar-brand:hover {
            color: var(--primary-dark) !important;
        }

        .navbar-brand img {
            height: 90px;
            max-width: 400px;
            object-fit: contain;
        }

        .navbar-brand i {
            margin-right: 0.5rem;
            font-size: 1.5rem;
        }

        .nav-link {
            font-weight: 500;
            color: var(--text-secondary) !important;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 50%;
            background-color: var(--primary-color);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* Profile Badge */
        .profile-badge {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
            box-shadow: var(--shadow-sm);
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
            padding: 1.25rem;
        }

        .card-header h5 {
            font-weight: 600;
            margin: 0;
        }

        /* Buttons */
        .btn {
            border-radius: 0.75rem;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), #1e40af);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #059669);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Form Controls */
        .form-control {
            border: 2px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-weight: 400;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 1rem;
            padding: 1rem 1.25rem;
            font-weight: 500;
        }

        .alert-success {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
        }

        .alert-warning {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            color: #92400e;
        }

        /* Rodapé — tema claro (sem bloco escuro) */
        .footer {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #ecfdf5, #f8fafc);
            color: var(--text-primary);
            padding: 3rem 0 2rem;
            margin-top: 4rem;
            border-top: 1px solid rgba(0, 151, 57, 0.18);
        }

        .footer h5,
        .footer h6 {
            font-weight: 600;
            margin-bottom: 1rem;
            color: #047857 !important;
        }

        html body footer.footer .text-white {
            color: #0f766e !important;
        }

        html body footer.footer .text-white-50 {
            color: #64748b !important;
        }

        html body footer.footer a.text-white {
            color: #0d9488 !important;
        }

        html body footer.footer a.text-white-50 {
            color: #475569 !important;
        }

        html body footer.footer a.text-white-50:hover {
            color: var(--primary-color) !important;
        }

        /* Menor espaço entre colunas do rodapé (gutter Bootstrap é largo em col-lg-3) */
        @media (min-width: 992px) {
            .footer .footer-col-recursos-painel-gap {
                padding-right: 0.4rem;
            }
            .footer .footer-col-painel-recursos-gap {
                padding-left: 0.4rem;
            }
            /* PAINEL DE CONTROLE ↔ LEGAL: mesmo “aperto” que RECURSOS ↔ PAINEL */
            .footer .footer-col-painel-legal-gap {
                padding-right: 0.4rem !important;
            }
            .footer .footer-col-legal-painel-gap {
                padding-left: 0.4rem !important;
            }
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.25rem;
            }
            
            .navbar-brand img {
                height: 70px;
                max-width: 300px;
            }

            .card {
                margin-bottom: 1rem;
            }
        }

        /* Loading States */
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
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--light-bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 1.2rem;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: var(--shadow-lg);
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-xl);
        }

        /* Ripple Effect */
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }

        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        /* Loading States */
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

        /* Enhanced Card Hover Effects */
        .card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        /* Enhanced Button Effects */
        .btn {
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        /* Enhanced Form Controls */
        .form-control {
            transition: all 0.3s ease;
        }

        .form-control:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.15);
        }

        /* Enhanced Alerts */
        .alert {
            border: none;
            border-radius: 1rem;
            box-shadow: var(--shadow-md);
            animation: slideInDown 0.5s ease;
        }

        @keyframes slideInDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Enhanced Navigation */
        .navbar {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95);
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: var(--shadow-md);
        }

        /* Enhanced Profile Badge */
        .profile-badge {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
            box-shadow: var(--shadow-sm);
            animation: pulse 2s infinite;
        }

        /* Dropdown Menu Fix - Ensure it stays above all elements */
        .dropdown-menu {
            z-index: 9999 !important;
            position: absolute !important;
            top: 100% !important;
            left: 0 !important;
            min-width: 200px !important;
            background: white !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 0.75rem !important;
            box-shadow: var(--shadow-lg) !important;
            padding: 0.5rem 0 !important;
            margin-top: 0.5rem !important;
        }

        .dropdown-item {
            padding: 0.75rem 1.25rem !important;
            color: var(--text-primary) !important;
            text-decoration: none !important;
            transition: all 0.3s ease !important;
            border: none !important;
            background: none !important;
            width: 100% !important;
            text-align: left !important;
            display: flex !important;
            align-items: center !important;
        }

        .dropdown-item:hover {
            background: var(--light-bg) !important;
            color: var(--primary-color) !important;
            transform: translateX(5px) !important;
        }

        .dropdown-item i {
            width: 20px !important;
            margin-right: 0.75rem !important;
        }

        .dropdown-divider {
            margin: 0.5rem 0 !important;
            border-color: var(--border-color) !important;
        }

        /* Ensure dropdown container has proper positioning */
        .nav-item.dropdown {
            position: relative !important;
        }

        /* Fix for any overlapping elements */
        .navbar {
            z-index: 1000 !important;
        }

        /* Ensure main content doesn't overlap dropdown */
        main.container {
            position: relative !important;
            z-index: 1 !important;
        }

        /* Additional fixes for dropdown positioning */
        .navbar-nav .dropdown-menu {
            position: absolute !important;
            top: 100% !important;
            left: auto !important;
            right: 0 !important;
            transform: none !important;
            will-change: auto !important;
        }

        /* Ensure dropdown is visible above cards and other elements */
        .dropdown-menu.show {
            display: block !important;
            z-index: 9999 !important;
        }

        /* Fix for Bootstrap dropdown positioning */
        .dropdown-toggle::after {
            margin-left: 0.5rem !important;
        }

        /* Ensure dropdown doesn't get clipped by parent containers */
        .navbar-collapse {
            overflow: visible !important;
        }

        /* Fix for any potential overflow issues */
        .navbar {
            overflow: visible !important;
        }

        /* Ensure dropdown items are properly styled */
        .dropdown-item:focus,
        .dropdown-item:active {
            background: var(--light-bg) !important;
            color: var(--primary-color) !important;
            outline: none !important;
        }

        /* Fix for form elements inside dropdown */
        .dropdown-item form {
            margin: 0 !important;
            padding: 0 !important;
        }

        .dropdown-item button {
            width: 100% !important;
            text-align: left !important;
            padding: 0.75rem 1.25rem !important;
            border: none !important;
            background: none !important;
            color: inherit !important;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--primary-color), transparent);
        }

        /* Enhanced Animations */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Enhanced Responsive Design */
        @media (max-width: 768px) {
            .back-to-top {
                bottom: 1rem;
                right: 1rem;
                width: 45px;
                height: 45px;
                font-size: 1rem;
            }

            .card:hover {
                transform: translateY(-4px) scale(1.01);
            }

            .btn:hover {
                transform: translateY(-1px);
            }
        }

        /* Reduced Motion */
        @media (prefers-reduced-motion: reduce) {

            .card,
            .btn,
            .form-control,
            .alert,
            .fade-in {
                transition: none;
                animation: none;
            }
        }

        /* Client Sidebar Layout */
        .client-sidebar-toggle {
            border-radius: 1rem;
            border: 1px solid var(--border-color);
            background: #fff;
            box-shadow: var(--shadow-sm);
            padding: 0.85rem 1.25rem;
            font-weight: 600;
        }

        .client-sidebar-card {
            border-radius: 1.5rem;
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: var(--shadow-md);
            background: linear-gradient(160deg, rgba(0, 151, 57, 0.1), rgba(255, 255, 255, 0.95));
            overflow: hidden;
        }

        .client-sidebar-card .sidebar-header {
            background: rgba(0, 151, 57, 0.12);
            padding: 1.75rem 1.5rem;
            text-align: center;
        }

        .client-sidebar-card .sidebar-header .avatar {
            width: 58px;
            height: 58px;
            border-radius: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            color: var(--primary-color);
            font-size: 1.5rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 1rem;
        }

        .client-sidebar-links .client-sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.95rem 1.5rem;
            border: none;
            border-left: 4px solid transparent;
            border-radius: 0;
            background: transparent;
            color: var(--text-secondary);
            font-weight: 600;
            letter-spacing: 0.01em;
            transition: all 0.2s ease;
        }

        .client-sidebar-links .client-sidebar-link i {
            font-size: 1.05rem;
            opacity: 0.75;
        }

        .client-sidebar-links .client-sidebar-link.active,
        .client-sidebar-links .client-sidebar-link:hover {
            background: rgba(0, 151, 57, 0.1);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
            transform: translateX(4px);
        }

        .client-sidebar-card .sidebar-footer {
            padding: 1.25rem 1.5rem;
            border-top: 1px solid rgba(15, 23, 42, 0.08);
            background: #fff;
        }

        .client-sidebar-card .badge-tier {
            background: rgba(0, 151, 57, 0.12);
            color: var(--primary-color);
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.35rem 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        @media (max-width: 991.98px) {
            #clientSidebarMenu.collapse:not(.show) {
                display: none;
            }

            .client-sidebar-card {
                border-radius: 1.25rem;
            }
        }

        @media (min-width: 992px) {
            .client-sidebar-card {
                position: sticky;
                top: 110px;
            }
        }
    </style>

    @stack('styles')
    
    <!-- Pagination Modern Styles -->
    <style>
        .pagination-wrapper-modern {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
            padding: 1.5rem 0;
            border-top: 1px solid rgba(0, 151, 57, 0.1);
            margin-top: 2rem;
        }

        .pagination-info-modern {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .pagination-info-modern strong {
            color: #009739;
            font-weight: 600;
        }

        .pagination-modern-list {
            display: flex !important;
            align-items: center;
            gap: 0.35rem !important;
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
            flex-wrap: wrap;
            justify-content: center;
        }

        .pagination-modern-item {
            margin: 0 !important;
            list-style: none !important;
        }

        .pagination-modern-link {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 36px !important;
            height: 36px !important;
            padding: 0 0.5rem !important;
            background: var(--light-bg) !important;
            border: 1px solid rgba(0, 151, 57, 0.2) !important;
            border-radius: 6px !important;
            color: #64748b !important;
            font-weight: 500 !important;
            font-size: 0.875rem !important;
            text-decoration: none !important;
            transition: all 0.2s ease !important;
            cursor: pointer !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
            margin: 0 !important;
        }

        .pagination-modern-link:hover:not(.pagination-modern-link-disabled):not(.pagination-modern-link-active) {
            background: rgba(0, 151, 57, 0.06) !important;
            border-color: rgba(0, 151, 57, 0.35) !important;
            color: #009739 !important;
            box-shadow: 0 2px 6px rgba(0, 151, 57, 0.12) !important;
        }

        .pagination-modern-link-active {
            background: #009739 !important;
            border-color: #009739 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(0, 151, 57, 0.2) !important;
            font-weight: 600 !important;
            cursor: default !important;
        }

        .pagination-modern-link-disabled {
            background: #f8f9fa !important;
            border-color: #e2e8f0 !important;
            color: #cbd5e1 !important;
            cursor: not-allowed !important;
            opacity: 0.6 !important;
            transform: none !important;
            box-shadow: none !important;
        }

        .pagination-modern-link-disabled:hover {
            background: #f8f9fa !important;
            border-color: #e2e8f0 !important;
            color: #cbd5e1 !important;
            transform: none !important;
            box-shadow: none !important;
        }

        .pagination-modern-link-prev,
        .pagination-modern-link-next {
            min-width: 36px !important;
            font-weight: 500 !important;
        }

        .pagination-modern-link i {
            font-size: 0.75rem;
        }

        .pagination-modern-item-dots {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
        }

        .pagination-modern-dots {
            color: #cbd5e1;
            font-weight: 500;
            font-size: 0.875rem;
            line-height: 1;
        }

        @media (max-width: 768px) {
            .pagination-wrapper-modern {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem 0;
            }

            .pagination-info-modern {
                margin: 0;
                text-align: center;
                width: 100%;
                justify-content: center;
                font-size: 0.8rem;
            }

            .pagination-modern-link {
                min-width: 32px !important;
                height: 32px !important;
                padding: 0 0.4rem !important;
                font-size: 0.8rem !important;
            }
            
            .pagination-modern-link-prev,
            .pagination-modern-link-next {
                min-width: 32px !important;
            }
        }
    </style>
</head>

<body>
    <script>
        (function () {
            document.documentElement.setAttribute('data-bs-theme', 'light');
            document.documentElement.style.colorScheme = 'light';
            if (document.body) {
                document.body.setAttribute('data-bs-theme', 'light');
                document.body.style.colorScheme = 'light';
            }
            try {
                ['theme', 'color-theme', 'bs-theme', 'colorMode', 'darkMode', 'prefers-color-scheme'].forEach(function (k) {
                    localStorage.removeItem(k);
                });
            } catch (e) {}
        })();
    </script>
    @php
        $isOwnerDelegatorOnly = auth()->check()
            && !auth()->user()->hasAdminPrivileges()
            && auth()->user()->isProfileApproved()
            && auth()->user()->profile
            && (string) (auth()->user()->profile->getRawOriginal('has_quota') ?? auth()->user()->profile->has_quota) === '3';
        $showClientSidebar = auth()->check()
            && !auth()->user()->hasAdminPrivileges()
            && auth()->user()->isProfileApproved()
            && !$isOwnerDelegatorOnly;
        $clientProfile = $showClientSidebar ? auth()->user()->profile : null;
        $clientProfileConfig = $clientProfile ? $clientProfile->getProfileConfig() : [];
        $hideHeader = request()->routeIs('login') || request()->routeIs('register') || request()->routeIs('password.*');
        // Aguardando aprovação / reprovado: sem header global, sem rodapé, só o conteúdo da mensagem
        $minimalAccountGateLayout = auth()->check()
            && !auth()->user()->hasAdminPrivileges()
            && !auth()->user()->isProfileApproved();
    @endphp

    <!-- Navigation -->
    @unless($showClientSidebar || $hideHeader || $minimalAccountGateLayout || $isOwnerDelegatorOnly)
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <img src="{{ asset('images/logo/logo.png') }}" alt="Cota Brasilis" style="height: 90px; max-width: 400px;">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('quotas.index') }}">
                            <i class="fas fa-search me-1"></i>Explorar Destinos
                        </a>
                    </li>
                    @if(auth()->user()->profile && auth()->user()->profile->getProfileConfig()['can_publish'])
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('quotas.create') }}">
                            <i class="fas fa-plus me-1"></i>Oferecer Cota
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('quotas.my') }}">
                            <i class="fas fa-list me-1"></i>Minhas Viagens
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('transactions.index') }}">
                            <i class="fas fa-exchange-alt me-1"></i>Transações
                        </a>
                    </li>
                    @endauth
                </ul>

                <ul class="navbar-nav">
                    @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <x-user-avatar :profile="auth()->user()->profile" :size="32" class="me-2" alt="Foto" />
                            {{ auth()->user()->name }}
                            @if(auth()->user()->profile)
                            <span class="profile-badge ms-2">{{ auth()->user()->profile->profile_type }}</span>
                            @endif
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('profile.show') }}">
                                    <i class="fas fa-user me-2"></i>Meu Perfil
                                </a></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="fas fa-edit me-2"></i>Editar Perfil
                                </a></li>
                            @if(auth()->user()->hasAdminPrivileges())
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                    <i class="fas fa-cog me-2"></i>Painel Administrativo
                                </a></li>
                            @endif
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Sair
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm mt-2 mt-lg-0">
                                <i class="fas fa-sign-out-alt me-1"></i>Sair
                            </button>
                        </form>
                    </li>
                    @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt me-1"></i>Entrar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">
                            <i class="fas fa-user-plus me-1"></i>Cadastrar
                        </a>
                    </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
    @endunless
    <!-- Main Content -->
    <main class="{{ $showClientSidebar ? 'container-fluid px-lg-5' : 'container' }} my-4">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Erro!</strong> Por favor, corrija os seguintes problemas:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($showClientSidebar)
        <div class="row g-4 align-items-start">
            <div class="col-12 col-lg-3 col-xl-2">
                <button class="client-sidebar-toggle btn d-lg-none w-100 mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#clientSidebarMenu" aria-expanded="false" aria-controls="clientSidebarMenu">
                    <i class="fas fa-bars me-2"></i>Menu do Cliente
                </button>

                <div id="clientSidebarMenu" class="collapse d-lg-block">
                    <div class="client-sidebar-card card border-0">
                        <div class="sidebar-header text-center">
                            <x-user-avatar :profile="$clientProfile" :size="88" class="avatar border border-3 border-white shadow-sm" alt="Foto do cliente" />
                            <h5 class="fw-bold mb-1 mt-3">{{ \Illuminate\Support\Str::limit(auth()->user()->name, 26) }}</h5>
                            @if($clientProfile)
                                <span class="badge-tier">{{ ucfirst($clientProfile->profile_type) }}</span>
                            @endif
                            <span class="d-block text-muted small mt-2">
                                <i class="fas fa-clock me-1"></i>{{ now()->format('d/m/Y H:i') }}
                            </span>
                        </div>

                        <div class="list-group client-sidebar-links list-group-flush">
                            <!-- 1. Painel de Controle -->
                            <a href="{{ route('dashboard') }}" class="client-sidebar-link list-group-item list-group-item-action {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="fas fa-chart-line"></i>
                                <span>Painel de Controle</span>
                            </a>

                            <!-- 2. Cadastrar nova cota -->
                            @if(\Illuminate\Support\Facades\Route::has('quotas.create'))
                            <a href="{{ route('quotas.create') }}" class="client-sidebar-link list-group-item list-group-item-action {{ request()->routeIs('quotas.create') ? 'active' : '' }}">
                                <i class="fas fa-plus-circle"></i>
                                <span>Cadastrar nova cota</span>
                            </a>
                            @endif
                           
                            <!-- 3. Aluguel -->
                            @if(\Illuminate\Support\Facades\Route::has('rental-offers.my'))
                            <a href="{{ route('rental-offers.my') }}" class="client-sidebar-link list-group-item list-group-item-action {{ request()->routeIs('rental-offers.my') ? 'active' : '' }}">
                                <i class="fas fa-calendar-check"></i>
                                <span>Aluguel</span>
                            </a>
                            @endif

                            <!-- 4. Troca -->
                            @if(\Illuminate\Support\Facades\Route::has('exchanges.index'))
                            <a href="{{ route('exchanges.index') }}" class="client-sidebar-link list-group-item list-group-item-action {{ request()->routeIs('exchanges.*') ? 'active' : '' }}">
                                <i class="fas fa-exchange-alt"></i>
                                <span>Troca</span>
                            </a>
                            @endif

                            <!-- 5. Compra -->
                            @if(\Illuminate\Support\Facades\Route::has('purchases.index'))
                            <a href="{{ route('purchases.index') }}" class="client-sidebar-link list-group-item list-group-item-action {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Compra</span>
                            </a>
                            @endif

                            <!-- 6. Venda -->
                            @if(\Illuminate\Support\Facades\Route::has('sales.index'))
                            <a href="{{ route('sales.index') }}" class="client-sidebar-link list-group-item list-group-item-action {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                                <i class="fas fa-hand-holding-usd"></i>
                                <span>Venda</span>
                            </a>
                            @endif

                            <!-- 7. Favoritos -->
                            @if(\Illuminate\Support\Facades\Route::has('client.favorites'))
                            <a href="{{ route('client.favorites') }}" class="client-sidebar-link list-group-item list-group-item-action {{ request()->routeIs('client.favorites') ? 'active' : '' }}">
                                <i class="fas fa-heart"></i>
                                <span>Favoritos</span>
                            </a>
                            @endif

                            <!-- 8. Desejados -->
                            @if(\Illuminate\Support\Facades\Route::has('client.wishlist'))
                            <a href="{{ route('client.wishlist') }}" class="client-sidebar-link list-group-item list-group-item-action {{ request()->routeIs('client.wishlist') ? 'active' : '' }}">
                                <i class="fas fa-star"></i>
                                <span>Desejados</span>
                            </a>
                            @endif

                            <!-- 9. Bora lá! Cota Brasilis -->
                            @if(\Illuminate\Support\Facades\Route::has('bora-la.index'))
                            <a href="{{ route('bora-la.index') }}" class="client-sidebar-link list-group-item list-group-item-action {{ request()->routeIs('bora-la.*') ? 'active' : '' }}">
                                <i class="fas fa-gift"></i>
                                <span>Bora lá! Cota Brasilis</span>
                            </a>
                            @endif

                            <!-- 10. Conteúdo educativo -->
                            @if(\Illuminate\Support\Facades\Route::has('educational.index'))
                            <a href="{{ route('educational.index') }}" class="client-sidebar-link list-group-item list-group-item-action {{ request()->routeIs('educational.*') ? 'active' : '' }}">
                                <i class="fas fa-graduation-cap"></i>
                                <span>Conteúdo educativo</span>
                            </a>
                            @endif

                            <!-- 11. Termos de autorização -->
                            @if(\Illuminate\Support\Facades\Route::has('client.authorization-terms'))
                            <a href="{{ route('client.authorization-terms') }}" class="client-sidebar-link list-group-item list-group-item-action {{ request()->routeIs('client.authorization-terms') ? 'active' : '' }}">
                                <i class="fas fa-file-signature"></i>
                                <span>Termos de autorização</span>
                            </a>
                            @endif

                            <!-- 12. Meu perfil -->
                            <a href="{{ route('profile.show') }}" class="client-sidebar-link list-group-item list-group-item-action {{ request()->routeIs('profile.show') ? 'active' : '' }}">
                                <i class="fas fa-user"></i>
                                <span>Meu perfil</span>
                            </a>

                            <!-- Sair da conta -->
                            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                                @csrf
                                <button type="submit" class="client-sidebar-link list-group-item list-group-item-action text-start text-danger fw-semibold" style="border-radius: 14px;">
                                    <i class="fas fa-sign-out-alt me-2"></i>Sair da conta
                                </button>
                            </form>
                        </div>

                        <div class="sidebar-footer">
                            @if(($clientProfileConfig['can_publish'] ?? false) && !request()->routeIs('quotas.create'))
                            <a href="{{ route('quotas.create') }}" class="btn btn-primary w-100">
                                <i class="fas fa-rocket me-2"></i>Nova Oferta Premium
                            </a>
                            @else
                            <a href="{{ route('profile.show') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-shield-alt me-2"></i>Verificar Minha Conta
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-9 col-xl-10">
                @yield('content')
            </div>
        </div>
        @else
        @yield('content')
        @endif
    </main>

    <!-- Footer -->
    @unless($hideHeader || $minimalAccountGateLayout)
    <footer class="footer" id="site-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <img src="{{ asset('images/logo/logo.png') }}" alt="Cota Brasilis" style="height: 200px; max-width: 800px; margin-bottom: 1rem;">
                    <p class="mb-3 text-white-50">A plataforma completa de Hospedagem por cotas de multipropriedade hoteleira para democratização do turismo no Brasil. Conectamos proprietários e também locatários pelo menor custo, de forma segura, ágil e inteligente.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3 text-white">PLATAFORMA</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ route('site.page', 'historia') }}" class="text-white-50 text-decoration-none">História</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'como-funciona') }}" class="text-white-50 text-decoration-none">Como funciona</a></li>
                        <li class="mb-2"><a href="{{ route('faq') }}" class="text-white-50 text-decoration-none">Perguntas Frequentes e Respostas</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4 footer-col-recursos-painel-gap">
                    <h6 class="fw-bold mb-3 text-white">RECURSOS</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ route('site.page', 'fracionamento') }}" class="text-white-50 text-decoration-none">Fracionamento</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'destacar-oferta') }}" class="text-white-50 text-decoration-none">Destacar oferta</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'alerta-publicacao') }}" class="text-white-50 text-decoration-none">Alerta de publicação</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'leilao') }}" class="text-white-50 text-decoration-none">Leilão</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'sofamais') }}" class="text-white-50 text-decoration-none">SofáMais</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'superdesconto') }}" class="text-white-50 text-decoration-none">SuperDesconto</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'megaoferta') }}" class="text-white-50 text-decoration-none">MegaOferta</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'oferta-unica') }}" class="text-white-50 text-decoration-none">OfertaÚnica</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'troca-simples') }}" class="text-white-50 text-decoration-none">Troca Simples</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'troca-justa') }}" class="text-white-50 text-decoration-none">Troca Justa</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'torei-na-vespera') }}" class="text-white-50 text-decoration-none">Torei na Véspera</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'torei-no-dia') }}" class="text-white-50 text-decoration-none">Torei no Dia</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4 footer-col-painel-recursos-gap footer-col-painel-legal-gap">
                    <h6 class="fw-bold mb-3 text-white">PAINEL DE CONTROLE</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ route('site.page', 'painel-de-controle') }}" class="text-white-50 text-decoration-none">Painel de controle</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'cadastrar-nova-cota') }}" class="text-white-50 text-decoration-none">Cadastrar nova cota</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'aluguel') }}" class="text-white-50 text-decoration-none">Aluguel</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'troca') }}" class="text-white-50 text-decoration-none">Troca</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'compra') }}" class="text-white-50 text-decoration-none">Compra</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'venda') }}" class="text-white-50 text-decoration-none">Venda</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'favoritos') }}" class="text-white-50 text-decoration-none">Favoritos</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'desejados') }}" class="text-white-50 text-decoration-none">Desejados</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'bora-la-cota-brasilis') }}" class="text-white-50 text-decoration-none">Bora lá! Cota Brasilis</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'conteudo-educativo') }}" class="text-white-50 text-decoration-none">Conteúdo educativo</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'termo-de-autorizacao') }}" class="text-white-50 text-decoration-none">Termo de autorização</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'meu-perfil') }}" class="text-white-50 text-decoration-none">Meu perfil</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 footer-col-legal-painel-gap">
                    <h6 class="fw-bold mb-3 text-white">LEGAL</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ route('site.page', 'termos-uso') }}" class="text-white-50 text-decoration-none">Termos de Uso</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'termos-autorizacao') }}" class="text-white-50 text-decoration-none">Termos de Autorização</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'politicas') }}" class="text-white-50 text-decoration-none">Políticas</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'cookies') }}" class="text-white-50 text-decoration-none">Cookies</a></li>
                        <li class="mb-2"><a href="{{ route('site.page', 'lgpd') }}" class="text-white-50 text-decoration-none">LGPD</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 border-secondary opacity-50">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-white-50">&copy; {{ date('Y') }} Todos os direitos reservados.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-white-50">Feito com <i class="fas fa-heart text-danger"></i> no Brasil</p>
                </div>
            </div>
        </div>
    </footer>
    @endunless

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Custom JS -->
    <script src="{{ asset('js/custom.js') }}"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
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

        // Add loading state to forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }
            });
        });

        // Auto-hide de alerts desativado por padrão.
        // Caso queira que algum alerta feche sozinho, adicione a classe \"auto-dismiss\"
        // e ele será fechado após 5 segundos.
        document.querySelectorAll('.alert.auto-dismiss').forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });

        // Add fade-in animation to cards
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.card').forEach(card => {
                card.classList.add('fade-in');
            });
        });

        // Fix dropdown positioning and behavior
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure dropdown stays above all elements
            const dropdowns = document.querySelectorAll('.dropdown');
            dropdowns.forEach(dropdown => {
                const menu = dropdown.querySelector('.dropdown-menu');
                if (menu) {
                    // Set high z-index
                    menu.style.zIndex = '9999';

                    // Ensure proper positioning
                    menu.style.position = 'absolute';
                    menu.style.top = '100%';
                    menu.style.right = '0';
                    menu.style.left = 'auto';
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                const dropdowns = document.querySelectorAll('.dropdown');
                dropdowns.forEach(dropdown => {
                    if (!dropdown.contains(event.target)) {
                        const menu = dropdown.querySelector('.dropdown-menu');
                        if (menu && menu.classList.contains('show')) {
                            menu.classList.remove('show');
                        }
                    }
                });
            });

            // Prevent dropdown from closing when clicking inside
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.addEventListener('click', function(event) {
                    event.stopPropagation();
                });
            });
        });
    </script>

    @stack('scripts')
</body>

</html>