<?php

require __DIR__ . '/functions.php';
$config = require __DIR__ . '/config.php';

$files = glob($config['paths']['queue'] . '*.json');

foreach ($files as $file) {

    $fp = fopen($file, 'r+');
    if (!$fp) continue;

    if (!flock($fp, LOCK_EX | LOCK_NB)) {
        fclose($fp);
        continue;
    }

    rewind($fp);
    $content = json_decode(stream_get_contents($fp), true);

    if (!$content) {
        logError("Invalid JSON: $file");
        flock($fp, LOCK_UN);
        fclose($fp);
        continue;
    }

    // Validate structure
    if (!isset($content['type'], $content['data'], $content['id'])) {
        logError("Malformed job: $file");
        flock($fp, LOCK_UN);
        fclose($fp);
        unlink($file);
        continue;
    }

    // Retry delay
    if (isset($content['next_attempt']) && time() < $content['next_attempt']) {
        flock($fp, LOCK_UN);
        fclose($fp);
        continue;
    }

    try {

        switch ($content['type']) {
            case 'BOOKING':
                $response = json_decode(sendToCloud('/bookings', $content['data']), true);
                break;

            case 'CANCEL':
                $response = json_decode(sendToCloud('/cancel', $content['data']), true);
                break;

            default:
                throw new Exception("Unknown type");
        }

        if ($response && isset($response['success']) && $response['success']) {

            flock($fp, LOCK_UN);
            fclose($fp);
            unlink($file);

        } else {
            throw new Exception("API failed");
        }

    } catch (Exception $e) {

        $content['retries'] = isset($content['retries']) ? $content['retries'] + 1 : 1;
        $content['status'] = 'PENDING';
        $content['next_attempt'] = time() + (60 * $content['retries']);

        file_put_contents($file, json_encode($content));

        logError("Job failed: " . $e->getMessage());
    }

    flock($fp, LOCK_UN);
    fclose($fp);
}
