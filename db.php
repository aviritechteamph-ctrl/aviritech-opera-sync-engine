<?php

$config = require __DIR__ . '/config.php';

function getDBConnection() {
    global $config;

    if ($config['db']['type'] === 'oracle') {
        $conn = oci_connect(
            $config['db']['username'],
            $config['db']['password'],
            $config['db']['connection_string']
        );

        if (!$conn) {
            $e = oci_error();
            logError($e['message']);
            return null;
        }

        return $conn;
    }

    return null;
}
