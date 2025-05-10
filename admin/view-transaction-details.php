<?php
// Include authentication system
require_once '../auth_session.php';
require_admin();

// Log that transaction details was accessed
log_activity('viewed transaction details');

include 'connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Transaction Details</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <style>
    .receipt {
      max-width: 800px;
      margin: 0 auto;
      background-color: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    @media print {
      .no-print {
        display: none !important;
      }
      .receipt {
        box-shadow: none;
        max-width: 100%;
      }
      body {
        background-color: white !important;
      }
      .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
      }
    }
  </style>
</head>
<body>

  <div class="side-nav no-print">
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
        <a href="./MenuManagement/manage-cashier.php" class="nav-link">
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
    <div class="page-header no-print">
      <h1 class="page-title">Transaction Details</h1>
      <div>
        <button onclick="window.print();" class="btn btn-primary">
          <i class="fas fa-print"></i> Print
        </button>
        <a href="process-void-requests.php" class="btn btn-secondary ms-2">
          <i class="fas fa-arrow-left"></i> Back
        </a>
      </div>
    </div>

    <?php
    // Check if transaction ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Transaction ID not provided.</div>';
        exit;
    }
    
    $transaction_id = $_GET['id'];
    
    // Get transaction details
    $sql = "SELECT * FROM transactions WHERE id = $transaction_id";
    $result = mysqli_query($conn, $sql);
    
    if (!$result || mysqli_num_rows($result) === 0) {
        echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Transaction not found.</div>';
        exit;
    }
    
    $transaction = mysqli_fetch_assoc($result);
    
    // Get transaction items with menu details
    $sql = "SELECT ti.*, m.menu_name, m.price_per_serve, m.approximate_cost
            FROM transaction_items ti
            JOIN menu_items m ON ti.menu_item_id = m.id
            WHERE ti.transaction_id = $transaction_id";
    $items_result = mysqli_query($conn, $sql);
    
    if (!$items_result) {
        echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Error fetching transaction items: ' . mysqli_error($conn) . '</div>';
        exit;
    }
    
    // Get status badge class
    $status_class = 'badge-success';
    $status_text = 'Completed';
    $status_icon = 'fa-check-circle';
    
    if ($transaction['status'] == 'void_requested') {
        $status_class = 'badge-warning';
        $status_text = 'Void Requested';
        $status_icon = 'fa-clock';
    } else if ($transaction['status'] == 'voided') {
        $status_class = 'badge-danger';
        $status_text = 'Voided';
        $status_icon = 'fa-ban';
    }
    ?>
    
    <div class="receipt">
      <div class="card">
        <div class="card-header">
          <i class="fas fa-receipt"></i>
          <h4 class="card-title">
            Transaction #<?php echo str_pad($transaction_id, 6, '0', STR_PAD_LEFT); ?>
            <span class="badge <?php echo $status_class; ?> ms-2">
              <i class="fas <?php echo $status_icon; ?>"></i> <?php echo $status_text; ?>
            </span>
          </h4>
        </div>
        <div class="card-body">
          <div class="row mb-4">
            <div class="col-md-6">
              <p><strong><i class="fas fa-calendar-alt"></i> Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($transaction['transaction_date'])); ?></p>
              <p><strong><i class="fas fa-shopping-basket"></i> Total Items:</strong> <?php echo mysqli_num_rows($items_result); ?></p>
            </div>
            <div class="col-md-6">
              <p><strong><i class="fas fa-money-bill-wave"></i> Total Amount:</strong> $<?php echo number_format($transaction['total_amount'], 2); ?></p>
              <p><strong><i class="fas fa-hand-holding-usd"></i> Amount Paid:</strong> $<?php echo number_format($transaction['amount_paid'], 2); ?></p>
              <p><strong><i class="fas fa-exchange-alt"></i> Change:</strong> $<?php echo number_format($transaction['change_amount'], 2); ?></p>
            </div>
          </div>
          
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th>Item</th>
                  <th class="text-center">Quantity</th>
                  <th class="text-end">Unit Price</th>
                  <th class="text-end">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                $total_items = 0;
                $total_cost = 0;
                $profit = 0;
                
                mysqli_data_seek($items_result, 0); // Reset result pointer
                while ($item = mysqli_fetch_assoc($items_result)): 
                  $total_items += $item['quantity'];
                  $item_cost = $item['approximate_cost'] * $item['quantity'];
                  $total_cost += $item_cost;
                  $item_profit = $item['subtotal'] - $item_cost;
                  $profit += $item_profit;
                ?>
                  <tr>
                    <td><?php echo $item['menu_name']; ?></td>
                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                    <td class="text-end">$<?php echo number_format($item['price_per_item'], 2); ?></td>
                    <td class="text-end">$<?php echo number_format($item['subtotal'], 2); ?></td>
                  </tr>
                <?php endwhile; ?>
                <tr class="table-secondary">
                  <td colspan="3" class="text-end fw-bold">Total:</td>
                  <td class="text-end fw-bold">$<?php echo number_format($transaction['total_amount'], 2); ?></td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <?php if ($transaction['status'] == 'void_requested'): ?>
          <div class="mt-4 d-flex justify-content-center no-print">
            <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#approveModal">
              <i class="fas fa-check"></i> Approve Void
            </button>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
              <i class="fas fa-times"></i> Reject Void
            </button>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Menu toggle for responsive design -->
  <div class="menu-toggle no-print">
    <i class="fas fa-bars"></i>
  </div>
  
  <!-- Approve Modal -->
  <div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">Approve Void Request</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to approve the void request for Transaction #<?php echo $transaction_id; ?>?</p>
          <p><strong>Warning:</strong> This will restore the menu item servings and mark the transaction as voided.</p>
        </div>
        <div class="modal-footer">
          <form method="post" action="process-void-requests.php">
            <input type="hidden" name="transaction_id" value="<?php echo $transaction_id; ?>">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="approve_void" class="btn btn-success">Approve Void</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Reject Modal -->
  <div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">Reject Void Request</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to reject the void request for Transaction #<?php echo $transaction_id; ?>?</p>
        </div>
        <div class="modal-footer">
          <form method="post" action="process-void-requests.php">
            <input type="hidden" name="transaction_id" value="<?php echo $transaction_id; ?>">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="reject_void" class="btn btn-danger">Reject Void</button>
          </form>
        </div>
      </div>
    </div>
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