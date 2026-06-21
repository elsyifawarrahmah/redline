<?php
$conn = new mysqli("localhost", "root", "", "redline");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>