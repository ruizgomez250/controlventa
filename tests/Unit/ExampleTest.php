<?php

namespace Tests\Unit;

use App\Models\Empresa;
use App\Models\SubscriptionOrder;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }

    public function test_subscription_dates_are_cast_to_date_objects(): void
    {
        $empresa = new Empresa(['fecha_expiracion' => '2026-08-31 23:59:59']);
        $order = new SubscriptionOrder(['activation_expires_at' => '2026-08-01 12:00:00']);

        $this->assertInstanceOf(Carbon::class, $empresa->fecha_expiracion);
        $this->assertInstanceOf(Carbon::class, $order->activation_expires_at);
    }
}
