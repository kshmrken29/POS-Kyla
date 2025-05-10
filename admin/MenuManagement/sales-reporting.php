<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sales Reporting</title>
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
        <a href="monitor-menu-sales.php" class="nav-link">
          <i class="fas fa-chart-line"></i> Monitor Sales
        </a>
      </li>
      
      <li class="nav-item">
        <a href="sales-reporting.php" class="nav-link active">
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
      <h1 class="page-title">Sales Reports</h1>
    </div>

    <?php
    // Include database connection
    include '../connection.php';

    // Set default date range
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

    // Function to get sales data
    function getSalesData($conn, $start_date, $end_date) {
        $data = array(
            'dates' => array(),
            'expected_sales' => array(),
            'actual_sales' => array(),
            'total_expected' => 0,
            'total_actual' => 0,
            'best_selling_item' => null,
            'best_performing_date' => null,
            'avg_daily_sales' => 0,
            'date_summary' => array()
        );
        
        // Get daily sales data
        $sql = "SELECT 
                date_added,
                SUM(expected_sales) as daily_expected_sales,
                SUM(servings_sold * price_per_serve) as daily_actual_sales,
                COUNT(*) as item_count
              FROM 
                menu_items
              WHERE 
                date_added BETWEEN '$start_date' AND '$end_date'
              GROUP BY 
                date_added
              ORDER BY 
                date_added";
                
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $days_count = 0;
            $best_performance = 0;
            
            // Process daily data
            while($row = mysqli_fetch_assoc($result)) {
                $date = $row['date_added'];
                $expected = floatval($row['daily_expected_sales']);
                $actual = floatval($row['daily_actual_sales']);
                
                // Add to totals
                $data['total_expected'] += $expected;
                $data['total_actual'] += $actual;
                $days_count++;
                
                // Calculate performance percentage
                $performance = ($expected > 0) ? ($actual / $expected) * 100 : 0;
                
                // Check if this is best performing day
                if ($performance > $best_performance && $actual > 0) {
                    $best_performance = $performance;
                    $data['best_performing_date'] = array(
                        'date' => $date,
                        'expected' => $expected,
                        'actual' => $actual,
                        'performance' => $performance
                    );
                }
                
                // Add to arrays for chart
                $data['dates'][] = date('M d', strtotime($date));
                $data['expected_sales'][] = $expected;
                $data['actual_sales'][] = $actual;
                
                // Add to date summary
                $data['date_summary'][$date] = array(
                    'expected' => $expected,
                    'actual' => $actual,
                    'performance' => $performance,
                    'item_count' => $row['item_count']
                );
            }
            
            // Calculate average daily sales
            if ($days_count > 0) {
                $data['avg_daily_sales'] = $data['total_actual'] / $days_count;
            }
        }
        
        // Get best selling menu item
        $sql = "SELECT 
                menu_name,
                SUM(servings_sold) as total_servings_sold,
                SUM(servings_sold * price_per_serve) as total_sales
              FROM 
                menu_items
              WHERE 
                date_added BETWEEN '$start_date' AND '$end_date'
              GROUP BY 
                menu_name
              ORDER BY 
                total_servings_sold DESC
              LIMIT 1";
                
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $data['best_selling_item'] = mysqli_fetch_assoc($result);
        }
        
        return $data;
    }
    
    // Get sales data for the selected period
    $sales_data = getSalesData($conn, $start_date, $end_date);
    
    // Calculate profit (assume 40% profit margin)
    $profit_margin = 0.4;
    $estimated_profit = $sales_data['total_actual'] * $profit_margin;
    
    // Calculate comparison vs previous period
    $prev_start_date = date('Y-m-d', strtotime("$start_date -" . (strtotime($end_date) - strtotime($start_date)) . " seconds"));
    $prev_end_date = date('Y-m-d', strtotime("$end_date -" . (strtotime($end_date) - strtotime($start_date)) . " seconds"));
    $prev_sales_data = getSalesData($conn, $prev_start_date, $prev_end_date);
    
    $sales_growth = 0;
    if ($prev_sales_data['total_actual'] > 0) {
        $sales_growth = (($sales_data['total_actual'] - $prev_sales_data['total_actual']) / $prev_sales_data['total_actual']) * 100;
    }
    
    ?>

    <div class="row">
      <div class="col-lg-12 mb-4">
        <div class="card">
      <div class="card-header">
            <h5 class="card-title"><i class="fas fa-calendar-alt me-2"></i>Select Date Range</h5>
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
                <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search me-2"></i>Generate Report</button>
                <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-calendar me-2"></i>Quick Dates
                  </button>
                  <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="?start_date=<?php echo date('Y-m-d'); ?>&end_date=<?php echo date('Y-m-d'); ?>">Today</a></li>
                    <li><a class="dropdown-item" href="?start_date=<?php echo date('Y-m-d', strtotime('-7 days')); ?>&end_date=<?php echo date('Y-m-d'); ?>">Last 7 Days</a></li>
                    <li><a class="dropdown-item" href="?start_date=<?php echo date('Y-m-d', strtotime('-30 days')); ?>&end_date=<?php echo date('Y-m-d'); ?>">Last 30 Days</a></li>
                    <li><a class="dropdown-item" href="?start_date=<?php echo date('Y-m-01'); ?>&end_date=<?php echo date('Y-m-d'); ?>">This Month</a></li>
                    <li><a class="dropdown-item" href="?start_date=<?php echo date('Y-m-01', strtotime('-1 month')); ?>&end_date=<?php echo date('Y-m-t', strtotime('-1 month')); ?>">Last Month</a></li>
                  </ul>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
      
      <!-- Key Metrics -->
      <div class="col-lg-12 mb-4">
        <div class="row">
          <div class="col-md-3 mb-4">
            <div class="card h-100">
      <div class="card-body">
                <div class="stats-card">
                  <div class="stats-icon">
                    <i class="fas fa-dollar-sign"></i>
                  </div>
                  <div class="stats-info">
                    <p class="stats-label">Total Sales</p>
                    <h3 class="stats-value">₱<?php echo number_format($sales_data['total_actual'], 2); ?></h3>
                    <?php if ($sales_growth != 0): ?>
                      <small class="<?php echo $sales_growth >= 0 ? 'text-success' : 'text-danger'; ?>">
                        <i class="fas fa-<?php echo $sales_growth >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                        <?php echo abs(round($sales_growth, 1)); ?>% vs previous period
                      </small>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-md-3 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="stats-card">
                  <div class="stats-icon">
                    <i class="fas fa-hand-holding-usd"></i>
                  </div>
                  <div class="stats-info">
                    <p class="stats-label">Estimated Profit</p>
                    <h3 class="stats-value">₱<?php echo number_format($estimated_profit, 2); ?></h3>
                    <small class="text-muted">Based on 40% margin</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-md-3 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="stats-card">
                  <div class="stats-icon">
                    <i class="fas fa-chart-line"></i>
                  </div>
                  <div class="stats-info">
                    <p class="stats-label">Avg. Daily Sales</p>
                    <h3 class="stats-value">₱<?php echo number_format($sales_data['avg_daily_sales'], 2); ?></h3>
                    <small class="text-muted">
                      <?php echo count($sales_data['date_summary']); ?> days in period
                    </small>
                  </div>
                </div>
              </div>
      </div>
    </div>

          <div class="col-md-3 mb-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="stats-card">
                  <div class="stats-icon">
                    <i class="fas fa-trophy"></i>
                  </div>
                  <div class="stats-info">
                    <p class="stats-label">Best Selling Item</p>
                    <?php if ($sales_data['best_selling_item']): ?>
                      <h3 class="stats-value"><?php echo $sales_data['best_selling_item']['menu_name']; ?></h3>
                      <small class="text-muted">
                        <?php echo $sales_data['best_selling_item']['total_servings_sold']; ?> servings sold
                      </small>
                    <?php else: ?>
                      <h3 class="stats-value">N/A</h3>
                      <small class="text-muted">No sales data available</small>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
        </div>
      </div>
    </div>

      <!-- Daily Sales Breakdown -->
      <div class="col-lg-12">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title"><i class="fas fa-table me-2"></i>Daily Sales Breakdown</h5>
          </div>
          <div class="card-body">
            <?php if (count($sales_data['date_summary']) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                <thead>
                  <tr>
                      <th>Date</th>
                      <th>Menu Items</th>
                    <th>Expected Sales</th>
                    <th>Actual Sales</th>
                      <th>Performance</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    // Sort by date (most recent first)
                    krsort($sales_data['date_summary']);
                    
                    foreach ($sales_data['date_summary'] as $date => $summary): 
                      $performance = $summary['performance'];
                      $perf_class = $performance >= 100 ? 'badge-success' : ($performance >= 80 ? 'badge-warning' : 'badge-danger');
                    ?>
                      <tr>
                        <td><?php echo date('M d, Y', strtotime($date)); ?></td>
                        <td><?php echo $summary['item_count']; ?></td>
                        <td>₱<?php echo number_format($summary['expected'], 2); ?></td>
                        <td>₱<?php echo number_format($summary['actual'], 2); ?></td>
                        <td><span class="badge <?php echo $perf_class; ?>"><?php echo number_format($performance, 0); ?>%</span></td>
                      </tr>
                    <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <?php else: ?>
              <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>No sales data available for the selected date range.
              </div>
            <?php endif; ?>
          </div>
        </div>
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
