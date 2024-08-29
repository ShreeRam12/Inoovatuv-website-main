<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set CORS headers if required
header('Access-Control-Allow-Origin: *'); // Adjust if needed
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');


// Handle OPTIONS request (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and decode JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid JSON']);
        exit;
    }

    // Collect form data from JSON
    $name = $input['appointment_name'] ?? '';
    $email = $input['appointment_email'] ?? '';
    $phone = $input['appointment_phone'] ?? '';
    $website = $input['appointment_website'] ?? '';

    // Validate required fields
    if (empty($name) || empty($email)) {
        http_response_code(400);
        echo json_encode(['message' => 'Name and Email are required']);
        exit;
    }

    // Mailchimp API key and Audience ID
    $apiKey = '83605025e8b0a1732471f4c01f2cc4ad-us22'; // Replace with your Mailchimp API key
    $listId = 'aafb466de7';       // Replace with your Mailchimp Audience ID

    // Mailchimp API endpoint
    $url = "https://us22.admin.mailchimp.com/lists/settings/defaults?id=7963"; // Replace 'usX' with your Mailchimp data center prefix

    // Prepare the data
    $data = [
        'email_address' => $email,
        'status'        => 'subscribed', // 'subscribed' to add to the list
        'merge_fields'  => [
            'FNAME' => $name,
            'PHONE' => $phone,
            'WEBSITE' => $website
        ]
    ];

    // Initialize cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode('user:' . $apiKey),
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // Execute the request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check the response
    if ($httpCode == 200) {
        http_response_code(200);
        echo json_encode(['message' => 'Form submitted successfully and data saved']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Failed to save data to Mailchimp']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Invalid request method']);
}

