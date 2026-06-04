@extends('layouts.app')

@section('title', 'Товары')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <i class="fas fa-box me-2"></i> Товары
    </h1>
    <a href="{{ route('products.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Добавить товар
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
                        <th>Артикул</th>
                        <th>Цена</th>
                        <th>Остаток</th>
                        <th style="width: 140px">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>
                                <strong>{{ $product->name }}</strong>
                                @if($product->description)
                                    <br><small class="text-muted">{{ Str::limit($product->description, 60) }}</small>
                                @endif
                            </td>
                            <td>{{ $product->article }}</td>
                            <td>{{ number_format($product->price, 2) }} ₽</td>
                            <td>
                                <span class="badge bg-primary rounded-pill px-3 py-2">
                                    {{ $product->current_stock }} шт.
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-info" title="Просмотр">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-warning" title="Редактировать">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('products.destroy', $product) }}" class="d-inline" onsubmit="return confirm('Удалить товар?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-box-open fa-2x mb-2 d-block"></i>
                                Товаров пока нет. <a href="{{ route('products.create') }}">Добавить первый товар</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(method_exists($products, 'links'))
    <div class="mt-4 d-flex justify-content-center">
        {{ $products->links() }}
    </div>
@endif
@endsection