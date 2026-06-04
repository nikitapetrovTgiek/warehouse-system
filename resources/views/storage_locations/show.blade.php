@extends('layouts.app')

@section('title', 'Просмотр места хранения')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <i class="fas fa-eye me-2"></i> Просмотр места хранения
    </h1>
    <div>
        <a href="{{ route('locations.edit', $location) }}" class="btn btn-warning me-2">
            <i class="fas fa-edit"></i> Редактировать
        </a>
        <a href="{{ route('locations.index') }}" class="btn btn-secondary">
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
                        <td>{{ $location->id }}</td>
                    </tr>
                    <tr>
                        <th>Название:</th>
                        <td><strong>{{ $location->name }}</strong></td>
                    </tr>
                    <tr>
                        <th>Тип:</th>
                        <td>
                            @php
                                $types = [
                                    'cell' => 'Ячейка',
                                    'rack' => 'Стеллаж',
                                    'zone' => 'Зона',
                                    'floor' => 'Напольное'
                                ];
                            @endphp
                            {{ $types[$location->type] ?? $location->type }}
                        </td>
                    </tr>
                    <tr>
                        <th>Статус:</th>
                        <td>
                            @if($location->is_active)
                                <span class="badge bg-success">Активно</span>
                            @else
                                <span class="badge bg-secondary">Неактивно</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Вместимость:</th>
                        <td>
                            @if($location->capacity)
                                {{ $location->capacity }} шт.
                            @else
                                <span class="text-muted">Безлимитно</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Текущая загрузка:</th>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold">{{ $location->current_load }} шт.</span>
                                @if($location->capacity)
                                    <div class="progress flex-grow-1" style="height: 8px; max-width: 200px;">
                                        <div class="progress-bar bg-success" 
                                             style="width: {{ min(100, ($location->current_load / $location->capacity) * 100) }}%"></div>
                                    </div>
                                    <span class="small">
                                        ({{ round(($location->current_load / $location->capacity) * 100) }}%)
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Свободно:</th>
                        <td>
                            @if($location->capacity)
                                {{ $location->capacity - $location->current_load }} шт.
                            @else
                                <span class="text-muted">∞</span>
                            @endif
                        </td>
                    </tr>
                    @if($location->description)
                    <tr>
                        <th>Описание:</th>
                        <td>{{ $location->description }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>Дата создания:</th>
                        <td>{{ $location->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-bold">
                <i class="fas fa-boxes"></i> Товары в этом месте
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Товар</th>
                                <th>Партия</th>
                                <th>Срок (дней)</th>
                                <th>Количество</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($currentItems as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item['product']->name }}</strong>
                                    <br><small class="text-muted">{{ $item['product']->article }}</small>
                                </td>
                                <td>
                                    @if($item['batch'])
                                        {{ $item['batch']->batch_number }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item['batch'] && $item['batch']->expiration_date)
                                        @php
                                            $daysLeft = round($item['batch']->daysUntilExpiration());
                                        @endphp
                                        @if($daysLeft < 0)
                                            <span class="text-danger">просрочена на {{ abs($daysLeft) }} дн.</span>
                                        @else
                                            {{ $daysLeft }} дн.
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $item['quantity'] }} шт.</td>
                                <td>
                                    <a href="{{ route('products.show', $item['product']) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        В этом месте нет товаров
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

<!-- ИСТОРИЯ ДВИЖЕНИЙ ПО ЭТОМУ МЕСТУ -->
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-transparent fw-bold">
        <i class="fas fa-exchange-alt"></i> История движений (последние 20)
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Дата</th>
                        <th>Тип</th>
                        <th>Товар</th>
                        <th>Партия</th>
                        <th>Кол-во</th>
                        <th>Откуда → Куда</th>
                        <th>Документ</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $allMovements = collect($location->movementsFrom)
                            ->concat($location->movementsTo)
                            ->sortByDesc('created_at')
                            ->take(20);
                    @endphp
                    @forelse($allMovements as $movement)
                        <tr>
                            <td>{{ $movement->created_at->format('d.m.Y H:i') }}</td>
                            <td>{{ $movement->movement_type_name }}</td>
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
                            <td>{{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }} шт.</td>
                            <td>
                                @if($movement->fromLocation)
                                    {{ $movement->fromLocation->name }}
                                @endif
                                @if($movement->fromLocation && $movement->toLocation)
                                    →
                                @endif
                                @if($movement->toLocation)
                                    {{ $movement->toLocation->name }}
                                @endif
                            </td>
                            <td>{{ $movement->document_number ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">
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