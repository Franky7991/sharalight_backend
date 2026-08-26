<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderSummary;
use App\Models\ProductionOrder;
use App\Models\ProductionRecord;
use App\Models\Shipment;
use App\Models\Stock;
use App\Models\Movement;
use App\Models\Product;
use App\Models\Causal;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // ── AVVISI / CHECKLIST ────────────────────────────────────────────

        // 1. Ordini "Creato" con data ordine nei prossimi 7 giorni (da lavorare presto)
        $ordersUrgent = CustomerOrder::where('state', CustomerOrder::STATE_CREATED)
            ->whereBetween('order_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->with('user')->orderBy('order_date')->get();

        // 2. Ordini "Creato" senza nessun prodotto definito
        $ordersNoProducts = CustomerOrder::where('state', CustomerOrder::STATE_CREATED)
            ->whereDoesntHave('products')
            ->with('user')->orderBy('created_at')->get();

        // 3. Ordini "Prodotti Definiti" con prodotti privi di ingredienti configurati
        //    (CustomerOrderHasProduct senza CustomerOrderHasProductDetail)
        $ordersProductsNoIngredients = CustomerOrder::where('state', CustomerOrder::STATE_PRODUCTS_DEFINED)
            ->whereHas('products', fn($q) => $q->whereDoesntHave('details'))
            ->with(['user', 'products' => fn($q) => $q->whereDoesntHave('details')->with('product')])
            ->get();

        // 4. Ordini "Prodotti Allocati" completamente prodotti ma non ancora
        //    inseriti in nessuna spedizione
        $ordersReadyToShip = CustomerOrder::where('state', CustomerOrder::STATE_PRODUCTS_ALLOCATED)
            ->whereDoesntHave('shipmentDetails')
            ->withSum('products', 'qnt_produced')
            ->with('user')
            ->get()
            ->filter(fn($o) => $o->isFullyProduced());

        // 5. Ordini di produzione "Creato" da più di 2 giorni (dimenticati in bozza)
        $prodOrdersStale = ProductionOrder::where('state', ProductionOrder::STATE_CREATED)
            ->where('created_at', '<', now()->subDays(2))
            ->with('warehouse')->orderBy('created_at')->get();

        // 6. Spedizioni "Creato" con data spedizione passata (non confermate)
        $shipmentsOverdue = Shipment::where('state', Shipment::STATE_CREATED)
            ->where('date', '<', now()->toDateString())
            ->orderBy('date')->get();

        // 7. Spedizioni "Creato" senza nessun ordine aggiunto
        $shipmentsEmpty = Shipment::where('state', Shipment::STATE_CREATED)
            ->whereDoesntHave('details')
            ->orderBy('created_at')->get();

        // 8. Prodotti (finiti/semi-lavorati) senza nessuna ricetta definita
        $productsNoRecipe = Product::whereIn('type', Product::TYPES_WITH_RECIPE)
            ->whereDoesntHave('recipes')
            ->orderBy('name')->get();

        // 9. Impostazioni mancanti: causale di scarico spedizione
        $missingSetting = \App\Models\Setting::get(\App\Models\Setting::KEY_SHIPMENT_UNLOAD_CAUSAL) === null;

        // Costruisci array avvisi da passare alla view
        $alerts = collect([
            [
                'level'   => 'danger',
                'icon'    => 'fas fa-clock',
                'title'   => 'Ordini in scadenza (entro 7 gg) ancora in stato "Creato"',
                'items'   => $ordersUrgent->map(fn($o) => [
                    'label' => $o->progressive . ' — ' . ($o->user?->name ?? '-') . ' — ' . $o->order_date?->format('d/m/Y'),
                    'url'   => '/customer-orders/' . $o->id,
                ]),
            ],
            [
                'level'   => 'warning',
                'icon'    => 'fas fa-box-open',
                'title'   => 'Ordini "Creato" senza prodotti definiti',
                'items'   => $ordersNoProducts->map(fn($o) => [
                    'label' => $o->progressive . ' — ' . ($o->user?->name ?? '-'),
                    'url'   => '/customer-orders/' . $o->id,
                ]),
            ],
            [
                'level'   => 'warning',
                'icon'    => 'fas fa-puzzle-piece',
                'title'   => 'Ordini con prodotti privi di ingredienti configurati',
                'items'   => $ordersProductsNoIngredients->map(fn($o) => [
                    'label' => $o->progressive . ' — prodotti: ' . $o->products->map(fn($p) => $p->product?->name ?? '?')->implode(', '),
                    'url'   => '/customer-orders/' . $o->id,
                ]),
            ],
            [
                'level'   => 'info',
                'icon'    => 'fas fa-shipping-fast',
                'title'   => 'Ordini completamente prodotti pronti per la spedizione',
                'items'   => $ordersReadyToShip->map(fn($o) => [
                    'label' => $o->progressive . ' — ' . ($o->user?->name ?? '-'),
                    'url'   => '/customer-orders/' . $o->id,
                ]),
            ],
            [
                'level'   => 'secondary',
                'icon'    => 'fas fa-pause-circle',
                'title'   => 'Ordini di produzione in bozza da più di 2 giorni',
                'items'   => $prodOrdersStale->map(fn($p) => [
                    'label' => $p->progressive . ' — ' . ($p->warehouse?->name ?? '-') . ' — creato il ' . $p->created_at->format('d/m/Y'),
                    'url'   => '/production-orders/' . $p->id,
                ]),
            ],
            [
                'level'   => 'danger',
                'icon'    => 'fas fa-truck',
                'title'   => 'Spedizioni con data passata non ancora confermate',
                'items'   => $shipmentsOverdue->map(fn($s) => [
                    'label' => $s->progressive . ' — ' . $s->date?->format('d/m/Y'),
                    'url'   => '/shipments/' . $s->id,
                ]),
            ],
            [
                'level'   => 'warning',
                'icon'    => 'fas fa-folder-open',
                'title'   => 'Spedizioni aperte senza ordini aggiunti',
                'items'   => $shipmentsEmpty->map(fn($s) => [
                    'label' => $s->progressive . ' — ' . $s->date?->format('d/m/Y'),
                    'url'   => '/shipments/' . $s->id,
                ]),
            ],
            [
                'level'   => 'secondary',
                'icon'    => 'fas fa-book',
                'title'   => 'Prodotti finiti/semi-lavorati senza ricetta',
                'items'   => $productsNoRecipe->map(fn($p) => [
                    'label' => $p->name . ' (' . $p->typeLabel() . ')',
                    'url'   => route('products.show', $p->id),
                ]),
            ],
            [
                'level'   => 'danger',
                'icon'    => 'fas fa-cog',
                'title'   => 'Impostazioni mancanti',
                'items'   => $missingSetting ? collect([['label' => 'Causale scarico spedizione non configurata', 'url' => route('settings.index')]]) : collect(),
            ],
        ])->filter(fn($a) => $a['items']->isNotEmpty())->values();
        $ordersCreated   = CustomerOrder::where('state', CustomerOrder::STATE_CREATED)->count();
        $ordersDefined   = CustomerOrder::where('state', CustomerOrder::STATE_PRODUCTS_DEFINED)->count();
        $ordersAllocated = CustomerOrder::where('state', CustomerOrder::STATE_PRODUCTS_ALLOCATED)->count();
        $ordersShipped   = CustomerOrder::where('state', CustomerOrder::STATE_SHIPPED)->count();
        $ordersTotal     = $ordersCreated + $ordersDefined + $ordersAllocated + $ordersShipped;

        // ── KPI produzione ────────────────────────────────────────────────
        $prodTotal     = ProductionOrder::count();
        $prodCreated   = ProductionOrder::where('state', ProductionOrder::STATE_CREATED)->count();
        $prodInProcess = ProductionOrder::where('state', ProductionOrder::STATE_IN_PROCESSING)->count();
        $prodCompleted = ProductionOrder::where('state', ProductionOrder::STATE_COMPLETED)->count();

        // ── KPI spedizioni ────────────────────────────────────────────────
        $shipTotal   = Shipment::count();
        $shipCreated = Shipment::where('state', Shipment::STATE_CREATED)->count();
        $shipShipped = Shipment::where('state', Shipment::STATE_SHIPPED)->count();

        // ── Stock negativi / top 10 ───────────────────────────────────────
        $negativeStocks = Stock::where('qnt', '<', 0)
            ->with(['product', 'warehouse', 'unitOfMeasure'])
            ->get();

        $topStocks = Stock::where('qnt', '>', 0)
            ->with(['product', 'unitOfMeasure'])
            ->orderByDesc('qnt')
            ->limit(10)
            ->get();

        // ── Produzioni completate: questo mese vs mese scorso ─────────────
        $prodThisMonth = ProductionOrder::where('state', ProductionOrder::STATE_COMPLETED)
            ->whereYear('updated_at', now()->year)
            ->whereMonth('updated_at', now()->month)
            ->count();

        $prodLastMonth = ProductionOrder::where('state', ProductionOrder::STATE_COMPLETED)
            ->whereYear('updated_at', now()->subMonth()->year)
            ->whereMonth('updated_at', now()->subMonth()->month)
            ->count();

        // ── Movimenti ultimi 30 giorni (carichi vs scarichi per giorno) ───
        $causalIds = Causal::pluck('type', 'id');

        $movLast30 = Movement::query()
            ->select(
                DB::raw('DATE(created_at) as day'),
                'causal_id',
                DB::raw('SUM(qnt) as total_qnt')
            )
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('day', 'causal_id')
            ->orderBy('day')
            ->get();

        $days = collect();
        for ($i = 29; $i >= 0; $i--) {
            $days->push(now()->subDays($i)->format('Y-m-d'));
        }

        $loadsByDay   = $days->mapWithKeys(fn($d) => [$d => 0.0]);
        $unloadsByDay = $days->mapWithKeys(fn($d) => [$d => 0.0]);

        foreach ($movLast30 as $m) {
            $type = $causalIds[$m->causal_id] ?? null;
            if ($type === Causal::TYPE_LOAD)   $loadsByDay[$m->day]   = (float) $m->total_qnt;
            if ($type === Causal::TYPE_UNLOAD) $unloadsByDay[$m->day] = (float) $m->total_qnt;
        }

        // ── Ordini cliente per mese (ultimi 12 mesi) ─────────────────────
        $orderMonths = collect();
        for ($i = 11; $i >= 0; $i--) {
            $orderMonths->push(now()->subMonths($i)->format('Y-m'));
        }

        $ordersByMonth = CustomerOrder::query()
            ->select(DB::raw("DATE_FORMAT(created_at,'%Y-%m') as month"), DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->pluck('total', 'month');

        $ordersPerMonth = $orderMonths->map(fn($m) => (int) ($ordersByMonth[$m] ?? 0));
        $orderMonthLabels = $orderMonths->map(function ($m) {
            [$y, $mo] = explode('-', $m);
            $names = ['Gen','Feb','Mar','Apr','Mag','Giu','Lug','Ago','Set','Ott','Nov','Dic'];
            return $names[(int)$mo - 1] . ' ' . $y;
        });

        // ── Spedizioni per mese (ultimi 6 mesi) ───────────────────────────
        $shipMonths = collect();
        for ($i = 5; $i >= 0; $i--) {
            $shipMonths->push(now()->subMonths($i)->format('Y-m'));
        }

        $shipmentsByMonthRaw = Shipment::query()
            ->select(DB::raw("DATE_FORMAT(date,'%Y-%m') as month"), DB::raw('COUNT(*) as total'))
            ->where('date', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->pluck('total', 'month');

        $shipmentsPerMonth = $shipMonths->map(fn($m) => (int) ($shipmentsByMonthRaw[$m] ?? 0));
        $shipMonthLabels   = $shipMonths->map(function ($m) {
            [$y, $mo] = explode('-', $m);
            $names = ['Gen','Feb','Mar','Apr','Mag','Giu','Lug','Ago','Set','Ott','Nov','Dic'];
            return $names[(int)$mo - 1] . ' ' . $y;
        });

        // ── Top 5 prodotti più prodotti (da ProductionRecord) ─────────────
        $topProduced = ProductionRecord::query()
            ->select('product_id', DB::raw('SUM(qnt) as total_produced'))
            ->groupBy('product_id')
            ->orderByDesc('total_produced')
            ->limit(5)
            ->with('product')
            ->get();

        // ── Ordini cliente per stato (donut) ──────────────────────────────
        $ordersByState = [
            CustomerOrder::STATES[CustomerOrder::STATE_CREATED]            => $ordersCreated,
            CustomerOrder::STATES[CustomerOrder::STATE_PRODUCTS_DEFINED]   => $ordersDefined,
            CustomerOrder::STATES[CustomerOrder::STATE_PRODUCTS_ALLOCATED] => $ordersAllocated,
            CustomerOrder::STATES[CustomerOrder::STATE_SHIPPED]            => $ordersShipped,
        ];

        // ── Produzioni in lavorazione con avanzamento ─────────────────────
        $inProcessOrders = ProductionOrder::where('state', ProductionOrder::STATE_IN_PROCESSING)
            ->with(['details.customerOrderHasProduct', 'warehouse'])
            ->get()
            ->map(function ($po) {
                $total    = $po->details->sum(fn($d) => (float) ($d->customerOrderHasProduct?->qnt ?? 0));
                $produced = $po->details->sum(fn($d) => (float) ($d->customerOrderHasProduct?->qnt_produced ?? 0));
                $pct      = $total > 0 ? min(100, round($produced / $total * 100)) : 0;
                return [
                    'id'          => $po->id,
                    'progressive' => $po->progressive,
                    'warehouse'   => $po->warehouse?->name ?? '-',
                    'date'        => $po->production_date?->format('d/m/Y') ?? '-',
                    'pct'         => $pct,
                    'produced'    => $produced,
                    'total'       => $total,
                ];
            });

        // ── Ordini cliente in ritardo ─────────────────────────────────────
        $lateOrders = CustomerOrder::query()
            ->where('order_date', '<', now()->toDateString())
            ->whereNotIn('state', [CustomerOrder::STATE_SHIPPED])
            ->with('user')
            ->orderBy('order_date')
            ->get();

        // ── Fabbisogno materie prime aggregato (ordini aperti) ────────────
        // Somma i CustomerOrderSummary degli ordini non ancora spediti
        $openOrderIds = CustomerOrder::whereNotIn('state', [CustomerOrder::STATE_SHIPPED])
            ->pluck('id');

        $rawMaterialNeeds = CustomerOrderSummary::query()
            ->whereIn('customer_order_id', $openOrderIds)
            ->select('product_id', 'unit_of_measure_id', DB::raw('SUM(total_qnt) as total'))
            ->groupBy('product_id', 'unit_of_measure_id')
            ->with(['product', 'unitOfMeasure'])
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // ── Stock per magazzino (bar chart) ───────────────────────────────
        $warehouses = Warehouse::orderBy('name')->get();

        $stockByWarehouse = $warehouses->map(function ($wh) {
            $total = Stock::where('warehouse_id', $wh->id)->where('qnt', '>', 0)->sum('qnt');
            return ['name' => $wh->name, 'total' => (float) $total];
        });

        // ── Prodotti per tipo (donut) ─────────────────────────────────────
        $productsByType = [
            Product::TYPES[Product::TYPE_RAW_MATERIAL]  => Product::where('type', Product::TYPE_RAW_MATERIAL)->count(),
            Product::TYPES[Product::TYPE_SEMI_FINISHED] => Product::where('type', Product::TYPE_SEMI_FINISHED)->count(),
            Product::TYPES[Product::TYPE_FINISHED]      => Product::where('type', Product::TYPE_FINISHED)->count(),
        ];

        return view('home', compact(
            'alerts',
            'ordersTotal', 'ordersCreated', 'ordersDefined', 'ordersAllocated', 'ordersShipped',
            'prodTotal', 'prodCreated', 'prodInProcess', 'prodCompleted',
            'prodThisMonth', 'prodLastMonth',
            'shipTotal', 'shipCreated', 'shipShipped',
            'negativeStocks', 'topStocks',
            'days', 'loadsByDay', 'unloadsByDay',
            'ordersByState',
            'ordersPerMonth', 'orderMonthLabels',
            'shipmentsPerMonth', 'shipMonthLabels',
            'topProduced',
            'inProcessOrders',
            'lateOrders',
            'rawMaterialNeeds',
            'stockByWarehouse',
            'productsByType'
        ));
    }
}
