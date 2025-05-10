<?php
// Include authentication system
require_once '../auth_session.php';
require_admin();

// Log that admin dashboard was accessed
log_activity('accessed admin dashboard');

include 'connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <div class="side-nav">
    <div class="logo-wrapper">
      <div class="logo">Restaurant Admin</div>
    </div>

    
    
    <ul class="nav-links">
      <li class="nav-item">
        <a href="index.php" class="nav-link active">
          <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
      </li>
      <span>Menu Management</span>

      <li class="nav-item">
        <a href="MenuManagement/input-daily-menu.php" class="nav-link">
          <i class="fas fa-utensils"></i> Input Daily Menu
        </a>
      </li>
      <li class="nav-item">
        <a href="MenuManagement/edit-menu-details.php" class="nav-link">
          <i class="fas fa-edit"></i> Edit Menu Details
        </a>
      </li>
      <li class="nav-item">
        <a href="MenuManagement/monitor-menu-sales.php" class="nav-link">
          <i class="fas fa-chart-line"></i> Monitor Sales
        </a>
      </li>
      
      <li class="nav-item">
        <a href="./MenuManagement/sales-reporting.php" class="nav-link">
          <i class="fas fa-file-invoice-dollar"></i> Sales Reporting
        </a>
      </li>
      
      <span>Inventory Management</span>
      
      <li class="nav-item">
        <a href="InventoryManagement/input-purchase-details.php" class="nav-link">
          <i class="fas fa-shopping-cart"></i> Purchase Details
        </a>
      </li>
      <li class="nav-item">
        <a href="InventoryManagement/input-daily-usage.php" class="nav-link">
          <i class="fas fa-clipboard-list"></i> Daily Usage
        </a>
      </li>
      <li class="nav-item">
        <a href="InventoryManagement/remaining-stock-view.php" class="nav-link">
          <i class="fas fa-boxes"></i> Stock View
        </a>
      </li>
      <span>Other</span>
      <li class="nav-item">
        <a href="manage-cashier.php" class="nav-link">
          <i class="fas fa-users"></i> Manage Cashiers
        </a>
      </li>
      <li class="nav-item">
        <a href="process-void-requests.php" class="nav-link">
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

  <div class="main-content">
    <div class="page-header">
      <h1 class="page-title">Admin Dashboard</h1>
      <div>
        <span class="badge bg-primary">
          <?php echo htmlspecialchars($_SESSION['username']); ?>
        </span>
      </div>
    </div>

    <?php
    // Get today's date
    $today = date('Y-m-d');
    
    // Get quick stats
    $menu_count_query = "SELECT COUNT(*) as count FROM menu_items";
    $menu_count_result = mysqli_query($conn, $menu_count_query);
    $menu_count = mysqli_fetch_assoc($menu_count_result)['count'];
    
    $today_menu_query = "SELECT COUNT(*) as count FROM menu_items WHERE date_added = '$today'";
    $today_menu_result = mysqli_query($conn, $today_menu_query);
    $today_menu_count = mysqli_fetch_assoc($today_menu_result)['count'];
    
    $sales_query = "SELECT SUM(servings_sold * price_per_serve) as total FROM menu_items";
    $sales_result = mysqli_query($conn, $sales_query);
    $total_sales = mysqli_fetch_assoc($sales_result)['total'] ?: 0;
    
    $cashier_count_query = "SELECT COUNT(*) as count FROM cashiers";
    $cashier_count_result = mysqli_query($conn, $cashier_count_query);
    $cashier_count = mysqli_fetch_assoc($cashier_count_result)['count'];
    ?>

    <!-- Stats cards in row -->
    <div class="row">
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <div class="stats-card">
              <div class="stats-icon">
                <i class="fas fa-utensils"></i>
              </div>
              <div class="stats-info">
                <h3 class="stats-value"><?php echo $menu_count; ?></h3>
                <p class="stats-label">Total Menu Items</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <div class="stats-card">
              <div class="stats-icon">
                <i class="fas fa-calendar-day"></i>
              </div>
              <div class="stats-info">
                <h3 class="stats-value"><?php echo $today_menu_count; ?></h3>
                <p class="stats-label">Added Today</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <div class="stats-card">
              <div class="stats-icon">
                <i class="fas fa-dollar-sign"></i>
              </div>
              <div class="stats-info">
                <h3 class="stats-value">$<?php echo number_format($total_sales, 2); ?></h3>
                <p class="stats-label">Total Sales</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-3">
        <div class="card">
          <div class="card-body">
            <div class="stats-card">
              <div class="stats-icon">
                <i class="fas fa-users"></i>
              </div>
              <div class="stats-info">
                <h3 class="stats-value"><?php echo $cashier_count; ?></h3>
                <p class="stats-label">Active Cashiers</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Menu Management Section -->
    <div class="card mt-4">
      <div class="card-header">
        <i class="fas fa-utensils"></i>
        <h4 class="card-title">Menu Management</h4>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-4">
            <div class="card">
              <div class="card-body text-center">
                <i class="fas fa-plus-circle fa-3x text-accent mb-3"></i>
                <h5>Input Daily Menu</h5>
                <a href="MenuManagement/input-daily-menu.php" class="btn btn-primary mt-3">Open</a>
              </div>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="card">
              <div class="card-body text-center">
                <i class="fas fa-edit fa-3x text-accent mb-3"></i>
                <h5>Edit Menu Details</h5>
                <a href="MenuManagement/edit-menu-details.php" class="btn btn-primary mt-3">Open</a>
              </div>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="card">
              <div class="card-body text-center">
                <i class="fas fa-chart-pie fa-3x text-accent mb-3"></i>
                <h5>Monitor Menu Sales</h5>
                <a href="MenuManagement/monitor-menu-sales.php" class="btn btn-primary mt-3">Open</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Inventory Management Section -->
    <div class="card mt-4">
      <div class="card-header">
        <i class="fas fa-warehouse"></i>
        <h4 class="card-title">Inventory Management</h4>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-4">
            <div class="card">
              <div class="card-body text-center">
                <i class="fas fa-shopping-cart fa-3x text-accent mb-3"></i>
                <h5>Input Purchase Details</h5>
                <a href="InventoryManagement/input-purchase-details.php" class="btn btn-success mt-3">Open</a>
              </div>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="card">
              <div class="card-body text-center">
                <i class="fas fa-clipboard-list fa-3x text-accent mb-3"></i>
                <h5>Input Daily Usage</h5>
                <a href="InventoryManagement/input-daily-usage.php" class="btn btn-success mt-3">Open</a>
              </div>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="card">
              <div class="card-body text-center">
                <i class="fas fa-boxes fa-3x text-accent mb-3"></i>
                <h5>Remaining Stock View</h5>
                <a href="InventoryManagement/remaining-stock-view.php" class="btn btn-success mt-3">Open</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Additional Features Section -->
    <div class="card mt-4">
      <div class="card-header">
        <i class="fas fa-star"></i>
        <h4 class="card-title">Additional Features</h4>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <div class="card">
              <div class="card-body text-center">
                <i class="fas fa-file-invoice-dollar fa-3x text-accent mb-3"></i>
                <h5>Sales Reporting</h5>
                <a href="./MenuManagement/sales-reporting.php" class="btn btn-primary mt-3">Open</a>
              </div>
            </div>
          </div>
          
          <div class="col-md-6">
            <div class="card">
              <div class="card-body text-center">
                <i class="fas fa-users-cog fa-3x text-accent mb-3"></i>
                <h5>Manage Cashiers</h5>
                <a href="./MenuManagement/manage-cashier.php" class="btn btn-primary mt-3">Open</a>
              </div>
            </div>
          </div>
          
          <div class="col-md-12 mt-4">
            <div class="card">
              <div class="card-body text-center">
                <i class="fas fa-ban fa-3x text-accent mb-3"></i>
                <h5>Process Void Requests</h5>
                <a href="process-void-requests.php" class="btn btn-warning mt-3">Open</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php
    // Function to get recent activity logs
    function get_recent_activity($conn, $limit = 5) {
        $sql = "SELECT * FROM activity_log ORDER BY timestamp DESC LIMIT $limit";
        $result = mysqli_query($conn, $sql);
        return $result;
    }
    
    // Check if activity_log table exists before trying to display it
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'activity_log'");
    if (mysqli_num_rows($table_check) > 0) {
        $recent_activity = get_recent_activity($conn);
        if ($recent_activity && mysqli_num_rows($recent_activity) > 0) {
            // Only show if we have activity logs
            echo '<div class="card mt-4">
                    <div class="card-header">
                      <i class="fas fa-history"></i>
                      <h4 class="card-title">Recent Activity</h4>
                    </div>
                    <div class="card-body">
                      <div class="table-responsive">
                        <table class="table table-striped table-hover">
                          <thead>
                            <tr>
                              <th>Time</th>
                              <th>User</th>
                              <th>Activity</th>
                            </tr>
                          </thead>
                          <tbody>';
                          
            while ($log = mysqli_fetch_assoc($recent_activity)) {
                echo '<tr>
                        <td>' . date('M d, g:i a', strtotime($log['timestamp'])) . '</td>
                        <td>' . htmlspecialchars($log['username']) . '</td>
                        <td>' . htmlspecialchars($log['action']) . '</td>
                      </tr>';
            }
            
            echo '    </tbody>
                    </table>
                  </div>
                </div>
              </div>';
        }
    }
    ?>
    
  </div>

  <!-- Menu toggle for responsive design -->
  <div class="menu-toggle">
    <i class="fas fa-bars"></i>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Add responsive menu toggle functionality
    document.querySelector('.menu-toggle').addEventListener('click', function() {
      document.querySelector('.side-nav').classList.toggle('show');
    });
  </script>
</body>
</html>
