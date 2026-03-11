<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать место хранения</title>
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
            <h2 class="mb-4">Редактировать место хранения</h2>

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
            <form method="POST" action="{{ route('locations.update', $location) }}">
                @csrf
                @method('PUT')

                <!-- Название -->
                <div class="mb-3">
                    <label for="name" class="form-label">Название места *</label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $location->name) }}" 
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Тип места -->
                <div class="mb-3">
                    <label for="type" class="form-label">Тип места *</label>
                    <select class="form-control @error('type') is-invalid @enderror" 
                            id="type" 
                            name="type" 
                            required>
                        <option value="">Выберите тип</option>
                        @foreach($types as $key => $value)
                            <option value="{{ $key }}" {{ old('type', $location->type) == $key ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Вместимость -->
                <div class="mb-3">
                    <label for="capacity" class="form-label">Вместимость (шт.)</label>
                    <input type="number" 
                           class="form-control @error('capacity') is-invalid @enderror" 
                           id="capacity" 
                           name="capacity" 
                           value="{{ old('capacity', $location->capacity) }}">
                    @error('capacity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Текущая загрузка (только для информации) -->
                <div class="mb-3">
                    <label class="form-label">Текущая загрузка</label>
                    <input type="text" class="form-control" value="{{ $location->current_load }} шт." readonly disabled>
                    <div class="form-text">Изменяется только через движения товаров</div>
                </div>

                <!-- Описание -->
                <div class="mb-3">
                    <label for="description" class="form-label">Описание</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" 
                              name="description" 
                              rows="3">{{ old('description', $location->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Активно/неактивно -->
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $location->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Место активно</label>
                </div>

                <!-- Кнопки -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('locations.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Отмена
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Обновить
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 