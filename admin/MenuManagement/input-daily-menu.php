<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Input Daily Menu</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="menu-styles.css">
</head>
<body>
  <!-- Mobile menu toggle button -->
  <div class="menu-toggle d-lg-none">
    <i class="fas fa-bars"></i>
  </div>

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
        <a href="input-daily-menu.php" class="nav-link active">
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
      <h1 class="page-title">Input Daily Menu</h1>
    </div>

    <?php
    // Include database connection
    include '../connection.php';

    // Debug variable to track submission process
    $debug_message = "";
    
    // Check if the form has been submitted for a new item
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
        $menu_name = mysqli_real_escape_string($conn, $_POST['menu_name']);
        $approximate_cost = $_POST['approximate_cost'];
        $number_of_servings = $_POST['number_of_servings'];
        $price_per_serve = $_POST['price_per_serve'];
        $expected_sales = $number_of_servings * $price_per_serve;
        $date_added = date('Y-m-d');

        // Check connection first
        if (!$conn) {
            echo '<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-circle me-2"></i>Database connection failed: ' . mysqli_connect_error() . '</div>';
        } else {
            // Check if menu name already exists for today's date (case-insensitive comparison)
            $check_query = "SELECT * FROM menu_items WHERE LOWER(menu_name) = LOWER('$menu_name') AND date_added = '$date_added'";
            $result = mysqli_query($conn, $check_query);
            
            if (!$result) {
                echo '<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-circle me-2"></i>Error checking for duplicates: ' . mysqli_error($conn) . '</div>';
            } 
            else if (mysqli_num_rows($result) > 0) {
                // Instead of warning, update the existing menu item
                $existing_item = mysqli_fetch_assoc($result);
                $existing_id = $existing_item['id'];
                $servings_sold = $existing_item['servings_sold']; // Preserve servings sold
                
                // Update the existing item
                $update_sql = "UPDATE menu_items SET 
                            approximate_cost = '$approximate_cost',
                            number_of_servings = '$number_of_servings',
                            price_per_serve = '$price_per_serve',
                            expected_sales = '$expected_sales'
                            WHERE id = $existing_id";
                            
                if (mysqli_query($conn, $update_sql)) {
                    echo '<div class="alert alert-success" role="alert"><i class="fas fa-check-circle me-2"></i>Existing menu item "' . $menu_name . '" has been updated!</div>';
                    // Clear form after successful submission with immediate effect
                    echo '<script>
                        document.addEventListener("DOMContentLoaded", function() {
                            document.getElementById("new-menu-form").reset();
                            document.getElementById("new_expected_sales").value = "";
                        });
                    </script>';
                } else {
                    echo '<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-circle me-2"></i>Error updating existing item: ' . mysqli_error($conn) . '</div>';
                }
            } 
            else {
                // Prepare SQL statement
                $sql = "INSERT INTO menu_items (menu_name, approximate_cost, number_of_servings, price_per_serve, expected_sales, servings_sold, date_added) 
                        VALUES ('$menu_name', '$approximate_cost', '$number_of_servings', '$price_per_serve', '$expected_sales', 0, '$date_added')";

                // Execute SQL and check if successful
                if (mysqli_query($conn, $sql)) {
                    echo '<div class="alert alert-success" role="alert"><i class="fas fa-check-circle me-2"></i>Menu item added successfully!</div>';
                    // Clear form after successful submission with immediate effect
                    echo '<script>
                        document.addEventListener("DOMContentLoaded", function() {
                            document.getElementById("new-menu-form").reset();
                            document.getElementById("new_expected_sales").value = "";
                        });
                    </script>';
                } else {
                    echo '<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-circle me-2"></i>Error: ' . mysqli_error($conn) . '</div>';
                }
            }
        }
    }
    
    // Handle form submission for update
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {
        $menu_id = $_POST['menu_id'];
        $menu_name = mysqli_real_escape_string($conn, $_POST['menu_name']);
        $approximate_cost = $_POST['approximate_cost'];
        $current_servings = $_POST['current_servings'];
        $additional_servings = $_POST['additional_servings'];
        $new_total_servings = $current_servings + $additional_servings;
        $price_per_serve = $_POST['price_per_serve'];
        $expected_sales = $new_total_servings * $price_per_serve;
        
        // Update the menu item
        $sql = "UPDATE menu_items SET 
                menu_name = '$menu_name',
                approximate_cost = '$approximate_cost',
                number_of_servings = '$new_total_servings',
                price_per_serve = '$price_per_serve',
                expected_sales = '$expected_sales'
                WHERE id = $menu_id";
                
        if (mysqli_query($conn, $sql)) {
            $message = "Menu item updated successfully!";
            if ($additional_servings > 0) {
                $message .= " Added $additional_servings new servings.";
            }
            echo '<div class="alert alert-success" role="alert"><i class="fas fa-check-circle me-2"></i>' . $message . '</div>';
        } else {
            echo '<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-circle me-2"></i>Error updating menu item: ' . mysqli_error($conn) . '</div>';
        }
    }
    
    // Get all menu items for selection dropdown
    $menu_items_sql = "SELECT id, menu_name, date_added FROM menu_items ORDER BY date_added DESC";
    $menu_items_result = mysqli_query($conn, $menu_items_sql);
    $has_menu_items = $menu_items_result && mysqli_num_rows($menu_items_result) > 0;
    
    // Initialize variable for selected menu item details
    $selected_item = null;
    
    // If menu item is selected, get its details
    if (isset($_GET['menu_id'])) {
        $menu_id = $_GET['menu_id'];
        $item_sql = "SELECT * FROM menu_items WHERE id = $menu_id";
        $item_result = mysqli_query($conn, $item_sql);
        
        if ($item_result && mysqli_num_rows($item_result) > 0) {
            $selected_item = mysqli_fetch_assoc($item_result);
        }
    }
    ?>

    <div class="row">
      <div class="col-lg-8">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title"><i class="fas fa-clipboard-list me-2"></i>Manage Menu Items</h5>
          </div>
          <div class="card-body">
            <ul class="nav nav-tabs" id="menuTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo !isset($_GET['menu_id']) ? 'active' : ''; ?>" id="new-tab" data-bs-toggle="tab" data-bs-target="#new" type="button" role="tab">
                  <i class="fas fa-plus-circle me-2"></i>Add New Menu Item
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo isset($_GET['menu_id']) ? 'active' : ''; ?>" id="existing-tab" data-bs-toggle="tab" data-bs-target="#existing" type="button" role="tab">
                  <i class="fas fa-edit me-2"></i>Edit Existing Menu Item
                </button>
              </li>
            </ul>

            <div class="tab-content p-3" id="menuTabsContent">
              <!-- Add New Menu Item -->
              <div class="tab-pane fade <?php echo !isset($_GET['menu_id']) ? 'show active' : ''; ?>" id="new" role="tabpanel">
                <div class="alert alert-info mb-3">
                  <i class="fas fa-info-circle me-2"></i> Adding a menu item with a name that already exists for today will update that existing item instead of creating a duplicate.
                </div>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="new-menu-form" onsubmit="return validateNewForm()">
                  <input type="hidden" name="action" value="add">
                  <div class="mb-3">
                    <label for="menu_name" class="form-label">Menu Name</label>
                    <input type="text" class="form-control" id="menu_name" name="menu_name" required>
                  </div>
                  <div class="mb-3">
                    <label for="approximate_cost" class="form-label">Approximate Cost</label>
                    <div class="input-group">
                      <span class="input-group-text">₱</span>
                      <input type="number" class="form-control" id="approximate_cost" name="approximate_cost" step="0.01" min="0.01" required>
                    </div>
                  </div>
                  <div class="mb-3">
                    <label for="number_of_servings" class="form-label">Approximate Number of Servings</label>
                    <input type="number" class="form-control" id="number_of_servings" name="number_of_servings" min="1" required>
                  </div>
                  <div class="mb-3">
                    <label for="price_per_serve" class="form-label">Price Per Serve</label>
                    <div class="input-group">
                      <span class="input-group-text">₱</span>
                      <input type="number" class="form-control" id="price_per_serve" name="price_per_serve" step="0.01" min="0.01" required>
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Expected Sales</label>
                    <div class="input-group">
                      <span class="input-group-text">₱</span>
                      <input type="text" class="form-control" id="new_expected_sales" disabled>
                      <span class="input-group-text">Will be calculated automatically</span>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Menu Item</button>
                </form>
              </div>

              <!-- Edit Existing Menu Item -->
              <div class="tab-pane fade <?php echo isset($_GET['menu_id']) ? 'show active' : ''; ?>" id="existing" role="tabpanel">
                <?php if ($has_menu_items): ?>
                  <div class="row mb-4">
                    <div class="col-md-6">
                      <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="select-menu-form">
                        <div class="mb-3">
                          <label for="menu_id" class="form-label">Select Menu Item to Edit</label>
                          <select class="form-select" id="menu_id" name="menu_id" onchange="this.form.submit()">
                            <option value="">-- Select Menu Item --</option>
                            <?php 
                            // Reset pointer to start
                            mysqli_data_seek($menu_items_result, 0);
                            
                            while ($item = mysqli_fetch_assoc($menu_items_result)): 
                              $date_formatted = date('M d, Y', strtotime($item['date_added']));
                            ?>
                              <option value="<?php echo $item['id']; ?>" <?php echo (isset($_GET['menu_id']) && $_GET['menu_id'] == $item['id']) ? 'selected' : ''; ?>>
                                <?php echo $item['menu_name']; ?> (<?php echo $date_formatted; ?>)
                              </option>
                            <?php endwhile; ?>
                          </select>
                        </div>
                      </form>
                    </div>
                  </div>

                  <?php if ($selected_item): ?>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="update-menu-form" onsubmit="return validateUpdateForm()">
                      <input type="hidden" name="action" value="update">
                      <input type="hidden" name="menu_id" value="<?php echo $selected_item['id']; ?>">
                      <input type="hidden" name="current_servings" value="<?php echo $selected_item['number_of_servings']; ?>">
                      
                      <div class="mb-3">
                        <label for="menu_name" class="form-label">Menu Name</label>
                        <input type="text" class="form-control" id="edit_menu_name" name="menu_name" value="<?php echo $selected_item['menu_name']; ?>" required>
                      </div>
                      <div class="mb-3">
                        <label for="approximate_cost" class="form-label">Approximate Cost</label>
                        <div class="input-group">
                          <span class="input-group-text">₱</span>
                          <input type="number" class="form-control" id="edit_approximate_cost" name="approximate_cost" step="0.01" min="0.01" value="<?php echo $selected_item['approximate_cost']; ?>" required>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="mb-3">
                            <label class="form-label">Current Number of Servings</label>
                            <input type="text" class="form-control" value="<?php echo $selected_item['number_of_servings']; ?>" disabled>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="mb-3">
                            <label for="additional_servings" class="form-label">Additional Servings</label>
                            <input type="number" class="form-control" id="additional_servings" name="additional_servings" min="0" value="0">
                          </div>
                        </div>
                      </div>
                      <div class="mb-3">
                        <label for="price_per_serve" class="form-label">Price Per Serve</label>
                        <div class="input-group">
                          <span class="input-group-text">₱</span>
                          <input type="number" class="form-control" id="edit_price_per_serve" name="price_per_serve" step="0.01" min="0.01" value="<?php echo $selected_item['price_per_serve']; ?>" required>
                        </div>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Expected Sales</label>
                        <div class="input-group">
                          <span class="input-group-text">₱</span>
                          <input type="text" class="form-control" id="edit_expected_sales" value="<?php echo $selected_item['expected_sales']; ?>" disabled>
                          <span class="input-group-text">Will be updated automatically</span>
                        </div>
                      </div>
                      <?php if($selected_item['servings_sold'] > 0): ?>
                        <div class="mb-3">
                          <label class="form-label">Servings Sold</label>
                          <input type="text" class="form-control" value="<?php echo $selected_item['servings_sold']; ?>" disabled>
                        </div>
                      <?php endif; ?>
                      <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Menu Item</button>
                    </form>
                  <?php endif; ?>
                <?php else: ?>
                  <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i> No menu items found. Please add a new item first.
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4">
        <!-- Today's Menu Summary -->
        <div class="card">
          <div class="card-header">
            <h5 class="card-title"><i class="fas fa-calendar-day me-2"></i>Today's Menu Summary</h5>
          </div>
          <div class="card-body">
            <?php
            // Get today's menu
            $today = date('Y-m-d');
            $today_menu_sql = "SELECT * FROM menu_items WHERE date_added = '$today' ORDER BY menu_name";
            $today_menu_result = mysqli_query($conn, $today_menu_sql);
            
            if ($today_menu_result && mysqli_num_rows($today_menu_result) > 0) {
              $total_expected = 0;
              $total_actual = 0;
            ?>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Menu Item</th>
                      <th>Price</th>
                      <th>Servings</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($item = mysqli_fetch_assoc($today_menu_result)): 
                      $total_expected += $item['expected_sales'];
                      $total_actual += ($item['price_per_serve'] * $item['servings_sold']);
                    ?>
                      <tr>
                        <td><strong><?php echo $item['menu_name']; ?></strong></td>
                        <td>₱<?php echo number_format($item['price_per_serve'], 2); ?></td>
                        <td><?php echo $item['servings_sold']; ?> / <?php echo $item['number_of_servings']; ?></td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
              
              <div class="mt-3">
                <div class="stats-card mb-3">
                  <div class="stats-icon">
                    <i class="fas fa-chart-line"></i>
                  </div>
                  <div class="stats-info">
                    <p class="stats-label">Expected Sales</p>
                    <h3 class="stats-value">₱<?php echo number_format($total_expected, 2); ?></h3>
                  </div>
                </div>
                
                <div class="stats-card">
                  <div class="stats-icon">
                    <i class="fas fa-coins"></i>
                  </div>
                  <div class="stats-info">
                    <p class="stats-label">Actual Sales</p>
                    <h3 class="stats-value">₱<?php echo number_format($total_actual, 2); ?></h3>
                  </div>
                </div>
              </div>
            <?php } else { ?>
              <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>No menu items have been added for today yet.
              </div>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Calculate expected sales for new item
    document.addEventListener('DOMContentLoaded', function() {
      const calculateNewExpectedSales = function() {
        const servings = document.getElementById('number_of_servings').value || 0;
        const price = document.getElementById('price_per_serve').value || 0;
        const expected = servings * price;
        document.getElementById('new_expected_sales').value = expected.toFixed(2);
      };
      
      // Add event listeners for input fields
      document.getElementById('number_of_servings').addEventListener('input', calculateNewExpectedSales);
      document.getElementById('price_per_serve').addEventListener('input', calculateNewExpectedSales);
      
      // Calculate expected sales for edited item
      const calculateEditExpectedSales = function() {
        if (document.getElementById('edit_price_per_serve')) {
          const currentServings = <?php echo isset($selected_item) ? $selected_item['number_of_servings'] : 0; ?>;
          const additionalServings = parseInt(document.getElementById('additional_servings').value) || 0;
          const totalServings = currentServings + additionalServings;
          const price = document.getElementById('edit_price_per_serve').value || 0;
          const expected = totalServings * price;
          document.getElementById('edit_expected_sales').value = expected.toFixed(2);
        }
      };
      
      // Add event listeners for edit fields if they exist
      if (document.getElementById('additional_servings')) {
        document.getElementById('additional_servings').addEventListener('input', calculateEditExpectedSales);
      }
      if (document.getElementById('edit_price_per_serve')) {
        document.getElementById('edit_price_per_serve').addEventListener('input', calculateEditExpectedSales);
      }
    });
    
    // Form validation functions
    function validateNewForm() {
      // Simple validation could be added here
      return true;
    }
    
    function validateUpdateForm() {
      // Simple validation could be added here
      return true;
    }
    
    // Toggle mobile menu
    document.querySelector('.menu-toggle').addEventListener('click', function() {
      document.querySelector('.side-nav').classList.toggle('show');
    });
  </script>
</body>
</html>
