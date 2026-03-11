<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Места хранения</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .badge-cell {
            font-size: 0.9rem;
            padding: 5px 10px;
        }
        .progress {
            height: 20px;
            margin-bottom: 0;
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
        <!-- Сообщения -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Заголовок и кнопка добавления -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Места хранения</h1>
            <a href="{{ route('locations.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Добавить место
            </a>
        </div>

        <!-- Таблица с местами -->
        <div class="table-container">
            @if($locations->isEmpty())
                <p class="text-center text-muted my-5">Мест хранения пока нет</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Тип</th>
                                <th>Вместимость</th>
                                <th>Загрузка</th>
                                <th>Свободно</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($locations as $location)
                            <tr>
                                <td>{{ $location->id }}</td>
                                <td>
                                    <strong>{{ $location->name }}</strong>
                                    @if($location->description)
                                        <br><small class="text-muted">{{ $location->description }}</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $typeNames = [
                                            'cell' => 'Ячейка',
                                            'rack' => 'Стеллаж',
                                            'zone' => 'Зона',
                                            'floor' => 'Напольное'
                                        ];
                                        $type = $typeNames[$location->type] ?? $location->type;
                                    @endphp
                                    {{ $type }}
                                </td>
                                <td>
                                    @if($location->capacity)
                                        {{ $location->capacity }} шт.
                                    @else
                                        <span class="text-muted">∞</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">{{ $location->current_load }} шт.</span>
                                        @if($location->capacity)
                                            @php
                                                $percent = ($location->current_load / $location->capacity) * 100;
                                                $barClass = $percent > 90 ? 'bg-danger' : ($percent > 70 ? 'bg-warning' : 'bg-success');
                                            @endphp
                                            <div class="progress flex-grow-1" style="width: 100px;">
                                                <div class="progress-bar {{ $barClass }}" 
                                                     style="width: {{ min($percent, 100) }}%"></div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($location->capacity)
                                        {{ $location->available_capacity }} шт.
                                    @else
                                        <span class="text-muted">∞</span>
                                    @endif
                                </td>
                                <td>
                                    @if($location->is_active)
                                        <span class="badge bg-success">Активно</span>
                                    @else
                                        <span class="badge bg-secondary">Неактивно</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('locations.show', $location) }}" class="btn btn-sm btn-info" title="Просмотр">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('locations.edit', $location) }}" class="btn btn-sm btn-warning" title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('locations.destroy', $location) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Удалить место хранения?')"
                                                title="Удалить"
                                                @if($location->current_load > 0) disabled @endif>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 