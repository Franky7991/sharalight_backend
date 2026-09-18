<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderHasProduct;
use App\Models\CustomerOrderHasProductDetail;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\UnitConversion;
use App\Models\UnitOfMeasure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Elenco degli ordini cliente dell'utente autenticato.
     *
     * GET /api/orders
     */
    public function index(Request $request)
    {
        $orders = CustomerOrder::query()
            ->where('user_id', $request->user()->id)
            ->with('user')
            ->withCount('products')
            ->withSum('products', 'qnt')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(function ($order) {
                return [
                    'id'             => $order->id,
                    'progressive'    => $order->progressive,
                    'address'        => $order->address,
                    'order_date'     => $order->order_date?->toDateString(),
                    'order_date_fmt' => $order->order_date?->format('d/m/Y'),
                    'state'          => $order->state,
                    'state_label'    => $order->stateLabel(),
                    'qnt'            => (float) ($order->products_sum_qnt ?? 0),
                    'products_count' => (int) $order->products_count,
                    'price'          => (float) ($order->price ?? 0),
                    'user_name'      => $order->user?->name ?? '-',
                ];
            });

        return response()->json(['orders' => $orders]);
    }

    /**
     * Catalogo necessario per comporre un ordine localmente nella webapp:
     * prodotti ordinarili (con categoria e U.M.), ricette, unità di misura
     * e conversioni.
     *
     * GET /api/orders/catalog
     */
    public function catalog()
    {
        $products = Product::query()
            ->with(['productCategory.unitOfMeasure'])
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'id'                     => $p->id,
                'name'                   => $p->name,
                'type'                   => $p->type,
                'type_label'             => $p->typeLabel(),
                'category_id'            => $p->product_category_id,
                'category_name'          => $p->productCategory?->name ?? '',
                'product_category_id'    => $p->product_category_id,
                'unit_of_measure_id'     => $p->productCategory?->unit_of_measure_id,
                'unit_of_measure_symbol' => $p->productCategory?->unitOfMeasure?->symbol ?? '',
                'price'                  => $p->price !== null ? (float) $p->price : null,
            ]);

        $unitOfMeasures = UnitOfMeasure::query()->orderBy('name')->get()
            ->map(fn ($u) => [
                'id'     => $u->id,
                'name'   => $u->name,
                'symbol' => $u->symbol,
            ]);

        $unitConversions = UnitConversion::all()
            ->map(fn ($c) => [
                'from_unit_of_measure_id' => $c->from_unit_of_measure_id,
                'to_unit_of_measure_id'   => $c->to_unit_of_measure_id,
                'from_quantity'           => (float) $c->from_quantity,
                'to_quantity'             => (float) $c->to_quantity,
            ]);

        $recipes = Recipe::query()
            ->with(['productCategory.unitOfMeasure', 'unitOfMeasure', 'details'])
            ->get()
            ->map(fn ($r) => [
                'id'                     => $r->id,
                'product_id'             => $r->product_id,
                'product_category_id'    => $r->product_category_id,
                'quantity'               => (float) $r->quantity,
                'unit_of_measure_id'     => $r->unit_of_measure_id,
                'unit_of_measure_symbol' => $r->unitOfMeasure?->symbol ?? '',
                'category_name'          => $r->productCategory?->name ?? '',
                'category_uom_id'        => $r->productCategory?->unit_of_measure_id,
                'category_uom_symbol'    => $r->productCategory?->unitOfMeasure?->symbol ?? '',
                'detail_product_ids'     => $r->details->pluck('product_id')->map(fn ($id) => (int) $id)->values()->all(),
            ]);

        return response()->json([
            'products'         => $products,
            'unit_of_measures' => $unitOfMeasures,
            'unit_conversions' => $unitConversions,
            'recipes'          => $recipes,
        ]);
    }

    /**
     * Crea un ordine cliente completo (dati consegna + prodotti + ingredienti)
     * in un'unica richiesta JSON. L'ordine viene creato già nello stato
     * "Prodotti Definiti" (STATE_PRODUCTS_DEFINED).
     *
     * POST /api/orders
     * body: {
     *   address: string,
     *   lat: number (-90..90, opzionale),
     *   lng: number (-180..180, opzionale),
     *   order_date: YYYY-MM-DD,
     *   products: [
     *     {
     *       product_id: int,
     *       qnt: number|string,
     *       unit_of_measure_id: int,
     *       details: [
     *         { recipe_id, product_id, original_qnt, original_unit_of_measure_id,
     *           conversion_qnt, conversion_unit_of_measure_id }
     *       ]
     *     }
     *   ]
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'address'    => ['required', 'string', 'max:500'],
            'lat'        => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng'],
            'lng'        => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat'],
            'order_date' => ['required', 'date', 'after:today'],
            'products'   => ['required', 'array', 'min:1'],

            'products.*.product_id'         => ['required', 'exists:products,id'],
            'products.*.qnt'                => ['required', 'regex:/^\d{1,15}([.,]\d{1,2})?$/'],
            'products.*.unit_of_measure_id' => ['required', 'exists:unit_of_measures,id'],
            'products.*.price'              => ['nullable', 'numeric', 'min:0'],

            'products.*.details'                          => ['nullable', 'array'],
            'products.*.details.*.recipe_id'              => ['required', 'exists:recipes,id'],
            'products.*.details.*.product_id'             => ['required', 'exists:products,id'],
            'products.*.details.*.original_qnt'           => ['nullable', 'numeric'],
            'products.*.details.*.original_unit_of_measure_id' => ['nullable', 'exists:unit_of_measures,id'],
            'products.*.details.*.conversion_qnt'         => ['nullable', 'numeric'],
            'products.*.details.*.conversion_unit_of_measure_id' => ['nullable', 'exists:unit_of_measures,id'],
            'products.*.details.*.price'                  => ['nullable', 'numeric', 'min:0'],
        ], [
            'address.required'         => 'L\'indirizzo di consegna è obbligatorio.',
            'address.max'              => 'L\'indirizzo non può superare :max caratteri.',
            'lat.between'              => 'Latitudine non valida.',
            'lng.between'              => 'Longitudine non valida.',
            'lat.required_with'        => 'Coordinate indirizzo incomplete.',
            'lng.required_with'        => 'Coordinate indirizzo incomplete.',
            'order_date.required'      => 'La data ordine è obbligatoria.',
            'order_date.after'         => 'La data ordine deve essere successiva a oggi.',
            'products.required'        => 'Aggiungi almeno un prodotto all\'ordine.',
            'products.min'             => 'Aggiungi almeno un prodotto all\'ordine.',
            'products.*.product_id.required' => 'Seleziona un prodotto.',
            'products.*.qnt.required'  => 'Indica la quantità del prodotto.',
            'products.*.qnt.regex'     => 'Quantità prodotto non valida.',
            'products.*.unit_of_measure_id.required' => 'Unità di misura prodotto mancante.',
            'products.*.price.numeric' => 'Prezzo prodotto non valido.',
            'products.*.price.min'     => 'Il prezzo del prodotto non può essere negativo.',
        ]);

        $order = DB::transaction(function () use ($request) {
            $totalQnt = 0;

            $order = CustomerOrder::query()->create([
                'progressive' => CustomerOrder::generateProgressive(),
                'address'     => $request->address,
                'lat'         => $request->filled('lat') ? $request->lat : null,
                'lng'         => $request->filled('lng') ? $request->lng : null,
                'user_id'     => $request->user()->id,
                'order_date'  => $request->order_date,
                'state'       => CustomerOrder::STATE_PRODUCTS_DEFINED,
                'qnt'         => 0,
            ]);

            foreach ($request->products as $productData) {
                $qnt = (float) str_replace(',', '.', $productData['qnt']);
                $totalQnt += $qnt;

                $product = Product::query()->find($productData['product_id']);

                // Prezzo "candela": quello inviato dal client oppure, in
                // assenza, il prezzo di listino attuale del prodotto.
                $basePrice = $productData['price'] ?? ($product?->price ?? null);

                // Prezzo degli ingredienti scelti: si sommano solo le materie
                // prime (prodotti senza ricetta), così semi-lavorati e loro
                // componenti non vengono conteggiati due volte.
                $ingredientPrice = 0;
                $detailRows = [];

                foreach ($productData['details'] ?? [] as $selection) {
                    $detailProduct = Product::query()->find($selection['product_id']);
                    $detailPrice   = $selection['price'] ?? ($detailProduct?->price ?? null);

                    if ($detailProduct
                        && $detailProduct->type === Product::TYPE_RAW_MATERIAL
                        && $detailPrice !== null) {
                        $ingredientPrice += (float) $detailPrice;
                    }

                    $detailRows[] = [
                        'recipe_id'                     => $selection['recipe_id'],
                        'product_id'                    => $selection['product_id'],
                        'original_qnt'                  => $selection['original_qnt'] ?? null,
                        'original_unit_of_measure_id'   => $selection['original_unit_of_measure_id'] ?? null,
                        'conversion_qnt'                => $selection['conversion_qnt'] ?? null,
                        'conversion_unit_of_measure_id' => $selection['conversion_unit_of_measure_id'] ?? null,
                        'price'                         => $detailPrice,
                    ];
                }

                // Prezzo unitario riga = prezzo candela + ingredienti scelti
                $unitPrice = ($basePrice === null && $ingredientPrice <= 0)
                    ? null
                    : (float) $basePrice + $ingredientPrice;

                $orderProduct = CustomerOrderHasProduct::query()->create([
                    'customer_order_id'    => $order->id,
                    'product_id'           => $productData['product_id'],
                    'qnt'                  => $qnt,
                    'qnt_produced'         => 0,
                    'unit_of_measure_id'   => $productData['unit_of_measure_id'],
                    'warehouses_allocated' => false,
                    'price'                => $unitPrice,
                ]);

                foreach ($detailRows as $detailRow) {
                    CustomerOrderHasProductDetail::query()->create(
                        $detailRow + ['customer_order_has_product_id' => $orderProduct->id]
                    );
                }
            }

            // Totale ordine = somma di (prezzo unitario riga × quantità)
            $orderPrice = (float) CustomerOrderHasProduct::query()
                ->where('customer_order_id', $order->id)
                ->sum(DB::raw('qnt * price'));

            $order->update(['qnt' => $totalQnt, 'price' => $orderPrice]);

            return $order;
        });

        return response()->json([
            'success' => true,
            'message' => 'Ordine creato con successo.',
            'order'   => [
                'id'          => $order->id,
                'progressive' => $order->progressive,
                'state'       => $order->state,
                'state_label' => $order->stateLabel(),
                'price'       => (float) $order->price,
            ],
        ], 201);
    }
}