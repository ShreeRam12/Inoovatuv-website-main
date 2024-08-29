<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set CORS headers
header('Access-Control-Allow-Origin: https://ayntech.in'); // Replace with your domain
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Process POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and decode JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid JSON']);
        exit;
    }

    // Retrieve and sanitize input data
    $name = filter_var($input['name'] ?? '', FILTER_SANITIZE_STRING);
    $phoneNumber = filter_var($input['phoneNumber'] ?? '', FILTER_SANITIZE_STRING);
    
    // Sanitize arrays
    $socialMedias = isset($input['socialMedias']) ? array_map('filter_var', $input['socialMedias'], array_fill(0, count($input['socialMedias']), FILTER_SANITIZE_STRING)) : [];
    $goals = isset($input['goals']) ? array_map('filter_var', $input['goals'], array_fill(0, count($input['goals']), FILTER_SANITIZE_STRING)) : [];
    $budgets = isset($input['budgets']) ? array_map('filter_var', $input['budgets'], array_fill(0, count($input['budgets']), FILTER_SANITIZE_STRING)) : [];
    $startingTimes = isset($input['startingTimes']) ? array_map('filter_var', $input['startingTimes'], array_fill(0, count($input['startingTimes']), FILTER_SANITIZE_STRING)) : [];
    $business = filter_var($input['business'] ?? '', FILTER_SANITIZE_STRING);

    // Prepare email message
    $message = "Name: $name\n";
    $message .= "Phone Number: $phoneNumber\n";
    $message .= "Social Media Channels: " . implode(', ', $socialMedias) . "\n";
    $message .= "Goals: " . implode(', ', $goals) . "\n";
    $message .= "Business Type: $business\n";
    $message .= "Budgets: " . implode(', ', $budgets) . "\n";
    $message .= "Starting Times: " . implode(', ', $startingTimes) . "\n";

    // Send the email
    $to = 'ceo.ayn@gmail.com';
    $subject = 'Enquiry from Digital Marketing Ad';
    $headers = "From: noreply@ayntech.in\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    if (mail($to, $subject, $message, $headers)) {
        http_response_code(200);
        echo json_encode(['message' => 'Email sent successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Failed to send email']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Invalid request method']);
}
?>
