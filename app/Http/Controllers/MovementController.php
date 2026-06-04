<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\StorageLocation;
use App\Models\Batch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MovementController extends Controller
{
    // все движения
    public function index()
    {
        $movements = InventoryMovement::with(['product', 'batch', 'fromLocation', 'toLocation', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);  // по 20 на странице

        return view('movements.index', compact('movements'));
    }

    // форма приемки товара
    public function createReceipt()
    {
        $products = Product::orderBy('name')->get();
        // активные места хранения
        $locations = StorageLocation::where('is_active', true)
            ->orderBy('name')
            ->get();
        // все партии
        $batches = Batch::with('product')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('movements.receipt', compact('products', 'locations','batches'));
    }
    public function storeReceipt(Request $request)
    {
        // валидация
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_id' => 'required|exists:batches,id',
            'quantity' => 'required|integer|min:1',
            'location_id' => 'required|exists:storage_locations,id',
            'document_number' => 'nullable|string|max:255',
            'comments' => 'nullable|string',
        ]);
        // проверка что партия относится к этому товару
        $batch = Batch::findOrFail($validated['batch_id']);
        if ($batch->product_id != $validated['product_id']) {
            return back()
                ->withInput()
                ->withErrors(['batch_id' => 'Эта партия не принадлежит выбранному товару']);
        }
        // проверка на вместимость места
        $location = StorageLocation::findOrFail($validated['location_id']);
        if ($location->capacity && $location->available_capacity < $validated['quantity']) {
            return back()
                ->withInput()
                ->withErrors(['location_id' => 'В этом месте недостаточно свободного места']);
        }
        // начало транзакции
        DB::beginTransaction();
        try {
            // движение (приёмка)
            $movement = InventoryMovement::create([
                'product_id' => $validated['product_id'],
                'batch_id' => $validated['batch_id'],
                'to_location_id' => $validated['location_id'],
                'user_id' => Auth::id(),
                'movement_type' => 'receipt',
                'quantity' => $validated['quantity'],
                'document_number' => $validated['document_number'] ?? null,
                'comments' => $validated['comments'] ?? null,
                'status' => 'confirmed',
            ]);
            // обновляем загрузку места
            $location->increment('current_load', $validated['quantity']);
            DB::commit();

            return redirect()
                ->route('movements.index')
                ->with('success', 'Приёмка успешно оформлена');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при сохранении: ' . $e->getMessage()]);
        }
    }
    // форма отгрузки товара
    public function createShipment()
    {
        $products = Product::orderBy('name')->get();
        $locations = StorageLocation::where('is_active', true)
            ->where('current_load', '>', 0) // только непустые
            ->orderBy('name')
            ->get();
        $batches = Batch::with('product')
            ->get()
            ->map(function ($batch) {
            // Находим места, где есть остаток этой партии
            $locationIds = \App\Models\InventoryMovement::where('batch_id', $batch->id)
                ->where('quantity', '>', 0)
                ->pluck('to_location_id')
                ->unique()
                ->values()
                ->toArray();
            
            // Добавляем свойство location_ids к объекту партии
            $batch->location_ids = $locationIds;
            
            return $batch;
        });

        return view('movements.shipment', compact('products', 'locations', 'batches'));
    }
    // сохранить отгрузку
    public function storeShipment(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_id' => 'required|exists:batches,id',
            'location_id' => 'required|exists:storage_locations,id',
            'quantity' => 'required|integer|min:1',
            'document_number' => 'nullable|string|max:255',
            'comments' => 'nullable|string',
        ]);
        // проверка на кол во товара для отгрузки
        $location = StorageLocation::findOrFail($validated['location_id']);
        
        if ($location->current_load < $validated['quantity']) {
            return back()
                ->withInput()
                ->withErrors(['quantity' => 'В этом месте недостаточно товара']);
        }
        DB::beginTransaction();
        try {
            // двтжение отгрузка
            $movement = InventoryMovement::create([
                'product_id' => $validated['product_id'],
                'from_location_id' => $validated['location_id'],
                'user_id' => Auth::id(),
                'movement_type' => 'shipment',
                'batch_id' => $request->get('batch_id'), 
                'quantity' => -$validated['quantity'], // отрицательное число
                'document_number' => $validated['document_number'] ?? null,
                'comments' => $validated['comments'] ?? null,
                'status' => 'confirmed',
            ]);
            if (!is_numeric($validated['quantity']) || $validated['quantity'] <= 0) {
                throw new \Exception('Некорректное количество');
            }
            // уменьшение нагруженносмти места
            $location->decrement('current_load', $validated['quantity']);
            DB::commit();

            return redirect()
                ->route('movements.index')
                ->with('success', 'Отгрузка успешно оформлена');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при сохранении: ' . $e->getMessage()]);
        }
    }
    // сделать перемещение
    public function createTransfer()
    {
        $products = Product::orderBy('name')->get();
        $fromLocations = StorageLocation::where('is_active', true)
            ->where('current_load', '>', 0) // только непустые
            ->orderBy('name')
            ->get();
        $toLocations = StorageLocation::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($location) {
            // Добавляем виртуальное поле "свободно"
            $location->available = $location->capacity 
                ? $location->capacity - $location->current_load 
                : null;
            return $location;
        });
        $batches = Batch::with('product')
            ->get()
            ->map(function ($batch) {
            // Находим места, где есть остаток этой партии
            $locationIds = \App\Models\InventoryMovement::where('batch_id', $batch->id)
                ->where('quantity', '>', 0)
                ->pluck('to_location_id')
                ->unique()
                ->values()
                ->toArray();
            
            // Добавляем свойство location_ids к объекту партии
            $batch->location_ids = $locationIds;
            
            return $batch;
        });

        return view('movements.transfer', compact('products', 'fromLocations', 'toLocations','batches'));
    }
    // сохранить перемещение
    public function storeTransfer(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_id' => 'required|exists:batches,id',
            'from_location_id' => 'required|exists:storage_locations,id|different:to_location_id',
            'to_location_id' => 'required|exists:storage_locations,id',
            'quantity' => 'required|integer|min:1',
            'comments' => 'nullable|string',
        ]);
        // проверка мест
        $fromLocation = StorageLocation::findOrFail($validated['from_location_id']);
        $toLocation = StorageLocation::findOrFail($validated['to_location_id']);
        // проверка на кол во товара
        if ($fromLocation->current_load < $validated['quantity']) {
            return back()
                ->withInput()
                ->withErrors(['quantity' => 'В месте отправки недостаточно товара']);
        }
        // проверка на свободное место
        if ($toLocation->capacity && $toLocation->available_capacity < $validated['quantity']) {
            return back()
                ->withInput()
                ->withErrors(['to_location_id' => 'В месте назначения недостаточно свободного места']);
        }
        DB::beginTransaction();
    
        try {
            // движение перемещение
            $movement = InventoryMovement::create([
                'product_id' => $validated['product_id'],
                'from_location_id' => $validated['from_location_id'],
                'to_location_id' => $validated['to_location_id'],
                'user_id' => Auth::id(),
                'movement_type' => 'transfer',
                'batch_id' => $request->get('batch_id'),
                'quantity' => $quantity,
                'comments' => $validated['comments'] ?? null,
                'status' => 'confirmed',
            ]);
            // обновление нагрузки места
            $fromLocation->decrement('current_load', $validated['quantity']);
            $toLocation->increment('current_load', $validated['quantity']);
            DB::commit();
            return redirect()
                ->route('movements.index')
                ->with('success', 'Товар успешно перемещён');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при сохранении: ' . $e->getMessage()]);
        }
    }
    // списание
    public function createWriteOff()
    {
        $products = Product::orderBy('name')->get();
        $locations = StorageLocation::where('is_active', true)
            ->where('current_load', '>', 0) // только непустые
            ->orderBy('name')
            ->get();
        $batches = Batch::with('product')
            ->get()
            ->map(function ($batch) {
            // Находим места, где есть остаток этой партии
            $locationIds = \App\Models\InventoryMovement::where('batch_id', $batch->id)
                ->where('quantity', '>', 0)
                ->pluck('to_location_id')
                ->unique()
                ->values()
                ->toArray();
            
            // Добавляем свойство location_ids к объекту партии
            $batch->location_ids = $locationIds;
            
            return $batch;
        });

        return view('movements.write-off', compact('products', 'locations', 'batches'));
    }
    // сохранить списание
    public function storeWriteOff(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_id' => 'required|exists:batches,id',
            'location_id' => 'required|exists:storage_locations,id',
            'batch_id' => 'nullable|exists:batches,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:100',
            'comments' => 'nullable|string',
        ]);
        $location = StorageLocation::findOrFail($validated['location_id']);
        if ($location->current_load < $validated['quantity']) {
            return back()
                ->withInput()
                ->withErrors(['quantity' => 'В этом месте недостаточно товара для списания']);
        }
        DB::beginTransaction();
        try {
            // комментарий с причиной
            $comments = $validated['comments'] ?? '';
            if (!empty($validated['reason'])) {
                $reasonText = match($validated['reason']) {
                    'expired' => 'Причина: истек срок годности',
                    'damaged' => 'Причина: брак/повреждение',
                    'loss' => 'Причина: недостача',
                    default => 'Причина: другое',
                };
                $comments = $reasonText . ($comments ? ' — ' . $comments : '');
            }
            //  движение списание
            $movement = InventoryMovement::create([
                'product_id' => $validated['product_id'],
                'batch_id' => $validated['batch_id'] ?? null,
                'from_location_id' => $validated['location_id'],
                'user_id' => Auth::id(),
                'movement_type' => 'write_off',
                'batch_id' => $request->get('batch_id'),
                'quantity' => -$validated['quantity'], // отрицательное
                'comments' => $comments,
                'status' => 'confirmed',
            ]);
            // уменьшение нагруженности места
            $location->decrement('current_load', $validated['quantity']);
            DB::commit();
            return redirect()
                ->route('movements.index')
                ->with('success', 'Товар успешно списан');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при сохранении: ' . $e->getMessage()]);
        }
    }
    // просмотр движения
    public function show(InventoryMovement $movement)
    {
        $movement->load([
            'product',
            'batch',
            'fromLocation',
            'toLocation',
            'user'
        ]);

        return view('movements.show', compact('movement'));
    }
}
