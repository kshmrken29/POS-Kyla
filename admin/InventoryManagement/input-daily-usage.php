<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Input Daily Usage</title>
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
        <a href="input-purchase-details.php" class="nav-link">
          <i class="fas fa-shopping-cart"></i> Purchase Details
        </a>
      </li>
      <li class="nav-item">
        <a href="input-daily-usage.php" class="nav-link active">
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
      <h1 class="page-title">Record Daily Inventory Usage</h1>
    </div>

    <?php
    // Include database connection
    include '../connection.php';

    // Initialize variables
    $has_items = false;
    $items_result = null;

    try {
        // Check if tables exist
        $tables_check = mysqli_query($conn, "SHOW TABLES LIKE 'inventory_items'");
        $tables_exist = mysqli_num_rows($tables_check) > 0;
        
        // Check if form is submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // First verify tables exist
            if ($tables_exist) {
                $usage_check = mysqli_query($conn, "SHOW TABLES LIKE 'inventory_usage'");
                $usage_exists = mysqli_num_rows($usage_check) > 0;
                
                if ($usage_exists) {
                    $item_id = $_POST['item_id'];
                    $quantity = $_POST['quantity'];
                    $date_used = $_POST['date_used'];
                    
                    // Insert usage record
                    $sql = "INSERT INTO inventory_usage (item_id, quantity_used, date_used) 
                            VALUES ($item_id, $quantity, '$date_used')";
                    
                    if (mysqli_query($conn, $sql)) {
                        echo '<div class="alert alert-success" role="alert"><i class="fas fa-check-circle me-2"></i>Usage recorded successfully!</div>';
                        
                        // Update current_stock in inventory_items
                        $update_stock = "UPDATE inventory_items SET current_stock = current_stock - $quantity WHERE id = $item_id";
                        mysqli_query($conn, $update_stock);
                    } else {
                        echo '<div class="alert alert-danger" role="alert"><i class="fas fa-times-circle me-2"></i>Error recording usage: ' . mysqli_error($conn) . '</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger" role="alert"><i class="fas fa-database me-2"></i>Database tables not set up correctly. Please run <a href="../../create_tables.php">create_tables.php</a> first.</div>';
                }
            } else {
                echo '<div class="alert alert-danger" role="alert"><i class="fas fa-database me-2"></i>Database tables not set up correctly. Please run <a href="../../create_tables.php">create_tables.php</a> first.</div>';
            }
        }

        // Get inventory items if tables exist
        if ($tables_exist) {
            $items_sql = "SELECT id, item_name FROM inventory_items ORDER BY item_name";
            $items_result = mysqli_query($conn, $items_sql);
            
            // Check if we have any inventory items
            $has_items = mysqli_num_rows($items_result) > 0;
        }
    } catch (Exception $e) {
        echo '<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle me-2"></i>An error occurred: ' . $e->getMessage() . '</div>';
    }
    ?>

    <div class="row">
      <div class="col-lg-5">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title"><i class="fas fa-clipboard-list me-2"></i>Record Daily Usage</h5>
          </div>
          <div class="card-body">
            <?php if ($has_items): ?>
              <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="mb-3">
                  <label for="item_id" class="form-label">Select Item</label>
                  <select class="form-select" id="item_id" name="item_id" required>
                    <option value="">-- Select Item --</option>
                    <?php 
                    // Reset the result pointer
                    mysqli_data_seek($items_result, 0);
                    while ($item = mysqli_fetch_assoc($items_result)): 
                    ?>
                      <option value="<?php echo $item['id']; ?>"><?php echo $item['item_name']; ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="quantity" class="form-label">Quantity Used</label>
                  <input type="number" class="form-control" id="quantity" name="quantity" step="0.01" required>
                </div>
                <div class="mb-3">
                  <label for="date_used" class="form-label">Date Used</label>
                  <input type="date" class="form-control" id="date_used" name="date_used" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Record Usage</button>
              </form>
            <?php else: ?>
              <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No inventory items found. Please <a href="input-purchase-details.php">add inventory items</a> first or run <a href="../../create_tables.php">create_tables.php</a> to set up the database.
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-lg-7">
        <!-- Recent Usage Table -->
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title"><i class="fas fa-history me-2"></i>Recent Usage Records</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Item Name</th>
                    <th>Quantity Used</th>
                    <th>Date Used</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  try {
                      // Check if both tables exist and have proper structure
                      $items_exist = mysqli_query($conn, "SHOW TABLES LIKE 'inventory_items'");
                      $usage_exist = mysqli_query($conn, "SHOW TABLES LIKE 'inventory_usage'");
                      
                      if (mysqli_num_rows($items_exist) > 0 && mysqli_num_rows($usage_exist) > 0) {
                          // Check if we have any records in both tables
                          $count_items = mysqli_query($conn, "SELECT COUNT(*) as count FROM inventory_items");
                          $count_usage = mysqli_query($conn, "SELECT COUNT(*) as count FROM inventory_usage");
                          
                          $have_items = mysqli_fetch_assoc($count_items)['count'] > 0;
                          $have_usage = mysqli_fetch_assoc($count_usage)['count'] > 0;
                          
                          if ($have_items && $have_usage) {
                              // Safe to run the full query
                              $recent_usage_sql = "SELECT i.item_name, u.quantity_used, u.date_used 
                                                  FROM inventory_usage u
                                                  JOIN inventory_items i ON u.item_id = i.id
                                                  ORDER BY u.date_used DESC, u.id DESC
                                                  LIMIT 10";
                              $recent_usage_result = mysqli_query($conn, $recent_usage_sql);
                              
                              if ($recent_usage_result && mysqli_num_rows($recent_usage_result) > 0) {
                                  while ($row = mysqli_fetch_assoc($recent_usage_result)) {
                                      echo "<tr>";
                                      echo "<td><strong>" . $row['item_name'] . "</strong></td>";
                                      echo "<td>" . number_format($row['quantity_used'], 2) . "</td>";
                                      echo "<td>" . date('M d, Y', strtotime($row['date_used'])) . "</td>";
                                      echo "</tr>";
                                  }
                              } else {
                                  echo "<tr><td colspan='3' class='text-center'>No usage records found</td></tr>";
                              }
                          } else {
                              echo "<tr><td colspan='3' class='text-center'>No usage records found. Please record some inventory usage.</td></tr>";
                          }
                      } else {
                          echo "<tr><td colspan='3' class='text-center'>Please run <a href='../../create_tables.php'>create_tables.php</a> to set up the database tables.</td></tr>";
                      }
                  } catch (Exception $e) {
                      echo "<tr><td colspan='3' class='text-center'>Error: " . $e->getMessage() . "</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Usage Summary -->
        <div class="card">
          <div class="card-header">
            <h5 class="card-title"><i class="fas fa-chart-bar me-2"></i>Usage Summary (Last 7 Days)</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Item Name</th>
                    <th>Total Quantity Used</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  try {
                      // Check if both tables exist and have proper structure
                      $items_exist = mysqli_query($conn, "SHOW TABLES LIKE 'inventory_items'");
                      $usage_exist = mysqli_query($conn, "SHOW TABLES LIKE 'inventory_usage'");
                      
                      if (mysqli_num_rows($items_exist) > 0 && mysqli_num_rows($usage_exist) > 0) {
                          // Check if we have any records in both tables
                          $count_items = mysqli_query($conn, "SELECT COUNT(*) as count FROM inventory_items");
                          $count_usage = mysqli_query($conn, "SELECT COUNT(*) as count FROM inventory_usage");
                          
                          $have_items = mysqli_fetch_assoc($count_items)['count'] > 0;
                          $have_usage = mysqli_fetch_assoc($count_usage)['count'] > 0;
                          
                          if ($have_items && $have_usage) {
                              $today = date('Y-m-d');
                              $week_ago = date('Y-m-d', strtotime('-7 days'));
                              
                              $summary_sql = "SELECT i.item_name, SUM(u.quantity_used) as total_used 
                                              FROM inventory_usage u
                                              JOIN inventory_items i ON u.item_id = i.id
                                              WHERE u.date_used BETWEEN '$week_ago' AND '$today'
                                              GROUP BY u.item_id
                                              ORDER BY total_used DESC";
                              $summary_result = mysqli_query($conn, $summary_sql);
                              
                              if ($summary_result && mysqli_num_rows($summary_result) > 0) {
                                  while ($row = mysqli_fetch_assoc($summary_result)) {
                                      echo "<tr>";
                                      echo "<td><strong>" . $row['item_name'] . "</strong></td>";
                                      echo "<td>" . number_format($row['total_used'], 2) . "</td>";
                                      echo "</tr>";
                                  }
                              } else {
                                  echo "<tr><td colspan='2' class='text-center'>No usage data found for the last 7 days</td></tr>";
                              }
                          } else {
                              echo "<tr><td colspan='2' class='text-center'>No usage records found. Please record some inventory usage.</td></tr>";
                          }
                      } else {
                          echo "<tr><td colspan='2' class='text-center'>Please run <a href='../../create_tables.php'>create_tables.php</a> to set up the database tables.</td></tr>";
                      }
                  } catch (Exception $e) {
                      echo "<tr><td colspan='2' class='text-center'>Error: " . $e->getMessage() . "</td></tr>";
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
