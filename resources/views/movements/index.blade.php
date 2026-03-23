<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Операции с товарами</title>
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
        .type-badge {
            font-size: 0.85rem;
            padding: 5px 10px;
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

        <!-- Заголовок и кнопки операций -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Операции с товарами</h1>
            <div class="btn-group">
                <a href="{{ route('movements.receipt.create') }}" class="btn btn-success">
                    <i class="fas fa-arrow-down"></i> Приёмка
                </a>
                <a href="{{ route('movements.shipment.create') }}" class="btn btn-warning">
                    <i class="fas fa-arrow-up"></i> Отгрузка
                </a>
                <a href="{{ route('movements.transfer.create') }}" class="btn btn-info">
                    <i class="fas fa-exchange-alt"></i> Перемещение
                </a>
                <a href="{{ route('movements.write-off.create') }}" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Списание
                </a>
            </div>
        </div>

        <!-- Таблица с операциями -->
        <div class="table-container">
            @if($movements->isEmpty())
                <p class="text-center text-muted my-5">Операций пока нет</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Дата</th>
                                <th>Тип</th>
                                <th>Товар</th>
                                <th>Партия</th>
                                <th>Кол-во</th>
                                <th>Откуда</th>
                                <th>Куда</th>
                                <th>Пользователь</th>
                                <th>Документ</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($movements as $movement)
                            <tr>
                                <td>{{ $movement->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    @php
                                        $typeColors = [
                                            'receipt' => 'success',
                                            'shipment' => 'warning',
                                            'transfer' => 'info',
                                            'write_off' => 'danger',
                                            'return' => 'secondary',
                                            'inventory' => 'dark'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $typeColors[$movement->movement_type] ?? 'secondary' }} type-badge">
                                        {{ $movement->movement_type_name }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('products.show', $movement->product) }}">
                                        {{ $movement->product->name }}
                                    </a>
                                </td>
                                <td>
                                    @if($movement->batch)
                                        {{ $movement->batch->batch_number }}
                                        @if($movement->batch->expiration_date)
                                            <br>
                                            <small class="text-muted">
                                                до {{ date('d.m.Y', strtotime($movement->batch->expiration_date)) }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="{{ $movement->quantity > 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                    {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                                </td>
                                <td>{{ $movement->fromLocation->name ?? '—' }}</td>
                                <td>{{ $movement->toLocation->name ?? '—' }}</td>
                                <td>{{ $movement->user->name ?? '—' }}</td>
                                <td>
                                    @if($movement->document_number)
                                        {{ $movement->document_number }}
                                        @if($movement->document_type)
                                            <br>
                                            <small class="text-muted">({{ $movement->document_type }})</small>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('movements.show', $movement) }}" class="btn btn-sm btn-info" title="Просмотр">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Пагинация -->
                <div class="mt-4">
                    {{ $movements->links() }}
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 