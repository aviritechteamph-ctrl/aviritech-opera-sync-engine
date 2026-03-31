<?php

require __DIR__ . '/functions.php';

$config = require __DIR__ . '/config.php';

$queuePath = $config['paths']['queue'];
$files = glob($queuePath . '*.json');

foreach ($files as $file) {

    $fp = fopen($file, 'c+');

    if (!$fp) {
        logError("Unable to open file: $file");
        continue;
    }

    // 🔒 Acquire lock
    if (!flock($fp, LOCK_EX | LOCK_NB)) {
        fclose($fp);
        continue;
    }

    // Read file safely
    $raw = stream_get_contents($fp);
    rewind($fp);

    $content = json_decode($raw, true);

    if (!$content) {
        logError("Invalid JSON in file: $file");
        flock($fp, LOCK_UN);
        fclose($fp);
        continue;
    }

    // 🛡️ Normalize structure
    $content['id'] = $content['id'] ?? uniqid('job_');
    $content['type'] = $content['type'] ?? 'UNKNOWN';
    $content['status'] = $content['status'] ?? 'PENDING';
    $content['retries'] = $content['retries'] ?? 0;
    $content['next_attempt_at'] = $content['next_attempt_at'] ?? 0;

    // ⏱ Retry delay check
    if (time() < $content['next_attempt_at']) {
        flock($fp, LOCK_UN);
        fclose($fp);
        continue;
    }

    // Skip already completed
    if ($content['status'] === 'SUCCESS') {
        flock($fp, LOCK_UN);
        fclose($fp);
        unlink($file);
        continue;
    }

    echo "Processing {$content['id']}...\n";

    // Update status → PROCESSING
    $content['status'] = 'PROCESSING';

    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($content));
    fflush($fp);

    $response = null;
    $success = false;

    try {

        switch ($content['type']) {

            case 'BOOKING':
                $response = sendToCloud('/bookings', $content['data']);
                break;

            case 'CANCEL':
                $response = sendToCloud('/cancel', $content['data']);
                break;

            default:
                throw new Exception("Unknown job type: {$content['type']}");
        }

        // Decode response safely
        $decoded = json_decode($response, true);

        if ($decoded && isset($decoded['success']) && $decoded['success'] === true) {
            $success = true;
        } else {
            throw new Exception("Invalid API response");
        }

    } catch (Exception $e) {

        $content['retries']++;

        logError("Job failed: {$content['id']} | Error: " . $e->getMessage());

        // Retry strategy (exponential backoff)
        $delay = min(300, pow(2, $content['retries']) * 5); // max 5 mins
        $content['next_attempt_at'] = time() + $delay;

        if ($content['retries'] >= 5) {
            $content['status'] = 'FAILED';

            logError("Job permanently failed: {$content['id']}");
        } else {
            $content['status'] = 'PENDING';
        }

        // Save updated state
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($content));
        fflush($fp);

        flock($fp, LOCK_UN);
        fclose($fp);
        continue;
    }

    // ✅ SUCCESS FLOW
    if ($success) {

        logError("Job success: {$content['id']}");

        flock($fp, LOCK_UN);
        fclose($fp);

        unlink($file); // delete after unlock
    }
}
