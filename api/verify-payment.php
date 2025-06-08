<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get the JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON input']);
    exit();
}

// Required fields
$required_fields = ['transaction_id', 'name', 'email', 'phone', 'website', 'company'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit();
    }
}

// PayPal configuration
$paypal_client_id = 'AUQS8B6k2qGo_e8m3aDc7n3QVprh-8yHr9u3VwAaUACfdkQM78p6tk4Pg3rw0c4U_Z6SSQy4t5RjzYL6';
$paypal_client_secret = 'EJgQ1WdchtFJeXErwTNDgcV1ib8CHFPi5BNbIcA2a_q78rFF1-Em5tPvUFpRFJTtximd5eSo-uKm7f3B'; // You need to add this
$paypal_base_url = 'https://api-m.paypal.com'; // Use https://api-m.sandbox.paypal.com for sandbox

try {
    // Step 1: Get PayPal access token
    $access_token = getPayPalAccessToken($paypal_client_id, $paypal_client_secret, $paypal_base_url);
    
    // Step 2: Verify the transaction with PayPal
    $transaction_details = verifyPayPalTransaction($input['transaction_id'], $access_token, $paypal_base_url);
    
    // Step 3: Validate transaction details
    if (!validateTransaction($transaction_details)) {
        throw new Exception('Transaction validation failed');
    }
    
    // Step 4: Create user credentials in your document management system
    $user_credentials = createUserCredentials($input);
    
    // Step 5: Send welcome email with credentials
    sendWelcomeEmail($input, $user_credentials);
    
    // Step 6: Log the successful transaction
    logTransaction($input['transaction_id'], $input['email'], $user_credentials);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Payment verified and access granted',
        'user_credentials' => [
            'username' => $user_credentials['username'],
            'portal_url' => 'https://seo-dashboard.speed.cy/login.php'
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Payment verification error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Payment verification failed',
        'message' => $e->getMessage()
    ]);
}

function getPayPalAccessToken($client_id, $client_secret, $base_url) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $base_url . '/v1/oauth2/token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
        CURLOPT_USERPWD => $client_id . ':' . $client_secret,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Accept-Language: en_US',
            'Content-Type: application/x-www-form-urlencoded'
        ]
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        throw new Exception('Failed to get PayPal access token');
    }
    
    $data = json_decode($response, true);
    return $data['access_token'];
}

function verifyPayPalTransaction($transaction_id, $access_token, $base_url) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $base_url . '/v2/checkout/orders/' . $transaction_id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token
        ]
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        throw new Exception('Failed to verify PayPal transaction');
    }
    
    return json_decode($response, true);
}

function validateTransaction($transaction_details) {
    // Validate transaction status
    if ($transaction_details['status'] !== 'COMPLETED') {
        return false;
    }
    
    // Validate amount (750.00 EUR)
    $amount = $transaction_details['purchase_units'][0]['amount'];
    if ($amount['currency_code'] !== 'EUR' || floatval($amount['value']) !== 750.00) {
        return false;
    }
    
    return true;
}

function createUserCredentials($user_data) {
    // Include your database configuration
    require_once 'config/database.php';
    
    try {
        // Generate unique username and password
        $username = generateUsername($user_data['email']);
        $password = generateSecurePassword();
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user into your document management system database
        $stmt = $pdo->prepare("
            INSERT INTO users (
                username, 
                password_hash, 
                email, 
                full_name, 
                phone, 
                website, 
                company, 
                access_level,
                created_at,
                status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'premium', NOW(), 'active')
        ");
        
        $stmt->execute([
            $username,
            $password_hash,
            $user_data['email'],
            $user_data['name'],
            $user_data['phone'],
            $user_data['website'],
            $user_data['company']
        ]);
        
        return [
            'username' => $username,
            'password' => $password,
            'user_id' => $pdo->lastInsertId()
        ];
        
    } catch (PDOException $e) {
        throw new Exception('Failed to create user credentials: ' . $e->getMessage());
    }
}

function generateUsername($email) {
    // Create username from email prefix + random suffix
    $email_prefix = explode('@', $email)[0];
    $clean_prefix = preg_replace('/[^a-zA-Z0-9]/', '', $email_prefix);
    $random_suffix = substr(str_shuffle('0123456789'), 0, 4);
    
    return strtolower($clean_prefix . $random_suffix);
}

function generateSecurePassword($length = 12) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, strlen($characters) - 1)];
    }
    
    return $password;
}

function sendWelcomeEmail($user_data, $credentials) {
    $to = $user_data['email'];
    $subject = 'Welcome to SEO Masterplan - Your Access Credentials';
    
    $message = "
    <html>
    <head>
        <title>Welcome to SEO Masterplan</title>
    </head>
    <body>
        <h2>Welcome to SEO Masterplan, {$user_data['name']}!</h2>
        
        <p>Thank you for your purchase. Your payment has been successfully processed and your access to the complete SEO Masterplan has been activated.</p>
        
        <h3>Your Login Credentials:</h3>
        <p><strong>Portal URL:</strong> <a href='https://seo-dashboard.speed.cy/login.php'>https://seo-dashboard.speed.cy/login.php</a></p>
        <p><strong>Username:</strong> {$credentials['username']}</p>
        <p><strong>Password:</strong> {$credentials['password']}</p>
        
        <h3>What You Get Access To:</h3>
        <ul>
            <li>180+ Professional SEO Templates & Documents</li>
            <li>Executive Strategy Toolkit</li>
            <li>Manager's Implementation Guide</li>
            <li>Expert Execution Guidelines</li>
            <li>Channel Synergy Playbooks</li>
            <li>Ongoing Management Framework</li>
        </ul>
        
        <p><strong>Important:</strong> Please save these credentials in a secure location. You can change your password after logging in.</p>
        
        <p>If you have any questions or need assistance, please contact our support team.</p>
        
        <p>Best regards,<br>
        The SEO Masterplan Team</p>
    </body>
    </html>
    ";
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: SEO Masterplan <support@speed.cy>',
        'Reply-To: support@speed.cy'
    ];
    
    mail($to, $subject, $message, implode("\r\n", $headers));
}

function logTransaction($transaction_id, $email, $credentials) {
    require_once 'config/database.php';
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO transaction_log (
                paypal_transaction_id,
                user_email,
                username_created,
                created_at
            ) VALUES (?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $transaction_id,
            $email,
            $credentials['username']
        ]);
        
    } catch (PDOException $e) {
        error_log('Failed to log transaction: ' . $e->getMessage());
    }
}
?>