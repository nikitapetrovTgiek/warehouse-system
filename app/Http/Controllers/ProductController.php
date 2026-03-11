<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Все товары
     */
    public function index()
    {
        $products = Product::all();
        return view('products.index', compact('products'));
    }
    /**
     * Форма создания нового товара
     */
    public function create()
    {
        return view('products.create');
    }
    /**
     * Сохранение нового товара
     */
    public function store(Request $request)
    {
        // Валидация данных
        $validated = $request->validate([
        'name' => 'required|string|max:255',
        'article' => 'required|string|max:100|unique:products',
        'barcode' => 'nullable|string|max:50',
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
    ]);
        Product::create($validated);
        return redirect()->route('products.index')
        ->with('success', 'Товар успешно добавлен');
    }
    /**
     * Просмотр товара
     */
    public function show(Product $product)
    {
        // Загружаем связанные партии и движения
        $product->load(['batches', 'movements' => function ($q) {
        $q->latest()->limit(10); // последние 10 движений
    }]);
        return view('products.show', compact('product'));
    }
    /**
     * Редактирование товара (форма)
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }
    /**
     * Обновить товар
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
        'name' => 'required|string|max:255',
        'article' => 'required|string|max:100|unique:products,article,' . $product->id,
        'barcode' => 'nullable|string|max:50',
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
    ]);
        $product->update($validated);
        return redirect()->route('products.index')
        ->with('success', 'Товар успешно обновлён');
    }

    /**
     * Удалить товар
     */
    public function destroy(Product $product)
    {
        // Проверяем, можно ли удалить, если есть движения товар удалить нельзя
        if ($product->movements()->exists()) {
        return redirect()
            ->route('products.index')
            ->with('error', 'Нельзя удалить товар, по которому были движения');
    }
        // в противном случае удаляем
        $product->delete();
        return redirect()
        ->route('products.index')
        ->with('success', 'Товар успешно удалён');
    }
}
