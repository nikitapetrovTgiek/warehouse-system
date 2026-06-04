<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Models\InventoryMovement;
use App\Models\StorageLocation;
use Illuminate\Support\Facades\DB;

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
        $locations = StorageLocation::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('batches.create', compact('products', 'locations'));
    }

    /**
     * Сохранение партии
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_number' => 'required|string|max:255|unique:batches,batch_number',
            'manufactured_date' => 'nullable|date',
            'expiration_date' => 'nullable|date|after:manufactured_date',
            'certificate' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'location_id' => 'required|exists:storage_locations,id',
    ]);
            // проверка хватит ли места
        $location = StorageLocation::findOrFail($request->location_id);
        if ($location->capacity && $location->available_capacity < $request->quantity) {
            return back()->withInput()->withErrors(['location_id' => 'Недостаточно свободного места в этой ячейке']);
        }

        DB::beginTransaction();

        try {
            // создаём партию
            $batch = Batch::create([
                'product_id' => $request->product_id,
                'batch_number' => $request->batch_number,
                'manufactured_date' => $request->manufactured_date,
                'expiration_date' => $request->expiration_date,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            // создаём движение приёмки
            InventoryMovement::create([
                'product_id' => $request->product_id,
                'batch_id' => $batch->id,
                'to_location_id' => $request->location_id,
                'user_id' => auth()->id(),
                'movement_type' => 'receipt',
                'quantity' => $request->quantity,
                'status' => 'confirmed',
                'comments' => 'Создано автоматически при добавлении партии',
            ]);

            // обновление загрузки места
            $location->increment('current_load', $request->quantity);
            DB::commit();

            return redirect()->route('batches.index')
                ->with('success', "Партия '{$batch->batch_number}' создана, товар принят на склад ({$request->quantity} шт.)");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Ошибка при создании: ' . $e->getMessage());
        }
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
