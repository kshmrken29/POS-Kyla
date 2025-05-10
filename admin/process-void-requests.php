<?php
// Include authentication system
require_once '../auth_session.php';
require_admin();

// Log that void requests page was accessed
log_activity('accessed void requests page');

include 'connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Process Void Requests</title>
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
        <a href="index.php" class="nav-link">
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
        <a href="process-void-requests.php" class="nav-link active">
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
      <h1 class="page-title">Process Void Requests</h1>
      <div>
        <span class="badge bg-primary">
          <?php echo htmlspecialchars($_SESSION['username']); ?>
        </span>
      </div>
    </div>
    
    <?php
    // Handle approval/rejection
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['approve_void'])) {
            $transaction_id = $_POST['transaction_id'];
            
            // Start transaction to ensure data integrity
            mysqli_begin_transaction($conn);
            
            try {
                // Get transaction items
                $items_sql = "SELECT menu_item_id, quantity FROM transaction_items WHERE transaction_id = $transaction_id";
                $items_result = mysqli_query($conn, $items_sql);
                
                if (!$items_result) {
                    throw new Exception("Error getting transaction items: " . mysqli_error($conn));
                }
                
                // Update each menu item to return servings
                while ($item = mysqli_fetch_assoc($items_result)) {
                    $menu_item_id = $item['menu_item_id'];
                    $quantity = $item['quantity'];
                    
                    $update_sql = "UPDATE menu_items 
                                  SET servings_sold = servings_sold - $quantity 
                                  WHERE id = $menu_item_id";
                    
                    if (!mysqli_query($conn, $update_sql)) {
                        throw new Exception("Error updating menu item: " . mysqli_error($conn));
                    }
                }
                
                // Update transaction status
                $update_transaction = "UPDATE transactions SET status = 'voided', void_processed = TRUE WHERE id = $transaction_id";
                
                if (!mysqli_query($conn, $update_transaction)) {
                    throw new Exception("Error updating transaction: " . mysqli_error($conn));
                }
                
                // Commit transaction
                mysqli_commit($conn);
                
                echo '<div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Transaction #' . $transaction_id . ' has been voided successfully.
                      </div>';
                
            } catch (Exception $e) {
                // Rollback transaction on error
                mysqli_rollback($conn);
                
                echo '<div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> Error: ' . $e->getMessage() . '
                      </div>';
            }
        } else if (isset($_POST['reject_void'])) {
            $transaction_id = $_POST['transaction_id'];
            
            // Update transaction status back to completed and mark as processed
            $update_sql = "UPDATE transactions SET status = 'completed', void_processed = TRUE WHERE id = $transaction_id";
            
            if (mysqli_query($conn, $update_sql)) {
                echo '<div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Void request for Transaction #' . $transaction_id . ' has been rejected.
                      </div>';
            } else {
                echo '<div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> Error: ' . mysqli_error($conn) . '
                      </div>';
            }
        }
    }
    
    // Get pending void requests - only show unprocessed void requests
    $sql = "SELECT t.*, COUNT(ti.id) as item_count, SUM(ti.subtotal) as total_value
            FROM transactions t
            LEFT JOIN transaction_items ti ON t.id = ti.transaction_id
            WHERE t.status = 'void_requested' AND (t.void_processed IS NULL OR t.void_processed = FALSE)
            GROUP BY t.id
            ORDER BY t.transaction_date DESC";
            
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Error: ' . mysqli_error($conn) . '</div>';
    }
    ?>
    
    <div class="card">
      <div class="card-header">
        <i class="fas fa-ban"></i>
        <h4 class="card-title">Pending Void Requests</h4>
      </div>
      <div class="card-body">
        <?php if (mysqli_num_rows($result) > 0): ?>
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Date</th>
                  <th>Total Amount</th>
                  <th>Items</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($transaction = mysqli_fetch_assoc($result)): ?>
                  <tr>
                    <td><?php echo $transaction['id']; ?></td>
                    <td><?php echo date('M d, Y H:i', strtotime($transaction['transaction_date'])); ?></td>
                    <td>$<?php echo number_format($transaction['total_amount'], 2); ?></td>
                    <td>
                      <span class="badge badge-success">
                        <i class="fas fa-shopping-basket"></i> <?php echo $transaction['item_count']; ?> items
                      </span>
                    </td>
                    <td>
                      <a href="view-transaction-details.php?id=<?php echo $transaction['id']; ?>" class="btn btn-sm btn-secondary">
                        <i class="fas fa-eye"></i> View
                      </a>
                      <form method="post" class="d-inline-block">
                        <input type="hidden" name="transaction_id" value="<?php echo $transaction['id']; ?>">
                        <button type="submit" name="approve_void" class="btn btn-sm btn-success">
                          <i class="fas fa-check"></i> Approve
                        </button>
                      </form>
                      <form method="post" class="d-inline-block">
                        <input type="hidden" name="transaction_id" value="<?php echo $transaction['id']; ?>">
                        <button type="submit" name="reject_void" class="btn btn-sm btn-danger">
                          <i class="fas fa-times"></i> Reject
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No pending void requests.
          </div>
        <?php endif; ?>
      </div>
    </div>
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