<?php

require 'db.php';
require 'functions.php';

$conn = getDBConnection();

if (!$conn) exit;

// ⚠️ CHANGE THIS QUERY BASED ON OPERA DB
$query = "SELECT room_type, available_rooms, rate 
          FROM room_inventory 
          WHERE business_date = TRUNC(SYSDATE)";

$stid = oci_parse($conn, $query);
oci_execute($stid);

$data = [];

while ($row = oci_fetch_assoc($stid)) {
    $data[] = $row;
}

sendToCloud('/sync/availability', $data);

echo "Availability synced\n";
