<?php
header('Content-Type: application/json');

// Configuration
$n8n_webhook_url = 'YOUR_N8N_WEBHOOK_URL_HERE'; // Placeholder, user said TBD

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

// Basic validation
if (empty($name) || empty($email) || empty($message)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Please fill in all fields']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address']);
    exit;
}

// Data to send to N8N
$data = [
    'name' => $name,
    'email' => $email,
    'message' => $message,
    'timestamp' => date('Y-m-d H:i:s')
];

// Send to N8N
$ch = curl_init($n8n_webhook_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($http_code >= 200 && $http_code < 300) {
    echo json_encode(['status' => 'success', 'message' => 'Message sent successfully']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to send message']);
}
?>
