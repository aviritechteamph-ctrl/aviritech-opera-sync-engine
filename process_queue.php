<?php

require 'db.php';
require 'functions.php';

$config = require __DIR__ . '/config.php';

$conn = getDBConnection();

if (!$conn) exit;

$files = glob($config['paths']['queue'] . '*.json');

foreach ($files as $file) {

    $booking = json_decode(file_get_contents($file), true);

    if (insertReservation($conn, $booking)) {
        unlink($file);
    } else {
        logError("Failed to insert booking: " . $file);
    }
}
