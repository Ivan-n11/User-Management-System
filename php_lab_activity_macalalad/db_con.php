<?php
    $host = "localhost";
    $username = "root";
    $password = "";
    $dbname = "php_db";

    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("connection error". $conn->connect_error);
    }

?>