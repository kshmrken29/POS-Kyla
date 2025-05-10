<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Input Purchase Details</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="inventory-styles.css">
</head>
<body>
  <!-- Mobile menu toggle button -->
  <div class="menu-toggle d-lg-none">
    <i class="fas fa-bars"></i>
  </div>

  <!-- Side Navigation -->
  <div class="side-nav">
    <div class="logo-wrapper">
      <div class="logo">Restaurant Admin</div>
    </div>
    <ul class="nav-links">
      <li class="nav-item">
        <a href="../index.php" class="nav-link">
          <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
      </li>
      <span>Menu Management</span>

      <li class="nav-item">
        <a href="../MenuManagement/input-daily-menu.php" class="nav-link">
          <i class="fas fa-utensils"></i> Input Daily Menu
        </a>
      </li>
      <li class="nav-item">
        <a href="../MenuManagement/edit-menu-details.php" class="nav-link">
          <i class="fas fa-edit"></i> Edit Menu Details
        </a>
      </li>
      <li class="nav-item">
        <a href="../MenuManagement/monitor-menu-sales.php" class="nav-link">
          <i class="fas fa-chart-line"></i> Monitor Sales
        </a>
      </li>
      
      <li class="nav-item">
        <a href="../MenuManagement/sales-reporting.php" class="nav-link">
          <i class="fas fa-file-invoice-dollar"></i> Sales Reporting
        </a>
      </li>
      
      <span>Inventory Management</span>
      
      <li class="nav-item">
        <a href="input-purchase-details.php" class="nav-link active">
          <i class="fas fa-shopping-cart"></i> Purchase Details
        </a>
      </li>
      <li class="nav-item">
        <a href="input-daily-usage.php" class="nav-link">
          <i class="fas fa-clipboard-list"></i> Daily Usage
        </a>
      </li>
      <li class="nav-item">
        <a href="remaining-stock-view.php" class="nav-link">
          <i class="fas fa-boxes"></i> Stock View
        </a>
      </li>
      <span>Other</span>

      <li class="nav-item">
        <a href="../manage-cashier.php" class="nav-link">
          <i class="fas fa-users"></i> Manage Cashiers
        </a>
      </li>
      <li class="nav-item">
        <a href="../process-void-requests.php" class="nav-link">
          <i class="fas fa-ban"></i> Void Requests
        </a>
      </li>
      
      <li class="nav-item">
        <a href="../logout.php" class="nav-link">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="page-header">
      <h1 class="page-title">Inventory Purchase Management</h1>
    </div>

    <?php
    // Include database connection
    include '../connection.php';

    // Initialize variables
    $has_items = false;
    $items_result = null;

    try {
        // Check if form is submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // First check if the inventory_items table exists
            $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'inventory_items'");
            $items_table_exists = mysqli_num_rows($table_check) > 0;
            
            $purchases_check = mysqli_query($conn, "SHOW TABLES LIKE 'inventory_purchases'");
            $purchases_table_exists = mysqli_num_rows($purchases_check) > 0;
            
            if (!$items_table_exists || !$purchases_table_exists) {
                echo '<div class="alert alert-danger" role="alert"><i class="fas fa-database me-2"></i>Inventory tables not set up. Please run <a href="../../create_tables.php">create_tables.php</a> first.</div>';
            } else {
                // Check if a new item is being added
                if (isset($_POST['item_name']) && !empty($_POST['item_name'])) {
                    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
                    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
                    $quantity = floatval($_POST['quantity']);
                    $total_price = floatval($_POST['total_price']);
                    $date_purchased = $_POST['date_purchased'];

                    // Check if item already exists
                    $check_sql = "SELECT id FROM inventory_items WHERE item_name = '$item_name'";
                    $check_result = mysqli_query($conn, $check_sql);

                    if (mysqli_num_rows($check_result) > 0) {
                        echo '<div class="alert alert-warning" role="alert"><i class="fas fa-exclamation-triangle me-2"></i>Item already exists in inventory!</div>';
                        $item_id = mysqli_fetch_assoc($check_result)['id'];
                    } else {
                        // Insert new inventory item
                        $item_sql = "INSERT INTO inventory_items (item_name, description, current_stock, created_at) VALUES ('$item_name', '$description', 0, NOW())";
                        if (mysqli_query($conn, $item_sql)) {
                            echo '<div class="alert alert-success" role="alert"><i class="fas fa-check-circle me-2"></i>New inventory item added successfully!</div>';
                            $item_id = mysqli_insert_id($conn);
                        } else {
                            echo '<div class="alert alert-danger" role="alert"><i class="fas fa-times-circle me-2"></i>Error adding item: ' . mysqli_error($conn) . '</div>';
                        }
                    }

                    // Add purchase details
                    if (isset($item_id)) {
                        // Check if a similar purchase already exists on the same date
                        $check_duplicate = "SELECT * FROM inventory_purchases 
                                          WHERE item_id = $item_id 
                                          AND quantity_purchased = $quantity 
                                          AND date_purchased = '$date_purchased'";
                        $duplicate_result = mysqli_query($conn, $check_duplicate);
                        
                        if (mysqli_num_rows($duplicate_result) > 0) {
                            echo '<div class="alert alert-warning" role="alert"><i class="fas fa-exclamation-triangle me-2"></i>A similar purchase for this item with the same quantity already exists for the selected date. Please verify if this is a duplicate entry.</div>';
                        } else {
                            $purchase_sql = "INSERT INTO inventory_purchases (item_id, quantity_purchased, total_price, date_purchased) 
                                            VALUES ($item_id, $quantity, $total_price, '$date_purchased')";
                            
                            if (mysqli_query($conn, $purchase_sql)) {
                                echo '<div class="alert alert-success" role="alert"><i class="fas fa-check-circle me-2"></i>Purchase details added successfully!</div>';
                                
                                // Update current_stock in inventory_items
                                $update_stock = "UPDATE inventory_items SET current_stock = current_stock + $quantity WHERE id = $item_id";
                                mysqli_query($conn, $update_stock);
                                
                                // Clear form after successful submission
                                echo '<script>
                                    document.addEventListener("DOMContentLoaded", function() {
                                        document.getElementById("new-purchase-form").reset();
                                    });
                                </script>';
                            } else {
                                echo '<div class="alert alert-danger" role="alert"><i class="fas fa-times-circle me-2"></i>Error adding purchase details: ' . mysqli_error($conn) . '</div>';
                            }
                        }
                    }
                } else if (isset($_POST['existing_item_id']) && !empty($_POST['existing_item_id'])) {
                    // Add purchase details for existing item
                    $item_id = $_POST['existing_item_id'];
                    $quantity = floatval($_POST['quantity']);
                    $total_price = floatval($_POST['total_price']);
                    $date_purchased = $_POST['date_purchased'];

                    // Check if a similar purchase already exists on the same date
                    $check_duplicate = "SELECT * FROM inventory_purchases 
                                      WHERE item_id = $item_id 
                                      AND quantity_purchased = $quantity 
                                      AND date_purchased = '$date_purchased'";
                    $duplicate_result = mysqli_query($conn, $check_duplicate);
                    
                    if (mysqli_num_rows($duplicate_result) > 0) {
                        echo '<div class="alert alert-warning" role="alert"><i class="fas fa-exclamation-triangle me-2"></i>A similar purchase for this item with the same quantity already exists for the selected date. Please verify if this is a duplicate entry.</div>';
                    } else {
                        $purchase_sql = "INSERT INTO inventory_purchases (item_id, quantity_purchased, total_price, date_purchased) 
                                        VALUES ($item_id, $quantity, $total_price, '$date_purchased')";
                        
                        if (mysqli_query($conn, $purchase_sql)) {
                            echo '<div class="alert alert-success" role="alert"><i class="fas fa-check-circle me-2"></i>Purchase details added successfully!</div>';
                            
                            // Update current_stock in inventory_items
                            $update_stock = "UPDATE inventory_items SET current_stock = current_stock + $quantity WHERE id = $item_id";
                            mysqli_query($conn, $update_stock);
                            
                            // Clear form after successful submission
                            echo '<script>
                                document.addEventListener("DOMContentLoaded", function() {
                                    document.getElementById("existing-purchase-form").reset();
                                });
                            </script>';
                        } else {
                            echo '<div class="alert alert-danger" role="alert"><i class="fas fa-times-circle me-2"></i>Error adding purchase details: ' . mysqli_error($conn) . '</div>';
                        }
                    }
                }
            }
        }

        // Check if inventory_items table exists before querying it
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'inventory_items'");
        if (mysqli_num_rows($table_check) > 0) {
            // Get existing inventory items
            $items_sql = "SELECT id, item_name FROM inventory_items ORDER BY item_name";
            $items_result = mysqli_query($conn, $items_sql);
            
            // Check if we have any inventory items
            $has_items = $items_result && mysqli_num_rows($items_result) > 0;
        } else {
            echo '<div class="alert alert-warning" role="alert"><i class="fas fa-exclamation-triangle me-2"></i>Inventory tables not found. Please run <a href="../../create_tables.php">create_tables.php</a> first.</div>';
        }
    } catch (Exception $e) {
        echo '<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle me-2"></i>An error occurred: ' . $e->getMessage() . '</div>';
    }
    ?>

    <div class="row">
      <div class="col-lg-5">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title"><i class="fas fa-cart-plus me-2"></i>Add New Purchase</h5>
          </div>
          <div class="card-body">
            <?php if (isset($table_check) && mysqli_num_rows($table_check) > 0): ?>
              <ul class="nav nav-tabs" id="purchaseTabs" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="existing-tab" data-bs-toggle="tab" data-bs-target="#existing" type="button" role="tab">
                    <i class="fas fa-clipboard-list me-2"></i>Existing Item
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="new-tab" data-bs-toggle="tab" data-bs-target="#new" type="button" role="tab">
                    <i class="fas fa-plus-circle me-2"></i>New Item
                  </button>
                </li>
              </ul>

              <div class="tab-content p-3" id="purchaseTabsContent">
                <!-- Existing Item Form -->
                <div class="tab-pane fade show active" id="existing" role="tabpanel">
                  <?php if ($has_items): ?>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="existing-purchase-form">
                      <div class="mb-3">
                        <label for="existing_item_id" class="form-label">Select Item</label>
                        <select class="form-select" id="existing_item_id" name="existing_item_id" required>
                          <option value="">-- Select Item --</option>
                          <?php 
                          // Reset the result pointer
                          if ($items_result) {
                            mysqli_data_seek($items_result, 0);
                            while ($item = mysqli_fetch_assoc($items_result)): 
                          ?>
                            <option value="<?php echo $item['id']; ?>"><?php echo $item['item_name']; ?></option>
                          <?php 
                            endwhile;
                          }
                          ?>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity Purchased</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" step="0.01" required>
                      </div>
                      <div class="mb-3">
                        <label for="total_price" class="form-label">Total Purchase Price</label>
                        <div class="input-group">
                          <span class="input-group-text">₱</span>
                          <input type="number" class="form-control" id="total_price" name="total_price" step="0.01" required>
                        </div>
                      </div>
                      <div class="mb-3">
                        <label for="date_purchased" class="form-label">Date Purchased</label>
                        <input type="date" class="form-control" id="date_purchased" name="date_purchased" value="<?php echo date('Y-m-d'); ?>" required>
                      </div>
                      <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Purchase</button>
                    </form>
                  <?php else: ?>
                    <div class="alert alert-info">
                      <i class="fas fa-info-circle me-2"></i>
                      No inventory items found. Please add a new item first using the "New Item" tab.
                    </div>
                  <?php endif; ?>
                </div>

                <!-- New Item Form -->
                <div class="tab-pane fade" id="new" role="tabpanel">
                  <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="new-purchase-form">
                    <div class="mb-3">
                      <label for="item_name" class="form-label">Item Name</label>
                      <input type="text" class="form-control" id="item_name" name="item_name" required>
                    </div>
                    <div class="mb-3">
                      <label for="description" class="form-label">Description</label>
                      <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                      <label for="quantity" class="form-label">Quantity Purchased</label>
                      <input type="number" class="form-control" id="quantity" name="quantity" step="0.01" required>
                    </div>
                    <div class="mb-3">
                      <label for="total_price" class="form-label">Total Purchase Price</label>
                      <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" class="form-control" id="total_price" name="total_price" step="0.01" required>
                      </div>
                    </div>
                    <div class="mb-3">
                      <label for="date_purchased" class="form-label">Date Purchased</label>
                      <input type="date" class="form-control" id="date_purchased" name="date_purchased" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save New Item & Purchase</button>
                  </form>
                </div>
              </div>
            <?php else: ?>
              <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Database tables are not set up. Please run <a href="../../create_tables.php">create_tables.php</a> first.
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-lg-7">
        <!-- Recent Purchases Table -->
        <div class="card">
          <div class="card-header">
            <h5 class="card-title"><i class="fas fa-history me-2"></i>Recent Purchases</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Date Purchased</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  try {
                      // Check if tables exist
                      $items_check = mysqli_query($conn, "SHOW TABLES LIKE 'inventory_items'");
                      $purchases_check = mysqli_query($conn, "SHOW TABLES LIKE 'inventory_purchases'");
                      
                      $items_exist = mysqli_num_rows($items_check) > 0;
                      $purchases_exist = mysqli_num_rows($purchases_check) > 0;
                      
                      if ($items_exist && $purchases_exist) {
                          // Check if we have any records
                          $count_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM inventory_purchases");
                          $count = mysqli_fetch_assoc($count_query)['count'];
                          
                          if ($count > 0) {
                              $recent_sql = "SELECT 
                                              i.item_name,
                                              p.quantity_purchased,
                                              p.total_price,
                                              p.date_purchased
                                            FROM 
                                              inventory_purchases p
                                            JOIN 
                                              inventory_items i ON p.item_id = i.id
                                            ORDER BY 
                                              p.date_purchased DESC,
                                              p.id DESC
                                            LIMIT 15";
                              
                              $recent_result = mysqli_query($conn, $recent_sql);
                              
                              if ($recent_result && mysqli_num_rows($recent_result) > 0) {
                                  while ($row = mysqli_fetch_assoc($recent_result)) {
                                      echo "<tr>";
                                      echo "<td><strong>" . $row['item_name'] . "</strong></td>";
                                      echo "<td>" . number_format($row['quantity_purchased'], 2) . "</td>";
                                      echo "<td>₱" . number_format($row['total_price'], 2) . "</td>";
                                      echo "<td>" . date('M d, Y', strtotime($row['date_purchased'])) . "</td>";
                                      echo "</tr>";
                                  }
                              }
                          } else {
                              echo "<tr><td colspan='4' class='text-center'>No purchase records found. Add a purchase to see records here.</td></tr>";
                          }
                      } else {
                          echo "<tr><td colspan='4' class='text-center'>Inventory tables not set up. Please run <a href='../../create_tables.php'>create_tables.php</a> first.</td></tr>";
                      }
                  } catch (Exception $e) {
                      echo "<tr><td colspan='4' class='text-center'>Error: " . $e->getMessage() . "</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Toggle mobile menu
    document.querySelector('.menu-toggle').addEventListener('click', function() {
      document.querySelector('.side-nav').classList.toggle('show');
    });
  </script>
</body>
</html>
