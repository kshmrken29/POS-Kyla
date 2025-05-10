<?php
// Include authentication system if not already included
if (!function_exists('require_cashier')) {
    require_once '../auth_session.php';
    require_cashier();
}

// Database connection
if (!isset($conn)) {
    include_once '../admin/connection.php';
}

// Function to check if the current page matches the given page
function isActivePage($page) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return ($current_page == $page) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cashier System</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <div class="side-nav">
    <div class="logo-wrapper">
      <div class="logo">Cashier Panel</div>
    </div>
    
    <ul class="nav-links">
      <li class="nav-item">
        <a href="index.php" class="nav-link <?php echo isActivePage('index.php'); ?>">
          <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
      </li>
      
      <li class="nav-item">
        <a href="take-customer-order.php" class="nav-link <?php echo isActivePage('take-customer-order.php'); ?>">
          <i class="fas fa-cart-plus"></i> Take Order
        </a>
      </li>
      
      <li class="nav-item">
        <a href="view-transactions.php" class="nav-link <?php echo isActivePage('view-transactions.php'); ?>">
          <i class="fas fa-list-alt"></i> View Transactions
        </a>
      </li>
      
      <li class="nav-item">
        <a href="void-transaction.php" class="nav-link <?php echo isActivePage('void-transaction.php'); ?>">
          <i class="fas fa-ban"></i> Void Transaction
        </a>
      </li>
      
      <?php if (is_admin()): ?>
      <li class="nav-item">
        <a href="../admin/index.php" class="nav-link">
          <i class="fas fa-user-shield"></i> Admin Panel
        </a>
      </li>
      <?php endif; ?>
      
      <li class="nav-item">
        <a href="../logout.php" class="nav-link">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </li>
    </ul>
  </div>

  <div class="main-content">
    <div class="page-header">
      <h1 class="page-title"><?php echo isset($page_title) ? $page_title : 'Cashier System'; ?></h1>
      <div>
        <span class="badge bg-primary">
          <?php echo htmlspecialchars($_SESSION['username']); ?>
        </span>
      </div>
    </div>
    
    <!-- Menu toggle for responsive design -->
    <div class="menu-toggle">
      <i class="fas fa-bars"></i>
    </div>
</body>
</html> 