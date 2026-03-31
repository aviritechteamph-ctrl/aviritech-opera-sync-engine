<?php

$config = require __DIR__ . '/config.php';

function logError($message) {
    global $config;

    file_put_contents(
        $config['paths']['logs'] . 'error.log',
        date("Y-m-d H:i:s") . " - " . $message . PHP_EOL,
        FILE_APPEND
    );
}

function sendToCloud($endpoint, $data) {
    global $config;

    $ch = curl_init($config['api']['base_url'] . $endpoint);

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $config['api']['token']
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10, // ⛔ prevent hanging
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        logMessage('ERROR', 'CURL Error', [
            'error' => curl_error($ch),
            'endpoint' => $endpoint
        ]);
        curl_close($ch);
        return false;
    }

    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($statusCode >= 400) {
        logMessage('ERROR', 'API Error', [
            'status' => $statusCode,
            'response' => $response
        ]);
        return false;
    }

    return json_decode($response, true);
}

function getFromCloud($endpoint) {
    global $config;

    $ch = curl_init($config['api']['base_url'] . $endpoint);

    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $config['api']['token']
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        logMessage('ERROR', 'CURL Error', [
            'error' => curl_error($ch),
            'endpoint' => $endpoint
        ]);
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    return json_decode($response, true);
}

function queueBooking($booking) {
    global $config;

    // Prevent duplicates
    $bookingId = $booking['booking_id'] ?? uniqid();

    $payload = [
        'id' => $bookingId,
        'type' => 'BOOKING',
        'status' => 'PENDING',
        'retries' => 0,
        'created_at' => date("Y-m-d H:i:s"),
        'data' => $booking
    ];

    $filename = $config['paths']['queue'] . $bookingId . '.json';

    file_put_contents($filename, json_encode($payload));

    logMessage('INFO', 'Booking queued', ['id' => $bookingId]);
}
