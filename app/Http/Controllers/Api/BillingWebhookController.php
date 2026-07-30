<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class BillingWebhookController extends Controller
{
    public function __invoke(Request $request, string $provider): JsonResponse
    {
        $secret = (string) config('billing.webhook_secret');
        abort_if($secret === '', 503, 'Webhook de pagos no configurado.');

        $signature = (string) $request->header('X-Webhook-Signature');
        $expected = hash_hmac('sha256', $request->getContent(), $secret);
        abort_unless($signature !== '' && hash_equals($expected, $signature), 401);

        $data = $request->validate([
            'external_id' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:paid,rejected'],
            'amount' => ['required', 'integer', 'min:1'],
            'currency' => ['required', 'string', 'size:3'],
            'customer_email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $activationToken = null;

        DB::connection('mysql')->transaction(function () use (
            $data,
            $provider,
            $request,
            &$activationToken,
        ) {
            $order = SubscriptionOrder::query()
                ->where('external_id', $data['external_id'])
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $order->provider === $provider
                && hash_equals($order->customer_email, strtolower($data['customer_email']))
                && $order->amount === (int) $data['amount']
                && $order->currency === strtoupper($data['currency']),
                422,
                'Los datos del pago no coinciden con la orden.'
            );

            if ($order->status === SubscriptionOrder::STATUS_ACTIVATED) {
                return;
            }

            if ($data['status'] === 'rejected') {
                $order->update([
                    'status' => SubscriptionOrder::STATUS_REJECTED,
                    'provider_payload' => $request->all(),
                ]);

                return;
            }

            if ($order->status !== SubscriptionOrder::STATUS_PAID) {
                $activationToken = Str::random(64);
                $order->update([
                    'status' => SubscriptionOrder::STATUS_PAID,
                    'paid_at' => now(),
                    'activation_token_hash' => hash('sha256', $activationToken),
                    'activation_expires_at' => now()->addHours(config('billing.activation_ttl_hours')),
                    'provider_payload' => $request->all(),
                ]);
            }
        });

        if ($activationToken !== null) {
            $url = route('onboarding.show', ['token' => $activationToken]);

            try {
                Mail::raw(
                    "Tu pago fue confirmado. Configurá tu empresa desde este enlace: {$url}",
                    fn ($message) => $message
                        ->to(strtolower($data['customer_email']))
                        ->subject('Activá tu cuenta de Control Venta')
                );
            } catch (\Throwable $exception) {
                Log::error('No se pudo enviar el enlace de activación.', [
                    'external_id' => $data['external_id'],
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        return response()->json(['received' => true]);
    }
}
