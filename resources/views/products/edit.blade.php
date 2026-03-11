<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать товар</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .form-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">Складская система</a>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link">{{ Auth::user()->name }} ({{ Auth::user()->role_name }})</span>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Выйти</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Основной контент -->
    <div class="container mt-4">
        <div class="form-container">
            <h2 class="mb-4">Редактировать товар</h2>

            <!-- Вывод ошибок валидации -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Форма редактирования -->
            <form method="POST" action="{{ route('products.update', $product) }}">
                @csrf
                @method('PUT')

                <!-- Название -->
                <div class="mb-3">
                    <label for="name" class="form-label">Название товара *</label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $product->name) }}" 
                           required>
                </div>

                <!-- Артикул -->
                <div class="mb-3">
                    <label for="article" class="form-label">Артикул *</label>
                    <input type="text" 
                           class="form-control @error('article') is-invalid @enderror" 
                           id="article" 
                           name="article" 
                           value="{{ old('article', $product->article) }}" 
                           required>
                </div>

                <!-- Штрихкод -->
                <div class="mb-3">
                    <label for="barcode" class="form-label">Штрихкод</label>
                    <input type="text" 
                           class="form-control @error('barcode') is-invalid @enderror" 
                           id="barcode" 
                           name="barcode" 
                           value="{{ old('barcode', $product->barcode) }}">
                </div>

                <!-- Описание -->
                <div class="mb-3">
                    <label for="description" class="form-label">Описание</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" 
                              name="description" 
                              rows="3">{{ old('description', $product->description) }}</textarea>
                </div>

                <!-- Цена -->
                <div class="mb-3">
                    <label for="price" class="form-label">Цена *</label>
                    <input type="number" 
                           step="0.01" 
                           class="form-control @error('price') is-invalid @enderror" 
                           id="price" 
                           name="price" 
                           value="{{ old('price', $product->price) }}" 
                           required>
                </div>

                <!-- Кнопки -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">Отмена</a>
                    <button type="submit" class="btn btn-primary">Обновить</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
