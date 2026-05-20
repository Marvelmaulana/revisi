<?php

// timezone
date_default_timezone_set("Asia/Jakarta");

// base path
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_kantin";

// koneksi
$koneksi = mysqli_connect($host, $user, $pass, $db);

// cek koneksi
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

?>