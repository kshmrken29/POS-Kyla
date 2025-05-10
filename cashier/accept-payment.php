<?php
// Set the page title
$page_title = "Accept Payment";

// Include the header
include 'header.php';

// Process the payment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['process_payment'])) {
    $transaction_id = $_POST['transaction_id'] ?? null;
    $total_amount = $_POST['total_amount'] ?? 0;
    $amount_paid = $_POST['amount_paid'] ?? 0;
    $change_amount = $_POST['change_amount'] ?? 0;
    
    if ($transaction_id && $amount_paid >= $total_amount) {
        // Update the transaction with payment details
        $sql = "UPDATE transactions 
                SET amount_paid = ?, change_amount = ?, status = 'completed'
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ddi", $amount_paid, $change_amount, $transaction_id);
        
        if ($stmt->execute()) {
            // Redirect to display change page
            header("Location: display-change.php?id=" . $transaction_id);
            exit;
        } else {
            echo '<div class="alert alert-danger">
                    <i class="fas fa-times-circle"></i> Error processing payment: ' . $conn->error . '
                  </div>';
        }
    } else {
        echo '<div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> Invalid payment data.
              </div>';
    }
}

// Get transaction ID from URL
$transaction_id = $_GET['id'] ?? null;
if (!$transaction_id) {
    echo '<div class="alert alert-danger">Transaction ID not provided.</div>';
    exit;
}

// Get transaction details
$sql = "SELECT t.*, COUNT(ti.id) as item_count, SUM(ti.quantity) as total_items
        FROM transactions t
        LEFT JOIN transaction_items ti ON t.id = ti.transaction_id
        WHERE t.id = ?
        GROUP BY t.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $transaction_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="alert alert-danger">Transaction not found.</div>';
    exit;
}

$transaction = $result->fetch_assoc();

// Get transaction items
$sql = "SELECT ti.*, m.menu_name 
        FROM transaction_items ti
        JOIN menu_items m ON ti.menu_item_id = m.id
        WHERE ti.transaction_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $transaction_id);
$stmt->execute();
$items_result = $stmt->get_result();
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-shopping-basket"></i>
                <h4 class="card-title">Order Summary</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $items_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $item['menu_name']; ?></td>
                                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                                    <td class="text-end">₱<?php echo number_format($item['price_per_item'], 2); ?></td>
                                    <td class="text-end">₱<?php echo number_format($item['subtotal'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">Total:</td>
                                <td class="text-end">₱<?php echo number_format($transaction['total_amount'], 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-credit-card"></i>
                <h4 class="card-title">Payment</h4>
            </div>
            <div class="card-body">
                <form method="post" id="paymentForm">
                    <input type="hidden" name="transaction_id" value="<?php echo $transaction_id; ?>">
                    <input type="hidden" name="total_amount" value="<?php echo $transaction['total_amount']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Total Amount:</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="text" class="form-control" value="<?php echo number_format($transaction['total_amount'], 2); ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount_paid" class="form-label">Amount Paid:</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" id="amount_paid" name="amount_paid" step="0.01" min="<?php echo $transaction['total_amount']; ?>" required>
                        </div>
                        <div class="invalid-feedback" id="paymentError">
                            Amount paid must be greater than or equal to the total amount.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Change:</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="text" class="form-control" id="change_amount" name="change_amount" readonly>
                        </div>
                    </div>
                    
                    <div class="payment-options mb-4">
                        <div class="payment-option" data-value="<?php echo $transaction['total_amount']; ?>">
                            <i class="fas fa-money-bill"></i> Exact
                        </div>
                        <div class="payment-option" data-value="<?php echo ceil($transaction['total_amount'] / 50) * 50; ?>">
                            <i class="fas fa-money-bill"></i> ₱<?php echo ceil($transaction['total_amount'] / 50) * 50; ?>
                        </div>
                        <div class="payment-option" data-value="<?php echo ceil($transaction['total_amount'] / 100) * 100; ?>">
                            <i class="fas fa-money-bill"></i> ₱<?php echo ceil($transaction['total_amount'] / 100) * 100; ?>
                        </div>
                        <div class="payment-option" data-value="<?php echo ceil($transaction['total_amount'] / 500) * 500; ?>">
                            <i class="fas fa-money-bill"></i> ₱<?php echo ceil($transaction['total_amount'] / 500) * 500; ?>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" name="process_payment" class="btn btn-success" id="completePaymentBtn" disabled>
                            <i class="fas fa-check-circle"></i> Complete Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountPaid = document.getElementById('amount_paid');
    const changeAmount = document.getElementById('change_amount');
    const completePaymentBtn = document.getElementById('completePaymentBtn');
    const totalAmount = <?php echo $transaction['total_amount']; ?>;
    
    // Payment amount presets
    const paymentOptions = document.querySelectorAll('.payment-option');
    paymentOptions.forEach(option => {
        option.addEventListener('click', function() {
            const value = parseFloat(this.getAttribute('data-value'));
            amountPaid.value = value.toFixed(2);
            calculateChange();
            
            // Mark this option as selected
            paymentOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
    
    // Calculate change
    function calculateChange() {
        const paid = parseFloat(amountPaid.value) || 0;
        
        if (paid >= totalAmount) {
            const change = paid - totalAmount;
            changeAmount.value = change.toFixed(2);
            amountPaid.classList.remove('is-invalid');
            completePaymentBtn.disabled = false;
        } else {
            changeAmount.value = '';
            amountPaid.classList.add('is-invalid');
            completePaymentBtn.disabled = true;
        }
    }
    
    // Listen for amount changes
    amountPaid.addEventListener('input', calculateChange);
});
</script>

<?php
// Include the footer
include 'footer.php';
?>
