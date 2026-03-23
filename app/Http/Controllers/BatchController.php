<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Models\InventoryMovement;

class BatchController extends Controller
{
    /**
     * Все партии
     */
    public function index()
    {
        $batches = Batch::with(['product'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('batches.index', compact('batches'));
    }

    /**
     * Создание новой партии
     */
    public function create()
    {
        $products = Product::orderBy('name')->get();

        return view('batches.create', compact('products'));
    }

    /**
     * Сохранение партии
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_number' => 'required|string|max:255|unique:batches,batch_number',
            'manufactured_date' => 'nullable|date',
            'expiration_date' => 'nullable|date|after:manufactured_date',
            'certificate' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
    ]);
        // id текущего пользователя как создатель
        $validated['created_by'] = Auth::id();
        Batch::create($validated);

        return redirect()
            ->route('batches.index')
            ->with('success', 'Партия успешно добавлена');
    }

    /**
     * Информация о партии
     */
    public function show(Batch $batch)
    {
        $batch->load(['product', 'creator']);
        // движения по этой партии
        $movements = InventoryMovement::where('batch_id', $batch->id)
            ->with(['fromLocation', 'toLocation', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('batches.show', compact('batch', 'movements'));
    }

    /**
     * Редактирование партии
     */
    public function edit(Batch $batch)
    {
        $products = Product::orderBy('name')->get();

        return view('batches.edit', compact('batch', 'products'));
    }

    /**
     * Сохранение редактирования
     */
    public function update(Request $request, Batch $batch)
    {
         $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_number' => 'required|string|max:255|unique:batches,batch_number,' . $batch->id,
            'manufactured_date' => 'nullable|date',
            'expiration_date' => 'nullable|date|after:manufactured_date',
            'certificate' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
    ]);
        $batch->update($validated);

        return redirect()
            ->route('batches.index')
            ->with('success', 'Партия успешно обновлена');
    }

    /**
     * Удаление партии
     */
    public function destroy(Batch $batch)
    {
        // проверка есть ли движения по этой партии
        if ($batch->movements()->exists()) {
            return redirect()
                ->route('batches.index')
                ->with('error', 'Нельзя удалить партию, по которой были движения');
    }
        $batch->delete();

        return redirect()
            ->route('batches.index')
            ->with('success', 'Партия успешно удалена');
    }
}
