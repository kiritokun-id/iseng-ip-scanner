<?php
// Hubungkan langsung ke database (sesuaikan file koneksi Anda jika diperlukan, misal db.php / config.php)
// Asumsi $conn sudah tersedia atau kita include file koneksi:
if (file_exists('db.php')) {
    include 'db.php';
} elseif (file_exists('config.php')) {
    include 'config.php';
}

header('Content-Type: application/json');

$org_id = isset($_GET['org_id']) ? intval($_GET['org_id']) : 0;
$data_asn = [];

if ($org_id > 0 && isset($conn)) {
    $res_asn = $conn->query("SELECT id, as_number, description FROM as_numbers WHERE organization_id = $org_id ORDER BY id DESC");
    if ($res_asn) {
        while ($row = $res_asn->fetch_assoc()) {
            $data_asn[] = $row;
        }
    }
}

echo json_encode($data_asn);
exit;
