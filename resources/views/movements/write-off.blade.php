@extends('layouts.app')

@section('title', 'Списание товара')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <i class="fas fa-trash-alt me-2"></i> Списание товара
    </h1>
    <a href="{{ route('movements.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Назад к списку
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm" style="transition: none !important; transform: none !important;">
            <div class="card-header bg-transparent fw-bold">
                <i class="fas fa-trash-alt"></i> Списание товара (брак, порча, потеря)
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

                <form method="POST" action="{{ route('movements.write-off.store') }}">
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
                                    data-location-ids='@json($batch->location_ids)'
                                    data-quantity="{{ $batch->movements()->whereNull('deleted_at')->sum('quantity') }}">
                                    {{ $batch->product->name }} — {{ $batch->batch_number }}
                                    (остаток: {{ $batch->movements()->whereNull('deleted_at')->sum('quantity') }} шт.)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="location_id" class="form-label">Место хранения <span class="text-danger">*</span></label>
                        <select class="form-control @error('location_id') is-invalid @enderror" id="location_id" name="location_id" required>
                            <option value=""> Выберите место </option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">
                                    {{ $location->name }} (доступно: {{ $location->current_load }} шт.)
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
                        <label for="reason" class="form-label">Причина списания</label>
                        <select class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason">
                            <option value=""> Выберите причину </option>
                            <option value="expired" {{ old('reason') == 'expired' ? 'selected' : '' }}>Истек срок годности</option>
                            <option value="damaged" {{ old('reason') == 'damaged' ? 'selected' : '' }}>Брак / повреждение</option>
                            <option value="loss" {{ old('reason') == 'loss' ? 'selected' : '' }}>Потеря / недостача</option>
                            <option value="other" {{ old('reason') == 'other' ? 'selected' : '' }}>Другое</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="comments" class="form-label">Комментарий</label>
                        <textarea class="form-control @error('comments') is-invalid @enderror" 
                                  id="comments" name="comments" rows="2">{{ old('comments') }}</textarea>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Внимание:</strong> Списание невозможно отменить. Убедитесь, что количество указано верно.
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="fas fa-trash"></i> Списать
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
        const locationSelect = document.getElementById('location_id');
        const quantityInput = document.getElementById('quantity');
        const quantityHelp = document.getElementById('quantityHelp');

        // Сохраняем все оригинальные места
        const originalLocations = [];
        for (let i = 0; i < locationSelect.options.length; i++) {
            if (locationSelect.options[i].value !== '') {
                originalLocations.push({
                    value: locationSelect.options[i].value,
                    text: locationSelect.options[i].text
                });
            }
        }

        // Фильтр партий по товару
        function filterBatches() {
            const selectedProductId = productSelect.value;

            batchSelect.value = '';
            locationSelect.innerHTML = '<option value=""> Выберите партию </option>';
            quantityHelp.textContent = '';

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

        // Фильтр мест по выбранной партии
        function filterLocations() {
            const selectedBatchId = batchSelect.value;

            locationSelect.innerHTML = '<option value=""> Выберите место </option>';

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
            for (let i = 0; i < originalLocations.length; i++) {
                const loc = originalLocations[i];
                if (validIds.includes(parseInt(loc.value))) {
                    const option = document.createElement('option');
                    option.value = loc.value;
                    option.textContent = loc.text;
                    locationSelect.appendChild(option);
                    hasVisible = true;
                }
            }

            if (!hasVisible) {
                locationSelect.innerHTML = '<option value="">Нет мест для этой партии</option>';
            }

            // Обновляем максимальное количество
            updateMaxQuantity();
        }

        // Обновление максимального количества
        function updateMaxQuantity() {
            const selectedLocation = locationSelect.options[locationSelect.selectedIndex];
            const selectedBatch = batchSelect.options[batchSelect.selectedIndex];

            let maxQty = 0;

            if (selectedLocation && selectedLocation.value) {
                const match = selectedLocation.text.match(/доступно: (\d+) шт/);
                if (match) {
                    maxQty = parseInt(match[1]);
                }
            }

            if (selectedBatch && selectedBatch.value && selectedBatch.getAttribute('data-quantity')) {
                const batchQty = parseInt(selectedBatch.getAttribute('data-quantity'));
                maxQty = Math.min(maxQty, batchQty);
            }

            quantityInput.max = maxQty;
            if (maxQty > 0) {
                quantityHelp.textContent = `Можно списать максимум ${maxQty} шт.`;
            } else {
                quantityHelp.textContent = 'Нет доступного товара для списания';
            }
        }

        productSelect.addEventListener('change', function() {
            filterBatches();
            filterLocations();
        });

        batchSelect.addEventListener('change', function() {
            filterLocations();
        });

        locationSelect.addEventListener('change', updateMaxQuantity);
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