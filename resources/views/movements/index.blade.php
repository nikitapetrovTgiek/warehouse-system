@extends('layouts.app')

@section('title', 'Операции')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <i class="fas fa-exchange-alt me-2"></i> Операции с товарами
    </h1>
    <div class="d-flex gap-2">
        <a href="{{ route('movements.receipt.create') }}" class="btn btn-success">
            <i class="fas fa-arrow-down"></i> Приёмка
        </a>
        <a href="{{ route('movements.shipment.create') }}" class="btn btn-warning">
            <i class="fas fa-arrow-up"></i> Отгрузка
        </a>
        <a href="{{ route('movements.transfer.create') }}" class="btn btn-info">
            <i class="fas fa-arrows-alt"></i> Перемещение
        </a>
        <a href="{{ route('movements.write-off.create') }}" class="btn btn-danger">
            <i class="fas fa-trash"></i> Списание
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm" style="transition: none !important; transform: none !important;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Дата</th>
                        <th>Тип</th>
                        <th>Товар</th>
                        <th>Партия</th>
                        <th>Кол-во</th>
                        <th>Откуда</th>
                        <th>Куда</th>
                        <th>Документ</th>
                        <th style="width: 60px"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td>{{ $movement->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                @php
                                    $typeColors = [
                                        'receipt' => 'success',
                                        'shipment' => 'warning',
                                        'transfer' => 'info',
                                        'write_off' => 'danger',
                                    ];
                                    $color = $typeColors[$movement->movement_type] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }}">
                                    {{ $movement->movement_type_name }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('products.show', $movement->product) }}" class="text-decoration-none">
                                    {{ $movement->product->name }}
                                </a>
                            </td>
                            <td>
                                @if($movement->batch)
                                    <a href="{{ route('batches.show', $movement->batch) }}" class="text-decoration-none">
                                        {{ $movement->batch->batch_number }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="{{ $movement->quantity > 0 ? 'text-success fw-bold' : 'text-danger fw-bold' }}">
                                {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }} шт.
                            </td>
                            <td>{{ $movement->fromLocation->name ?? '—' }}</td>
                            <td>{{ $movement->toLocation->name ?? '—' }}</td>
                            <td>{{ $movement->document_number ?? '—' }}</td>
                            <td>
                                <a href="{{ route('movements.show', $movement) }}" class="btn btn-sm btn-outline-info" title="Просмотр">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-exchange-alt fa-2x mb-2 d-block"></i>
                                Операций пока нет.
                                <div class="mt-2">
                                    <a href="{{ route('movements.receipt.create') }}" class="btn btn-sm btn-success">Приёмка</a>
                                    <a href="{{ route('movements.shipment.create') }}" class="btn btn-sm btn-warning">Отгрузка</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(method_exists($movements, 'links'))
    <div class="mt-4 d-flex justify-content-center">
        {{ $movements->links() }}
    </div>
@endif
@endsection