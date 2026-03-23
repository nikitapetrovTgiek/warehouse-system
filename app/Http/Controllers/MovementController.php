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

        return view('movements.receipt', compact('products', 'locations'));
    }
    public function storeReceipt(Request $request)
    {
        // валидация
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_number' => 'nullable|string|max:255',
            'expiration_date' => 'nullable|date|after:today',
            'quantity' => 'required|integer|min:1',
            'location_id' => 'required|exists:storage_locations,id',
            'document_number' => 'nullable|string|max:255',
            'comments' => 'nullable|string',
        ]);
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
            // если номер партии есть создаем новую партию
            $batchId = null;
            if (!empty($validated['batch_number'])) {
                $batch = Batch::create([
                    'product_id' => $validated['product_id'],
                    'batch_number' => $validated['batch_number'],
                    'expiration_date' => $validated['expiration_date'] ?? null,
                    'created_by' => Auth::id(),
                ]);
                $batchId = $batch->id;
            }
            // создание движения
            $movement = InventoryMovement::create([
                'product_id' => $validated['product_id'],
                'batch_id' => $batchId,
                'to_location_id' => $validated['location_id'],
                'user_id' => Auth::id(),
                'movement_type' => 'receipt',
                'quantity' => $validated['quantity'],
                'document_number' => $validated['document_number'] ?? null,
                'comments' => $validated['comments'] ?? null,
                'status' => 'confirmed',
            ]);
            // обновление вместимости места
            $location->increment('current_load', $validated['quantity']);
            // если нет ошибок транзакция сохраняется
            DB::commit();
            return redirect()
                ->route('movements.index')
                ->with('success', 'Приёмка успешно оформлена');

        } catch (\Exception $e) {
            // если хоть где то ошибка то откатываем
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

        return view('movements.shipment', compact('products', 'locations'));
    }
    // сохранить отгрузку
    public function storeShipment(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
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
                'quantity' => -$validated['quantity'], // отрицательное число
                'document_number' => $validated['document_number'] ?? null,
                'comments' => $validated['comments'] ?? null,
                'status' => 'confirmed',
            ]);
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
            ->get();

        return view('movements.transfer', compact('products', 'fromLocations', 'toLocations'));
    }
    // сохранить перемещение
    public function storeTransfer(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
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
                'quantity' => 0,
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
        $batches = Batch::with('product')->orderBy('created_at', 'desc')->get();

        return view('movements.write-off', compact('products', 'locations', 'batches'));
    }
    // сохранить списание
    public function storeWriteOff(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
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
