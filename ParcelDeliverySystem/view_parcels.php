<?php
require 'db.php';
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>View Parcels</title><link rel="stylesheet" href="styles.css"></head>
<body>
  <header class="site-header">
    <div class="container">
      <h1>Parcel Delivery System</h1>
      <nav>
        <a href="index.html">Home</a>
        <a href="insert_parcel.php">Create Order</a>
        <a href="view_parcels.php">View Parcels</a>
      </nav>
    </div>
  </header>

  <main>
    <div class="container">
    <h2 style="text-align: center; color: var(--accent); margin-bottom: 24px;">All Parcel Records</h2>

    <table class="parcels-table">
      <thead><tr><th>ID</th><th>Tracking #</th><th>Recipient</th><th>Sender</th><th>Status</th></tr></thead>
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
  </main>

  <footer class="site-footer">
    <div class="container">
      Parcel Delivery System — Demo project
    </div>
  </footer>
</body>
</html>
