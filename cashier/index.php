<?php
// Set the page title
$page_title = "Cashier Dashboard";

// Include the header
include 'header.php';

// Log that cashier dashboard was accessed
log_activity('accessed cashier dashboard');
?>

<!-- Welcome message -->
<div class="alert alert-info mb-4">
  <i class="fas fa-info-circle"></i> 
  Welcome back, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!
  <?php
    // Get last login time from database
    $user_id = $_SESSION['user_id'];
    $last_login_query = "SELECT last_login FROM users WHERE id = $user_id";
    $last_login_result = mysqli_query($conn, $last_login_query);
    if ($last_login_result && mysqli_num_rows($last_login_result) > 0) {
      $last_login = mysqli_fetch_assoc($last_login_result)['last_login'];
      if ($last_login) {
        echo ' Your last login was: ' . date('F j, Y, g:i a', strtotime($last_login));
      } else {
        echo ' This is your first login.';
      }
    }
  ?>
</div>

<!-- Quick actions cards -->
<div class="row">
  <div class="col-md-4">
    <div class="card">
      <div class="card-body text-center">
        <div class="stats-icon">
          <i class="fas fa-cart-plus"></i>
        </div>
        <h5 class="card-title mt-3">Take Customer Order</h5>
        <p class="card-text">Create new customer orders and select menu items.</p>
        <a href="take-customer-order.php" class="btn btn-primary">Take Order</a>
      </div>
    </div>
  </div>
  
  <div class="col-md-4">
    <div class="card">
      <div class="card-body text-center">
        <div class="stats-icon">
          <i class="fas fa-list-alt"></i>
        </div>
        <h5 class="card-title mt-3">View Transactions</h5>
        <p class="card-text">View all completed transactions and details.</p>
        <a href="view-transactions.php" class="btn btn-primary">View Transactions</a>
      </div>
    </div>
  </div>
  
  <div class="col-md-4">
    <div class="card">
      <div class="card-body text-center">
        <div class="stats-icon">
          <i class="fas fa-ban"></i>
        </div>
        <h5 class="card-title mt-3">Request Void Transaction</h5>
        <p class="card-text">Request to void a completed transaction.</p>
        <a href="void-transaction.php" class="btn btn-danger">Void Transaction</a>
      </div>
    </div>
  </div>
</div>

<!-- Recent transactions -->
<div class="card mt-4">
  <div class="card-header">
    <i class="fas fa-history"></i>
    <h4 class="card-title">Recent Transactions</h4>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>Transaction ID</th>
            <th>Date & Time</th>
            <th>Items</th>
            <th>Total</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php
            // Get recent transactions
            $cashier_id = $_SESSION['user_id'];
            $transactions_query = "SELECT t.*, COUNT(ti.id) as total_items 
                                 FROM transactions t 
                                 LEFT JOIN transaction_items ti ON t.id = ti.transaction_id 
                                 GROUP BY t.id 
                                 ORDER BY transaction_date DESC LIMIT 5";
            $transactions_result = mysqli_query($conn, $transactions_query);
            
            if ($transactions_result && mysqli_num_rows($transactions_result) > 0) {
              while ($transaction = mysqli_fetch_assoc($transactions_result)) {
                // Get status class
                $status_class = 'status-completed';
                if ($transaction['status'] == 'void') {
                  $status_class = 'status-void';
                } elseif ($transaction['status'] == 'pending_void') {
                  $status_class = 'status-pending';
                }
                
                echo '<tr>
                        <td>' . $transaction['id'] . '</td>
                        <td>' . date('M d, g:i a', strtotime($transaction['transaction_date'])) . '</td>
                        <td>' . $transaction['total_items'] . '</td>
                        <td>$' . number_format($transaction['total_amount'], 2) . '</td>
                        <td><span class="transaction-status ' . $status_class . '">' . ucfirst($transaction['status']) . '</span></td>
                      </tr>';
              }
            } else {
              echo '<tr><td colspan="5" class="text-center">No recent transactions found.</td></tr>';
            }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php
// Include the footer
include 'footer.php';
?> 