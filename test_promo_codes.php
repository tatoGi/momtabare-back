<?php

use App\Models\PromoCode;

// Test creating a promo code
$promoCode = PromoCode::create([
    'code' => 'SUMMER20',
    'discount_percentage' => 20,
    'description' => 'Summer sale - 20% off on all products',
    'usage_limit' => 100,
    'per_user_limit' => 5,
    'is_active' => true,
    'minimum_order_amount' => 50,
]);

echo 'Created promo code: '.$promoCode->code.' with '.$promoCode->discount_percentage."% discount\n";
echo 'ID: '.$promoCode->id."\n";

// Test validation
echo "\nTesting validation:\n";
echo 'Is valid: '.($promoCode->isValid() ? 'Yes' : 'No')."\n";
echo 'Meets minimum amount (150): '.($promoCode->meetsMinimumOrderAmount(150) ? 'Yes' : 'No')."\n";
echo 'Discount on 200: '.$promoCode->calculateDiscount(200)."\n";

// Test applying to products and categories
$promoCode2 = PromoCode::create([
    'code' => 'PREMIUM10',
    'discount_percentage' => 10,
    'description' => 'Premium product discount',
    'applicable_products' => [1, 2, 3], // Apply to specific products
    'applicable_categories' => [1, 2], // Apply to specific categories
    'is_active' => true,
]);

echo "\nCreated second promo code: ".$promoCode2->code."\n";
echo 'Applies to product 1: '.($promoCode2->appliesToProduct(1) ? 'Yes' : 'No')."\n";
echo 'Applies to product 5: '.($promoCode2->appliesToProduct(5) ? 'Yes' : 'No')."\n";
