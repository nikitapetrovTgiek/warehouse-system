@extends('layouts.app')

@section('title', 'Перемещение товара')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <i class="fas fa-arrows-alt me-2"></i> Перемещение товара
    </h1>
    <a href="{{ route('movements.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Назад к списку
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm" style="transition: none !important; transform: none !important;">
            <div class="card-header bg-transparent fw-bold">
                <i class="fas fa-arrows-alt"></i> Перемещение между местами хранения
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

                <form method="POST" action="{{ route('movements.transfer.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="product_id" class="form-label">Товар <span class="text-danger">*</span></label>
                        <select class="form-control @error('product_id') is-invalid @enderror" id="product_id" name="product_id" required>
                            <option value=""> Выберите товар </option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} ({{ $product->article }}) — остаток: {{ $product->current_stock }} шт.
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="batch_id" class="form-label">Партия <span class="text-danger">*</span></label>
                        <select class="form-control @error('batch_id') is-invalid @enderror" id="batch_id" name="batch_id" required>
                            <option value=""> Сначала выберите товар </option>
                            @foreach($batches as $batch)
                                <option value="{{ $batch->id }}" 
                                    data-product-id="{{ $batch->product_id }}"
                                    data-location-ids='@json($batch->location_ids)'>
                                    {{ $batch->product->name }} — {{ $batch->batch_number }}
                                    (остаток: {{ $batch->movements()->whereNull('deleted_at')->sum('quantity') }} шт.)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="from_location_id" class="form-label">Откуда <span class="text-danger">*</span></label>
                        <select class="form-control @error('from_location_id') is-invalid @enderror" id="from_location_id" name="from_location_id" required>
                            <option value=""> Выберите место отправления </option>
                            @foreach($fromLocations as $location)
                                <option value="{{ $location->id }}" 
                                    {{ old('from_location_id') == $location->id ? 'selected' : '' }}>
                                    {{ $location->name }} (доступно: {{ $location->current_load }} шт.)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="to_location_id" class="form-label">Куда <span class="text-danger">*</span></label>
                        <select class="form-control @error('to_location_id') is-invalid @enderror" id="to_location_id" name="to_location_id" required>
                            <option value=""> Выберите место назначения </option>
                            @foreach($toLocations as $location)
                                <option value="{{ $location->id }}" 
                                    {{ old('to_location_id') == $location->id ? 'selected' : '' }}
                                    @if($location->capacity && $location->current_load >= $location->capacity) disabled @endif>
                                    {{ $location->name }}
                                    @if($location->capacity)
                                        (свободно: {{ $location->capacity - $location->current_load }} шт.)
                                    @else
                                        (безлимитно)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Количество <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                               id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" required>
                        <div class="form-text" id="quantityHelp"></div>
                    </div>

                    <div class="mb-3">
                        <label for="comments" class="form-label">Комментарий</label>
                        <textarea class="form-control @error('comments') is-invalid @enderror" 
                                  id="comments" name="comments" rows="2">{{ old('comments') }}</textarea>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Перемещение не изменяет общий остаток товара, только его расположение.
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-info px-4">
                            <i class="fas fa-arrows-alt"></i> Переместить
                        </button>
                        <a href="{{ route('movements.index') }}" class="btn btn-secondary px-4">
                            <i class="fas fa-times"></i> Отмена
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productSelect = document.getElementById('product_id');
        const batchSelect = document.getElementById('batch_id');
        const fromLocationSelect = document.getElementById('from_location_id');
        const quantityInput = document.getElementById('quantity');
        const quantityHelp = document.getElementById('quantityHelp');

        // Сохраняем оригинальные опции мест отправления
        const originalFromOptions = [];
        for (let i = 0; i < fromLocationSelect.options.length; i++) {
            if (fromLocationSelect.options[i].value !== '') {
                originalFromOptions.push({
                    value: fromLocationSelect.options[i].value,
                    text: fromLocationSelect.options[i].text
                });
            }
        }

        // Фильтр партий по товару
        function filterBatches() {
            const selectedProductId = productSelect.value;

            batchSelect.value = '';
            fromLocationSelect.innerHTML = '<option value=""> Выберите партию </option>';

            let hasVisible = false;
            for (let i = 0; i < batchSelect.options.length; i++) {
                const option = batchSelect.options[i];
                const productId = option.getAttribute('data-product-id');

                if (option.value === '') continue;

                if (selectedProductId && productId == selectedProductId) {
                    option.style.display = '';
                    hasVisible = true;
                } else {
                    option.style.display = 'none';
                }
            }

            if (!hasVisible && selectedProductId) {
                batchSelect.style.display = 'none';
            } else {
                batchSelect.style.display = '';
            }
        }

        // Фильтр мест отправления по партии
        function filterFromLocations() {
            const selectedBatchId = batchSelect.value;

            fromLocationSelect.innerHTML = '<option value=""> Выберите место отправления </option>';

            if (!selectedBatchId) return;

            let selectedOption = null;
            for (let i = 0; i < batchSelect.options.length; i++) {
                if (batchSelect.options[i].value == selectedBatchId) {
                    selectedOption = batchSelect.options[i];
                    break;
                }
            }

            if (!selectedOption) return;

            const locationIdsAttr = selectedOption.getAttribute('data-location-ids');
            let validIds = [];
            if (locationIdsAttr) {
                try {
                    validIds = JSON.parse(locationIdsAttr);
                } catch(e) {
                    validIds = [];
                }
            }

            let hasVisible = false;
            for (let i = 0; i < originalFromOptions.length; i++) {
                const loc = originalFromOptions[i];
                if (validIds.includes(parseInt(loc.value))) {
                    const option = document.createElement('option');
                    option.value = loc.value;
                    option.textContent = loc.text;
                    fromLocationSelect.appendChild(option);
                    hasVisible = true;
                }
            }

            if (!hasVisible) {
                fromLocationSelect.innerHTML = '<option value="">Нет мест для этой партии</option>';
            }
        }

        // Обновление подсказки по количеству
        function updateQuantityHelp() {
            const selectedFrom = fromLocationSelect.options[fromLocationSelect.selectedIndex];
            if (selectedFrom && selectedFrom.value) {
                const match = selectedFrom.text.match(/доступно: (\d+) шт/);
                if (match) {
                    const maxQty = parseInt(match[1]);
                    quantityInput.max = maxQty;
                    quantityHelp.textContent = `Максимум для перемещения: ${maxQty} шт.`;
                } else {
                    quantityHelp.textContent = '';
                }
            } else {
                quantityHelp.textContent = '';
            }
        }

        productSelect.addEventListener('change', function() {
            filterBatches();
            filterFromLocations();
        });

        batchSelect.addEventListener('change', function() {
            filterFromLocations();
        });

        fromLocationSelect.addEventListener('change', updateQuantityHelp);
        quantityInput.addEventListener('input', function() {
            const max = parseInt(this.max);
            if (max && parseInt(this.value) > max) {
                this.value = max;
            }
        });

        filterBatches();
    });
</script>
@endsection