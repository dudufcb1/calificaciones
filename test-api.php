<?php
/**
 * API Testing Script
 * This script tests the API endpoint for our AI integration
 */

// Configuration
$baseUrl = 'http://127.0.0.1:8000';
$endpoint = '/api/alumnos-data';
$token = 'test_token_1234'; // Same token as in our .env file
$userId = 1;

// Function to make API calls with proper headers
function callApi($url, $data, $token = null) {
    $curl = curl_init();

    $headers = ['Content-Type: application/json', 'Accept: application/json'];

    if ($token) {
        $headers[] = "X-AI-Agent-Token: $token";
    }

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_HEADER => true,
    ]);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);

    // Split header and body
    $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);

    curl_close($curl);

    return [
        'status' => $httpCode,
        'response' => $body ? json_decode($body, true) : null,
        'headers' => $header,
        'error' => $error
    ];
}

// Test cases
$testCases = [
    [
        'name' => 'Valid token',
        'url' => $baseUrl . $endpoint,
        'data' => ['user_id' => $userId],
        'token' => $token,
        'expected_status' => 200
    ],
    [
        'name' => 'Invalid token',
        'url' => $baseUrl . $endpoint,
        'data' => ['user_id' => $userId],
        'token' => 'wrong_token',
        'expected_status' => 403
    ],
    [
        'name' => 'Missing token',
        'url' => $baseUrl . $endpoint,
        'data' => ['user_id' => $userId],
        'token' => null,
        'expected_status' => 401
    ]
];

// Run tests
echo "Running API Tests\n";
echo "================\n\n";

foreach ($testCases as $test) {
    echo "Test: {$test['name']}\n";

    $result = callApi($test['url'], $test['data'], $test['token']);

    echo "Status: {$result['status']} (Expected: {$test['expected_status']})\n";

    if ($result['status'] == $test['expected_status']) {
        echo "✅ PASS\n";
    } else {
        echo "❌ FAIL\n";
    }

    echo "Headers:\n{$result['headers']}\n";
    echo "Response: " . ($result['error'] ? "Error: {$result['error']}" : json_encode($result['response'], JSON_PRETTY_PRINT)) . "\n\n";
}

echo "Tests completed.\n";
