@extends('layouts.app')

@section('title', 'Просмотр партии')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <i class="fas fa-eye me-2"></i> Просмотр партии
    </h1>
    <div>
        <a href="{{ route('batches.edit', $batch) }}" class="btn btn-warning me-2">
            <i class="fas fa-edit"></i> Редактировать
        </a>
        <a href="{{ route('batches.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Назад к списку
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-bold">
                <i class="fas fa-info-circle"></i> Информация о партии
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr><th style="width: 160px">Номер партии:</th><td><strong>{{ $batch->batch_number }}</strong></td></tr>
                            <tr><th>Товар:</th><td>{{ $batch->product->name }} ({{ $batch->product->article }})</td></tr>
                            <tr><th>Количество:</th><td>{{ $batch->movements->sum('quantity') }} шт.</td></tr>
                            @if($batch->manufactured_date)
                            <tr><th>Дата производства:</th><td>{{ \Carbon\Carbon::parse($batch->manufactured_date)->format('d.m.Y') }}</td></tr>
                            @endif
                            @if($batch->expiration_date)
                            <tr>
                                <th>Срок до:</th>
                                <td>
                                    {{ \Carbon\Carbon::parse($batch->expiration_date)->format('d.m.Y') }}
                                    @php $daysLeft = round($batch->daysUntilExpiration()); @endphp
                                    @if($daysLeft < 0)
                                        <span class="text-danger ms-2">(просрочена на {{ abs($daysLeft) }} дн.)</span>
                                    @else
                                        <span class="text-success ms-2">(осталось {{ $daysLeft }} дн.)</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            <tr><th>Статус:</th>
                                <td>
                                    @if($batch->expiration_date && $batch->isExpired())
                                        <span class="badge bg-danger">Просрочена</span>
                                    @elseif($batch->expiration_date && $batch->daysUntilExpiration() <= 30)
                                        <span class="badge bg-warning text-dark">Скоро</span>
                                    @else
                                        <span class="badge bg-success">Активна</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr><th style="width: 160px">Создана:</th><td>{{ $batch->created_at->format('d.m.Y H:i') }}</td></tr>
                            <tr><th>Кем создана:</th><td>{{ $batch->creator->name ?? '—' }}</td></tr>
                            @if($batch->notes)
                            <tr><th>Примечания:</th><td>{{ $batch->notes }}</td></tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Движения этой партии -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent fw-bold">
        <i class="fas fa-exchange-alt"></i> Движения партии
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Дата</th>
                        <th>Тип</th>
                        <th>Кол-во</th>
                        <th>Откуда</th>
                        <th>Куда</th>
                        <th>Документ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batch->movements as $movement)
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
                            <td colspan="6" class="text-center text-muted py-3">Нет движений</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection