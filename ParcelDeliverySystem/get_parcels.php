<?php
header('Content-Type: application/json; charset=utf-8');
require 'db.php';

$sql = "SELECT p.id, p.tracking_number, c.full_name AS sender, p.recipient_name, p.delivery_status
        FROM parcels p
        JOIN customers c ON p.customer_id = c.id
        ORDER BY p.order_datetime DESC
        LIMIT 50";

$result = $conn->query($sql);
$out = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        $out[] = $row;
    }
}
echo json_encode($out);
$conn->close();
?>