<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Menu Details</title>
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
        <a href="edit-menu-details.php" class="nav-link active">
          <i class="fas fa-edit"></i> Edit Menu Details
        </a>
      </li>
      <li class="nav-item">
        <a href="monitor-menu-sales.php" class="nav-link">
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
      <h1 class="page-title">Edit Menu Details</h1>
    </div>
    
    <?php
    // Include database connection
    include '../connection.php';
    
    // Initialize variable to hold selected menu item
    $selectedMenuItem = null;
    
    // Handle form submission for update
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_menu'])) {
        $menu_id = $_POST['menu_id'];
        $menu_name = $_POST['menu_name'];
        $approximate_cost = $_POST['approximate_cost'];
        $number_of_servings = $_POST['number_of_servings'];
        $price_per_serve = $_POST['price_per_serve'];
        $expected_sales = $number_of_servings * $price_per_serve;
        
        // Update the menu item
        $sql = "UPDATE menu_items SET 
                menu_name = '$menu_name',
                approximate_cost = '$approximate_cost',
                number_of_servings = '$number_of_servings',
                price_per_serve = '$price_per_serve',
                expected_sales = '$expected_sales'
                WHERE id = $menu_id";
                
        if (mysqli_query($conn, $sql)) {
            echo '<div class="alert alert-success" role="alert"><i class="fas fa-check-circle me-2"></i>Menu item updated successfully!</div>';
        } else {
            echo '<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-circle me-2"></i>Error updating menu item: ' . mysqli_error($conn) . '</div>';
        }
    }
    
    // Handle selection of menu item to edit
    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['menu_id'])) {
        $menu_id = $_GET['menu_id'];
        
        // Get the menu item details
        $sql = "SELECT * FROM menu_items WHERE id = $menu_id";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            $selectedMenuItem = mysqli_fetch_assoc($result);
        }
    }
    
    // Get all menu items
    $sql = "SELECT id, menu_name, date_added FROM menu_items ORDER BY date_added DESC";
    $result = mysqli_query($conn, $sql);
    ?>
    
    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-list me-2"></i>Menu Items</h5>
                </div>
                <div class="card-body">
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        echo '<div class="list-group">';
                        while($row = mysqli_fetch_assoc($result)) {
                            $activeClass = (isset($_GET['menu_id']) && $_GET['menu_id'] == $row['id']) ? "active" : "";
                            $date_formatted = date('M d, Y', strtotime($row['date_added']));
                            echo '<a href="?menu_id=' . $row['id'] . '" class="list-group-item list-group-item-action ' . $activeClass . '">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">' . $row['menu_name'] . '</h6>
                                        <small>' . $date_formatted . '</small>
                                    </div>
                                    <small class="text-muted"><i class="fas fa-calendar-alt me-1"></i> Added: ' . $date_formatted . '</small>
                                  </a>';
                        }
                        echo '</div>';
                    } else {
                        echo '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No menu items found. <a href="input-daily-menu.php">Add some</a> first.</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <?php if ($selectedMenuItem): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-edit me-2"></i>Edit: <?php echo $selectedMenuItem['menu_name']; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?menu_id=<?php echo $selectedMenuItem['id']; ?>">
                            <input type="hidden" name="menu_id" value="<?php echo $selectedMenuItem['id']; ?>">
                            
                            <div class="mb-3">
                                <label for="menu_name" class="form-label">Menu Name</label>
                                <input type="text" class="form-control" id="menu_name" name="menu_name" 
                                    value="<?php echo $selectedMenuItem['menu_name']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="approximate_cost" class="form-label">Approximate Cost</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="approximate_cost" name="approximate_cost" 
                                        step="0.01" value="<?php echo $selectedMenuItem['approximate_cost']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="number_of_servings" class="form-label">Approximate Number of Servings</label>
                                <input type="number" class="form-control" id="number_of_servings" name="number_of_servings" 
                                    value="<?php echo $selectedMenuItem['number_of_servings']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="price_per_serve" class="form-label">Price Per Serve</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="price_per_serve" name="price_per_serve" 
                                        step="0.01" value="<?php echo $selectedMenuItem['price_per_serve']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Expected Sales</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" class="form-control" id="expected_sales" 
                                        value="<?php echo $selectedMenuItem['expected_sales']; ?>" disabled>
                                    <span class="input-group-text">Will be calculated automatically</span>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <button type="submit" name="update_menu" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Menu Item
                                </button>
                                <a href="?menu_id=<?php echo $selectedMenuItem['id']; ?>" class="btn btn-secondary">
                                    <i class="fas fa-undo me-2"></i>Reset Changes
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Quick Stats Card -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Item Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="stats-card mb-3">
                                    <div class="stats-icon">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                    <div class="stats-info">
                                        <p class="stats-label">Cost Per Serving</p>
                                        <h3 class="stats-value">₱<?php echo number_format($selectedMenuItem['approximate_cost'] / $selectedMenuItem['number_of_servings'], 2); ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                    <div class="stats-info">
                                        <p class="stats-label">Profit Margin</p>
                                        <?php 
                                        $cost_per_serving = $selectedMenuItem['approximate_cost'] / $selectedMenuItem['number_of_servings'];
                                        $price = $selectedMenuItem['price_per_serve'];
                                        $margin = (($price - $cost_per_serving) / $price) * 100;
                                        ?>
                                        <h3 class="stats-value"><?php echo number_format($margin, 2); ?>%</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-hand-point-left fa-3x text-accent mb-3"></i>
                        <h5>Select a menu item from the list to edit its details</h5>
                        <p class="text-muted">Choose an item from the list on the left to view and edit its details.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
      // Calculate expected sales when input changes
      document.addEventListener('DOMContentLoaded', function() {
          if (document.getElementById('number_of_servings') && document.getElementById('price_per_serve')) {
              document.getElementById('number_of_servings').addEventListener('input', calculateExpectedSales);
              document.getElementById('price_per_serve').addEventListener('input', calculateExpectedSales);
              
              function calculateExpectedSales() {
                  const servings = document.getElementById('number_of_servings').value || 0;
                  const price = document.getElementById('price_per_serve').value || 0;
                  const expected = servings * price;
                  document.getElementById('expected_sales').value = expected.toFixed(2);
              }
          }
          
          // Toggle mobile menu
          document.querySelector('.menu-toggle').addEventListener('click', function() {
              document.querySelector('.side-nav').classList.toggle('show');
          });
      });
  </script>
</body>
</html> 