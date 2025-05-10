<?php
// Set the page title
$page_title = "Transaction Complete";

// Include the header
include 'header.php';

// Include database connection
include '../admin/connection.php';

// Check if transaction ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<div class="alert alert-danger">Transaction ID not provided.</div>';
    exit;
}

$transaction_id = $_GET['id'];

// Get transaction details
$sql = "SELECT * FROM transactions WHERE id = $transaction_id";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
    echo '<div class="alert alert-danger">Transaction not found.</div>';
    exit;
}

$transaction = mysqli_fetch_assoc($result);

// Get transaction items
$sql = "SELECT ti.*, m.menu_name 
        FROM transaction_items ti
        JOIN menu_items m ON ti.menu_item_id = m.id
        WHERE ti.transaction_id = $transaction_id";
$items_result = mysqli_query($conn, $sql);

if (!$items_result) {
    echo '<div class="alert alert-danger">Error fetching transaction items: ' . mysqli_error($conn) . '</div>';
    exit;
}
?>

<div class="card">
  <div class="card-header">
    <i class="fas fa-receipt"></i>
    <h4 class="card-title">Transaction Details</h4>
  </div>
  <div class="card-body">
    <div class="receipt-container">
      <div class="receipt-header">
        <div class="receipt-logo">Restaurant POS</div>
        <div class="receipt-subtitle">123 Main Street, City, Country</div>
        <div class="receipt-subtitle">Tel: (123) 456-7890</div>
        <div class="receipt-subtitle">Receipt #<?php echo str_pad($transaction_id, 6, '0', STR_PAD_LEFT); ?></div>
        <div class="receipt-subtitle"><?php echo date('F j, Y, g:i a', strtotime($transaction['transaction_date'])); ?></div>
      </div>
      
      <h5 class="mt-4 mb-2">Order Items</h5>
      <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
        <div class="receipt-item">
          <div>
            <span><?php echo $item['menu_name']; ?></span>
            <span class="text-muted">(<?php echo $item['quantity']; ?>x)</span>
          </div>
          <div>₱<?php echo number_format($item['subtotal'], 2); ?></div>
        </div>
      <?php endwhile; ?>
      
      <div class="receipt-total">
        <span>Total:</span>
        <span>₱<?php echo number_format($transaction['total_amount'], 2); ?></span>
      </div>
      
      <div class="receipt-info">
        <span>Amount Paid:</span>
        <span>₱<?php echo number_format($transaction['amount_paid'], 2); ?></span>
      </div>
      
      <div class="change-amount">₱<?php echo number_format($transaction['change_amount'], 2); ?></div>
      
      <div class="receipt-footer">
        <p>Thank you for your purchase!</p>
        <p>We appreciate your business.</p>
      </div>
    </div>
    
    <div class="d-flex justify-content-center mt-4 no-print">
      <button class="btn btn-primary me-2" onclick="window.print();">
        <i class="fas fa-print"></i> Print Receipt
      </button>
      <a href="take-customer-order.php" class="btn btn-success">
        <i class="fas fa-cart-plus"></i> New Order
      </a>
    </div>
  </div>
</div>

<?php
// Include the footer
include 'footer.php';
?>
