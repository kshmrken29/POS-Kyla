<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Remaining Stock View</title>
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
        <a href="input-daily-usage.php" class="nav-link">
          <i class="fas fa-clipboard-list"></i> Daily Usage
        </a>
      </li>
      <li class="nav-item">
        <a href="remaining-stock-view.php" class="nav-link active">
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
      <h1 class="page-title">Inventory Stock View</h1>
    </div>

    <?php
    // Include database connection
    include '../connection.php';

    // Initialize variables
    $has_items = false;
    $stock_result = null;

    try {
        // Check if tables exist
        $items_check = mysqli_query($conn, "SHOW TABLES LIKE 'inventory_items'");
        $items_exist = mysqli_num_rows($items_check) > 0;
        
        if ($items_exist) {
            // First check if we have any inventory items
            $count_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM inventory_items");
            $item_count = mysqli_fetch_assoc($count_check)['count'];
            
            if ($item_count > 0) {
                // We have inventory items, get them with their current stock
                $stock_sql = "SELECT 
                            id,
                            item_name,
                            current_stock,
                            description
                        FROM 
                            inventory_items
                        ORDER BY 
                            item_name";
                
                $stock_result = mysqli_query($conn, $stock_sql);
                $has_items = mysqli_num_rows($stock_result) > 0;
            } else {
                $has_items = false;
            }
        } else {
            $has_items = false;
            echo '<div class="alert alert-warning" role="alert">Inventory items table not found. Please run <a href="../../create_tables.php">create_tables.php</a> first.</div>';
        }
    } catch (Exception $e) {
        // Handle any exception
        $has_items = false;
        echo '<div class="alert alert-danger" role="alert">Error: ' . $e->getMessage() . '</div>';
    }
    
    // Low stock threshold
    $low_stock_threshold = 10; // can be adjusted as needed
    ?>

    <!-- Stock Overview -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title"><i class="fas fa-boxes me-2"></i>Current Inventory Status</h5>
      </div>
      <div class="card-body">
        <?php if ($has_items): ?>
          <div class="mb-3">
            <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="row g-3">
              <div class="col-md-6">
                <div class="input-group">
                  <input type="text" class="form-control" name="search" placeholder="Search items..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                  <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                  <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                    <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="btn btn-secondary"><i class="fas fa-times"></i></a>
                  <?php endif; ?>
                </div>
              </div>
              <div class="col-md-6 text-end">
                <div class="form-check d-inline-block me-3">
                  <input class="form-check-input" type="checkbox" id="show_low_stock" name="show_low_stock" <?php echo isset($_GET['show_low_stock']) ? 'checked' : ''; ?> onchange="this.form.submit()">
                  <label class="form-check-label" for="show_low_stock">
                    Show Low Stock Only
                  </label>
                </div>
              </div>
            </form>
          </div>

          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Item Name</th>
                  <th>Description</th>
                  <th>Current Stock</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $has_matching_items = false;
                $search_term = isset($_GET['search']) ? $_GET['search'] : '';
                $show_low_stock = isset($_GET['show_low_stock']);
                
                // Reset the result pointer
                mysqli_data_seek($stock_result, 0);
                
                while ($item = mysqli_fetch_assoc($stock_result)) {
                  $remaining = $item['current_stock'];
                  $is_low_stock = $remaining <= $low_stock_threshold && $remaining > 0;
                  $is_out_of_stock = $remaining <= 0;
                  
                  // Apply search and low stock filters
                  if ((!empty($search_term) && stripos($item['item_name'], $search_term) === false) ||
                      ($show_low_stock && !$is_low_stock && !$is_out_of_stock)) {
                    continue;
                  }
                  
                  $has_matching_items = true;
                  
                  echo "<tr>";
                  echo "<td><strong>" . $item['item_name'] . "</strong></td>";
                  echo "<td>" . $item['description'] . "</td>";
                  echo "<td>" . number_format($item['current_stock'], 2) . "</td>";
                  
                  // Status column with new badge styles
                  echo "<td>";
                  if ($is_out_of_stock) {
                    echo "<span class='badge badge-danger'><i class='fas fa-times-circle me-1'></i>Out of Stock</span>";
                  } else if ($is_low_stock) {
                    echo "<span class='badge badge-warning'><i class='fas fa-exclamation-triangle me-1'></i>Low Stock</span>";
                  } else {
                    echo "<span class='badge badge-success'><i class='fas fa-check-circle me-1'></i>In Stock</span>";
                  }
                  echo "</td>";
                  
                  echo "</tr>";
                }
                
                if (!$has_matching_items) {
                  echo "<tr><td colspan='4' class='text-center'>No matching inventory items found</td></tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
          
        <?php else: ?>
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            No inventory items found. Please <a href="input-purchase-details.php">add inventory items</a> first or run <a href="../../create_tables.php">create_tables.php</a> to set up the database.
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Stock Movement History -->
    <div class="card">
      <div class="card-header">
        <h5 class="card-title"><i class="fas fa-history me-2"></i>Recent Stock Movement</h5>
      </div>
      <div class="card-body">
        <?php if ($has_items): ?>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Item Name</th>
                  <th>Type</th>
                  <th>Quantity</th>
                </tr>
              </thead>
              <tbody>
                <?php
                try {
                    // Check if tables exist
                    $items_check = mysqli_query($conn, "SHOW TABLES LIKE 'inventory_items'");
                    $purchases_check = mysqli_query($conn, "SHOW TABLES LIKE 'inventory_purchases'");
                    $usage_check = mysqli_query($conn, "SHOW TABLES LIKE 'inventory_usage'");
                    
                    $items_exist = mysqli_num_rows($items_check) > 0;
                    $purchases_exist = mysqli_num_rows($purchases_check) > 0;
                    $usage_exist = mysqli_num_rows($usage_check) > 0;
                    
                    // First check if inventory items table exists and has items
                    if ($items_exist) {
                        $count_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM inventory_items");
                        $item_count = mysqli_fetch_assoc($count_check)['count'];
                        
                        if ($item_count > 0) {
                            // Check if we have any purchases or usage records
                            $have_purchases = false;
                            $have_usage = false;
                            
                            if ($purchases_exist) {
                                $purchase_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM inventory_purchases");
                                $have_purchases = mysqli_fetch_assoc($purchase_count)['count'] > 0;
                            }
                            
                            if ($usage_exist) {
                                $usage_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM inventory_usage");
                                $have_usage = mysqli_fetch_assoc($usage_count)['count'] > 0;
                            }
                            
                            if ($have_purchases || $have_usage) {
                                // Create unified query for both purchases and usage
                                $movement_sql = "";
                                
                                if ($have_purchases) {
                                    $movement_sql .= "SELECT 
                                                        i.item_name,
                                                        p.quantity_purchased as quantity,
                                                        p.date_purchased as movement_date,
                                                        'Purchase' as movement_type
                                                    FROM 
                                                        inventory_purchases p
                                                    JOIN 
                                                        inventory_items i ON p.item_id = i.id";
                                }
                                
                                if ($have_usage) {
                                    if ($have_purchases) {
                                        $movement_sql .= " UNION ";
                                    }
                                    
                                    $movement_sql .= "SELECT 
                                                        i.item_name,
                                                        u.quantity_used as quantity,
                                                        u.date_used as movement_date,
                                                        'Usage' as movement_type
                                                    FROM 
                                                        inventory_usage u
                                                    JOIN 
                                                        inventory_items i ON u.item_id = i.id";
                                }
                                
                                $movement_sql .= " ORDER BY movement_date DESC, item_name LIMIT 20";
                                
                                $movement_result = mysqli_query($conn, $movement_sql);
                                
                                if ($movement_result && mysqli_num_rows($movement_result) > 0) {
                                    while ($row = mysqli_fetch_assoc($movement_result)) {
                                        echo "<tr>";
                                        echo "<td>" . date('M d, Y', strtotime($row['movement_date'])) . "</td>";
                                        echo "<td><strong>" . $row['item_name'] . "</strong></td>";
                                        
                                        // Different styling based on type
                                        if ($row['movement_type'] == 'Purchase') {
                                            echo "<td><span class='badge badge-success'><i class='fas fa-plus-circle me-1'></i>Purchase</span></td>";
                                            echo "<td>+" . number_format($row['quantity'], 2) . "</td>";
                                        } else {
                                            echo "<td><span class='badge badge-warning'><i class='fas fa-minus-circle me-1'></i>Usage</span></td>";
                                            echo "<td>-" . number_format($row['quantity'], 2) . "</td>";
                                        }
                                        
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center'>No movement records found</td></tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center'>No inventory movements recorded yet. Add purchases or usage to see activity.</td></tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center'>No inventory items found. Please add items first.</td></tr>";
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
        <?php else: ?>
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            No inventory items found. Please <a href="input-purchase-details.php">add inventory items</a> first.
          </div>
        <?php endif; ?>
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
