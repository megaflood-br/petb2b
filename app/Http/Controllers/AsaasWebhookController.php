<?php

namespace App\Http\Controllers;

use App\Services\Pix\PixCreditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AsaasWebhookController extends Controller
{
    public function __invoke(Request $request, PixCreditService $credit): JsonResponse
    {
        $expected = (string) config('services.asaas.webhook_token');
        $received = (string) $request->header('asaas-access-token');

        // Sem token configurado ou token inválido => rejeita.
        if ($expected === '' || ! hash_equals($expected, $received)) {
            abort(401, 'Invalid webhook token.');
        }

        $event = $request->input('event');
        $paymentId = $request->input('payment.id');

        if (in_array($event, ['PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED'], true) && $paymentId) {
            $credit->confirmByPaymentId($paymentId);
        }

        // Sempre 200 para o Asaas não reenviar desnecessariamente.
        return response()->json(['received' => true]);
    }
}
