<?php

require 'db.php';
require 'functions.php';

$conn = getDBConnection();

if (!$conn) exit;

// ⚠️ CHANGE THIS QUERY BASED ON OPERA DB
$query = "SELECT reservation_id, guest_name, room_type, checkin_date, checkout_date 
          FROM reservations 
          WHERE last_updated > SYSDATE - 1";

$stid = oci_parse($conn, $query);
oci_execute($stid);

$data = [];

while ($row = oci_fetch_assoc($stid)) {
    $data[] = $row;
}

sendToCloud('/sync/reservations', $data);

echo "Reservations synced\n";
