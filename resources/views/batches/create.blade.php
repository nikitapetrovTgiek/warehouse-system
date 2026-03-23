<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить партию</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            <h2 class="mb-4">Добавить новую партию</h2>

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

            <!-- Форма добавления -->
            <form method="POST" action="{{ route('batches.store') }}">
                @csrf

                <!-- Выбор товара -->
                <div class="mb-3">
                    <label for="product_id" class="form-label">Товар *</label>
                    <select class="form-control @error('product_id') is-invalid @enderror" 
                            id="product_id" 
                            name="product_id" 
                            required>
                        <option value="">Выберите товар</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} ({{ $product->article }})
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Номер партии -->
                <div class="mb-3">
                    <label for="batch_number" class="form-label">Номер партии *</label>
                    <input type="text" 
                           class="form-control @error('batch_number') is-invalid @enderror" 
                           id="batch_number" 
                           name="batch_number" 
                           value="{{ old('batch_number') }}" 
                           placeholder="Например: П-2025-001"
                           required>
                    @error('batch_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Дата производства -->
                <div class="mb-3">
                    <label for="manufactured_date" class="form-label">Дата производства</label>
                    <input type="date" 
                           class="form-control @error('manufactured_date') is-invalid @enderror" 
                           id="manufactured_date" 
                           name="manufactured_date" 
                           value="{{ old('manufactured_date') }}">
                    @error('manufactured_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Срок годности -->
                <div class="mb-3">
                    <label for="expiration_date" class="form-label">Срок годности</label>
                    <input type="date" 
                           class="form-control @error('expiration_date') is-invalid @enderror" 
                           id="expiration_date" 
                           name="expiration_date" 
                           value="{{ old('expiration_date') }}">
                    @error('expiration_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Сертификат -->
                <div class="mb-3">
                    <label for="certificate" class="form-label">Сертификат</label>
                    <input type="text" 
                           class="form-control @error('certificate') is-invalid @enderror" 
                           id="certificate" 
                           name="certificate" 
                           value="{{ old('certificate') }}" 
                           placeholder="Номер или ссылка на сертификат">
                    @error('certificate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Примечания -->
                <div class="mb-3">
                    <label for="notes" class="form-label">Примечания</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                              id="notes" 
                              name="notes" 
                              rows="3">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Кнопки -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('batches.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Отмена
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 