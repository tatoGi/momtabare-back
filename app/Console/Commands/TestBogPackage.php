<?php

namespace App\Console\Commands;

use Bog\Payment\Models\BogCard;
use Bog\Payment\Models\BogPayment;
use Bog\Payment\Services\BogAuthService;
use Bog\Payment\Services\BogPaymentService;
use Illuminate\Console\Command;

class TestBogPackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:bog-package';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test BOG Payment Package functionality';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Testing BOG Payment Package...');
        $this->line('================================');
        $this->newLine();

        // Test 1: Check if classes exist
        $this->info('1. Testing class existence:');
        $this->line('   BogAuthService: '.(class_exists('Bog\Payment\Services\BogAuthService') ? '✓ EXISTS' : '✗ NOT FOUND'));
        $this->line('   BogPaymentService: '.(class_exists('Bog\Payment\Services\BogPaymentService') ? '✓ EXISTS' : '✗ NOT FOUND'));
        $this->line('   BogPayment Model: '.(class_exists('Bog\Payment\Models\BogPayment') ? '✓ EXISTS' : '✗ NOT FOUND'));
        $this->line('   BogCard Model: '.(class_exists('Bog\Payment\Models\BogCard') ? '✓ EXISTS' : '✗ NOT FOUND'));
        $this->newLine();

        // Test 2: Test instantiation
        $this->info('2. Testing instantiation:');
        try {
            $auth = new BogAuthService;
            $this->line('   BogAuthService: ✓ INSTANTIATED SUCCESSFULLY');
        } catch (\Exception $e) {
            $this->error('   BogAuthService: ✗ ERROR - '.$e->getMessage());
        }

        try {
            $payment = new BogPaymentService;
            $this->line('   BogPaymentService: ✓ INSTANTIATED SUCCESSFULLY');
        } catch (\Exception $e) {
            $this->error('   BogPaymentService: ✗ ERROR - '.$e->getMessage());
        }

        // Test 3: Check configuration
        $this->newLine();
        $this->info('3. Configuration check:');
        $this->line('   API Base URL: '.(config('bog-payment.api_base_url') ?: 'NOT SET'));
        $this->line('   Auth URL: '.(config('bog-payment.auth_url') ?: 'NOT SET'));
        $this->line('   Orders URL: '.(config('bog-payment.orders_url') ?: 'NOT SET'));
        $this->line('   Client ID: '.(config('bog-payment.client_id') ? 'SET' : 'NOT SET'));
        $this->line('   Client Secret: '.(config('bog-payment.client_secret') ? 'SET' : 'NOT SET'));
        $this->line('   Callback URL: '.(config('bog-payment.callback_url') ?: 'NOT SET'));

        // Test 4: Test database models
        $this->newLine();
        $this->info('4. Testing database models:');
        try {
            $paymentCount = BogPayment::count();
            $this->line('   BogPayment model: ✓ ACCESSIBLE (Count: '.$paymentCount.')');
        } catch (\Exception $e) {
            $this->error('   BogPayment model: ✗ ERROR - '.$e->getMessage());
        }

        try {
            $cardCount = BogCard::count();
            $this->line('   BogCard model: ✓ ACCESSIBLE (Count: '.$cardCount.')');
        } catch (\Exception $e) {
            $this->error('   BogCard model: ✗ ERROR - '.$e->getMessage());
        }

        // Test 5: Test authentication (if credentials are set)
        $this->newLine();
        $this->info('5. Testing authentication:');
        if (config('bog-payment.client_id') && config('bog-payment.client_secret')) {
            try {
                $auth = new BogAuthService;
                $token = $auth->getAccessToken();
                if ($token && isset($token['access_token'])) {
                    $this->line('   Authentication: ✓ SUCCESS - Token received');
                } else {
                    $this->warn('   Authentication: ⚠ PARTIAL - No token in response');
                }
            } catch (\Exception $e) {
                $this->error('   Authentication: ✗ ERROR - '.$e->getMessage());
            }
        } else {
            $this->warn('   Authentication: ⚠ SKIPPED - No credentials configured');
        }

        $this->newLine();
        $this->line('================================');
        $this->info('Test completed!');

        return Command::SUCCESS;
    }
}
