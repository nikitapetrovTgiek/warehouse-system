@extends('layouts.app')

@section('title', 'Редактирование партии')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <i class="fas fa-edit me-2"></i> Редактирование партии
    </h1>
    <a href="{{ route('batches.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Назад к списку
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm" style="transition: none !important; transform: none !important;">
            <div class="card-header bg-transparent fw-bold">
                <i class="fas fa-layer-group"></i> Информация о партии
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('batches.update', $batch) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="product_id" class="form-label">Товар <span class="text-danger">*</span></label>
                        <select class="form-control @error('product_id') is-invalid @enderror" id="product_id" name="product_id" required>
                            <option value=""> Выберите товар </option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id', $batch->product_id) == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} ({{ $product->article }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="batch_number" class="form-label">Номер партии <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('batch_number') is-invalid @enderror" 
                               id="batch_number" name="batch_number" value="{{ old('batch_number', $batch->batch_number) }}" required>
                        <div class="form-text">Уникальный номер партии</div>
                    </div>

                    <div class="mb-3">
                        <label for="manufactured_date" class="form-label">Дата производства</label>
                        <input type="date" class="form-control @error('manufactured_date') is-invalid @enderror" 
                               id="manufactured_date" name="manufactured_date" 
                               value="{{ old('manufactured_date', $batch->manufactured_date ? \Carbon\Carbon::parse($batch->manufactured_date)->format('Y-m-d') : '') }}">
                    </div>

                    <div class="mb-3">
                        <label for="expiration_date" class="form-label">Срок годности / гарантия до</label>
                        <input type="date" class="form-control @error('expiration_date') is-invalid @enderror" 
                               id="expiration_date" name="expiration_date" 
                               value="{{ old('expiration_date', $batch->expiration_date ? \Carbon\Carbon::parse($batch->expiration_date)->format('Y-m-d') : '') }}">
                        <div class="form-text">Оставьте пустым, если не ограничен</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Текущее количество (не редактируется)</label>
                        <input type="text" class="form-control" value="{{ $batch->movements->sum('quantity') }} шт." readonly disabled>
                        <div class="form-text">Количество можно изменить только через операции приёмки/отгрузки</div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Примечания</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3">{{ old('notes', $batch->notes) }}</textarea>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Важно:</strong> Количество товара в партии не редактируется. 
                        Для изменения количества используйте операции приёмки или отгрузки.
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save"></i> Обновить партию
                        </button>
                        <a href="{{ route('batches.index') }}" class="btn btn-secondary px-4">
                            <i class="fas fa-times"></i> Отмена
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection