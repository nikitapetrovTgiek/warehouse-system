<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель управления</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Складская система</a>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link">{{ Auth::user()->name }} ({{ Auth::user()->role_name }})</span>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Выйти</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Добро пожаловать, {{ Auth::user()->name }}!</h1>
        
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Товары</h5>
                        <p class="card-text">Управление номенклатурой</p>
                        <a href="{{ route('products.index') }}" class="btn btn-primary">Перейти</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Места хранения</h5>
                        <p class="card-text">Ячейки, стеллажи, зоны</p>
                        <a href="{{ route('locations.index') }}" class="btn btn-success">Перейти</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Партии</h5>
                        <p class="card-text">Учёт по срокам годности</p>
                        <a href="{{ route('batches.index') }}" class="btn btn-info">Перейти</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Операции</h5>
                        <p class="card-text">Приёмка, отгрузка</p>
                        <a href="{{ route('movements.index') }}" class="btn btn-warning">Перейти</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
