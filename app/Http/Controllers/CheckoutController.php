<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(): View
    {
        return view('billing.checkout', [
            'amount' => config('billing.plan_amount'),
            'currency' => config('billing.currency'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if(
            ! config('billing.provider')
            || ! config('billing.checkout_url')
            || config('billing.plan_amount') < 1,
            503,
            'El medio de pago todavía no está configurado.'
        );

        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $uuid = (string) Str::uuid();
        $order = SubscriptionOrder::query()->create([
            'uuid' => $uuid,
            'provider' => config('billing.provider'),
            'external_id' => $uuid,
            'customer_email' => strtolower($validated['email']),
            'amount' => config('billing.plan_amount'),
            'currency' => strtoupper(config('billing.currency')),
            'status' => SubscriptionOrder::STATUS_PENDING,
        ]);

        $query = http_build_query([
            'external_id' => $order->external_id,
            'amount' => $order->amount,
            'currency' => $order->currency,
            'customer_email' => $order->customer_email,
            'webhook_url' => route('billing.webhook', ['provider' => $order->provider]),
        ]);

        return redirect()->away(rtrim(config('billing.checkout_url'), '?').'?'.$query);
    }
}
