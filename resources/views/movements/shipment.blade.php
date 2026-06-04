@extends('layouts.app')

@section('title', 'Отгрузка товара')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <i class="fas fa-truck me-2"></i> Отгрузка товара
    </h1>
    <a href="{{ route('movements.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Назад к списку
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm" style="transition: none !important; transform: none !important;">
            <div class="card-header bg-transparent fw-bold">
                <i class="fas fa-arrow-up"></i> Отгрузка со склада
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

                <form method="POST" action="{{ route('movements.shipment.store') }}">
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
                    </div>

                    <div class="mb-3">
                        <label for="document_number" class="form-label">Номер документа</label>
                        <input type="text" class="form-control @error('document_number') is-invalid @enderror" 
                               id="document_number" name="document_number" value="{{ old('document_number') }}" 
                               placeholder="Накладная, счёт-фактура...">
                    </div>

                    <div class="mb-3">
                        <label for="comments" class="form-label">Комментарий</label>
                        <textarea class="form-control @error('comments') is-invalid @enderror" 
                                  id="comments" name="comments" rows="2">{{ old('comments') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-warning px-4">
                            <i class="fas fa-truck"></i> Оформить отгрузку
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

        // Сохраняем все места
        const allLocations = [];
        for (let i = 0; i < locationSelect.options.length; i++) {
            if (locationSelect.options[i].value !== '') {
                allLocations.push({
                    value: locationSelect.options[i].value,
                    text: locationSelect.options[i].text
                });
            }
        }

        // Фильтр партий по товару
        function filterBatches() {
            const selectedProductId = productSelect.value;

            batchSelect.value = '';
            locationSelect.innerHTML = '<option value=""> Сначала выберите партию </option>';

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

        // Фильтр мест по партии
        function filterLocations() {
            const selectedBatchId = batchSelect.value;

            locationSelect.innerHTML = '<option value=""> Выберите место </option>';

            if (!selectedBatchId) return;

            // Находим выбранную партию
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
            for (let i = 0; i < allLocations.length; i++) {
                const loc = allLocations[i];
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
        }

        productSelect.addEventListener('change', function() {
            filterBatches();
            filterLocations();
        });

        batchSelect.addEventListener('change', filterLocations);

        filterBatches();
    });
</script>
@endsection