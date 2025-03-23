<?php
/**
 * Browser-friendly API Test Page
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .test-card { border: 1px solid #ddd; margin-bottom: 20px; padding: 15px; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .failure { background-color: #f8d7da; border-color: #f5c6cb; }
        pre { background: #f4f4f4; padding: 10px; overflow: auto; }
        button { padding: 10px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0069d9; }
        .controls { margin-bottom: 20px; }
        input { padding: 8px; margin-right: 10px; width: 300px; }
    </style>
</head>
<body>
    <h1>API Test Page</h1>

    <div class="controls">
        <input type="text" id="server-url" value="http://127.0.0.1:8000" placeholder="Server URL">
        <button onclick="runTests()">Run All Tests</button>
    </div>

    <div id="results"></div>

    <script>
        // Test cases
        const tests = [
            {
                name: 'Valid token',
                endpoint: '/api/alumnos-data',
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-AI-Agent-Token': 'test_token_1234'
                },
                body: JSON.stringify({ user_id: 1 }),
                expectedStatus: 200
            },
            {
                name: 'Invalid token',
                endpoint: '/api/alumnos-data',
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-AI-Agent-Token': 'wrong_token'
                },
                body: JSON.stringify({ user_id: 1 }),
                expectedStatus: 403
            },
            {
                name: 'Missing token',
                endpoint: '/api/alumnos-data',
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ user_id: 1 }),
                expectedStatus: 401
            }
        ];

        async function runTest(test, baseUrl) {
            const resultsDiv = document.getElementById('results');
            const testDiv = document.createElement('div');
            testDiv.className = 'test-card';
            testDiv.innerHTML = `<h3>Test: ${test.name}</h3><p>Running...</p>`;
            resultsDiv.appendChild(testDiv);

            try {
                const response = await fetch(`${baseUrl}${test.endpoint}`, {
                    method: test.method,
                    headers: test.headers,
                    body: test.body
                });

                const data = await response.json().catch(() => 'No JSON response');
                const isSuccess = response.status === test.expectedStatus;

                testDiv.className = `test-card ${isSuccess ? 'success' : 'failure'}`;
                testDiv.innerHTML = `
                    <h3>Test: ${test.name}</h3>
                    <p>Status: ${response.status} (Expected: ${test.expectedStatus}) - ${isSuccess ? '✅ PASS' : '❌ FAIL'}</p>
                    <p>Response:</p>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
            } catch (error) {
                testDiv.className = 'test-card failure';
                testDiv.innerHTML = `
                    <h3>Test: ${test.name}</h3>
                    <p>Error: ${error.message}</p>
                    <p>❌ FAIL</p>
                `;
            }
        }

        async function runTests() {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = '';

            const baseUrl = document.getElementById('server-url').value.trim();

            for (const test of tests) {
                await runTest(test, baseUrl);
            }
        }
    </script>
</body>
</html>
