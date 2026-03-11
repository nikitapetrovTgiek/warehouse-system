<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр товара</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">← Назад к списку</a>
        </div>

        <!-- Карточка товара -->
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Карточка товара</h4>
                <div>
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-light">✏️ Редактировать</a>
                </div>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <div class="info-label">ID:</div>
                    <div class="info-value">{{ $product->id }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Название:</div>
                    <div class="info-value">{{ $product->name }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Артикул:</div>
                    <div class="info-value">{{ $product->article }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Штрихкод:</div>
                    <div class="info-value">{{ $product->barcode ?? '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Цена:</div>
                    <div class="info-value">{{ number_format($product->price, 2) }} ₽</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Описание:</div>
                    <div class="info-value">{{ $product->description ?? '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Текущий остаток:</div>
                    <div class="info-value">
                        <span class="badge bg-info fs-6">{{ $product->current_stock }}</span> шт.
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Дата создания:</div>
                    <div class="info-value">{{ $product->created_at->format('d.m.Y H:i') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Последнее обновление:</div>
                    <div class="info-value">{{ $product->updated_at->format('d.m.Y H:i') }}</div>
                </div>
            </div>
        </div>

        <!-- Партии товара -->
        <div class="card mt-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Партии товара</h5>
            </div>
            <div class="card-body">
                @if($product->batches->isEmpty())
                    <p class="text-muted mb-0">Нет партий</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>№ партии</th>
                                    <th>Произведён</th>
                                    <th>Годен до</th>
                                    <th>Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->batches as $batch)
                                <tr>
                                    <td>{{ $batch->batch_number }}</td>
                                    <td>{{ $batch->manufactured_date ?? '—' }}</td>
                                    <td>{{ $batch->expiration_date ?? '—' }}</td>
                                    <td>
                                        @php
                                            $status = $batch->status;
                                            $badge = match($status) {
                                                'expired' => 'bg-danger',
                                                'expiring_soon' => 'bg-warning',
                                                default => 'bg-success'
                                            };
                                        @endphp
                                        <span class="badge {{ $badge }}">
                                            {{ $batch->status === 'expired' ? 'Просрочено' : ($batch->status === 'expiring_soon' ? 'Скоро истекает' : 'Активна') }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Последние движения -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Последние движения</h5>
            </div>
            <div class="card-body">
                @if($product->movements->isEmpty())
                    <p class="text-muted mb-0">Нет движений</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Дата</th>
                                    <th>Тип</th>
                                    <th>Количество</th>
                                    <th>Откуда</th>
                                    <th>Куда</th>
                                    <th>Документ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->movements as $movement)
                                <tr>
                                    <td>{{ $movement->created_at->format('d.m.Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $movement->movement_type_name }}</span>
                                    </td>
                                    <td class="{{ $movement->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                                    </td>
                                    <td>{{ $movement->fromLocation->name ?? '—' }}</td>
                                    <td>{{ $movement->toLocation->name ?? '—' }}</td>
                                    <td>{{ $movement->document_number ?? '—' }}</td>
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