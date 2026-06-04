<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\InventoryMovement;
use App\Models\StorageLocation;
use App\models\Batch;

class ReportController extends Controller
{
    // отчет по остаткам
    public function stock(Request $request)
    {
        $products = Product::with(['movements', 'batches' => function($q) {
            $q->with('movements');
        }])->get();
        // массив для данных
        $reportData = [];
        
        foreach ($products as $product) {
            $totalStock = $product->current_stock; // общий остаток
            
            if ($totalStock == 0) {
                continue; // если остаток 0 пропускаем
            }
            // остатки по местам
            $locationMovements = $product->movements()
                ->whereIn('movement_type', ['receipt', 'shipment', 'transfer', 'write_off'])
                ->whereNull('deleted_at')
                ->orderBy('created_at')
                ->get();
            // вычисление этих остатков
            $locationStock = [];
            foreach ($locationMovements as $movement) {
                if ($movement->to_location_id) {
                    $locId = $movement->to_location_id;
                    $locationStock[$locId] = ($locationStock[$locId] ?? 0) + $movement->quantity;
                }
                if ($movement->from_location_id) {
                    $locId = $movement->from_location_id;
                    $locationStock[$locId] = ($locationStock[$locId] ?? 0) + $movement->quantity;
                }
            }
            
            $locationStock = array_filter($locationStock, fn($qty) => $qty > 0); // фильтр для остатков больше 0
            $locations = [];
            // полуцчаем названия мест
            if (!empty($locationStock)) {
                $locationIds = array_keys($locationStock);
                $locationsData = StorageLocation::whereIn('id', $locationIds)->get()->keyBy('id');
                
                foreach ($locationStock as $locId => $qty) {
                    $loc = $locationsData[$locId] ?? null;
                    if ($loc) {
                        $locations[] = [
                            'name' => $loc->name,
                            'type' => $loc->type_name ?? $loc->type,
                            'quantity' => $qty,
                            'capacity' => $loc->capacity,
                            'load_percent' => $loc->capacity ? round(($qty / $loc->capacity) * 100) : null
                        ];
                    }
                }
            }
            // остатки по партиям
            $batches = [];
            foreach ($product->batches as $batch) {
                $batchQuantity = $batch->movements->sum('quantity');
                if ($batchQuantity > 0) {
                    $batches[] = [
                        'id' => $batch->id,
                        'number' => $batch->batch_number,
                        'quantity' => $batchQuantity,
                        'expiration_date' => $batch->expiration_date,
                        'days_left' => $batch->daysUntilExpiration(),
                        'status' => $batch->status,
                    ];
                }
            }
            $reportData[] = [
                'product' => $product,
                'total_stock' => $totalStock,
                'locations' => $locations,
                'batches' => $batches,
            ];
        }
        // сортировка по убыванию остатка
        usort($reportData, fn($a, $b) => $b['total_stock'] <=> $a['total_stock']);
        
        return view('reports.stock', compact('reportData'));
    }

    // отчет по движениям
    public function movements(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        // фильтрация по дате
        $movements = InventoryMovement::with([
                'product',
                'batch',
                'fromLocation',
                'toLocation',
                'user'
            ])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        // итоговый подсчет
        $summary = [
            'receipt' => $movements->where('movement_type', 'receipt')->sum('quantity'),
            'shipment' => abs($movements->where('movement_type', 'shipment')->sum('quantity')),
            'transfer' => $movements->where('movement_type', 'transfer')->count(),
            'write_off' => abs($movements->where('movement_type', 'write_off')->sum('quantity')),
        ];
        
        return view('reports.movements', compact('movements', 'startDate', 'endDate', 'summary'));
    }
    // отчет по товарам у которых скоро истекает срок годности
    public function expiring(Request $request)
    {
        // фильтрация
        $days = $request->get('days', 30); // сколько дней до истечения (по умолчанию 30)
        $onlyExpiring = $request->has('only_expiring'); // показывать только истекающие
    
        $batches = Batch::with(['product', 'movements' => function ($q) {
                $q->whereNull('deleted_at');
            }])
            ->whereNotNull('expiration_date')
            ->get()
            ->filter(function ($batch) use ($days, $onlyExpiring) {
                $daysLeft = $batch->daysUntilExpiration();
                if ($daysLeft === null) return false;
                
                if ($onlyExpiring) {
                    // только истекающие (от 0 до $days дней)
                    return $daysLeft >= 0 && $daysLeft <= $days;
                } else {
                // все партии (включая просрочку)
                return $daysLeft <= $days;
                }
            })
            ->map(function ($batch) {
                // реальное количество в партии
                $quantity = $batch->movements->sum('quantity');
                
                return [
                    'batch' => $batch,
                    'product' => $batch->product,
                    'quantity' => $quantity,
                    'days_left' => (int)$batch->daysUntilExpiration(),
                    'expiration_date' => $batch->expiration_date,
                    'status' => $batch->status,
                ];
            })
            ->filter(fn($item) => $item['quantity'] > 0) // только непустые партии
            ->sortBy('days_left') // сортируем по возрастанию дней
            ->values();
        
        return view('reports.expiring', compact('batches', 'days', 'onlyExpiring'));
    }
    // отчет по истекшим срокам
    public function expired(Request $request)
    {
        // партии с истекшим сроком службы или гарантии
        $batches = Batch::with(['product', 'movements' => function ($q) {
                $q->whereNull('deleted_at');
            }])
            ->whereNotNull('expiration_date')
            ->where('expiration_date', '<', now())
            ->get()
            ->map(function ($batch) {
                //  реальное количество в партии
                $quantity = $batch->movements->sum('quantity');
                
                return [
                    'batch' => $batch,
                    'product' => $batch->product,
                    'quantity' => $quantity,
                    'days_overdue' => abs($batch->daysUntilExpiration()), // сколько дней просрочки
                    'expiration_date' => $batch->expiration_date,
                ];
            })
            ->filter(fn($item) => $item['quantity'] > 0) // только непустые партии
            ->sortByDesc('days_overdue') // сначала самые старые
            ->values();
        // общее количества просроченного товара
        $totalExpired = $batches->sum('quantity');
        
        return view('reports.expired', compact('batches', 'totalExpired'));
    }
}
