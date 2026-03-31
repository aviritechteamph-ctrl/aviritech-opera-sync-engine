<?php

function insertReservation($conn, $booking) {

    // ⚠️ THIS IS A PLACEHOLDER — MUST MATCH OPERA TABLES
    $query = "INSERT INTO reservations 
              (guest_name, room_type, checkin_date, checkout_date)
              VALUES (:name, :room, :checkin, :checkout)";

    $stid = oci_parse($conn, $query);

    oci_bind_by_name($stid, ":name", $booking['name']);
    oci_bind_by_name($stid, ":room", $booking['room']);
    oci_bind_by_name($stid, ":checkin", $booking['checkin']);
    oci_bind_by_name($stid, ":checkout", $booking['checkout']);

    $result = oci_execute($stid);

    if (!$result) {
        $e = oci_error($stid);
        logError($e['message']);
    }

    return $result;
}
