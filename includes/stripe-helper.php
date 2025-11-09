<?php
/**
 * Stripe Helper Functions
 * Utility functions for Stripe integration
 */

/**
 * Check if Stripe is properly configured
 */
function isStripeConfigured($db) {
    try {
        $stmt = $db->prepare("SELECT publishable_key, secret_key FROM stripe_config WHERE is_active = 1 LIMIT 1");
        $stmt->execute();
        $config = $stmt->fetch();
        
        return $config && !empty($config['publishable_key']) && !empty($config['secret_key']);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get Stripe configuration status
 */
function getStripeStatus($db) {
    try {
        $stmt = $db->prepare("SELECT * FROM stripe_config WHERE is_active = 1 LIMIT 1");
        $stmt->execute();
        $config = $stmt->fetch();
        
        if (!$config) {
            return ['status' => 'not_configured', 'message' => 'Stripe not configured'];
        }
        
        if (empty($config['publishable_key']) || empty($config['secret_key'])) {
            return ['status' => 'incomplete', 'message' => 'Missing API keys'];
        }
        
        return [
            'status' => 'configured', 
            'message' => 'Stripe configured',
            'environment' => $config['environment'],
            'currency' => $config['default_currency']
        ];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => 'Database error'];
    }
}

/**
 * Get Stripe publishable key for frontend use
 */
function getStripePublishableKey($db) {
    try {
        $stmt = $db->prepare("SELECT publishable_key FROM stripe_config WHERE is_active = 1 LIMIT 1");
        $stmt->execute();
        $config = $stmt->fetch();
        
        return $config ? $config['publishable_key'] : null;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Validate Stripe key format
 */
function validateStripeKey($key, $type) {
    if (empty($key)) return false;
    
    switch ($type) {
        case 'publishable':
            return preg_match('/^pk_(test_|live_)/', $key);
        case 'secret':
            return preg_match('/^sk_(test_|live_)/', $key);
        case 'webhook':
            return preg_match('/^whsec_/', $key);
        default:
            return false;
    }
}

/**
 * Check if keys match environment
 */
function validateKeyEnvironment($key, $environment) {
    if (empty($key)) return true; // Allow empty keys
    
    if ($environment === 'live') {
        return str_contains($key, '_live_');
    } else {
        return str_contains($key, '_test_');
    }
}
?>
