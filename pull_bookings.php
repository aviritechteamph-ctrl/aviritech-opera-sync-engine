<?php

require __DIR__ . '/functions.php';

$bookings = getFromCloud('/bookings/pending');

if ($bookings === null) {
    logError("API fetch failed");
    exit;
}

if (empty($bookings)) {
    logError("No bookings available");
    exit;
}

logError("Fetched " . count($bookings) . " bookings");

foreach ($bookings as $booking) {

    // 🛡️ Validate booking
    if (!isset($booking['id'], $booking['guest_name'])) {
        logError("Invalid booking structure", $booking);
        continue;
    }

    // 🔁 Prevent duplicates
    if (bookingAlreadyQueued($booking['id'])) {
        continue;
    }

    // 📦 Queue properly
    queueBooking([
        'id' => $booking['id'],
        'type' => 'BOOKING',
        'data' => $booking,
        'status' => 'PENDING',
        'retries' => 0
    ]);

    // ✅ Acknowledge to cloud
    sendToCloud('/bookings/ack', [
        'id' => $booking['id']
    ]);
}

logError("Queueing completed");
