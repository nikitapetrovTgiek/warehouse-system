<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Складская система')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #eef2fa;
            font-family: 'Inter', system-ui, 'Segoe UI', sans-serif;
        }
        .navbar {
            background-color: #1e2a47 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: -0.3px;
        }
        .card {
            border: none;
            border-radius: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
            background: white;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
        }
        .table-container {
            background: white;
            border-radius: 20px;
            padding: 1rem;
        }
        footer {
            font-size: 0.85rem;
        }
    </style>
    @stack('styles')
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <i class="fas fa-warehouse me-2"></i> Складская система
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center gap-2">
                <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Главная</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}"><i class="fas fa-box"></i> Товары</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('locations.index') }}"><i class="fas fa-map-marker-alt"></i> Места</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('batches.index') }}"><i class="fas fa-layer-group"></i> Партии</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('movements.index') }}"><i class="fas fa-exchange-alt"></i> Операции</a></li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-chart-bar"></i> Отчёты
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('reports.stock') }}">
                            <i class="fas fa-chart-bar me-2"></i> Остатки
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('reports.movements') }}">
                            <i class="fas fa-list-alt me-2"></i> Движения
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('reports.expiring') }}">
                            <i class="fas fa-calendar-alt me-2"></i> Сроки годности
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('reports.expired') }}">
                            <i class="fas fa-times-circle me-2"></i> Просрочка
                        </a></li>
                    </ul>
                </li>

                @if(auth()->user()?->hasRole('admin'))
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="fas fa-crown"></i> Админка</a></li>
                @endif
            </ul>

            <div class="d-flex align-items-center gap-2 ms-lg-3">
                <span class="text-light bg-dark bg-opacity-25 px-3 py-1 rounded-pill small">
                    <i class="fas fa-user-circle"></i> {{ Auth::user()->name }} ({{ Auth::user()->role_name }})
                </span>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm rounded-pill px-3">
                        <i class="fas fa-sign-out-alt"></i> Выйти
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<main class="container py-4">
    @yield('content')
</main>

<footer class="text-center text-muted py-3 border-top mt-4">
    <small>© {{ date('Y') }} Складская система управления. </small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>