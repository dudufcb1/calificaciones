<?php
echo "Testing server connection...\n";

$url = "http://127.0.0.1:8000";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "Status code: " . $httpCode . "\n";
echo "Error: " . ($error ? $error : "None") . "\n";
echo "Response: " . ($response ? $response : "No response") . "\n";
