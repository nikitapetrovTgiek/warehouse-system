@extends('layouts.app')

@section('title', 'Создание партии + приёмка')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <i class="fas fa-plus-circle me-2"></i> Создание партии и приемка товаров
    </h1>
    <a href="{{ route('batches.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Назад к списку
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm" style="transition: none !important; transform: none !important;">
            <div class="card-header bg-transparent fw-bold">
                <i class="fas fa-layer-group"></i> Новая партия
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

                <form method="POST" action="{{ route('batches.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="product_id" class="form-label">Товар <span class="text-danger">*</span></label>
                        <select class="form-control @error('product_id') is-invalid @enderror" id="product_id" name="product_id" required>
                            <option value=""> Выберите товар </option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} ({{ $product->article }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="batch_number" class="form-label">Номер партии <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('batch_number') is-invalid @enderror" 
                               id="batch_number" name="batch_number" value="{{ old('batch_number') }}" required>
                        <div class="form-text">Уникальный номер партии</div>
                    </div>

                    <div class="mb-3">
                        <label for="manufactured_date" class="form-label">Дата производства</label>
                        <input type="date" class="form-control @error('manufactured_date') is-invalid @enderror" 
                               id="manufactured_date" name="manufactured_date" value="{{ old('manufactured_date') }}">
                    </div>

                    <div class="mb-3">
                        <label for="expiration_date" class="form-label">Срок годности / гарантия до</label>
                        <input type="date" class="form-control @error('expiration_date') is-invalid @enderror" 
                               id="expiration_date" name="expiration_date" value="{{ old('expiration_date') }}">
                        <div class="form-text">Оставьте пустым, если не ограничен</div>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Количество (шт.) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                               id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label for="location_id" class="form-label">Место хранения <span class="text-danger">*</span></label>
                        <select class="form-control @error('location_id') is-invalid @enderror" id="location_id" name="location_id" required>
                            <option value=""> Выберите место </option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" 
                                    {{ old('location_id') == $location->id ? 'selected' : '' }}
                                    @if($location->capacity && $location->current_load >= $location->capacity) disabled @endif>
                                    {{ $location->name }} 
                                    (свободно: 
                                    @if($location->capacity)
                                        {{ $location->capacity - $location->current_load }} шт.
                                    @else
                                        ∞
                                    @endif)
                                </option>
                            @endforeach
                        </select>
                        @error('location_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Примечания</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save"></i> Сохранить партию и принять товар
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