@extends('layouts.app')

@section('title', 'Панель управления')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">
    <i class="fas fa-home text-primary me-2"></i> Добро пожаловать, {{ Auth::user()->name }}!
    </h1>
    <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill">{{ Auth::user()->role_name }}</span>
</div>

<!-- статистика -->
<div class="row g-4 mb-5">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center p-3 h-100">
            <i class="fas fa-box text-primary fa-3x mb-2"></i>
            <h5 class="card-title">Товары</h5>
            <p class="display-6 fw-bold">{{ \App\Models\Product::count() }}</p>
            <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-sm">Управление →</a>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center p-3 h-100">
            <i class="fas fa-map-marker-alt text-success fa-3x mb-2"></i>
            <h5 class="card-title">Места хранения</h5>
            <p class="display-6 fw-bold">{{ \App\Models\StorageLocation::count() }}</p>
            <a href="{{ route('locations.index') }}" class="btn btn-outline-success btn-sm">Управление →</a>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center p-3 h-100">
            <i class="fas fa-layer-group text-info fa-3x mb-2"></i>
            <h5 class="card-title">Партии</h5>
            <p class="display-6 fw-bold">{{ \App\Models\Batch::count() }}</p>
            <a href="{{ route('batches.index') }}" class="btn btn-outline-info btn-sm">Управление →</a>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center p-3 h-100">
            <i class="fas fa-exchange-alt text-warning fa-3x mb-2"></i>
            <h5 class="card-title">Операции</h5>
            <p class="display-6 fw-bold">{{ \App\Models\InventoryMovement::count() }}</p>
            <a href="{{ route('movements.index') }}" class="btn btn-outline-warning btn-sm">Журнал →</a>
        </div>
    </div>
</div>

<!-- отчеты -->
<div class="row mb-5">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-bold">
                <i class="fas fa-chart-line"></i> Аналитика и отчёты
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <a href="{{ route('reports.stock') }}" class="btn btn-outline-primary">
                        <i class="fas fa-chart-bar"></i> Остатки товаров
                    </a>
                    <a href="{{ route('reports.movements') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list"></i> Движения за период
                    </a>
                    <a href="{{ route('reports.expiring') }}" class="btn btn-outline-warning">
                        <i class="fas fa-hourglass-half"></i> Сроки годности
                    </a>
                    <a href="{{ route('reports.expired') }}" class="btn btn-outline-danger">
                        <i class="fas fa-times-circle"></i> Просрочка
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- последние операции -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent fw-bold">
                <i class="fas fa-history"></i> Последние операции
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Дата</th>
                                <th>Тип</th>
                                <th>Товар</th>
                                <th>Кол-во</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Models\InventoryMovement::with('product')->latest()->limit(5)->get() as $movement)
                            <tr>
                                <td>{{ $movement->created_at->format('d.m.Y H:i') }}</td>
                                <td>{{ $movement->movement_type_name }}</td>
                                <td>{{ $movement->product->name }}</td>
                                <td>{{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- популярные товары (по числу операций) -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent fw-bold">
                <i class="fas fa-fire"></i> Часто используемые товары
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Товар</th>
                                <th>Кол-во операций</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Models\Product::withCount('movements')->orderBy('movements_count', 'desc')->limit(5)->get() as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->movements_count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection