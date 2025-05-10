<?php
// Set the page title
$page_title = "Void Transaction";

// Include the header
include 'header.php';

// Include database connection
include '../admin/connection.php';

// Handle void request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['void_transaction'])) {
    $transaction_id = $_POST['transaction_id'];
    $reason = $_POST['void_reason'];
    
    // Update transaction status to 'void_requested'
    $sql = "UPDATE transactions SET status = 'void_requested', void_processed = FALSE WHERE id = $transaction_id";
    
    if (mysqli_query($conn, $sql)) {
        // Add void reason to a separate table or log for admin review
        echo '<div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                Void request submitted successfully. An administrator will review this request.
              </div>';
    } else {
        echo '<div class="alert alert-danger">
                <i class="fas fa-times-circle"></i>
                Error submitting void request: ' . mysqli_error($conn) . '
              </div>';
    }
}

// Get recent transactions
$sql = "SELECT t.*, COUNT(ti.id) as item_count 
        FROM transactions t
        LEFT JOIN transaction_items ti ON t.id = ti.transaction_id
        WHERE t.status = 'completed' AND (t.void_processed IS NULL OR t.void_processed = FALSE)
        GROUP BY t.id
        ORDER BY t.transaction_date DESC
        LIMIT 20";
        
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo '<div class="alert alert-danger">Error: ' . mysqli_error($conn) . '</div>';
}
?>

<div class="row">
  <div class="col-md-12">
    <div class="alert alert-warning">
      <i class="fas fa-exclamation-triangle"></i>
      <strong>Note:</strong> Voiding a transaction requires admin approval. This will restore menu item servings.
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <i class="fas fa-ban"></i>
    <h4 class="card-title">Recent Transactions</h4>
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
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($transaction = mysqli_fetch_assoc($result)): ?>
              <tr>
                <td><?php echo $transaction['id']; ?></td>
                <td><?php echo date('M d, Y H:i', strtotime($transaction['transaction_date'])); ?></td>
                <td>₱<?php echo number_format($transaction['total_amount'], 2); ?></td>
                <td><?php echo $transaction['item_count']; ?> items</td>
                <td><span class="transaction-status status-completed"><?php echo ucfirst($transaction['status']); ?></span></td>
                <td>
                  <button type="button" class="btn btn-sm btn-danger" 
                          data-bs-toggle="modal" 
                          data-bs-target="#voidModal" 
                          data-id="<?php echo $transaction['id']; ?>"
                          data-date="<?php echo date('M d, Y H:i', strtotime($transaction['transaction_date'])); ?>"
                          data-amount="₱<?php echo number_format($transaction['total_amount'], 2); ?>">
                    <i class="fas fa-ban"></i> Void
                  </button>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-info">No completed transactions found.</div>
    <?php endif; ?>
  </div>
</div>

<!-- Void Confirmation Modal -->
<div class="modal fade" id="voidModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Void Transaction</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post">
        <div class="modal-body">
          <p>Are you sure you want to request to void this transaction?</p>
          <div class="mb-3">
            <label class="form-label">Transaction ID:</label>
            <input type="text" class="form-control" id="modal-transaction-id" readonly>
            <input type="hidden" name="transaction_id" id="hidden-transaction-id">
          </div>
          <div class="mb-3">
            <label class="form-label">Date:</label>
            <input type="text" class="form-control" id="modal-transaction-date" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Amount:</label>
            <input type="text" class="form-control" id="modal-transaction-amount" readonly>
          </div>
          <div class="mb-3">
            <label for="void_reason" class="form-label">Reason for Void:</label>
            <textarea class="form-control" id="void_reason" name="void_reason" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="void_transaction" class="btn btn-danger">Submit Void Request</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // Update modal with transaction details
  document.addEventListener('DOMContentLoaded', function() {
    const voidModal = document.getElementById('voidModal');
    
    voidModal.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;
      const id = button.getAttribute('data-id');
      const date = button.getAttribute('data-date');
      const amount = button.getAttribute('data-amount');
      
      document.getElementById('modal-transaction-id').value = id;
      document.getElementById('hidden-transaction-id').value = id;
      document.getElementById('modal-transaction-date').value = date;
      document.getElementById('modal-transaction-amount').value = amount;
    });
  });
</script>

<?php
// Include the footer
include 'footer.php';
?>
