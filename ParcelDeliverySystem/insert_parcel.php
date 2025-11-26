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
    <h2 style="text-align: center; color: var(--accent); margin-bottom: 24px;">Create New Parcel Order</h2>
    <?php if (!empty($msg)) {
        $isError = (strpos($msg, 'Error') !== false);
        $messageClass = $isError ? 'message-error' : 'message-success';
        echo "<div class='message $messageClass'>" . htmlspecialchars($msg) . "</div>";
    } ?>
    <form method="post">
      <fieldset>
        <legend>Sender Information</legend>
        <div class="form-group">
          <label for="customer_select">Customer (Sender)</label>
          <select name="customer_id" id="customer_select" onchange="toggleNewCustomer()" required>
            <option value="">-- Select Existing Customer --</option>
            <option value="0" style="font-weight:bold;color:#007bff;">➕ Create New Customer</option>
            <?php while($customer = $customers_query->fetch_assoc()): ?>
              <option value="<?= $customer['id'] ?>">
                <?= htmlspecialchars($customer['full_name']) ?> (ID: <?= $customer['id'] ?>)
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        
        <div id="new_customer_fields" class="new-customer-fields">
          <h4>New Customer Details</h4>
          <div class="form-group"><label for="new_customer_name">Customer Name</label><input type="text" id="new_customer_name" name="new_customer_name"></div>
          <div class="form-group"><label for="new_customer_phone">Customer Phone</label><input type="text" id="new_customer_phone" name="new_customer_phone"></div>
          <div class="form-group"><label for="new_customer_email">Customer Email</label><input type="email" id="new_customer_email" name="new_customer_email"></div>
          <div class="form-group"><label for="new_customer_address">Customer Address</label><textarea id="new_customer_address" name="new_customer_address"></textarea></div>
        </div>
      </fieldset>

      <fieldset>
        <legend>Recipient Information</legend>
        <div class="form-group"><label for="recipient_name">Recipient Name</label><input id="recipient_name" name="recipient_name" required></div>
        <div class="form-group"><label for="recipient_address">Recipient Address</label><textarea id="recipient_address" name="recipient_address" required></textarea></div>
        <div class="form-group"><label for="recipient_phone">Recipient Phone</label><input id="recipient_phone" name="recipient_phone"></div>
      </fieldset>

      <fieldset>
        <legend>Parcel Details</legend>
        <div class="form-group"><label for="tracking_number">Tracking Number</label><input id="tracking_number" name="tracking_number" required></div>
        <div class="form-group"><label for="description">Description</label><textarea id="description" name="description"></textarea></div>
        <div class="form-group"><label for="weight_kg">Weight (kg)</label><input id="weight_kg" name="weight_kg" type="number" step="0.01" value="0.5"></div>
        <div class="form-group">
          <label for="service_type">Service Type</label>
          <select id="service_type" name="service_type">
            <option>Standard</option>
            <option>Express</option>
            <option>Overnight</option>
          </select>
        </div>
        <div class="form-group"><label for="shipping_cost">Shipping Cost</label><input id="shipping_cost" name="shipping_cost" type="number" step="0.01" value="1000"></div>
      </fieldset>

      <div class="form-buttons">
        <button type="submit">Create Parcel</button>
        <button type="reset" class="btn" style="background-color: #6c757d;">Cancel</button>
      </div>
    </form>
  </div>
  </main>

  <footer class="site-footer">
    <div class="container">Parcel Delivery System — Demo project</div>
  </footer>

</body>
</html>