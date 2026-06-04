@extends('layouts.app')

@section('title', 'Просроченные товары')

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
        <i class="fas fa-times-circle me-2"></i> Просроченные товары
    </h1>
    <button onclick="window.print();" class="btn btn-secondary no-print">
        <i class="fas fa-print"></i> Печать
    </button>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent fw-bold">
        Всего просрочено: <span class="text-danger">{{ $totalExpired ?? 0 }}</span> шт.
    </div>
</div>

<div class="card border-0 shadow-sm" style="transition: none !important; transform: none !important;">
    <div class="card-header bg-transparent fw-bold">
        <i class="fas fa-list me-1"></i> Список просроченных партий
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Товар</th>
                        <th>Артикул</th>
                        <th>Партия</th>
                        <th>Кол-во</th>
                        <th>Срок до</th>
                        <th>Просрочка (дней)</th>
                        <th class="no-print"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $item)
                        <tr>
                            <td><strong>{{ $item['product']->name }}</strong></td>
                            <td>{{ $item['product']->article }}</td>
                            <td>{{ $item['batch']->batch_number }}</td>
                            <td>{{ $item['quantity'] }} шт.</td>
                            <td>{{ \Carbon\Carbon::parse($item['valid_until'])->format('d.m.Y') }}</td>
                            <td><span class="text-danger">{{ $item['days_overdue'] }} дн.</span></td>
                            <td class="no-print">
                                <a href="{{ route('batches.show', $item['batch']) }}" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Нет просроченных товаров.</td>
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