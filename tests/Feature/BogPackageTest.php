<?php

namespace Tests\Feature;

use Bog\Payment\Models\BogCard;
use Bog\Payment\Models\BogPayment;
use Bog\Payment\Services\BogAuthService;
use Bog\Payment\Services\BogPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BogPackageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that BOG package classes exist and can be instantiated.
     */
    public function test_bog_package_classes_exist(): void
    {
        $this->assertTrue(class_exists('Bog\Payment\Services\BogAuthService'));
        $this->assertTrue(class_exists('Bog\Payment\Services\BogPaymentService'));
        $this->assertTrue(class_exists('Bog\Payment\Models\BogPayment'));
        $this->assertTrue(class_exists('Bog\Payment\Models\BogCard'));
    }

    /**
     * Test that BOG services can be instantiated.
     */
    public function test_bog_services_can_be_instantiated(): void
    {
        $authService = new BogAuthService;
        $paymentService = new BogPaymentService;

        $this->assertInstanceOf(BogAuthService::class, $authService);
        $this->assertInstanceOf(BogPaymentService::class, $paymentService);
    }

    /**
     * Test that BOG models can be accessed.
     */
    public function test_bog_models_can_be_accessed(): void
    {
        $paymentCount = BogPayment::count();
        $cardCount = BogCard::count();

        $this->assertIsInt($paymentCount);
        $this->assertIsInt($cardCount);
    }

    /**
     * Test that BOG configuration is loaded.
     */
    public function test_bog_configuration_is_loaded(): void
    {
        $this->assertNotNull(config('bog-payment.api_base_url'));
        $this->assertNotNull(config('bog-payment.auth_url'));
        $this->assertNotNull(config('bog-payment.orders_url'));
    }

    /**
     * Test that BOG authentication works (if credentials are set).
     */
    public function test_bog_authentication(): void
    {
        if (config('bog-payment.client_id') && config('bog-payment.client_secret')) {
            $authService = new BogAuthService;
            $token = $authService->getAccessToken();

            $this->assertIsArray($token);
            $this->assertArrayHasKey('access_token', $token);
            $this->assertNotEmpty($token['access_token']);
        } else {
            $this->markTestSkipped('BOG credentials not configured');
        }
    }

    /**
     * Test that BOG routes are registered.
     */
    public function test_bog_routes_are_registered(): void
    {
        $this->assertTrue($this->app['router']->has('bog.orders.create'));
        $this->assertTrue($this->app['router']->has('bog.cards.list'));
        $this->assertTrue($this->app['router']->has('bog.callback'));
    }

    /**
     * Test that BOG payment creation endpoint exists.
     */
    public function test_bog_payment_creation_endpoint_exists(): void
    {
        $response = $this->postJson('/api/bog/orders', [
            'amount' => 100.00,
            'currency' => 'GEL',
            'callback_url' => 'https://example.com/callback',
        ]);

        // Should not return 404 (route exists)
        $this->assertNotEquals(404, $response->getStatusCode());
    }
}
