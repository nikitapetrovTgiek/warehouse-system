@extends('layouts.app')

@section('title', 'Отчёт по остаткам')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <i class="fas fa-chart-bar me-2"></i> Отчёт по остаткам товаров
    </h1>
    <button onclick="window.print();" class="btn btn-secondary">
        <i class="fas fa-print"></i> Печать
    </button>
</div>

@if(empty($reportData))
    <div class="alert alert-info">Нет товаров с ненулевым остатком.</div>
@else
    @php
        $totalProducts = count($reportData);
        $totalQuantity = array_sum(array_column($reportData, 'total_stock'));
        $totalLocations = collect($reportData)->sum(function($item) {
            return count($item['locations']);
        });
        $totalBatches = collect($reportData)->sum(function($item) {
            return count($item['batches']);
        });
    @endphp

    {{-- 4 блока сводки --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <h5>
                    <i class="fas fa-box me-2"></i> Всего наименований товаров
                </h5>
                <h2 class="text-primary">{{ $totalProducts }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <h5>
                    <i class="fas fa-calculator me-2"></i> Общее количество
                </h5>
                <h2 class="text-success">{{ $totalQuantity }} шт.</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <h5>
                    <i class="fas fa-map-marker-alt me-2"></i> Задействовано мест
                </h5>
                <h2 class="text-info">{{ $totalLocations }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <h5>
                    <i class="fas fa-tags me-2"></i> Активных партий
                </h5>
                <h2 class="text-warning">{{ $totalBatches }}</h2>
            </div>
        </div>
    </div>

    {{-- Таблица с детализацией --}}
    <div class="card border-0 shadow-sm" style="transition: none !important; transform: none !important;">
        <div class="card-header bg-transparent fw-bold">
            Детализация по товарам
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Товар</th>
                            <th>Артикул</th>
                            <th>Общий остаток</th>
                            <th>Места хранения</th>
                            <th>Партии</th>
                            <th style="width: 60px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item['product']->name }}</strong>
                                @if($item['product']->description)
                                    <br><small class="text-muted">{{ Str::limit($item['product']->description, 80) }}</small>
                                @endif
                            </td>
                            <td>{{ $item['product']->article }}</td>
                            <td><span class="badge bg-primary">{{ $item['total_stock'] }} шт.</span></td>
                            <td>
                                @if(!empty($item['locations']))
                                    @foreach($item['locations'] as $loc)
                                        <div class="mb-1">
                                            <i class="fas fa-map-marker-alt text-secondary"></i>
                                            <strong>{{ $loc['name'] }}</strong> ({{ $loc['type'] }})
                                            <span class="badge bg-secondary">{{ $loc['quantity'] }} шт.</span>
                                            @if($loc['capacity'])
                                                <div class="progress mt-1" style="height: 5px; width: 100px;">
                                                    <div class="progress-bar bg-info" style="width: {{ $loc['load_percent'] }}%"></div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if(!empty($item['batches']))
                                    @foreach($item['batches'] as $batch)
                                        <div class="mb-1">
                                            <i class="fas fa-layer-group text-secondary"></i>
                                            {{ $batch['number'] }}
                                            <span class="badge bg-secondary">{{ $batch['quantity'] }} шт.</span>
                                            @if($batch['expiration_date'])
                                                <small class="text-muted">
                                                    (до {{ \Carbon\Carbon::parse($batch['expiration_date'])->format('d.m.Y') }})
                                                </small>
                                            @endif
                                            <span class="badge 
                                                @if($batch['status'] === 'active') bg-success
                                                @elseif($batch['status'] === 'expiring_soon') bg-warning text-dark
                                                @else bg-danger
                                                @endif">
                                                @if($batch['status'] === 'active') Активна
                                                @elseif($batch['status'] === 'expiring_soon') Скоро
                                                @else Просрочена
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('products.show', $item['product']) }}" class="btn btn-sm btn-outline-info" title="Просмотр товара">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<div class="text-center text-muted mt-3 small">
    Отчёт сформирован: {{ now()->format('d.m.Y H:i:s') }}
</div>
@endsection
@push('styles')
<style>
    @media print {
        /* Скрываем всё лишнее */
        .navbar,
        .btn,
        footer,
        form,
        .card-header button,
        .btn-close,
        .no-print,
        .print-hide,
        .btn-secondary,
        .d-flex .btn,
        .text-center .btn,
        a.btn,
        .table .btn,
        .btn-sm,
        .btn-outline-info,
        .fa-eye,
        [class*="btn"],
        button {
            display: none !important;
        }

        /* Оставляем только таблицу и сводку */
        body, .container, .card, .table-container, .row, .col-md-3, .card-body, .table-responsive {
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: none !important;
            border: none !important;
        }

        /* Карточки сводки */
        .card.border-0.shadow-sm {
            border: 1px solid #ccc !important;
            margin-bottom: 10px !important;
        }

        /* Таблица */
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            border: 1px solid #000 !important;
            padding: 6px;
        }
        .table th {
            background: #f2f2f2 !important;
        }

        /* Заголовок и подвал */
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
            margin: 1.5cm;
        }
    }
</style>
@endpush