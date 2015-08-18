<?php
try {
    ini_set("error_log", "/var/log/mailparser/basic.log");
    umask(0002);

    // fetch data from stdin
    $data = array('data' => file_get_contents("php://stdin"));

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "http://localhost:40080/_mailparser");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    // receive server response ...
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec($ch);

    curl_close ($ch);
} catch (Exception $e) {
    error_log("Parser Error: " . $e->getMessage() . $e->getTraceAsString());
    // error_log("Parser Error: " . $e->getMessage() . $e->getTraceAsString() . ( $data ? "\nReceived data: " . print_r($data, true) : ""));
}
