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

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $config['api']['token']
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        logError('CURL Error: ' . curl_error($ch));
    }

    curl_close($ch);

    return $response;
}

function getFromCloud($endpoint) {
    global $config;

    $ch = curl_init($config['api']['base_url'] . $endpoint);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $config['api']['token']
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        logError('CURL Error: ' . curl_error($ch));
    }

    curl_close($ch);

    return json_decode($response, true);
}

function queueBooking($booking) {
    global $config;

    $filename = $config['paths']['queue'] . time() . '_' . rand(1000,9999) . '.json';

    file_put_contents($filename, json_encode($booking));
}
