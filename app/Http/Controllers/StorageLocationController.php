<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StorageLocation;
use App\Models\InventoryMovement;

class StorageLocationController extends Controller
{
    /**
     * Все места для хранения
     */
    public function index()
    {
        $locations = StorageLocation::orderBy('name')->get();
        return view('storage_locations.index', compact('locations'));
    }

    /**
     * Форма создания
     */
    public function create()
    {
        // Массив типов мест для выпадающего списка
        $types = [
            'cell' => 'Ячейка',
            'rack' => 'Стеллаж',
            'zone' => 'Зона',
            'floor' => 'Напольное хранение'
        ];
        return view('storage_locations.create', compact('types'));
    }

    /**
     * Сохравнение нового места хранения
     */
    public function store(Request $request)
    {
        // Валидация данных
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:storage_locations,name',
            'type' => 'required|in:cell,rack,zone,floor',
            'capacity' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);
        $validated['current_load'] = 0;  // новое место всегда пустое
        $validated['is_active'] = $request->has('is_active');

        StorageLocation::create($validated);
        return redirect()
        ->route('locations.index')
        ->with('success', 'Место хранения успешно добавлено');
    }

    /**
     * Показать информацию о месте хранения
     */
    public function show(StorageLocation $location)
    {
        $location->load([
            'movementsFrom' => function ($q) {
                $q->with(['product', 'batch'])->latest()->limit(20);
            },
            'movementsTo' => function ($q) {
                $q->with(['product', 'batch'])->latest()->limit(20);
            }
        ]);

        // Получаем текущие товары в этом месте
        $currentItems = InventoryMovement::query()
            ->where(function ($q) use ($location) {
                $q->where('to_location_id', $location->id)
                ->orWhere('from_location_id', $location->id);
            })
            ->whereIn('movement_type', ['receipt', 'shipment', 'transfer', 'write_off'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($item) {
                return $item->product_id . '-' . ($item->batch_id ?? '0');
            })
            ->map(function ($group) {
                $lastMovement = $group->first();
                $total = $group->sum('quantity');
                return [
                    'product' => $lastMovement->product,
                    'batch' => $lastMovement->batch,
                    'quantity' => $total,
                    'last_movement' => $lastMovement->created_at
                ];
            })
            ->filter(fn($item) => $item['quantity'] > 0);
        return view('storage_locations.show', compact('location', 'currentItems'));
    }

    /**
     * Изменение места хранения
     */
    public function edit(StorageLocation $location)
    {
        $types = [
            'cell' => 'Ячейка',
            'rack' => 'Стеллаж',
            'zone' => 'Зона',
            'floor' => 'Напольное хранение'
        ];
        return view('storage_locations.edit', compact('location', 'types'));
    }

    /**
     * Сохранить изменения
     */
    public function update(Request $request, StorageLocation $location)
    {
        // Валидация
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:storage_locations,name,' . $location->id,
            'type' => 'required|in:cell,rack,zone,floor',
            'capacity' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        // Обработка чекбокса
        $validated['is_active'] = $request->has('is_active');
        // current_load не обновляем, оно меняется только через движения
        unset($validated['current_load']); 
        $location->update($validated);

        return redirect()
        ->route('locations.index')
        ->with('success', 'Место хранения успешно обновлено');
    }

    /**
     * Удалить место хранения
     */
    public function destroy(StorageLocation $location)
    {
        // Проверка пустое место или нет
        if ($location->current_load > 0) {
            return redirect()
                ->route('locations.index')
                ->with('error', 'Нельзя удалить непустое место. Сначала переместите товары.');
        }
        // Есть ли движения?
        if ($location->movementsFrom()->exists() || $location->movementsTo()->exists()) {
            return redirect()
                ->route('locations.index')
                ->with('error', 'Нельзя удалить место, по которому были движения');
        }
        $location->delete();
        
        return redirect()
            ->route('locations.index')
            ->with('success', 'Место хранения успешно удалено');
    }
}
