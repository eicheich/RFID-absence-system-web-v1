<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Karyawan') — Absensi RFID</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f5f6fa;
        }

        #sidebar {
            width: 240px;
            min-height: 100vh;
            background: #0f172a;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        #sidebar .brand {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        }

        #sidebar .brand h5 {
            color: #fff;
            font-weight: 600;
            margin: 0;
            font-size: 1rem;
        }

        #sidebar .brand small {
            color: #64748b;
            font-size: 0.75rem;
        }

        #sidebar .nav-link {
            color: #94a3b8;
            padding: 0.6rem 1.25rem;
            border-radius: 0.5rem;
            margin: 0.1rem 0.75rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            transition: all 0.15s;
        }

        #sidebar .nav-link:hover,
        #sidebar .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.07);
        }

        #sidebar .nav-link.active {
            color: #34d399;
        }

        #sidebar .nav-link i {
            font-size: 1rem;
            width: 20px;
        }

        #sidebar .sidebar-footer {
            margin-top: auto;
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, 0.07);
        }

        #main {
            margin-left: 240px;
            padding: 2rem;
            min-height: 100vh;
        }

        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.07);
            transition: transform 0.15s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .table-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.07);
            overflow: hidden;
        }

        .table thead th {
            background: #f8fafc;
            color: #64748b;
            font-weight: 500;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.85rem 1rem;
        }

        .table tbody td {
            padding: 0.85rem 1rem;
            font-size: 0.875rem;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: #f8fafc;
        }

        .badge-present {
            background: #dcfce7;
            color: #16a34a;
        }

        .badge-late {
            background: #fef9c3;
            color: #ca8a04;
        }

        .badge-absent {
            background: #fee2e2;
            color: #dc2626;
        }

        .badge-blocked {
            background: #f1f5f9;
            color: #475569;
        }

        .badge-valid {
            background: #dcfce7;
            color: #16a34a;
        }

        .badge-invalid {
            background: #fee2e2;
            color: #dc2626;
        }

        .topbar {
            background: #fff;
            border-radius: 12px;
            padding: 0.85rem 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .topbar h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
        }

        .form-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.07);
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }

        .form-control,
        .form-select {
            font-size: 0.875rem;
            border-color: #e2e8f0;
            border-radius: 8px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        /* KPI meter */
        .kpi-ring {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            font-weight: 700;
        }
    </style>
</head>

<body>

    <nav id="sidebar">
        <div class="brand">
            <h5><i class="bi bi-broadcast me-2" style="color:#34d399"></i>Absensi RFID</h5>
            <small>Portal Karyawan</small>
        </div>

        <div class="mt-3">
            <small class="text-uppercase px-3" style="color:#334155;font-size:0.7rem;letter-spacing:0.08em">Menu</small>
            <a href="{{ route('karyawan.dashboard') }}"
                class="nav-link {{ request()->routeIs('karyawan.dashboard') ? 'active' : '' }}">
                <i class="bi bi-house"></i> Dashboard
            </a>
            <a href="{{ route('karyawan.attendance') }}"
                class="nav-link {{ request()->routeIs('karyawan.attendance') ? 'active' : '' }}">
                <i class="bi bi-calendar-check"></i> Riwayat Absensi
            </a>
            <a href="{{ route('karyawan.tasks') }}"
            class="nav-link {{ request()->routeIs('karyawan.tasks*') ? 'active' : '' }}">
              <i class="bi bi-check2-square"></i> Task Hari Ini
              </a>
            <a href="{{ route('karyawan.kpi') }}"
                class="nav-link {{ request()->routeIs('karyawan.kpi') ? 'active' : '' }}">
                <i class="bi bi-graph-up"></i> KPI Saya
            </a>
            <a href="{{ route('karyawan.profile') }}"
                class="nav-link {{ request()->routeIs('karyawan.profile') ? 'active' : '' }}">
                <i class="bi bi-person-circle"></i> Profil
            </a>
        </div>

        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-semibold"
                    style="width:32px;height:32px;font-size:0.8rem;color:#fff;background:#10b981;flex-shrink:0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <div style="color:#e2e8f0;font-size:0.8rem;font-weight:500">{{ auth()->user()->name }}</div>
                    <div style="color:#64748b;font-size:0.7rem">Karyawan</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm w-100"
                    style="background:rgba(255,255,255,0.05);color:#94a3b8;border:1px solid rgba(255,255,255,0.07)">
                    <i class="bi bi-box-arrow-left me-1"></i> Logout
                </button>
            </form>
        </div>
    </nav>

    <div id="main">
        <div class="topbar">
            <h4>@yield('title', 'Dashboard')</h4>
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-clock text-muted" style="font-size:0.85rem"></i>
                <small class="text-muted" id="clock"></small>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
