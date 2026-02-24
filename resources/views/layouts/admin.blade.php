<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - AANI Market')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    @stack('styles')
    <style>
        :root {
            --admin-green: #1f7a3e;
            --admin-green-dark: #155c2f;
            --admin-green-soft: #e8f3ec;
        }
        body.admin-theme {
            background-color: #f6f6f4;
            color: #1a1916;
        }
        .admin-theme .btn-primary {
            background-color: var(--admin-green);
            border-color: var(--admin-green);
        }
        .admin-theme .btn-primary:hover {
            background-color: var(--admin-green-dark);
            border-color: var(--admin-green-dark);
        }
        .admin-theme .btn-outline-primary {
            color: var(--admin-green);
            border-color: var(--admin-green);
        }
        .admin-theme .btn-outline-primary:hover {
            background-color: var(--admin-green);
            border-color: var(--admin-green);
        }
        .admin-theme .card-header {
            background-color: var(--admin-green-soft);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
        }
        .admin-theme .badge.bg-success {
            background-color: var(--admin-green) !important;
        }
        .admin-theme .table thead th {
            background-color: #f6fbf7;
        }
        .admin-theme .stat-card {
            border: 1px solid rgba(31, 122, 62, 0.15);
            background: linear-gradient(180deg, #ffffff 0%, #f3faf6 100%);
        }
    </style>
</head>
<body class="admin-theme">
    <div class="container mt-3">
        @include('layouts.flash-messages')
    </div>

    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
