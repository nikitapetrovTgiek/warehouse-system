@extends('layouts.app')

@section('title', 'Просмотр операции')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <i class="fas fa-eye me-2"></i> Просмотр операции
    </h1>
    <a href="{{ route('movements.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Назад к списку
    </a>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-bold">
                <i class="fas fa-info-circle"></i> Детали движения
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 140px">ID операции:</th>
                                <td>{{ $movement->id }}</td>
                            </tr>
                            <tr>
                                <th>Дата и время:</th>
                                <td>{{ $movement->created_at->format('d.m.Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Тип операции:</th>
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
                                    <span class="badge bg-{{ $color }} fs-6 py-2 px-3">
                                        {{ $movement->movement_type_name }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Товар:</th>
                                <td>
                                    <a href="{{ route('products.show', $movement->product) }}">
                                        {{ $movement->product->name }}
                                    </a>
                                    <br><small class="text-muted">{{ $movement->product->article }}</small>
                                </td>
                            </tr>
                            <tr>
                                <th>Количество:</th>
                                <td class="{{ $movement->quantity > 0 ? 'text-success fw-bold' : 'text-danger fw-bold' }} fs-5">
                                    {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }} шт.
                                </td>
                            </tr>
                            <tr>
                                <th>Партия:</th>
                                <td>
                                    @if($movement->batch)
                                        <a href="{{ route('batches.show', $movement->batch) }}">
                                            {{ $movement->batch->batch_number }}
                                        </a>
                                        @if($movement->batch->expiration_date)
                                            <br><small class="text-muted">
                                                Срок: {{ \Carbon\Carbon::parse($movement->batch->expiration_date)->format('d.m.Y') }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Документ:</th>
                                <td>{{ $movement->document_number ?? '—' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 140px">Откуда:</th>
                                <td>
                                    @if($movement->fromLocation)
                                        <a href="{{ route('locations.show', $movement->fromLocation) }}">
                                            {{ $movement->fromLocation->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Куда:</th>
                                <td>
                                    @if($movement->toLocation)
                                        <a href="{{ route('locations.show', $movement->toLocation) }}">
                                            {{ $movement->toLocation->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Пользователь:</th>
                                <td>{{ $movement->user->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Статус:</th>
                                <td>
                                    @if($movement->status === 'confirmed')
                                        <span class="badge bg-success">Подтверждён</span>
                                    @else
                                        <span class="badge bg-secondary">Черновик</span>
                                    @endif
                                </td>
                            </tr>
                            @if($movement->comments)
                            <tr>
                                <th>Комментарий:</th>
                                <td colspan="2">{{ $movement->comments }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection