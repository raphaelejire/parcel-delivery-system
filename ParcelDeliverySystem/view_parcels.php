<?php
require 'db.php';
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>View Parcels</title><link rel="stylesheet" href="styles.css"></head>
<body>
  <div class="container">
    <h1>Parcels</h1>
    <p><a href="index.html">Home</a> — <a href="insert_parcel.php">Create Order</a></p>
    <table style="width:100%;border-collapse:collapse;background:#fff;border:1px solid #eef5fb;padding:8px;border-radius:6px;overflow:hidden">
      <thead><tr><th>#</th><th>Tracking</th><th>Recipient</th><th>Sender</th><th>Status</th></tr></thead>
      <tbody>
<?php
$sql = "SELECT p.id, p.tracking_number, p.recipient_name, p.delivery_status, c.full_name as sender FROM parcels p JOIN customers c ON p.customer_id = c.id ORDER BY p.id ASC";
$res = $conn->query($sql);
if ($res && $res->num_rows > 0) {
    while($r = $res->fetch_assoc()) {
        echo '<tr><td>'.htmlspecialchars($r['id']).'</td><td>'.htmlspecialchars($r['tracking_number']).'</td><td>'.htmlspecialchars($r['recipient_name']).'</td><td>'.htmlspecialchars($r['sender']).'</td><td>'.htmlspecialchars($r['delivery_status']).'</td></tr>';
    }
} else {
    echo '<tr><td colspan="5">No parcels found. Run the insert_sample_data.sql in Workbench.</td></tr>';
}
$conn->close();
?>
      </tbody>
    </table>
  </div>
</body>
</html>
