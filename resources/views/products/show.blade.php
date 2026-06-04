@extends('layouts.app')

@section('title', 'Просмотр товара')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
    <i class="fas fa-eye me-2"></i> Просмотр товара
    </h1>
    <div>
        <a href="{{ route('products.edit', $product) }}" class="btn btn-warning me-2">
            <i class="fas fa-edit"></i> Редактировать
        </a>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Назад к списку
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-bold">
                <i class="fas fa-info-circle"></i> Основная информация
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th style="width: 140px">ID:</th>
                        <td>{{ $product->id }}</td>
                    </tr>
                    <tr>
                        <th>Название:</th>
                        <td><strong>{{ $product->name }}</strong></td>
                    </tr>
                    <tr>
                        <th>Артикул:</th>
                        <td>{{ $product->article }}</td>
                    </tr>
                    <tr>
                        <th>Штрихкод:</th>
                        <td>{{ $product->barcode ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Цена:</th>
                        <td class="fs-4 text-success fw-bold">{{ number_format($product->price, 2) }} ₽</td>
                    </tr>
                    <tr>
                        <th>Остаток на складе:</th>
                        <td>
                            <span class="badge bg-primary fs-6 px-3 py-2">
                                {{ $product->current_stock }} шт.
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Дата создания:</th>
                        <td>{{ $product->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Последнее обновление:</th>
                        <td>{{ $product->updated_at->format('d.m.Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-bold">
                <i class="fas fa-align-left"></i> Описание
            </div>
            <div class="card-body">
                @if($product->description)
                    <p class="mb-0">{{ $product->description }}</p>
                @else
                    <p class="text-muted mb-0">Описание отсутствует</p>
                @endif
            </div>
        </div>

        <!-- партии -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-bold">
                <i class="fas fa-boxes"></i> Партии товара
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Номер партии</th>
                                <th>Количество</th>
                                <th>Срок годности</th>
                                <th>Статус</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($product->batches as $batch)
                            <tr>
                                <td><strong>{{ $batch->batch_number }}</strong></td>
                                <td>{{ $batch->movements->sum('quantity') }} шт.</td>
                                <td>
                                    @if($batch->expiration_date)
                                        {{ \Carbon\Carbon::parse($batch->expiration_date)->format('d.m.Y') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($batch->expiration_date && $batch->isExpired())
                                        <span class="badge bg-danger">Просрочена</span>
                                    @elseif($batch->expiration_date && $batch->daysUntilExpiration() <= 30)
                                        <span class="badge bg-warning text-dark">Скоро</span>
                                    @else
                                        <span class="badge bg-success">Активна</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('batches.show', $batch) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        Нет партий
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- последние движения -->
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-transparent fw-bold">
        <i class="fas fa-exchange-alt"></i> Последние движения товара
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
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
                    @forelse($product->movements()->latest()->limit(10)->get() as $movement)
                        <tr>
                            <td>{{ $movement->created_at->format('d.m.Y H:i') }}</td>
                            <td>{{ $movement->movement_type_name }}</td>
                            <td>{{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }} шт.</td>
                            <td>{{ $movement->fromLocation->name ?? '—' }}</td>
                            <td>{{ $movement->toLocation->name ?? '—' }}</td>
                            <td>{{ $movement->document_number ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                Нет движений
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection