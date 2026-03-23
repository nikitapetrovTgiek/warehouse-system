<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр движения</title>
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
        .type-badge {
            font-size: 1rem;
            padding: 8px 15px;
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
            <a href="{{ route('movements.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Назад к списку
            </a>
        </div>

        <!-- Карточка движения -->
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Движение #{{ $movement->id }}</h4>
                <span class="badge bg-light text-dark type-badge">
                    {{ $movement->movement_type_name }}
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Дата:</div>
                            <div class="info-value">{{ $movement->created_at->format('d.m.Y H:i:s') }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Товар:</div>
                            <div class="info-value">
                                <a href="{{ route('products.show', $movement->product) }}">
                                    {{ $movement->product->name }}
                                </a>
                                <br>
                                <small class="text-muted">Арт: {{ $movement->product->article }}</small>
                            </div>
                        </div>
                        @if($movement->batch)
                        <div class="info-row">
                            <div class="info-label">Партия:</div>
                            <div class="info-value">
                                <a href="{{ route('batches.show', $movement->batch) }}">
                                    {{ $movement->batch->batch_number }}
                                </a>
                                @if($movement->batch->expiration_date)
                                    <br>
                                    <small class="text-muted">
                                        Годен до: {{ $movement->batch->expiration_date->format('d.m.Y') }}
                                    </small>
                                @endif
                            </div>
                        </div>
                        @endif
                        <div class="info-row">
                            <div class="info-label">Количество:</div>
                            <div class="info-value">
                                <span class="{{ $movement->quantity > 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                    {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                                </span> шт.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Откуда:</div>
                            <div class="info-value">
                                @if($movement->fromLocation)
                                    <a href="{{ route('locations.show', $movement->fromLocation) }}">
                                        {{ $movement->fromLocation->name }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Куда:</div>
                            <div class="info-value">
                                @if($movement->toLocation)
                                    <a href="{{ route('locations.show', $movement->toLocation) }}">
                                        {{ $movement->toLocation->name }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </div>
                        @if($movement->batch)
                        <div class="info-row">
                            <div class="info-label">Партия:</div>
                            <div class="info-value">
                                <a href="{{ route('batches.show', $movement->batch) }}">
                                    {{ $movement->batch->batch_number }}
                                </a>
                                @if($movement->batch->expiration_date)
                                    <br><small>Годен до: {{ $movement->batch->expiration_date->format('d.m.Y') }}</small>
                                @endif
                            </div>
                        </div>
                        @endif
                        <div class="info-row">
                            <div class="info-label">Пользователь:</div>
                            <div class="info-value">{{ $movement->user->name ?? '—' }}</div>
                        </div>
                        @if($movement->document_number)
                        <div class="info-row">
                            <div class="info-label">Документ:</div>
                            <div class="info-value">
                                {{ $movement->document_number }}
                                @if($movement->document_type)
                                    <br><small class="text-muted">{{ $movement->document_type }}</small>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                @if($movement->comments)
                <div class="mt-3 p-3 bg-light rounded">
                    <strong>Комментарий:</strong>
                    <p class="mt-2 mb-0">{{ $movement->comments }}</p>
                </div>
                @endif

                <div class="mt-3 text-muted small">
                    Статус: {{ $movement->status === 'confirmed' ? 'Подтверждено' : 'Черновик' }}
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 