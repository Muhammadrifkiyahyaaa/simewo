<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIMEWO - @yield('title', 'Dashboard')</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root {
            --sw-navy: #1E3A5F;
            --sw-primary: #2563EB;
            --sw-primary-hover: #1D4ED8;
            --sw-text: #1E293B;
            --sw-text-muted: #64748B;
            --sw-border: #E2E8F0;
            --sw-bg-soft: #F8FAFC;
            --sw-dot-success: #16A34A;
            --sw-dot-danger: #DC2626;
            --sw-dot-warning: #F59E0B;
            --sw-dot-secondary: #94A3B8;
            --sw-text-success: #15803D;
            --sw-text-danger: #B91C1C;
            --sw-text-warning: #B45309;
        }

        body {
            display: flex;
            min-height: 100vh;
            margin: 0;
            background-color: var(--sw-bg-soft);
            color: var(--sw-text);
            font-family: 'Segoe UI', Arial, sans-serif;
            overflow-x: hidden;
        }

        .sidebar {
            width: 240px;
            background-color: var(--sw-navy);
            color: #d7dde3;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .main-content {
            flex-grow: 1;
            min-width: 0;
            margin-left: 240px;
            padding: 24px 30px;
        }

        .sidebar .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 18px 20px;
            font-size: 1.1rem;
            font-weight: 600;
            color: #fff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar .brand img {
            width: 28px;
            height: 28px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .sidebar .nav-section {
            font-size: 0.7rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #6b7a89;
            padding: 16px 20px 6px 20px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 20px;
            color: #c2cad3;
            text-decoration: none;
            font-size: 0.92rem;
            border-left: 3px solid transparent;
            transition: background-color .15s ease-in-out, color .15s ease-in-out, border-color .15s ease-in-out;
        }

        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: #fff;
        }

        .sidebar a.active {
            background-color: rgba(255, 255, 255, 0.06);
            color: #fff;
            border-left: 3px solid var(--sw-primary);
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .topbar h5 {
            margin: 0;
            font-weight: 600;
        }

        .main-content a:not(.btn):not(.page-link):not(.nav-link) {
            color: var(--sw-primary);
            transition: color .15s ease-in-out;
        }

        .main-content a:not(.btn):not(.page-link):not(.nav-link):hover {
            color: var(--sw-primary-hover);
        }

        .btn {
            transition: background-color .15s ease-in-out, border-color .15s ease-in-out, color .15s ease-in-out;
            box-shadow: none;
        }

        .btn-primary {
            background-color: var(--sw-primary);
            border-color: var(--sw-primary);
            color: #fff;
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: var(--sw-primary-hover) !important;
            border-color: var(--sw-primary-hover) !important;
            color: #fff !important;
        }

        .btn-success {
            background-color: #217346;
            border-color: #217346;
            color: #fff;
        }

        .btn-success:hover,
        .btn-success:focus {
            background-color: #185C37 !important;
            border-color: #185C37 !important;
            color: #fff !important;
        }

        .btn-secondary,
        .btn-warning,
        .btn-danger,
        .btn-outline-secondary,
        .btn-outline-primary {
            background-color: #fff;
            border: 1px solid var(--sw-border);
            color: #3a4047;
        }

        .btn-secondary:hover,
        .btn-secondary:focus,
        .btn-warning:hover,
        .btn-warning:focus,
        .btn-danger:hover,
        .btn-danger:focus,
        .btn-outline-secondary:hover,
        .btn-outline-secondary:focus,
        .btn-outline-primary:hover,
        .btn-outline-primary:focus {
            background-color: var(--sw-primary) !important;
            border-color: var(--sw-primary) !important;
            color: #fff !important;
        }

        .btn-outline-light:hover {
            background-color: rgba(255, 255, 255, 0.9);
            color: var(--sw-navy);
        }

        .badge {
            background-color: #fff !important;
            border: 1px solid var(--sw-border);
            color: #3a4047 !important;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.35em 0.65em 0.35em 0.55em;
        }

        .badge::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }

        .badge.bg-success::before {
            background-color: var(--sw-dot-success);
        }

        .badge.bg-danger::before {
            background-color: var(--sw-dot-danger);
        }

        .badge.bg-warning::before {
            background-color: var(--sw-dot-warning);
        }

        .badge.bg-secondary::before {
            background-color: var(--sw-dot-secondary);
        }

        .badge.bg-primary::before {
            background-color: var(--sw-primary);
        }

        .alert {
            background-color: #fff;
            border: 1px solid var(--sw-border);
            border-left-width: 5px;
            color: var(--sw-text);
        }

        .alert-success {
            border-left-color: var(--sw-dot-success);
        }

        .alert-danger {
            border-left-color: var(--sw-dot-danger);
        }

        .alert-warning {
            border-left-color: var(--sw-dot-warning);
        }

        .text-success {
            color: var(--sw-text-success) !important;
        }

        .text-danger {
            color: var(--sw-text-danger) !important;
        }

        .text-warning {
            color: var(--sw-text-warning) !important;
        }

        .table> :not(caption)>*>* {
            border-color: var(--sw-border);
        }

        .table-light,
        .table-light>th {
            background-color: #f7f8f9;
            color: var(--sw-text);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--sw-primary);
            box-shadow: 0 0 0 0.2rem rgba(53, 86, 110, 0.12);
        }

        .page-link {
            color: #3a4047;
            transition: background-color .15s ease-in-out, color .15s ease-in-out;
        }

        .page-link:hover {
            background-color: var(--sw-navy);
            color: #fff;
        }

        .page-item.active .page-link {
            background-color: var(--sw-primary);
            border-color: var(--sw-primary);
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="brand">
            <img src="{{ asset('images/logo-simewo.png') }}" alt="Logo SIMEWO">
            <span>SIMEWO</span>
        </div>

        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="nav-section">Data Master</div>
        <a href="{{ route('opd.index') }}" class="{{ request()->routeIs('opd.*') ? 'active' : '' }}">
            <i class="bi bi-building"></i> Data OPD
        </a>

        @if (auth()->user()->role === 'admin')
        <a href="{{ route('website.index') }}" class="{{ request()->routeIs('website.*') ? 'active' : '' }}">
            <i class="bi bi-globe2"></i> Data Website
        </a>
        @endif

        <div class="nav-section">Monitoring</div>
        <a href="{{ route('monitoring.index') }}" class="{{ request()->routeIs('monitoring.*') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i> Riwayat Monitoring
        </a>

        @if (auth()->user()->role === 'admin')
        <a href="{{ route('ssl-warnings.index') }}" class="{{ request()->routeIs('ssl-warnings.*') ? 'active' : '' }}">
            <i class="bi bi-shield-exclamation"></i> Peringatan SSL
        </a>
        <a href="{{ route('suspicious-keywords.index') }}" class="{{ request()->routeIs('suspicious-keywords.*') ? 'active' : '' }}">
            <i class="bi bi-slash-circle"></i> Kata Diblokir
        </a>
        <a href="{{ route('suspicious-content-report.index') }}" class="{{ request()->routeIs('suspicious-content-report.*') ? 'active' : '' }}">
            <i class="bi bi-exclamation-triangle"></i> Laporan Konten Mencurigakan
        </a>
        <a href="{{ route('compliance-score.index') }}" class="{{ request()->routeIs('compliance-score.*') ? 'active' : '' }}">
            <i class="bi bi-check2-square"></i> Compliance Score
        </a>
        <a href="{{ route('opd-summary-report.index') }}" class="{{ request()->routeIs('opd-summary-report.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i> Laporan Rekap OPD
        </a>
        @endif

        @if (auth()->user()->role === 'admin')
        <div class="nav-section">Sistem</div>
        <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Manajemen Pengguna
        </a>
        @endif

        <div style="margin-top: auto; padding: 15px 20px; border-top: 1px solid rgba(255,255,255,0.08);">
            <div style="font-size: 0.85rem; color: #fff;">{{ auth()->user()->name }}</div>
            <div style="font-size: 0.75rem; color: #6b7a89; text-transform: capitalize;">{{ auth()->user()->role }}</div>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light w-100">Logout</button>
            </form>
        </div>
    </div>

    <div class="main-content">
        @if (session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
        @endif

        @if (session('error'))
        <div class="alert alert-danger py-2">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>

</body>

</html>