<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр места хранения</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: 600;
            width: 150px;
            color: #555;
        }
        .info-value {
            flex: 1;
        }
        .progress {
            height: 25px;
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
        <!-- Кнопка "Назад" -->
        <div class="mb-3">
            <a href="{{ route('locations.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Назад к списку
            </a>
        </div>

        <!-- Основная информация -->
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Место хранения: {{ $location->name }}</h4>
                <div>
                    <a href="{{ route('locations.edit', $location) }}" class="btn btn-sm btn-light">
                        <i class="fas fa-edit"></i> Редактировать
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">ID:</div>
                            <div class="info-value">{{ $location->id }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Название:</div>
                            <div class="info-value">{{ $location->name }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Тип:</div>
                            <div class="info-value">
                                @php
                                    $types = [
                                        'cell' => 'Ячейка',
                                        'rack' => 'Стеллаж',
                                        'zone' => 'Зона',
                                        'floor' => 'Напольное хранение'
                                    ];
                                @endphp
                                {{ $types[$location->type] ?? $location->type }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Статус:</div>
                            <div class="info-value">
                                @if($location->is_active)
                                    <span class="badge bg-success">Активно</span>
                                @else
                                    <span class="badge bg-secondary">Неактивно</span>
                                @endif
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Вместимость:</div>
                            <div class="info-value">
                                @if($location->capacity)
                                    {{ $location->capacity }} шт.
                                @else
                                    <span class="text-muted">Безлимитно</span>
                                @endif
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Текущая загрузка:</div>
                            <div class="info-value">
                                {{ $location->current_load }} шт.
                                @if($location->capacity)
                                    ({{ round(($location->current_load / $location->capacity) * 100) }}%)
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($location->capacity)
                    <div class="mt-3">
                        @php
                            $percent = $location->capacity ? ($location->current_load / $location->capacity) * 100 : 0;
                            $barClass = $percent > 90 ? 'bg-danger' : ($percent > 70 ? 'bg-warning' : 'bg-success');
                        @endphp
                        <div class="progress">
                            <div class="progress-bar {{ $barClass }}" 
                                 style="width: {{ min($percent, 100) }}%">
                                {{ round($percent) }}%
                            </div>
                        </div>
                    </div>
                @endif

                @if($location->description)
                    <div class="mt-3">
                        <strong>Описание:</strong>
                        <p class="mt-2">{{ $location->description }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Товары в этом месте -->
        <div class="card mt-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Товары в этом месте</h5>
            </div>
            <div class="card-body">
                @if($currentItems->isEmpty())
                    <p class="text-muted mb-0">В этом месте нет товаров</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Товар</th>
                                    <th>Артикул</th>
                                    <th>Партия</th>
                                    <th>Количество</th>
                                    <th>Срок годности</th>
                                    <th>Последнее движение</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($currentItems as $item)
                                <tr>
                                    <td>
                                        <a href="{{ route('products.show', $item['product']) }}">
                                            {{ $item['product']->name }}
                                        </a>
                                    </td>
                                    <td>{{ $item['product']->article }}</td>
                                    <td>
                                        @if($item['batch'])
                                            {{ $item['batch']->batch_number }}
                                            @if($item['batch']->expiration_date)
                                                <br>
                                                <small class="text-muted">
                                                    до {{ \Carbon\Carbon::parse($item['batch']->expiration_date)->format('d.m.Y') }}
                                                </small>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td><strong>{{ $item['quantity'] }}</strong> шт.</td>
                                    <td>
                                        @if($item['batch'] && $item['batch']->expiration_date)
                                            @php
                                                $days = \Carbon\Carbon::now()->diffInDays($item['batch']->expiration_date, false);
                                            @endphp
                                            @if($days < 0)
                                                <span class="badge bg-danger">Просрочено</span>
                                            @elseif($days <= 30)
                                                <span class="badge bg-warning">{{ $days }} дн.</span>
                                            @else
                                                <span class="badge bg-success">{{ $days }} дн.</span>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $item['last_movement']->format('d.m.Y H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Последние движения (история) -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Последние движения</h5>
                <small>где участвовало это место</small>
            </div>
            <div class="card-body">
                @php
                    $allMovements = collect($location->movementsFrom)
                        ->concat($location->movementsTo)
                        ->sortByDesc('created_at')
                        ->take(20);
                @endphp

                @if($allMovements->isEmpty())
                    <p class="text-muted mb-0">Движений не найдено</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Дата</th>
                                    <th>Тип</th>
                                    <th>Товар</th>
                                    <th>Партия</th>
                                    <th>Кол-во</th>
                                    <th>Откуда</th>
                                    <th>Куда</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allMovements as $movement)
                                <tr>
                                    <td>{{ $movement->created_at->format('d.m.Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $movement->movement_type_name }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('products.show', $movement->product) }}">
                                            {{ $movement->product->name }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($movement->batch)
                                            {{ $movement->batch->batch_number }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="{{ $movement->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                                    </td>
                                    <td>{{ $movement->fromLocation->name ?? '—' }}</td>
                                    <td>{{ $movement->toLocation->name ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 