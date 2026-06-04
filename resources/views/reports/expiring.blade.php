@extends('layouts.app')

@section('title', 'Сроки годности')

@push('styles')
<style>
    @media print {
        .navbar, .btn, footer, form, .no-print, .print-hide,
        .card-header button, .btn-secondary, .d-flex .btn, a.btn,
        .table .btn, .btn-sm, button, .pagination,
        .filter-bar {
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
        <i class="fas fa-calendar-alt me-2"></i> Сроки годности товаров
    </h1>
    <button onclick="window.print();" class="btn btn-secondary no-print">
        <i class="fas fa-print"></i> Печать
    </button>
</div>

<div class="card border-0 shadow-sm mb-4 no-print">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.expiring') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="days" class="form-label">Показать товары, истекающие в течение (дней)</label>
                <select class="form-control" id="days" name="days">
                    <option value="7" {{ request('days', 30) == 7 ? 'selected' : '' }}>7 дней</option>
                    <option value="14" {{ request('days', 30) == 14 ? 'selected' : '' }}>14 дней</option>
                    <option value="30" {{ request('days', 30) == 30 ? 'selected' : '' }}>30 дней</option>
                    <option value="60" {{ request('days', 30) == 60 ? 'selected' : '' }}>60 дней</option>
                    <option value="90" {{ request('days', 30) == 90 ? 'selected' : '' }}>90 дней</option>
                </select>
            </div>
            <div class="col-md-4">
                <div class="form-check mt-4">
                    <input type="checkbox" class="form-check-input" id="only_expiring" name="only_expiring" value="1" {{ request('only_expiring', '') !== '' ? 'checked' : '' }}>
                    <label class="form-check-label" for="only_expiring">Показывать только истекающие (без просрочки)</label>
                </div>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> Применить фильтр
                </button>
            </div>
        </form>
    </div>
</div>

@php
    $days = request('days', 30);
    $onlyExpiring = request()->has('only_expiring');
@endphp

<div class="card border-0 shadow-sm" style="transition: none !important; transform: none !important;">
    <div class="card-header bg-transparent fw-bold d-flex justify-content-between align-items-center">
        <span>
            <i class="fas fa-list me-1"></i> Партии с истекающим сроком
        </span>
        <span class="badge bg-info">Фильтр: {{ $days }} дн. {{ $onlyExpiring ? '(только истекающие)' : '(включая просрочку)' }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Товар</th>
                        <th>Артикул</th>
                        <th>Партия</th>
                        <th>Количество</th>
                        <th>Срок до</th>
                        <th>Дней осталось</th>
                        <th>Статус</th>
                        <th class="no-print"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $item)
                        @php
                            $daysLeft = $item['days_left'];
                        @endphp
                        <tr>
                            <td><strong>{{ $item['product']->name }}</strong></td>
                            <td>{{ $item['product']->article }}</td>
                            <td>{{ $item['batch']->batch_number }}</td>
                            <td>{{ $item['quantity'] }} шт.</td>
                            <td>{{ \Carbon\Carbon::parse($item['expiration_date'])->format('d.m.Y') }}</td>
                            <td>
                                @if($daysLeft < 0)
                                    <span class="text-danger">просрочена на {{ abs($daysLeft) }} дн.</span>
                                @else
                                    {{ $daysLeft }} дн.
                                @endif
                            </td>
                            <td>
                                @if($item['status'] === 'expired')
                                    <span class="badge bg-danger">Просрочено</span>
                                @elseif($item['status'] === 'expiring_soon')
                                    <span class="badge bg-warning text-dark">Скоро</span>
                                @else
                                    <span class="badge bg-success">Активна</span>
                                @endif
                            </td>
                            <td class="no-print">
                                <a href="{{ route('batches.show', $item['batch']) }}" class="btn btn-sm btn-outline-info" title="Просмотр партии">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Нет партий с истекающим сроком.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="text-center text-muted mt-3 small no-print">
    Отчёт сформирован: {{ now()->format('d.m.Y H:i:s') }}
</div>
@endsection