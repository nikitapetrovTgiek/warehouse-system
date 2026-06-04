@extends('layouts.app')

@section('title', 'Партии товаров')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <i class="fas fa-layer-group me-2"></i> Партии товаров
    </h1>
    <a href="{{ route('batches.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Добавить партию
    </a>
</div>

<div class="card border-0 shadow-sm" style="transition: none !important; transform: none !important;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Товар</th>
                        <th>Номер партии</th>
                        <th>Количество</th>
                        <th>Срок до</th>
                        <th>Статус</th>
                        <th style="width: 110px">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                        <tr>
                            <td>{{ $batch->id }}</td>
                            <td>
                                <strong>{{ $batch->product->name }}</strong>
                                <br><small class="text-muted">{{ $batch->product->article }}</small>
                            </td>
                            <td>{{ $batch->batch_number }}</td>
                            <td>{{ $batch->movements->sum('quantity') }} шт.</td>
                            <td>
                                @if($batch->expiration_date)
                                    {{ \Carbon\Carbon::parse($batch->expiration_date)->format('d.m.Y') }}
                                @else
                                    <span class="text-muted">∞</span>
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
                            <td class="text-nowrap">
                                <div class="d-flex gap-1">
                                    <a href="{{ route('batches.show', $batch) }}" class="btn btn-sm btn-outline-info" title="Просмотр">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('batches.edit', $batch) }}" class="btn btn-sm btn-outline-warning" title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form method="POST" action="{{ route('batches.destroy', $batch) }}" onsubmit="return confirm('Удалить партию?')" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить"
                                            @if($batch->movements->sum('quantity') != 0) disabled @endif>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-layer-group fa-2x mb-2 d-block"></i>
                                Партий пока нет. <a href="{{ route('batches.create') }}">Добавить первую партию</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(method_exists($batches, 'links'))
    <div class="mt-4 d-flex justify-content-center">
        {{ $batches->links() }}
    </div>
@endif
@endsection