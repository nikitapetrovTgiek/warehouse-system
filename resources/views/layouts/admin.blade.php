<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Админ-панель')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #eef2fa;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .navbar {
            background-color: #1e2a47 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .sidebar {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        }
        .sidebar a {
            text-decoration: none;
            color: #1e2a47;
            font-weight: 500;
            padding: 10px 15px;
            border-radius: 12px;
            display: block;
            transition: all 0.2s;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #eef2fa;
            color: #1e2a47;
        }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-3px);
        }
        .table th {
            background-color: #f2f4f8;
        }
        footer {
            font-size: 0.85rem;
        }
        @media (max-width: 768px) {
            .sidebar {
                margin-bottom: 20px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <div class="d-flex align-items-center gap-3">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                <i class="fas fa-crown me-2"></i> Админ-панель
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> В систему
            </a>
        </div>
        <div class="navbar-nav ms-auto">
            <span class="nav-item nav-link text-light">{{ Auth::user()->name }} ({{ Auth::user()->role_name }})</span>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm rounded-pill px-3">Выйти</button>
            </form>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="sidebar">
                <h6 class="text-muted mb-3">
                    <i class="fas fa-bars me-2"></i> Меню
                </h6>
                <a href="{{ route('admin.dashboard') }}" class="mb-2"><i class="fas fa-tachometer-alt me-2"></i>Главная</a>
                <a href="{{ route('admin.users.index') }}" class="mb-2"><i class="fas fa-users me-2"></i>Пользователи</a>
                <a href="{{ route('admin.roles.index') }}"><i class="fas fa-tags me-2"></i>Роли</a>
            </div>
        </div>
        <div class="col-md-9">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @yield('content')
        </div>
    </div>
</div>

<footer class="text-center text-muted py-3 border-top mt-4">
    <small>© {{ date('Y') }} Складская система — Административная панель</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>