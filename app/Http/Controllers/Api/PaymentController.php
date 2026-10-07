<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;

class PaymentController extends Controller
{
    private PayPalHttpClient $client;

    public function __construct()
    {
        $clientId = config('services.paypal.client_id');
        $clientSecret = config('services.paypal.secret');

        if (config('services.paypal.mode') === 'sandbox') {
            $environment = new SandboxEnvironment($clientId, $clientSecret);
        } else {
            $environment = new ProductionEnvironment($clientId, $clientSecret);
        }

        $this->client = new PayPalHttpClient($environment);
    }

    /**
     * Crea un ordine PayPal per un ordine cliente esistente.
     *
     * POST /api/payments/create
     * body: { order_id: int }
     */
    public function create(Request $request)
    {
        $request->validate([
            'order_id' => ['required', 'exists:customer_orders,id'],
        ]);

        $order = CustomerOrder::query()
            ->where('id', $request->order_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Verifica che non esista già un pagamento completato per questo ordine
        $existingPayment = Payment::query()
            ->where('customer_order_id', $order->id)
            ->where('status', 'completed')
            ->first();

        if ($existingPayment) {
            return response()->json([
                'success' => false,
                'message' => 'Questo ordine è già stato pagato.',
            ], 400);
        }

        $amount = (float) $order->price;

        if ($amount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'L\'importo dell\'ordine non è valido.',
            ], 400);
        }

        $paypalOrderRequest = new OrdersCreateRequest();
        $paypalOrderRequest->prefer('return=representation');
        $paypalOrderRequest->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => (string) $order->id,
                'description' => "Ordine #{$order->progressive} - Shara Light Candle",
                'amount' => [
                    'currency_code' => 'EUR',
                    'value' => number_format($amount, 2, '.', ''),
                ],
            ]],
            'application_context' => [
                'cancel_url' => 'http://localhost:3000/payment-cancel.html',
                'return_url' => 'http://localhost:3000/payment-success.html',
                'brand_name' => 'Shara Light Candle',
                'user_action' => 'PAY_NOW',
            ],
        ];

        try {
            $response = $this->client->execute($paypalOrderRequest);

            // Crea il record payment in stato pending
            $payment = Payment::query()->create([
                'customer_order_id' => $order->id,
                'user_id' => $request->user()->id,
                'amount' => $amount,
                'currency' => 'EUR',
                'status' => 'pending',
                'paypal_order_id' => $response->result->id,
                'payment_method' => 'paypal',
                'raw_response' => json_decode(json_encode($response->result), true),
            ]);

            // Trova l'approval URL
            $approvalUrl = null;
            foreach ($response->result->links as $link) {
                if ($link->rel === 'approve') {
                    $approvalUrl = $link->href;
                    break;
                }
            }

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'paypal_order_id' => $response->result->id,
                'approval_url' => $approvalUrl,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella creazione dell\'ordine PayPal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cattura il pagamento PayPal dopo l'approvazione dell'utente.
     *
     * POST /api/payments/capture
     * body: { paypal_order_id: string }
     */
    public function capture(Request $request)
    {
        $request->validate([
            'paypal_order_id' => ['required', 'string'],
        ]);

        $payment = Payment::query()
            ->where('paypal_order_id', $request->paypal_order_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($payment->status === 'completed') {
            return response()->json([
                'success' => true,
                'message' => 'Pagamento già completato.',
                'payment' => $payment->load('customerOrder'),
            ]);
        }

        $captureRequest = new OrdersCaptureRequest($request->paypal_order_id);

        try {
            $response = $this->client->execute($captureRequest);

            if ($response->result->status === 'COMPLETED') {
                // Estrai il capture ID
                $captureId = null;
                foreach ($response->result->purchase_units as $purchaseUnit) {
                    foreach ($purchaseUnit->payments->captures as $capture) {
                        $captureId = $capture->id;
                        break;
                    }
                }

                DB::transaction(function () use ($payment, $captureId, $response) {
                    $payment->markAsCompleted(
                        $captureId,
                        json_decode(json_encode($response->result), true)
                    );

                    // Aggiorna lo stato dell'ordine cliente se necessario
                    $order = $payment->customerOrder;
                    if ($order->state === CustomerOrder::STATE_PRODUCTS_DEFINED) {
                        $order->update([
                            'state' => CustomerOrder::STATE_PRODUCTS_ALLOCATED,
                        ]);
                    }
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Pagamento completato con successo.',
                    'payment' => $payment->load('customerOrder'),
                ]);
            } else {
                $payment->markAsFailed(json_decode(json_encode($response->result), true));

                return response()->json([
                    'success' => false,
                    'message' => 'Il pagamento non è stato completato.',
                    'status' => $response->result->status,
                ], 400);
            }
        } catch (\Exception $e) {
            $payment->markAsFailed(['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Errore nella cattura del pagamento: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ottieni lo stato di un pagamento.
     *
     * GET /api/payments/{id}
     */
    public function show(Request $request, $id)
    {
        $payment = Payment::query()
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with('customerOrder')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'payment' => $payment,
        ]);
    }

    /**
     * Ottieni i dettagli dell'ordine PayPal.
     *
     * POST /api/payments/paypal-details
     * body: { paypal_order_id: string }
     */
    public function getPayPalOrderDetails(Request $request)
    {
        $request->validate([
            'paypal_order_id' => ['required', 'string'],
        ]);

        $payment = Payment::query()
            ->where('paypal_order_id', $request->paypal_order_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $paypalRequest = new OrdersGetRequest($request->paypal_order_id);

        try {
            $response = $this->client->execute($paypalRequest);

            return response()->json([
                'success' => true,
                'paypal_order' => json_decode(json_encode($response->result), true),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero dei dettagli PayPal: ' . $e->getMessage(),
            ], 500);
        }
    }
}
