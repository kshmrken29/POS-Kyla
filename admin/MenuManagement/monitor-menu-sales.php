<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Monitor Menu Sales</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="menu-styles.css">
</head>
<body>
  <!-- Mobile menu toggle button -->
  <div class="menu-toggle d-lg-none">
    <i class="fas fa-bars"></i>
  </div>

  <!-- Side Navigation -->
  <div class="side-nav">
    <div class="logo-wrapper">
      <div class="logo">Restaurant System</div>
    </div>
    <ul class="nav-links">
      <li class="nav-item">
        <a href="../index.php" class="nav-link">
          <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
      </li>
      <span>Menu Management</span>

      <li class="nav-item">
        <a href="input-daily-menu.php" class="nav-link">
          <i class="fas fa-utensils"></i> Input Daily Menu
        </a>
      </li>
      <li class="nav-item">
        <a href="edit-menu-details.php" class="nav-link">
          <i class="fas fa-edit"></i> Edit Menu Details
        </a>
      </li>
      <li class="nav-item">
        <a href="monitor-menu-sales.php" class="nav-link active">
          <i class="fas fa-chart-line"></i> Monitor Sales
        </a>
      </li>
      
      <li class="nav-item">
        <a href="sales-reporting.php" class="nav-link">
          <i class="fas fa-file-invoice-dollar"></i> Sales Reporting
        </a>
      </li>
      
      <span>Inventory Management</span>
      
      <li class="nav-item">
        <a href="../InventoryManagement/input-purchase-details.php" class="nav-link">
          <i class="fas fa-shopping-cart"></i> Purchase Details
        </a>
      </li>
      <li class="nav-item">
        <a href="../InventoryManagement/input-daily-usage.php" class="nav-link">
          <i class="fas fa-clipboard-list"></i> Daily Usage
        </a>
      </li>
      <li class="nav-item">
        <a href="../InventoryManagement/remaining-stock-view.php" class="nav-link">
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
      <h1 class="page-title">Monitor Menu Sales</h1>
    </div>
    
    <?php
    // Include database connection
    include '../connection.php';
    
    // Set default date range to today
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        
    // Initialize variables for results
    $daily_items = array();
    $sales_summary = array();
    $total_expected_sales = 0;
    $total_actual_sales = 0;
    
    // Get menu items and sales data for the selected period
    $sql = "SELECT m.id, m.menu_name, m.number_of_servings, m.servings_sold, m.price_per_serve, m.expected_sales, m.date_added
            FROM menu_items m
            WHERE m.date_added BETWEEN '$start_date' AND '$end_date'
            ORDER BY m.date_added DESC, m.menu_name";
    
        $result = mysqli_query($conn, $sql);
        
    if ($result && mysqli_num_rows($result) > 0) {
        // Process results into a structured format
        while ($row = mysqli_fetch_assoc($result)) {
            $date = $row['date_added'];
            
            // Initialize date in array if it doesn't exist
            if (!isset($daily_items[$date])) {
                $daily_items[$date] = array();
            }
            
            // Add menu item to the day
            $daily_items[$date][] = $row;
            
            // Calculate total expected and actual sales
            $expected_sales = $row['expected_sales'];
            $actual_sales = $row['servings_sold'] * $row['price_per_serve'];
            
            $total_expected_sales += $expected_sales;
            $total_actual_sales += $actual_sales;
            
            // Update sales summary
            if (!isset($sales_summary[$date])) {
                $sales_summary[$date] = array(
                    'date' => $date,
                    'expected' => 0,
                    'actual' => 0,
                    'items_count' => 0
                );
            }
            
            $sales_summary[$date]['expected'] += $expected_sales;
            $sales_summary[$date]['actual'] += $actual_sales;
            $sales_summary[$date]['items_count']++;
        }
    }
    ?>
    
    <div class="row">
      <div class="col-lg-12 mb-4">
        <div class="card">
        <div class="card-header">
            <h5 class="card-title"><i class="fas fa-filter me-2"></i>Date Range Selection</h5>
          </div>
          <div class="card-body">
            <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="row g-3">
              <div class="col-md-4">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
              </div>
              <div class="col-md-4">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
              </div>
              <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search me-2"></i>View Sales</button>
                <a href="?start_date=<?php echo date('Y-m-d'); ?>&end_date=<?php echo date('Y-m-d'); ?>" class="btn btn-secondary"><i class="fas fa-calendar-day me-2"></i>Today Only</a>
              </div>
            </form>
          </div>
        </div>
      </div>
      
      <!-- Sales Summary -->
      <div class="col-lg-4 mb-4">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title"><i class="fas fa-chart-pie me-2"></i>Sales Summary</h5>
        </div>
        <div class="card-body">
            <div class="stats-card mb-4">
              <div class="stats-icon">
                <i class="fas fa-money-bill-wave"></i>
              </div>
              <div class="stats-info">
                <p class="stats-label">Expected Sales</p>
                <h3 class="stats-value">₱<?php echo number_format($total_expected_sales, 2); ?></h3>
              </div>
            </div>
            
            <div class="stats-card mb-4">
              <div class="stats-icon">
                <i class="fas fa-cash-register"></i>
              </div>
              <div class="stats-info">
                <p class="stats-label">Actual Sales</p>
                <h3 class="stats-value">₱<?php echo number_format($total_actual_sales, 2); ?></h3>
              </div>
            </div>
            
            <div class="stats-card">
              <div class="stats-icon">
                <i class="fas fa-percentage"></i>
              </div>
              <div class="stats-info">
                <p class="stats-label">Performance</p>
                <?php
                $performance = $total_expected_sales > 0 ? ($total_actual_sales / $total_expected_sales) * 100 : 0;
                $performance_class = $performance >= 100 ? 'text-success' : ($performance >= 80 ? 'text-warning' : 'text-danger');
                ?>
                <h3 class="stats-value <?php echo $performance_class; ?>"><?php echo number_format($performance, 2); ?>%</h3>
              </div>
            </div>
            
            <?php if (count($sales_summary) > 0): ?>
              <hr>
              <h6 class="mb-3">Daily Breakdown</h6>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                      <th>Date</th>
                      <th>Expected</th>
                      <th>Actual</th>
                      <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($sales_summary as $date => $summary): 
                      $day_performance = $summary['expected'] > 0 ? ($summary['actual'] / $summary['expected']) * 100 : 0;
                      $perf_class = $day_performance >= 100 ? 'text-success' : ($day_performance >= 80 ? 'text-warning' : 'text-danger');
                    ?>
                      <tr>
                        <td><?php echo date('M d', strtotime($date)); ?></td>
                        <td>₱<?php echo number_format($summary['expected'], 2); ?></td>
                        <td>₱<?php echo number_format($summary['actual'], 2); ?></td>
                        <td class="<?php echo $perf_class; ?>"><?php echo number_format($day_performance, 0); ?>%</td>
                      </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
              </div>
            <?php endif; ?>
            </div>
        </div>
    </div>
    
      <!-- Menu Items Sales -->
      <div class="col-lg-8">
        <?php if (count($daily_items) > 0): ?>
          <?php foreach ($daily_items as $date => $items): ?>
            <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-calendar-alt me-2"></i><?php echo date('F d, Y', strtotime($date)); ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                            <tr>
                        <th>Menu Item</th>
                        <th>Price</th>
                        <th>Servings</th>
                        <th>Expected</th>
                        <th>Actual</th>
                        <th>%</th>
                            </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($items as $item): 
                        $actual_sales = $item['servings_sold'] * $item['price_per_serve'];
                        $performance = $item['expected_sales'] > 0 ? ($actual_sales / $item['expected_sales']) * 100 : 0;
                        $perf_class = $performance >= 100 ? 'badge-success' : ($performance >= 80 ? 'badge-warning' : 'badge-danger');
                      ?>
                        <tr>
                          <td><strong><?php echo $item['menu_name']; ?></strong></td>
                          <td>₱<?php echo number_format($item['price_per_serve'], 2); ?></td>
                          <td><?php echo $item['servings_sold']; ?> / <?php echo $item['number_of_servings']; ?></td>
                          <td>₱<?php echo number_format($item['expected_sales'], 2); ?></td>
                          <td>₱<?php echo number_format($actual_sales, 2); ?></td>
                          <td><span class="badge <?php echo $perf_class; ?>"><?php echo number_format($performance, 0); ?>%</span></td>
                            </tr>
                      <?php endforeach; ?>
                    </tbody>
                        </table>
                    </div>
                    </div>
                </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="card">
            <div class="card-body text-center py-5">
              <i class="fas fa-file-invoice-dollar fa-3x text-accent mb-3"></i>
              <h5>No menu items found for the selected date range</h5>
              <p class="text-muted">Try selecting a different date range or add menu items for today.</p>
              <a href="input-daily-menu.php" class="btn btn-primary"><i class="fas fa-plus-circle me-2"></i>Add Menu Items</a>
            </div>
        </div>
    <?php endif; ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Toggle mobile menu
      document.querySelector('.menu-toggle').addEventListener('click', function() {
        document.querySelector('.side-nav').classList.toggle('show');
      });
    });
  </script>
</body>
</html>
