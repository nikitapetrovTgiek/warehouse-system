<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Приёмка товара</title>
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
            <h2 class="mb-4">Приёмка товара</h2>

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

            <!-- Форма приёмки -->
            <form method="POST" action="{{ route('movements.receipt.store') }}">
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

                <!-- Номер партии (можно создать новую или выбрать существующую) -->
                <div class="mb-3">
                    <label for="batch_number" class="form-label">Номер партии</label>
                    <input type="text" 
                           class="form-control @error('batch_number') is-invalid @enderror" 
                           id="batch_number" 
                           name="batch_number" 
                           value="{{ old('batch_number') }}" 
                           placeholder="Оставьте пустым, если не нужно">
                    @error('batch_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Если указан создастся новая партия</div>
                </div>

                <!-- Срок годности (если есть) -->
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

                <!-- Количество -->
                <div class="mb-3">
                    <label for="quantity" class="form-label">Количество *</label>
                    <input type="number" 
                           class="form-control @error('quantity') is-invalid @enderror" 
                           id="quantity" 
                           name="quantity" 
                           value="{{ old('quantity', 1) }}" 
                           min="1"
                           required>
                    @error('quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Место хранения (куда положить) -->
                <div class="mb-3">
                    <label for="location_id" class="form-label">Место хранения *</label>
                    <select class="form-control @error('location_id') is-invalid @enderror" 
                            id="location_id" 
                            name="location_id" 
                            required>
                        <option value="">Выберите место</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" 
                                {{ old('location_id') == $location->id ? 'selected' : '' }}
                                @if($location->capacity && $location->available_capacity < old('quantity', 1)) disabled @endif>
                                {{ $location->name }} ({{ $location->type_name ?? $location->type }})
                                @if($location->capacity)
                                    — свободно {{ $location->available_capacity }} шт.
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('location_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Номер документа -->
                <div class="mb-3">
                    <label for="document_number" class="form-label">Номер документа</label>
                    <input type="text" 
                           class="form-control @error('document_number') is-invalid @enderror" 
                           id="document_number" 
                           name="document_number" 
                           value="{{ old('document_number') }}" 
                           placeholder="Накладная, счёт-фактура...">
                    @error('document_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Комментарий -->
                <div class="mb-3">
                    <label for="comments" class="form-label">Комментарий</label>
                    <textarea class="form-control @error('comments') is-invalid @enderror" 
                              id="comments" 
                              name="comments" 
                              rows="2">{{ old('comments') }}</textarea>
                    @error('comments')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Кнопки -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('movements.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Отмена
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Оформить приёмку
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 