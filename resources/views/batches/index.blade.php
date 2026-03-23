<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Партии товаров</title>
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
            <h1>Партии товаров</h1>
            <a href="{{ route('batches.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Добавить партию
            </a>
        </div>

        <!-- Таблица с партиями -->
        <div class="table-container">
            @if($batches->isEmpty())
                <p class="text-center text-muted my-5">Партий пока нет</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Товар</th>
                                <th>№ партии</th>
                                <th>Произведён</th>
                                <th>Годен до</th>
                                <th>Статус</th>
                                <th>Дней</th>
                                <th>Сертификат</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batches as $batch)
                            <tr>
                                <td>{{ $batch->id }}</td>
                                <td>
                                    <a href="{{ route('products.show', $batch->product) }}">
                                        {{ $batch->product->name }}
                                    </a>
                                </td>
                                <td><strong>{{ $batch->batch_number }}</strong></td>
                                <td>{{ $batch->manufactured_date ? date('d.m.Y', strtotime($batch->manufactured_date)) : '—' }}</td>
                                <td>{{ $batch->expiration_date ? date('d.m.Y', strtotime($batch->expiration_date)) : '—' }}</td>
                                <td>
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
                                </td>
                                <td>
                                    @if($batch->expiration_date)
                                        @php
                                            $days = $batch->daysUntilExpiration();
                                        @endphp
                                        @if($days < 0)
                                            <span class="text-danger">{{ (int)abs($days) }} дн. просрочки</span>
                                        @else
                                            <span class="text-success">{{ (int)$days }} дн.</span>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($batch->certificate)
                                        <a href="#" class="btn btn-sm btn-outline-info" title="Скачать">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('batches.show', $batch) }}" class="btn btn-sm btn-info" title="Просмотр">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('batches.edit', $batch) }}" class="btn btn-sm btn-warning" title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('batches.destroy', $batch) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Удалить партию?')"
                                                title="Удалить"
                                                @if($batch->movements()->exists()) disabled @endif>
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