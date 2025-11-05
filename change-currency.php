<?php
/**
 * Currency Change Handler - Updates user's preferred currency
 */

session_start();
require_once 'config.php';

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed');
}

// Get currency from POST data
$currency = sanitizeInput($_POST['currency'] ?? '');
$user_type = sanitizeInput($_POST['user_type'] ?? '');

if (empty($currency) || empty($user_type)) {
    $_SESSION['error'] = "Invalid currency selection.";
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
    exit();
}

// Validate currency code
$valid_currencies = [
    'USD', 'GHS', 'NGN', 'GBP', 'EUR', 'CAD', 'AUD', 'ZAR', 'KES', 'UGX', 
    'TZS', 'JPY', 'CHF', 'SEK', 'NOK', 'DKK', 'PLN', 'CZK', 'HUF', 'RON',
    'BGN', 'HRK', 'RUB', 'CNY', 'INR', 'BRL', 'MXN', 'ARS', 'CLP', 'COP',
    'PEN', 'UYU', 'EGP', 'MAD', 'TND', 'ETB', 'RWF', 'MWK', 'ZMW', 'BWP'
];

if (!in_array($currency, $valid_currencies)) {
    $_SESSION['error'] = "Invalid currency code.";
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
    exit();
}

try {
    if ($user_type === 'client') {
        // Update client currency preference
        require_once 'includes/client-auth.php';
        requireClientLogin();
        
        $client = getCurrentClient($db);
        if (!$client) {
            throw new Exception("Client not found.");
        }
        
        $stmt = $db->prepare("UPDATE clients SET preferred_currency = ? WHERE id = ?");
        $stmt->execute([$currency, $client['id']]);
        
        $_SESSION['success'] = "Currency preference updated to " . getCurrencyName($currency) . " successfully!";
        $redirect_url = 'client-dashboard.php';
        
    } elseif ($user_type === 'admin') {
        // Update admin currency preference (store in session for now)
        requireLogin();
        
        // For admin, we'll store in session since admin table structure may vary
        $_SESSION['admin_preferred_currency'] = $currency;
        
        $_SESSION['success'] = "Currency preference updated to " . getCurrencyName($currency) . " successfully!";
        $redirect_url = 'admin-dashboard.php';
        
    } else {
        throw new Exception("Invalid user type.");
    }
    
} catch (Exception $e) {
    error_log("Currency change error: " . $e->getMessage());
    $_SESSION['error'] = "Failed to update currency preference: " . $e->getMessage();
    $redirect_url = $_SERVER['HTTP_REFERER'] ?? '/';
}

header('Location: ' . $redirect_url);
exit();

/**
 * Get currency display name
 */
function getCurrencyName($code) {
    $currencies = [
        'USD' => 'US Dollar',
        'GHS' => 'Ghanaian Cedi',
        'NGN' => 'Nigerian Naira',
        'GBP' => 'British Pound',
        'EUR' => 'Euro',
        'CAD' => 'Canadian Dollar',
        'AUD' => 'Australian Dollar',
        'ZAR' => 'South African Rand',
        'KES' => 'Kenyan Shilling',
        'UGX' => 'Ugandan Shilling',
        'TZS' => 'Tanzanian Shilling',
        'JPY' => 'Japanese Yen',
        'CHF' => 'Swiss Franc',
        'SEK' => 'Swedish Krona',
        'NOK' => 'Norwegian Krone',
        'DKK' => 'Danish Krone',
        'PLN' => 'Polish Zloty',
        'CZK' => 'Czech Koruna',
        'HUF' => 'Hungarian Forint',
        'RON' => 'Romanian Leu',
        'BGN' => 'Bulgarian Lev',
        'HRK' => 'Croatian Kuna',
        'RUB' => 'Russian Ruble',
        'CNY' => 'Chinese Yuan',
        'INR' => 'Indian Rupee',
        'BRL' => 'Brazilian Real',
        'MXN' => 'Mexican Peso',
        'ARS' => 'Argentine Peso',
        'CLP' => 'Chilean Peso',
        'COP' => 'Colombian Peso',
        'PEN' => 'Peruvian Sol',
        'UYU' => 'Uruguayan Peso',
        'EGP' => 'Egyptian Pound',
        'MAD' => 'Moroccan Dirham',
        'TND' => 'Tunisian Dinar',
        'ETB' => 'Ethiopian Birr',
        'RWF' => 'Rwandan Franc',
        'MWK' => 'Malawian Kwacha',
        'ZMW' => 'Zambian Kwacha',
        'BWP' => 'Botswana Pula'
    ];
    
    return $currencies[$code] ?? $code;
}
?>
