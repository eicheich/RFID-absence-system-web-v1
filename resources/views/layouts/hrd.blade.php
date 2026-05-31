<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'HRD Panel') — Absensi RFID</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-main: #0b1022;
            --bg-surface: #131a34;
            --bg-surface-soft: #1a2142;
            --bg-surface-strong: #0f1530;
            --text-main: #f3f5ff;
            --text-dim: #9ca7cb;
            --line: rgba(255, 255, 255, 0.08);
            --accent: #5d78ff;
            --success: #16a34a;
            --warning: #f59e0b;
            --danger: #fb7185;
        }

        * {
            font-family: "Sora", sans-serif;
        }

        body {
            background: radial-gradient(circle at 20% 0%, #1b2450 0%, var(--bg-main) 45%);
            color: var(--text-main);
            min-height: 100vh;
        }

        #sidebar {
            width: 260px;
            min-height: 100vh;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.04) 0%, rgba(255, 255, 255, 0.01) 100%);
            backdrop-filter: blur(10px);
            border-right: 1px solid var(--line);
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        #sidebar .brand {
            padding: 2rem 1.25rem 1.5rem;
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        #sidebar .brand .logo-badge {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f15bb5 0%, #5d78ff 100%);
            box-shadow: 0 8px 22px rgba(93, 120, 255, 0.35);
        }

        #sidebar .brand h5 {
            color: var(--text-main);
            font-weight: 700;
            margin: 0;
            font-size: 1.2rem;
        }

        #sidebar .brand small {
            color: var(--text-dim);
            font-size: 0.75rem;
        }

        #sidebar .menu-title {
            color: var(--text-dim);
            font-size: 0.72rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 1.2rem 1.1rem 0.5rem;
        }

        #sidebar .nav-link {
            color: #d8dcf4;
            padding: 0.72rem 1rem;
            border-radius: 11px;
            margin: 0.18rem 0.85rem;
            font-size: 0.92rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            transition: all 0.2s ease;
        }

        #sidebar .nav-link:hover,
        #sidebar .nav-link.active {
            color: #101327;
            background: #f3f4f6;
        }

        #sidebar .nav-link i {
            font-size: 1rem;
            width: 20px;
        }

        #sidebar .sidebar-footer {
            margin-top: auto;
            padding: 1.1rem 1rem 1.3rem;
            border-top: 1px solid var(--line);
        }

        #sidebar .user-box {
            padding: 0.6rem 0.8rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.03);
            margin-bottom: 0.6rem;
        }

        #main {
            margin-left: 260px;
            padding: 2rem 2.2rem;
            min-height: 100vh;
        }

        .page-head {
            margin-bottom: 1.15rem;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
        }

        .page-head h4 {
            margin: 0;
            font-weight: 700;
            color: var(--text-main);
        }

        .page-head small {
            color: var(--text-dim);
        }

        .panel {
            background: linear-gradient(160deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.015));
            border: 1px solid var(--line);
            border-radius: 16px;
            overflow: hidden;
        }

        .metric-card {
            padding: 1.2rem 1rem;
            min-height: 122px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 0.5rem;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.04);
        }

        .metric-icon {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: #fff;
        }

        .metric-value {
            font-size: 2.1rem;
            font-weight: 700;
            line-height: 1;
            color: var(--text-main);
        }

        .metric-label {
            color: #dfe4ff;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .dash-table {
            --bs-table-bg: transparent;
            --bs-table-color: #eef1ff;
            --bs-table-border-color: rgba(255, 255, 255, 0.04);
            --bs-table-striped-bg: rgba(255, 255, 255, 0.015);
            --bs-table-striped-color: #eef1ff;
            --bs-table-hover-bg: rgba(255, 255, 255, 0.02);
            --bs-table-hover-color: #eef1ff;
            margin-bottom: 0;
            background: transparent;
        }

        .dash-table>:not(caption)>*>* {
            background-color: transparent;
        }

        .dash-table thead th {
            background: var(--bg-surface-soft);
            color: #b2bbdf;
            border-color: transparent;
            font-weight: 600;
            font-size: 0.76rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            padding: 1rem 1.05rem;
            white-space: nowrap;
        }

        .dash-table tbody td {
            color: #eef1ff;
            border-color: rgba(255, 255, 255, 0.04);
            padding: 1rem 1.05rem;
            font-size: 0.9rem;
            vertical-align: middle;
        }

        .dash-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.35rem 0.7rem;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 600;
            line-height: 1.1;
            color: #fff;
        }

        .status-pill.status-ok {
            background: #16a34a;
        }

        .status-pill.status-late {
            background: #ef4444;
        }

        .status-pill.status-off {
            background: #6b7280;
        }

        .muted {
            color: var(--text-dim);
        }

        .alert {
            border: 0;
        }

        #main .card,
        #main .table-card,
        #main .form-card {
            background: linear-gradient(160deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.015));
            border: 1px solid var(--line);
            color: var(--text-main);
            box-shadow: none;
        }

        #main .card-header,
        #main .card-footer {
            background: rgba(255, 255, 255, 0.02) !important;
            border-color: var(--line);
            color: var(--text-main);
        }

        #main .table {
            --bs-table-bg: transparent;
            --bs-table-color: #eef1ff;
            --bs-table-border-color: rgba(255, 255, 255, 0.06);
            --bs-table-striped-bg: rgba(255, 255, 255, 0.015);
            --bs-table-striped-color: #eef1ff;
            --bs-table-hover-bg: rgba(255, 255, 255, 0.02);
            --bs-table-hover-color: #eef1ff;
            color: #eef1ff;
        }

        #main .table>:not(caption)>*>* {
            background-color: transparent;
        }

        #main .table thead th {
            background: var(--bg-surface-soft) !important;
            color: #b2bbdf;
            border-color: transparent;
            font-weight: 600;
            letter-spacing: 0.04em;
        }

        #main .table tbody td,
        #main .table tbody th {
            color: #eef1ff;
            border-color: rgba(255, 255, 255, 0.06);
        }

        #main .text-muted,
        #main .text-dark,
        #main .text-secondary {
            color: var(--text-dim) !important;
        }

        #main code,
        #main code.text-dark {
            color: #dbe5ff !important;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            padding: 0.12rem 0.4rem;
        }

        #main .form-control,
        #main .form-select,
        #main .input-group-text {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.12);
            color: #f3f5ff;
        }

        #main .form-select {
            color-scheme: dark;
        }

        #main .form-select option,
        #main .form-select optgroup {
            background: #0f172a;
            color: #f3f5ff;
        }

        #main .form-control:focus,
        #main .form-select:focus {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(93, 120, 255, 0.8);
            color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(93, 120, 255, 0.2);
        }

        #main .form-control::placeholder {
            color: #9ca7cb;
        }

        #main .btn-outline-secondary,
        #main .btn-outline-primary,
        #main .btn-outline-warning,
        #main .btn-outline-danger,
        #main .btn-outline-success {
            border-color: rgba(255, 255, 255, 0.2);
            color: #dbe5ff;
            background: rgba(255, 255, 255, 0.02);
        }

        #main .btn-outline-secondary:hover,
        #main .btn-outline-primary:hover,
        #main .btn-outline-warning:hover,
        #main .btn-outline-danger:hover,
        #main .btn-outline-success:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.28);
        }

        /* Small square icon buttons used in tables */
        .btn-square {
            width: 36px;
            height: 36px;
            padding: 0.45rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        /* Badge styles for HRD dark theme */
        .badge-present {
            background: linear-gradient(180deg, #34d399 0%, #10b981 100%);
            color: #061a12;
            font-weight: 700;
        }

        .badge-blocked {
            background: rgba(255, 255, 255, 0.04);
            color: #cbd5e1;
            font-weight: 600;
        }

        /* Action button variants */
        .btn-square.view {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.06);
            color: #9fb0da;
        }

        .btn-square.edit {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(245, 158, 11, 0.18);
            color: #f59e0b;
        }

        .btn-square.delete {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(251, 113, 133, 0.18);
            color: #fb7185;
        }

        #main .pagination {
            --bs-pagination-bg: rgba(255, 255, 255, 0.04);
            --bs-pagination-color: #dbe5ff;
            --bs-pagination-border-color: rgba(255, 255, 255, 0.12);
            --bs-pagination-hover-bg: rgba(255, 255, 255, 0.1);
            --bs-pagination-hover-color: #fff;
            --bs-pagination-hover-border-color: rgba(255, 255, 255, 0.2);
            --bs-pagination-active-bg: #5d78ff;
            --bs-pagination-active-border-color: #5d78ff;
            --bs-pagination-disabled-bg: rgba(255, 255, 255, 0.02);
            --bs-pagination-disabled-color: #7d89b5;
        }

        @media (max-width: 991px) {
            #sidebar {
                position: static;
                width: 100%;
                min-height: auto;
                border-right: 0;
                border-bottom: 1px solid var(--line);
            }

            #main {
                margin-left: 0;
                padding: 1.25rem;
            }
        }

        @media (max-width: 576px) {
            .page-head {
                flex-direction: column;
                align-items: flex-start;
            }

            .metric-value {
                font-size: 1.7rem;
            }
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="brand">
            <span class="logo-badge"><i class="bi bi-upc-scan"></i></span>
            <div>
                <h5>TapLog</h5>
                <small>Panel HRD</small>
            </div>
        </div>

        <div class="mt-3">
            <div class="menu-title">General</div>
            <a href="{{ route('hrd.dashboard') }}"
                class="nav-link {{ request()->routeIs('hrd.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid"></i> Dashboard
            </a>
            <a href="{{ route('hrd.employees.index') }}"
                class="nav-link {{ request()->routeIs('hrd.employees.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Data Karyawan
            </a>
            <a href="{{ route('hrd.rfid-cards.index') }}"
                class="nav-link {{ request()->routeIs('hrd.rfid-cards.*') ? 'active' : '' }}">
                <i class="bi bi-credit-card-2-front"></i> Kartu RFID
            </a>
            <a href="{{ route('hrd.tasks.index') }}"
                class="nav-link {{ request()->routeIs('hrd.tasks.*') ? 'active' : '' }}">
                <i class="bi bi-list-task"></i> Manajemen Task
            </a>
            <a href="{{ route('hrd.attendances.index') }}"
                class="nav-link {{ request()->routeIs('hrd.attendances.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-check"></i> Absensi
            </a>
            <a href="{{ route('hrd.kpi.index') }}"
                class="nav-link {{ request()->routeIs('hrd.kpi.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow"></i> KPI
            </a>
            <a href="{{ route('hrd.task-reviews.index') }}"
                class="nav-link {{ request()->routeIs('hrd.task-reviews.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-check"></i> Review Laporan
                @php
                    $pendingCount = \App\Models\TaskCompletion::where('is_done', true)
                        ->where('review_status', 'pending')
                        ->count();
                @endphp
                @if ($pendingCount > 0)
                    <span class="badge rounded-pill ms-auto" style="background:#ef4444;color:#fff;font-size:10px">
                        {{ $pendingCount }}
                    </span>
                @endif
            </a>
        </div>

        <div class="sidebar-footer">
            <div class="user-box d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                    style="width:38px;height:38px;font-size:0.95rem;color:#fff;background:linear-gradient(135deg,#d946ef,#5d78ff)">
                    <i class="bi bi-person"></i>
                </div>
                <div>
                    <div style="color:#eef1ff;font-size:0.82rem;font-weight:600">{{ auth()->user()->name }}</div>
                    <div style="color:#9ca7cb;font-size:0.72rem">HRD</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm w-100"
                    style="background:var(--bg-surface-strong);color:#e5e7eb;border:1px solid var(--line);border-radius:10px">
                    <i class="bi bi-box-arrow-left me-1"></i> Logout
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content -->
    <div id="main">
        <div class="page-head">
            <div>
                <h4>@yield('title', 'Dashboard')</h4>
                <small id="clock"></small>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4"
                role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4"
                role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Jam realtime di topbar
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').textContent =
                now.toLocaleDateString('id-ID', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                }) +
                ' · ' + now.toLocaleTimeString('id-ID');
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>

</body>

</html>
