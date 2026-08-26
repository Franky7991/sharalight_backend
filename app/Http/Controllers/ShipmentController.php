<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerOrder;
use App\Models\Shipment;
use App\Services\ShipmentService;

class ShipmentController extends Controller
{
    public function index()
    {
        return view('shipment.index');
    }

    public function show(string $id)
    {
        $shipment = Shipment::query()->with('details.customerOrder')->findOrFail($id);

        // Ordini disponibili per l'aggiunta: solo quelli interamente prodotti
        // e non ancora presenti in questa spedizione.
        $alreadyIds = $shipment->details->pluck('customer_order_id');

        $availableOrders = CustomerOrder::query()
            ->with(['user'])
            ->withSum('products', 'qnt_produced')
            ->get()
            ->filter(function ($order) use ($alreadyIds) {
                if (! $order->isFullyProduced()) {
                    return false;
                }
                if ($alreadyIds->contains($order->id)) {
                    return false;
                }
                return true;
            })
            ->values();

        return view('shipment.show', compact('shipment', 'availableOrders'));
    }

    public function listDataTable(Request $request)
    {
        $query = Shipment::query()
            ->withCount('details')
            ->get();

        return datatables($query)
            ->addColumn('date_fmt', fn ($r) => $r->date?->format('d/m/Y') ?? '-')
            ->addColumn('date_ymd', fn ($r) => $r->date?->format('Y-m-d') ?? '')
            ->addColumn('orders_count', fn ($r) => $r->details_count)
            ->addColumn('state_label', function ($r) {
                return '<span class="badge badge-secondary">' . $r->stateLabel() . '</span>';
            })
            ->rawColumns(['state_label'])
            ->toJson();
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
        ]);

        Shipment::query()->create([
            'progressive' => Shipment::generateProgressive(),
            'date'        => $request->date,
            'state'       => Shipment::STATE_CREATED,
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, string $id)
    {
        $shipment = Shipment::query()->findOrFail($id);

        if (! $shipment->isCreated()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossibile modificare la spedizione: non è nello stato "Creato".',
            ], 403);
        }

        $request->validate([
            'date' => ['required', 'date'],
        ]);

        $shipment->update(['date' => $request->date]);

        return response()->json(['success' => true]);
    }

    public function destroy(string $id)
    {
        $shipment = Shipment::query()->findOrFail($id);
        $shipment->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Cambia lo stato della spedizione.
     * La transizione consentita è solo "created" → "shipped" (Spedito),
     * che genera i movimenti di scarico e porta gli ordini collegati a "Spedito".
     *
     * PUT /shipments/{shipment}/state
     */
    public function changeState(Request $request, string $id)
    {
        $shipment = Shipment::query()->findOrFail($id);

        $request->validate([
            'state' => ['required', 'string', 'in:' . implode(',', array_keys(Shipment::STATES))],
        ]);

        $newState = $request->input('state');

        // Passaggio da "created" a "shipped"
        if ($shipment->isCreated() && $newState === Shipment::STATE_SHIPPED) {
            try {
                app(ShipmentService::class)->markAsShipped($shipment);
            } catch (\RuntimeException $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            $shipment->refresh();

            return response()->json([
                'success'     => true,
                'state'       => $shipment->state,
                'state_label' => $shipment->stateLabel(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Transizione di stato non valida.',
        ], 422);
    }

    public function delete(Request $request)
    {
        $deleted = 0;

        foreach (($request->ids ?? []) as $id) {
            $shipment = Shipment::find($id);
            if (! $shipment) {
                continue;
            }
            $shipment->delete();
            $deleted++;
        }

        return response()->json(['success' => true, 'deleted' => $deleted]);
    }
}