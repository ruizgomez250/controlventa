<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExpiredLogoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
        $this->app->bind(VerifyCsrfToken::class, function ($app) {
            return new class($app, $app['encrypter']) extends VerifyCsrfToken {
                protected function runningUnitTests()
                {
                    return false;
                }
            };
        });
    }

    public function test_logout_con_token_vencido_redirige_al_login_si_la_sesion_expiro(): void
    {
        $this->withSession(['_token' => 'token-nuevo'])
            ->post(route('logout'), ['_token' => 'token-vencido'])
            ->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_otros_formularios_conservan_la_proteccion_csrf(): void
    {
        Route::middleware('web')->post('/csrf-test', fn () => 'guardado');
        $this->withSession(['_token' => 'token-nuevo'])
            ->post('/csrf-test', ['_token' => 'token-vencido'])
            ->assertStatus(419);
    }
}
