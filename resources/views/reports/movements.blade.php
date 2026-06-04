@extends('layouts.app')

@section('title', 'Отчёт по движениям')

@push('styles')
<style>
    @media print {
        .navbar, .btn, footer, form, .no-print, .print-hide,
        .card-header button, .btn-secondary, .d-flex .btn, a.btn,
        .table .btn, .btn-sm, button, .pagination,
        .row.g-3.mb-4 {
            display: none !important;
        }

        body, .container, .card, .table-container {
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: none !important;
            border: none !important;
        }

        .table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 8pt !important;
            table-layout: fixed !important;
            word-break: break-word !important;
        }

        .table th, .table td {
            border: 1px solid #000 !important;
            padding: 4px 4px !important;
            vertical-align: top !important;
        }

        .table th {
            background: #f2f2f2 !important;
        }

        .report-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
        }

        footer, .text-muted, .small {
            display: none !important;
        }

        @page {
            size: A4;
            margin: 1cm;
        }
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <i class="fas fa-list-alt me-2"></i> Отчёт по движениям товаров
    </h1>
    <button onclick="window.print();" class="btn btn-secondary no-print">
        <i class="fas fa-print"></i> Печать
    </button>
</div>

<div class="card border-0 shadow-sm mb-4 no-print" style="transition: none !important; transform: none !important;">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.movements') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Дата от</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date', $startDate ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Дата до</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date', $endDate ?? '') }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Применить фильтр</button>
            </div>
        </form>
    </div>
</div>

@php
    $totalReceipt = $movements->where('movement_type', 'receipt')->sum('quantity');
    $totalShipment = abs($movements->where('movement_type', 'shipment')->sum('quantity'));
    $totalWriteOff = abs($movements->where('movement_type', 'write_off')->sum('quantity'));
    $totalTransfer = $movements->where('movement_type', 'transfer')->count();
    $totalOperations = $movements->count();

    $periodText = '';
    if (request()->has('start_date') || request()->has('end_date')) {
        $periodText = ' — ' . request('start_date', 'все') . ' / ' . request('end_date', 'все');
    }
@endphp

<div class="row g-3 mb-4 no-print">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <h5>
                <i class="fas fa-arrow-down me-2"></i> Приход
            </h5>
            <h2 class="text-success">{{ $totalReceipt }} шт.</h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <h5>
                <i class="fas fa-arrow-up me-2"></i> Расход
            </h5>
            <h2 class="text-danger">{{ $totalShipment + $totalWriteOff }} шт.</h2>
            <small class="text-muted">(отгрузка: {{ $totalShipment }}, списание: {{ $totalWriteOff }})</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <h5>
                <i class="fas fa-exchange-alt me-2"></i> Перемещения
            </h5>
            <h2 class="text-info">{{ $totalTransfer }}</h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <h5>
                <i class="fas fa-calculator me-2"></i> Итого операций
            </h5>
            <h2 class="text-primary">{{ $totalOperations }}</h2>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm" style="transition: none !important; transform: none !important;">
    <div class="card-header bg-transparent fw-bold d-flex justify-content-between align-items-center">
        <span>
            <i class="fas fa-list me-1"></i> Детализация движений{{ $periodText }}
        </span>
    </div>
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
                        <th>Пользователь</th>
                        <th class="no-print"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td>{{ $movement->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                @php
                                    $typeColors = ['receipt' => 'success', 'shipment' => 'warning', 'transfer' => 'info', 'write_off' => 'danger'];
                                    $color = $typeColors[$movement->movement_type] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ $movement->movement_type_name }}</span>
                            </td>
                            <td>
                                <a href="{{ route('products.show', $movement->product) }}">{{ $movement->product->name }}</a>
                            </td>
                            <td>
                                @if($movement->batch)
                                    <a href="{{ route('batches.show', $movement->batch) }}">{{ $movement->batch->batch_number }}</a>
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
                            <td>{{ $movement->user->name ?? '—' }}</td>
                            <td class="no-print">
                                <a href="{{ route('movements.show', $movement) }}" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">Нет движений за выбранный период.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(method_exists($movements, 'links'))
    <div class="mt-4 d-flex justify-content-center no-print">
        {{ $movements->withQueryString()->links() }}
    </div>
@endif

<div class="text-center text-muted mt-3 small no-print">
    Отчёт сформирован: {{ now()->format('d.m.Y H:i:s') }}
</div>
@endsection