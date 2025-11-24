<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tracking = $conn->real_escape_string($_POST['tracking_number']);
    $recipient_name = $conn->real_escape_string($_POST['recipient_name']);
    $recipient_address = $conn->real_escape_string($_POST['recipient_address']);
    $recipient_phone = $conn->real_escape_string($_POST['recipient_phone']);
    $description = $conn->real_escape_string($_POST['description']);
    $weight = (float)$_POST['weight_kg'];
    $service = $conn->real_escape_string($_POST['service_type']);
    $cost = (float)$_POST['shipping_cost'];
    
    $customer_id = (int)$_POST['customer_id'];
    
    // If customer_id is 0, create a new customer
    if ($customer_id == 0) {
        $new_customer_name = $conn->real_escape_string($_POST['new_customer_name']);
        $new_customer_phone = $conn->real_escape_string($_POST['new_customer_phone']);
        $new_customer_email = $conn->real_escape_string($_POST['new_customer_email']);
        $new_customer_address = $conn->real_escape_string($_POST['new_customer_address']);
        
        // Generate UUID for new customer
        $customer_uuid = 'cust-' . uniqid();
        
        $create_customer = $conn->prepare("INSERT INTO customers (customer_uuid, full_name, phone, email, address) VALUES (?, ?, ?, ?, ?)");
        $create_customer->bind_param('sssss', $customer_uuid, $new_customer_name, $new_customer_phone, $new_customer_email, $new_customer_address);
        
        if ($create_customer->execute()) {
            $customer_id = $conn->insert_id; // Get the new customer's ID
            $create_customer->close();
        } else {
            $msg = "Error creating customer: " . $conn->error;
            $create_customer->close();
        }
    }
    
    // Now insert the parcel if we have a valid customer_id
    if ($customer_id > 0) {
        $stmt = $conn->prepare("INSERT INTO parcels (tracking_number, customer_id, recipient_name, recipient_address, recipient_phone, description, weight_kg, service_type, shipping_cost) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('sissssdsd', $tracking, $customer_id, $recipient_name, $recipient_address, $recipient_phone, $description, $weight, $service, $cost);
        
        if ($stmt->execute()) {
            $msg = "Parcel created successfully! Tracking Number: $tracking";

            $conn->query("ALTER TABLE parcels AUTO_INCREMENT = 1");
        } else {
            $msg = "Error creating parcel: " . $conn->error;
        }
        $stmt->close();
    }
}

// Get all customers for the dropdown
$customers_query = $conn->query("SELECT id, full_name, customer_uuid FROM customers ORDER BY full_name");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Create Parcel</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .new-customer-fields {
            display: none;
            background: #f0f8ff;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #cce;
        }
    </style>
    <script>
        function toggleNewCustomer() {
            const select = document.getElementById('customer_select');
            const newFields = document.getElementById('new_customer_fields');
            
            if (select.value === '0') {
                newFields.style.display = 'block';
                // Make new customer fields required
                document.querySelectorAll('.new-customer-fields input[type="text"], .new-customer-fields input[type="email"]').forEach(input => {
                    input.required = true;
                });
            } else {
                newFields.style.display = 'none';
                // Make new customer fields not required
                document.querySelectorAll('.new-customer-fields input').forEach(input => {
                    input.required = false;
                });
            }
        }
    </script>
</head>
<body>
  <div class="container">
    <h1>Create Parcel</h1>
    <p><a href="index.html">Home</a> — <a href="view_parcels.php">View Parcels</a></p>
    <?php if (!empty($msg)) {
        $color = (strpos($msg, 'Error') !== false) ? 'red' : 'green';
        echo '<p style="color:'.$color.'">'.htmlspecialchars($msg).'</p>';
    } ?>
    <form method="post">
      <label>Tracking Number<input name="tracking_number" required></label><br><br>
      
      <label>Customer
        <select name="customer_id" id="customer_select" onchange="toggleNewCustomer()" required>
          <option value="">-- Select Customer --</option>
          <option value="0" style="font-weight:bold;color:#007bff;">➕ Create New Customer</option>
          <?php while($customer = $customers_query->fetch_assoc()): ?>
            <option value="<?= $customer['id'] ?>">
              <?= htmlspecialchars($customer['full_name']) ?> (ID: <?= $customer['id'] ?>)
            </option>
          <?php endwhile; ?>
        </select>
      </label><br><br>
      
      <!-- New Customer Fields (hidden by default) -->
      <div id="new_customer_fields" class="new-customer-fields">
        <h3>New Customer Information</h3>
        <label>Customer Name<input type="text" name="new_customer_name"></label><br><br>
        <label>Customer Phone<input type="text" name="new_customer_phone"></label><br><br>
        <label>Customer Email<input type="email" name="new_customer_email"></label><br><br>
        <label>Customer Address<textarea name="new_customer_address"></textarea></label><br><br>
      </div>
      
      <h3>Parcel Details</h3>
      <label>Recipient Name<input name="recipient_name" required></label><br><br>
      <label>Recipient Address<textarea name="recipient_address" required></textarea></label><br><br>
      <label>Recipient Phone<input name="recipient_phone"></label><br><br>
      <label>Description<textarea name="description"></textarea></label><br><br>
      <label>Weight (kg)<input name="weight_kg" type="number" step="0.01" value="0.5"></label><br><br>
      <label>Service Type
        <select name="service_type">
          <option>Standard</option>
          <option>Express</option>
          <option>Overnight</option>
        </select>
      </label><br><br>
      <label>Shipping Cost<input name="shipping_cost" type="number" step="0.01" value="1000"></label><br><br>
      <button type="submit">Create Parcel</button>
    </form>
  </div>
</body>
</html>