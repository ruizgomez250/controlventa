<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivateTenantRequest;
use App\Models\SubscriptionOrder;
use App\Services\TenantProvisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function show(string $token): View
    {
        $order = $this->findOrder($token);
        abort_unless($order->canBeActivated(), 410, 'El enlace de activación venció o ya fue utilizado.');

        return view('onboarding.activate', compact('order', 'token'));
    }

    public function store(
        ActivateTenantRequest $request,
        string $token,
        TenantProvisioner $provisioner,
    ): RedirectResponse {
        $order = DB::connection('mysql')->transaction(function () use ($token) {
            $order = SubscriptionOrder::query()
                ->where('activation_token_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($order->canBeActivated(), 410, 'El enlace de activación venció o ya fue utilizado.');

            $order->update(['status' => SubscriptionOrder::STATUS_PROVISIONING]);

            return $order;
        });

        try {
            $empresa = $provisioner->provision($request->validated());
        } catch (\Throwable $exception) {
            $order->update(['status' => SubscriptionOrder::STATUS_PAID]);
            report($exception);

            return back()
                ->withInput($request->except(['password_admin', 'password_admin_confirmation']))
                ->withErrors(['empresa' => 'No pudimos crear la empresa. Intentá nuevamente en unos minutos.']);
        }

        $order->forceFill([
            'status' => SubscriptionOrder::STATUS_ACTIVATED,
            'activated_at' => now(),
            'empresa_id' => $empresa->id,
            'activation_token_hash' => null,
        ])->save();

        $baseDomain = config('tenancy.base_domain');
        $scheme = app()->environment('production') ? 'https' : $request->getScheme();
        $port = in_array($request->getPort(), [80, 443], true)
            ? ''
            : ':'.$request->getPort();
        $loginUrl = "{$scheme}://{$empresa->dominio}.{$baseDomain}{$port}/login";

        return redirect()->away($loginUrl);
    }

    private function findOrder(string $token): SubscriptionOrder
    {
        return SubscriptionOrder::query()
            ->where('activation_token_hash', hash('sha256', $token))
            ->firstOrFail();
    }
}
