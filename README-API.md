# AI Agent API Integration Guide

## Overview

This document provides instructions for AI agents to access the student data API. The API provides secure access to student information for authorized AI agents through token-based authentication.

## API Endpoint

-   **Base URL**: `http://127.0.0.1:8000/api` (development) or your production server URL
-   **Endpoint Path**: `/alumnos-data`
-   **Full URL**: `http://127.0.0.1:8000/api/alumnos-data`
-   **Method**: POST

## Authentication

The API uses token-based authentication with a custom header:

```
X-AI-Agent-Token: test_token_1234
```

This specific token value (`test_token_1234`) is configured in your `.env` file. In production, use the token provided by your system administrator.

## Required Headers

The API requires the following headers with EXACT values:

| Header Name      | Value            | Required | Description                              |
| ---------------- | ---------------- | -------- | ---------------------------------------- |
| Content-Type     | application/json | Yes      | Specifies the format of the request body |
| Accept           | application/json | Yes      | Specifies the expected response format   |
| X-AI-Agent-Token | test_token_1234  | Yes      | Authentication token                     |

## Request Body Parameters

The request body must be a JSON object with the following parameters:

| Parameter | Type    | Required | Description                               |
| --------- | ------- | -------- | ----------------------------------------- |
| user_id   | integer | Yes      | ID of the user whose students to retrieve |

Example request body:

```json
{
    "user_id": 1
}
```

## Response Structure

### Successful Response (200 OK)

The successful response includes:

-   `success` (boolean): Always true for successful requests
-   `data` (object): Contains the main response data including:
    -   `user_id` (integer): The requested user ID
    -   `user_name` (string): The name of the user
    -   `periodo` (object): Start and end dates
    -   `alumnos` (array): List of student records with detailed information

### Error Responses

#### Invalid Token (403 Forbidden)

```json
{
    "success": false,
    "message": "Token de autenticación inválido"
}
```

#### Missing Token (401 Unauthorized)

```json
{
    "success": false,
    "message": "Token de autenticación no proporcionado"
}
```

## Complete API Call Example

### cURL Example (Copy/Paste Ready)

```bash
curl -X POST \
  http://127.0.0.1:8000/api/alumnos-data \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -H 'X-AI-Agent-Token: test_token_1234' \
  -d '{"user_id": 1}'
```

### Python Example (Copy/Paste Ready)

```python
import requests
import json

url = "http://127.0.0.1:8000/api/alumnos-data"
headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-AI-Agent-Token': 'test_token_1234'
}
payload = {
    'user_id': 1
}

response = requests.post(url, headers=headers, data=json.dumps(payload))

if response.status_code == 200:
    data = response.json()
    print(f"Successfully retrieved data for {len(data['data']['alumnos'])} students")
    # Process student data here
    for student in data['data']['alumnos']:
        print(f"Student: {student['nombre_completo']}")
else:
    print(f"Error: {response.status_code}")
    print(response.text)
```

### JavaScript Example (Copy/Paste Ready)

```javascript
const fetchStudentData = async () => {
    const url = "http://127.0.0.1:8000/api/alumnos-data";
    const options = {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            "X-AI-Agent-Token": "test_token_1234",
        },
        body: JSON.stringify({
            user_id: 1,
        }),
    };

    try {
        const response = await fetch(url, options);
        const data = await response.json();

        if (response.ok) {
            console.log(
                `Retrieved data for ${data.data.alumnos.length} students`
            );
            // Example: Display student names
            data.data.alumnos.forEach((student) => {
                console.log(`Student: ${student.nombre_completo}`);
            });
            return data;
        } else {
            console.error(`Error: ${response.status}`);
            console.error(data.message);
            return null;
        }
    } catch (error) {
        console.error("Failed to fetch student data:", error);
        return null;
    }
};

// Call the function to fetch data
fetchStudentData();
```

### PHP Example (Copy/Paste Ready)

```php
<?php
$url = 'http://127.0.0.1:8000/api/alumnos-data';
$data = ['user_id' => 1];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-AI-Agent-Token: test_token_1234'
    ],
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($httpCode == 200) {
    $responseData = json_decode($response, true);
    echo "Retrieved data for " . count($responseData['data']['alumnos']) . " students\n";

    // Example: Display student names
    foreach ($responseData['data']['alumnos'] as $student) {
        echo "Student: " . $student['nombre_completo'] . "\n";
    }
} else {
    echo "Error: " . $httpCode . "\n";
    echo $response . "\n";
}
?>
```

## Testing the API

You can use the provided test script to verify the API is working correctly:

```bash
# From the project root directory
php test-api.php
```

This will test the following scenarios:

1. Valid token (should return 200 OK)
2. Invalid token (should return 403 Forbidden)
3. Missing token (should return 401 Unauthorized)

## Troubleshooting

1. **401 Unauthorized Error**: Make sure the header name is exactly `X-AI-Agent-Token` (case sensitive)
2. **403 Forbidden Error**: Ensure the token is exactly `test_token_1234` (unless changed in the .env file)
3. **Connection Issues**:
    - Verify the Laravel server is running (`php artisan serve`)
    - Check the port number in the URL
    - Make sure there are no firewall issues

## Server Management

To start the Laravel development server:

```bash
php artisan serve
```

The server will start on `http://127.0.0.1:8000` by default.

## Support

For issues with this API integration, please contact the system administrator or development team.
