<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_debug_login_is_not_exposed(): void
    {
        $this->get('/debug-login')->assertNotFound();
    }

    public function test_company_administration_requires_authentication(): void
    {
        $this->get('/empresas')->assertRedirect(route('login'));
    }

    public function test_business_api_requires_a_token(): void
    {
        $this->getJson('/api/clientesa')->assertUnauthorized();
    }
}
