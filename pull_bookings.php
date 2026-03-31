<?php

require 'functions.php';

$bookings = getFromCloud('/bookings/pending');

if (!$bookings) {
    echo "No bookings\n";
    exit;
}

foreach ($bookings as $booking) {
    queueBooking($booking);
}

echo "Bookings queued\n";
