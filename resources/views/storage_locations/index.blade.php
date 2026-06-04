@extends('layouts.app')

@section('title', 'Места хранения')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <i class="fas fa-map-marker-alt me-2"></i> Места хранения
    </h1>
    <a href="{{ route('locations.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Добавить место
    </a>
</div>

<div class="card border-0 shadow-sm" style="transition: none !important; transform: none !important;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Тип</th>
                        <th>Вместимость</th>
                        <th>Загрузка</th>
                        <th>Свободно</th>
                        <th>Статус</th>
                        <th style="width: 100px">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($locations as $location)
                        <tr>
                            <td>{{ $location->id }}</td>
                            <td><strong>{{ $location->name }}</strong></td>
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
                            <td>
                                @if($location->capacity)
                                    {{ $location->capacity }} шт.
                                @else
                                    <span class="text-muted">∞</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span>{{ $location->current_load }} шт.</span>
                                    @if($location->capacity)
                                        <div class="progress flex-grow-1" style="height: 6px; width: 80px;">
                                            <div class="progress-bar bg-success" 
                                                 style="width: {{ min(100, ($location->current_load / $location->capacity) * 100) }}%"></div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($location->capacity)
                                    {{ $location->capacity - $location->current_load }} шт.
                                @else
                                    <span class="text-muted">∞</span>
                                @endif
                            </td>
                            <td>
                                @if($location->is_active)
                                    <span class="badge bg-success">Активно</span>
                                @else
                                    <span class="badge bg-secondary">Неактивно</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('locations.show', $location) }}" class="btn btn-sm btn-outline-info" title="Просмотр">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('locations.edit', $location) }}" class="btn btn-sm btn-outline-warning" title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('locations.destroy', $location) }}" class="d-inline" onsubmit="return confirm('Удалить место?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить" @if($location->current_load > 0) disabled @endif>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-map-marker-alt fa-2x mb-2 d-block"></i>
                                Мест хранения пока нет. <a href="{{ route('locations.create') }}">Добавить первое место</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(method_exists($locations, 'links'))
    <div class="mt-4 d-flex justify-content-center">
        {{ $locations->links() }}
    </div>
@endif
@endsection