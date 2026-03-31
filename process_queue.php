<?php

require __DIR__ . '/functions.php';

$config = require __DIR__ . '/config.php';

$queuePath = $config['paths']['queue'];

$files = glob($queuePath . '*.json');

foreach ($files as $file) {

    $fp = fopen($file, 'r+');

    if (!$fp) {
        logMessage('ERROR', 'Unable to open file', ['file' => $file]);
        continue;
    }

    // 🔒 Lock file to prevent double processing
    if (!flock($fp, LOCK_EX | LOCK_NB)) {
        fclose($fp);
        continue; // skip if already being processed
    }

    $content = json_decode(file_get_contents($file), true);

    if (!$content) {
        logMessage('ERROR', 'Invalid JSON in queue file', ['file' => $file]);
        flock($fp, LOCK_UN);
        fclose($fp);
        continue;
    }

    // Skip already successful jobs
    if ($content['status'] === 'SUCCESS') {
        unlink($file);
        flock($fp, LOCK_UN);
        fclose($fp);
        continue;
    }

    logMessage('INFO', 'Processing job', ['id' => $content['id']]);

    // Update status to PROCESSING
    $content['status'] = 'PROCESSING';
    file_put_contents($file, json_encode($content));

    $response = false;

    try {
        // 🔁 Handle different job types
        switch ($content['type']) {

            case 'BOOKING':
                $response = sendToCloud('/bookings', $content['data']);
                break;

            case 'CANCEL':
                $response = sendToCloud('/cancel', $content['data']);
                break;

            default:
                logMessage('WARNING', 'Unknown job type', ['type' => $content['type']]);
                break;
        }

        if ($response && isset($response['success']) && $response['success'] === true) {

            logMessage('INFO', 'Job successful', [
                'id' => $content['id'],
                'response' => $response
            ]);

            // Mark as success and delete
            unlink($file);

        } else {

            throw new Exception('Invalid API response');
        }

    } catch (Exception $e) {

        $content['retries'] += 1;

        logMessage('ERROR', 'Job failed', [
            'id' => $content['id'],
            'error' => $e->getMessage(),
            'retries' => $content['retries']
        ]);

        // Retry limit
        if ($content['retries'] >= 5) {

            $content['status'] = 'FAILED';

            file_put_contents($file, json_encode($content));

            logMessage('ERROR', 'Job permanently failed', [
                'id' => $content['id']
            ]);

        } else {

            $content['status'] = 'PENDING';

            file_put_contents($file, json_encode($content));
        }
    }

    flock($fp, LOCK_UN);
    fclose($fp);
}
