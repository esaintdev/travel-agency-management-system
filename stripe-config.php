<?php
/**
 * Stripe Configuration
 * Loads configuration from database (admin-configurable)
 */

// Load Stripe configuration from database
function loadStripeConfig($db) {
    try {
        $stmt = $db->prepare("SELECT * FROM stripe_config WHERE is_active = 1 ORDER BY updated_at DESC LIMIT 1");
        $stmt->execute();
        return $stmt->fetch();
    } catch (Exception $e) {
        // Table might not exist or no config found
        return null;
    }
}

// Get Stripe configuration
$stripe_config = loadStripeConfig($db);

// Define Stripe constants from database or fallback to defaults
if ($stripe_config) {
    define('STRIPE_PUBLISHABLE_KEY', $stripe_config['publishable_key'] ?: 'pk_test_your_publishable_key_here');
    define('STRIPE_SECRET_KEY', $stripe_config['secret_key'] ?: 'sk_test_your_secret_key_here');
    define('STRIPE_WEBHOOK_SECRET', $stripe_config['webhook_secret'] ?: 'whsec_your_webhook_secret_here');
    define('STRIPE_ENVIRONMENT', $stripe_config['environment'] ?: 'test');
    define('STRIPE_DEFAULT_CURRENCY', $stripe_config['default_currency'] ?: 'USD');
} else {
    // Fallback values if no database config exists
    define('STRIPE_PUBLISHABLE_KEY', 'pk_test_your_publishable_key_here');
    define('STRIPE_SECRET_KEY', 'sk_test_your_secret_key_here');
    define('STRIPE_WEBHOOK_SECRET', 'whsec_your_webhook_secret_here');
    define('STRIPE_ENVIRONMENT', 'test');
    define('STRIPE_DEFAULT_CURRENCY', 'USD');
}

// Currency settings
define('DEFAULT_CURRENCY', 'usd'); // Default currency for Stripe
define('PREFERRED_CURRENCIES', ['NGN', 'USD', 'GHS', 'EUR', 'GBP']); // Preferred currencies for your region

// Company information for Stripe
define('COMPANY_NAME', 'M25 Travel & Tour Agency');
define('COMPANY_EMAIL', 'info@m25travelagency.com');

/**
 * Convert currency codes to Stripe-compatible format
 */
function getStripeCurrency($currency_code) {
    $currency_map = [
        'USD' => 'usd',
        'NGN' => 'ngn',  // Nigerian Naira
        'GHS' => 'ghs',  // Ghana Cedis
        'EUR' => 'eur',
        'GBP' => 'gbp',
        'CAD' => 'cad',
        'AUD' => 'aud',
        'ZAR' => 'zar',  // South African Rand
        'KES' => 'kes',  // Kenyan Shilling
        'EGP' => 'egp'   // Egyptian Pound
    ];
    
    return $currency_map[$currency_code] ?? 'usd';
}

/**
 * Convert amount to Stripe format (cents)
 * Stripe requires amounts in the smallest currency unit
 */
function convertToStripeAmount($amount, $currency) {
    // Most currencies use 2 decimal places (cents)
    $zero_decimal_currencies = ['jpy', 'krw', 'vnd', 'clp']; // No decimal places
    
    if (in_array(strtolower($currency), $zero_decimal_currencies)) {
        return intval($amount);
    }
    
    return intval($amount * 100); // Convert to cents
}

/**
 * Convert amount from Stripe format back to regular amount
 */
function convertFromStripeAmount($stripe_amount, $currency) {
    $zero_decimal_currencies = ['jpy', 'krw', 'vnd', 'clp'];
    
    if (in_array(strtolower($currency), $zero_decimal_currencies)) {
        return $stripe_amount;
    }
    
    return $stripe_amount / 100;
}
?>
