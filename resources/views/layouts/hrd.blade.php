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

    <style>
        body { background-color: #f5f6fa; }

        /* Sidebar */
        #sidebar {
            width: 240px;
            min-height: 100vh;
            background: #1e293b;
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }
        #sidebar .brand {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        #sidebar .brand h5 {
            color: #fff;
            font-weight: 600;
            margin: 0;
            font-size: 1rem;
        }
        #sidebar .brand small { color: #94a3b8; font-size: 0.75rem; }

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
            background: rgba(255,255,255,0.08);
        }
        #sidebar .nav-link.active { color: #60a5fa; }
        #sidebar .nav-link i { font-size: 1rem; width: 20px; }

        #sidebar .sidebar-footer {
            margin-top: auto;
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        /* Main */
        #main { margin-left: 240px; padding: 2rem; min-height: 100vh; }

        /* Cards */
        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.07);
            transition: transform 0.15s;
        }
        .stat-card:hover { transform: translateY(-2px); }
        .stat-card .icon-box {
            width: 48px; height: 48px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
        }

        /* Table */
        .table-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.07);
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
        .table tbody td { padding: 0.85rem 1rem; font-size: 0.875rem; vertical-align: middle; }
        .table tbody tr:hover { background: #f8fafc; }

        /* Badge status */
        .badge-present  { background: #dcfce7; color: #16a34a; }
        .badge-late     { background: #fef9c3; color: #ca8a04; }
        .badge-absent   { background: #fee2e2; color: #dc2626; }
        .badge-blocked  { background: #f1f5f9; color: #475569; }
        .badge-active   { background: #dcfce7; color: #16a34a; }
        .badge-inactive { background: #fee2e2; color: #dc2626; }

        /* Topbar */
        .topbar {
            background: #fff;
            border-radius: 12px;
            padding: 0.85rem 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .topbar h4 { margin: 0; font-size: 1.1rem; font-weight: 600; color: #1e293b; }

        /* Form */
        .form-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.07);
        }
        .form-label { font-size: 0.875rem; font-weight: 500; color: #374151; }
        .form-control, .form-select {
            font-size: 0.875rem;
            border-color: #e2e8f0;
            border-radius: 8px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<nav id="sidebar">
    <div class="brand">
        <h5><i class="bi bi-broadcast me-2 text-primary"></i>Absensi RFID</h5>
        <small>Panel HRD</small>
    </div>

    <div class="mt-3">
        <small class="text-uppercase px-3" style="color:#475569;font-size:0.7rem;letter-spacing:0.08em">Menu</small>
        <a href="{{ route('hrd.dashboard') }}"
           class="nav-link {{ request()->routeIs('hrd.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
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
    </div>

    <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-2 mb-2">
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center"
                 style="width:32px;height:32px;font-size:0.8rem;color:#fff">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div>
                <div style="color:#e2e8f0;font-size:0.8rem;font-weight:500">{{ auth()->user()->name }}</div>
                <div style="color:#64748b;font-size:0.7rem">HRD</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm w-100"
                    style="background:rgba(255,255,255,0.06);color:#94a3b8;border:1px solid rgba(255,255,255,0.08)">
                <i class="bi bi-box-arrow-left me-1"></i> Logout
            </button>
        </form>
    </div>
</nav>

<!-- Main Content -->
<div id="main">

    <!-- Topbar -->
    <div class="topbar">
        <h4>@yield('title', 'Dashboard')</h4>
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-clock text-muted" style="font-size:0.85rem"></i>
            <small class="text-muted" id="clock"></small>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4" role="alert">
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
            now.toLocaleDateString('id-ID', {weekday:'long', day:'numeric', month:'long', year:'numeric'})
            + ' · ' + now.toLocaleTimeString('id-ID');
    }
    updateClock();
    setInterval(updateClock, 1000);
</script>

</body>
</html>
