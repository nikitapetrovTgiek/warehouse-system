<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр партии</title>
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
        .badge-expired {
            background-color: #dc3545;
            color: white;
        }
        .badge-expiring {
            background-color: #ffc107;
            color: black;
        }
        .badge-active {
            background-color: #28a745;
            color: white;
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
            <a href="{{ route('batches.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Назад к списку
            </a>
        </div>

        <!-- Основная информация -->
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Партия: {{ $batch->batch_number }}</h4>
                <div>
                    <a href="{{ route('batches.edit', $batch) }}" class="btn btn-sm btn-light">
                        <i class="fas fa-edit"></i> Редактировать
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">ID:</div>
                            <div class="info-value">{{ $batch->id }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Товар:</div>
                            <div class="info-value">
                                <a href="{{ route('products.show', $batch->product) }}">
                                    {{ $batch->product->name }}
                                </a>
                                <br>
                                <small class="text-muted">Арт: {{ $batch->product->article }}</small>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Номер партии:</div>
                            <div class="info-value">{{ $batch->batch_number }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <div class="info-label">Статус:</div>
                            <div class="info-value">
                                @php
                                    $status = $batch->status;
                                    $statusClass = match($status) {
                                        'expired' => 'badge-expired',
                                        'expiring_soon' => 'badge-expiring',
                                        default => 'badge-active'
                                    };
                                    $statusText = match($status) {
                                        'expired' => 'Просрочено',
                                        'expiring_soon' => 'Скоро истекает',
                                        default => 'Активна'
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Дата производства:</div>
                            <div class="info-value">{{ $batch->manufactured_date ? date('d.m.Y', strtotime($batch->manufactured_date)) : '—' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Срок годности:</div>
                            <div class="info-value">
                                @if($batch->expiration_date)
                                    {{ date('d.m.Y', strtotime($batch->expiration_date)) }}
                                    @php
                                        $days = (int)$batch->daysUntilExpiration();
                                    @endphp
                                    @if($days < 0)
                                        <span class="text-danger ms-2">(просрочена на {{ abs($days) }} дн.)</span>
                                    @elseif($days == 0)
                                        <span class="text-warning ms-2">(истекает сегодня!)</span>
                                    @else
                                        <span class="text-success ms-2">(осталось {{ $days }} дн.)</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($batch->certificate)
                <div class="info-row">
                    <div class="info-label">Сертификат:</div>
                    <div class="info-value">{{ $batch->certificate }}</div>
                </div>
                @endif

                @if($batch->notes)
                <div class="mt-3">
                    <strong>Примечания:</strong>
                    <p class="mt-2">{{ $batch->notes }}</p>
                </div>
                @endif

                <div class="mt-3 text-muted small">
                    Создал: {{ $batch->creator->name ?? '—' }} | 
                    Дата создания: {{ $batch->created_at->format('d.m.Y H:i') }}
                </div>
            </div>
        </div>

        <!-- Движения по партии -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Движения товара по этой партии</h5>
            </div>
            <div class="card-body">
                @if($movements->isEmpty())
                    <p class="text-muted mb-0">Движений по этой партии пока нет</p>
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
                                    <th>Пользователь</th>
                                    <th>Документ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($movements as $movement)
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
                                    <td>{{ $movement->user->name ?? '—' }}</td>
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