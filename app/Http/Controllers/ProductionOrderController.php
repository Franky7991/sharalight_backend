<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderHasProduct;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderDetail;
use App\Models\Warehouse;
use App\Services\ProductionService;

class ProductionOrderController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::query()->orderBy('name')->get();

        return view('production_order.index', compact('warehouses'));
    }

    public function show(string $id)
    {
        $productionOrder = ProductionOrder::query()
            ->with([
                'warehouse',
                'details.customerOrderHasProduct.product',
                'details.customerOrderHasProduct.unitOfMeasure',
                'details.customerOrderHasProduct.customerOrder',
                'records.product',
                'records.unitOfMeasure',
            ])
            ->findOrFail($id);

        // Riga di ordine cliente selezionabili: solo in ordini cliente "products_allocated"
        // e non ancora utilizzate in un ordine di produzione.
        $available = CustomerOrderHasProduct::query()
            ->whereHas('customerOrder', fn ($q) => $q->where('state', CustomerOrder::STATE_PRODUCTS_ALLOCATED))
            ->whereDoesntHave('productionOrderDetails')
            ->with(['product', 'unitOfMeasure', 'customerOrder'])
            ->orderBy('id')
            ->get();

        $plan = app(ProductionService::class)->plan($productionOrder);

        return view('production_order.show', compact('productionOrder', 'available', 'plan'));
    }

    public function listDataTable(Request $request)
    {
        $query = ProductionOrder::query()->with('warehouse')->get();

        return datatables($query)
            ->addColumn('warehouse_name', fn ($r) => $r->warehouse?->name ?? '-')
            ->addColumn('production_date_fmt', fn ($r) => $r->production_date?->format('d/m/Y') ?? '-')
            ->addColumn('production_date_ymd', fn ($r) => $r->production_date?->format('Y-m-d') ?? '')
            ->addColumn('state_label', function ($r) {
                return '<span class="badge badge-secondary">' . $r->stateLabel() . '</span>';
            })
            ->rawColumns(['state_label'])
            ->toJson();
    }

    public function store(Request $request)
    {
        $request->validate([
            'production_date' => ['required', 'date'],
            'warehouse_id'    => ['required', 'exists:warehouses,id'],
        ]);

        ProductionOrder::query()->create([
            'progressive'     => ProductionOrder::generateProgressive(),
            'production_date' => $request->production_date,
            'warehouse_id'    => $request->warehouse_id,
            'state'           => ProductionOrder::STATE_CREATED,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Modifica la testata dell'ordine di produzione.
     * Consentito solo nello stato "created".
     *
     * PUT /production-orders/{order}
     */
    public function update(Request $request, string $id)
    {
        $order = ProductionOrder::query()->findOrFail($id);

        if (! $order->isCreated()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossibile modificare la testata: l\'ordine di produzione non è nello stato "Creato".',
            ], 403);
        }

        $request->validate([
            'production_date' => ['required', 'date'],
            'warehouse_id'    => ['required', 'exists:warehouses,id'],
        ]);

        $order->update([
            'production_date' => $request->production_date,
            'warehouse_id'    => $request->warehouse_id,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Cambia lo stato dell'ordine di produzione.
     *
     * PUT /production-orders/{order}/state
     * body: { state: "in_processing" }
     */
    public function changeState(Request $request, string $id)
    {
        $order = ProductionOrder::query()->findOrFail($id);

        $request->validate([
            'state' => ['required', 'string', 'in:' . implode(',', array_keys(ProductionOrder::STATES))],
        ]);

        $newState = $request->input('state');

        // Passaggio da "created" a "in_processing"
        if ($order->state === ProductionOrder::STATE_CREATED
            && $newState === ProductionOrder::STATE_IN_PROCESSING) {
            $order->update(['state' => $newState]);
            return response()->json([
                'success'     => true,
                'state'       => $order->state,
                'state_label' => $order->stateLabel(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Transizione di stato non valida.',
        ], 422);
    }

    /**
     * Registra la produzione di una quantità per una specifica riga:
     * genera i movimenti di scarico materie prime e carico prodotto finito,
     * aggiorna qnt_produced e (se tutte le righe sono prodotte) completa l'ordine.
     *
     * POST /production-orders/{order}/produce
     */
    public function produce(Request $request, string $id)
    {
        $order = ProductionOrder::query()->findOrFail($id);

        if (! $order->isInProcessing()) {
            return response()->json([
                'success' => false,
                'message' => 'La produzione è consentita solo per ordini nello stato "In Lavorazione".',
            ], 403);
        }

        $request->validate([
            'production_order_detail_id' => ['required', 'exists:production_order_details,id'],
            'qnt'                        => ['required', 'regex:/^\d{1,15}([.,]\d{1,2})?$/'],
        ]);

        $detail = ProductionOrderDetail::query()
            ->where('production_order_id', $order->id)
            ->findOrFail($request->production_order_detail_id);

        $qnt = (float) str_replace(',', '.', $request->qnt);

        try {
            app(ProductionService::class)->produce($order, $detail, $qnt);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Restituisce il fabbisogno di materie prime (con giacenza) per una quantità.
     *
     * GET /production-orders/{order}/products/{detail}/requirements
     */
    public function requirements(Request $request, string $orderId, string $detailId)
    {
        $order = ProductionOrder::query()->findOrFail($orderId);

        $detail = ProductionOrderDetail::query()
            ->where('production_order_id', $orderId)
            ->findOrFail($detailId);

        $qnt = (float) str_replace(',', '.', (string) $request->query('qnt', 0));

        return response()->json(
            app(ProductionService::class)->requirements($order, $detail, $qnt)
        );
    }

    public function destroy(string $id)
    {
        $productionOrder = ProductionOrder::query()->findOrFail($id);
        $productionOrder->delete();

        return response()->json(['success' => true]);
    }

    public function delete(Request $request)
    {
        $deleted = 0;

        foreach (($request->ids ?? []) as $id) {
            $order = ProductionOrder::find($id);
            if (! $order) {
                continue;
            }
            $order->delete();
            $deleted++;
        }

        return response()->json(['success' => true, 'deleted' => $deleted]);
    }
}