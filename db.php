<?php
$host = "localhost";
$user = "root";       // Sesuaikan username database anda
$pass = "";           // Sesuaikan password database anda
$db   = "ipam_db";    // Sesuaikan nama database anda

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
?>
