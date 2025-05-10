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
  <title>Manage Cashiers</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <style>
    .side-nav {
      background-color: #4b148b;
      color: white;
    }
    .btn-primary {
      background-color: #e91e63;
      border-color: #e91e63;
    }
    .btn-primary:hover {
      background-color: #d81b60;
      border-color: #d81b60;
    }
    .badge-success {
      background-color: #4caf50;
      color: white;
    }
    .badge-danger {
      background-color: #f44336;
      color: white;
    }
    .card {
      margin-bottom: 20px;
      border-radius: 4px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
    }
    .card-header {
      padding: 15px;
      border-bottom: 1px solid #eee;
    }
    .table th {
      text-transform: uppercase;
      font-size: 0.8rem;
    }
    .btn-edit {
      background-color: #e91e63;
      color: white;
      border: none;
    }
    .btn-delete {
      background-color: #f44336;
      color: white;
      border: none;
    }
    .btn-cancel {
      background-color: #9c27b0;
      color: white;
      border: none;
    }
    .btn-confirm-delete {
      background-color: #f44336;
      color: white;
      border: none;
    }
    .nav-link.active {
      background-color: rgba(255, 255, 255, 0.1);
    }
    .page-title {
      font-weight: bold;
      margin-bottom: 0;
    }
    #refreshButton {
      background-color: #e91e63;
      border: none;
    }
  </style>
</head>
<body>

  <div class="side-nav">
    <div class="logo-wrapper">
      <div class="logo">RESTAURANT<br>ADMIN</div>
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
        <a href="MenuManagement/sales-reporting.php" class="nav-link">
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
        <a href="manage-cashier.php" class="nav-link active">
          <i class="fas fa-users"></i> Manage Cashiers
        </a>
      </li>
      <li class="nav-item">
        <a href="process-void-requests.php" class="nav-link">
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
    <div class="page-header d-flex justify-content-between align-items-center">
      <h1 class="page-title">Manage Cashiers</h1>
      <div>
        <button type="button" class="btn btn-primary" id="refreshButton">
          <i class="fas fa-sync-alt"></i> REFRESH
        </button>
      </div>
    </div>
    
    <?php
    // Include database connection
    include '../connection.php';
    
    // Function to sanitize input
    function sanitize_input($data) {
        global $conn;
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return mysqli_real_escape_string($conn, $data);
    }

    // Initialize alert message
    $alert_message = '';
    $alert_type = '';

    // Handle form submissions
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Create new cashier
        if (isset($_POST['add_cashier'])) {
            $name = sanitize_input($_POST['name']);
            $username = sanitize_input($_POST['username']);
            $password = $_POST['password'];
            $contact = sanitize_input($_POST['contact']);
            $address = sanitize_input($_POST['address']);
            $date_hired = date('Y-m-d');
            $status = 'Active';
            
            // Validate inputs
            $errors = [];
            
            if (empty($name)) {
                $errors[] = "Name is required";
            }
            
            if (empty($username)) {
                $errors[] = "Username is required";
            } else {
                // Check if username already exists
                $check_query = "SELECT * FROM cashiers WHERE username = ?";
                $stmt = mysqli_prepare($conn, $check_query);
                mysqli_stmt_bind_param($stmt, "s", $username);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($result) > 0) {
                    $errors[] = "Username already exists";
                }
            }
            
            if (empty($password)) {
                $errors[] = "Password is required";
            } elseif (strlen($password) < 6) {
                $errors[] = "Password must be at least 6 characters";
            }
            
            if (empty($contact)) {
                $errors[] = "Contact number is required";
            }
            
            if (empty($address)) {
                $errors[] = "Address is required";
            }
            
            if (empty($errors)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Use prepared statement to prevent SQL injection
                $sql = "INSERT INTO cashiers (name, username, password, contact, address, date_hired, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sssssss", $name, $username, $hashed_password, $contact, $address, $date_hired, $status);
                
                if (mysqli_stmt_execute($stmt)) {
                    $alert_type = "success";
                    $alert_message = "Cashier added successfully!";
                } else {
                    $alert_type = "danger";
                    $alert_message = "Error adding cashier: " . mysqli_error($conn);
                }
                
                mysqli_stmt_close($stmt);
            } else {
                $alert_type = "danger";
                $alert_message = "Please correct the following errors:<br>" . implode("<br>", $errors);
            }
        }
        
        // Update cashier
        if (isset($_POST['update_cashier'])) {
            $cashier_id = filter_var($_POST['cashier_id'], FILTER_VALIDATE_INT);
            $name = sanitize_input($_POST['name']);
            $username = sanitize_input($_POST['username']);
            $contact = sanitize_input($_POST['contact']);
            $address = sanitize_input($_POST['address']);
            $status = sanitize_input($_POST['status']);
            $password = $_POST['password'];
            
            // Validate inputs
            $errors = [];
            
            if (!$cashier_id) {
                $errors[] = "Invalid cashier ID";
            }
            
            if (empty($name)) {
                $errors[] = "Name is required";
            }
            
            if (empty($username)) {
                $errors[] = "Username is required";
            } else {
                // Check if username already exists (excluding current cashier)
                $check_query = "SELECT * FROM cashiers WHERE username = ? AND id != ?";
                $stmt = mysqli_prepare($conn, $check_query);
                mysqli_stmt_bind_param($stmt, "si", $username, $cashier_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($result) > 0) {
                    $errors[] = "Username already exists";
                }
            }
            
            if (!empty($password) && strlen($password) < 6) {
                $errors[] = "Password must be at least 6 characters";
            }
            
            if (empty($contact)) {
                $errors[] = "Contact number is required";
            }
            
            if (empty($address)) {
                $errors[] = "Address is required";
            }
            
            if (empty($errors)) {
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    $sql = "UPDATE cashiers SET 
                            name = ?, username = ?, password = ?, contact = ?, address = ?, status = ?
                            WHERE id = ?";
                    
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "ssssssi", $name, $username, $hashed_password, $contact, $address, $status, $cashier_id);
                } else {
                    $sql = "UPDATE cashiers SET 
                            name = ?, username = ?, contact = ?, address = ?, status = ?
                            WHERE id = ?";
                    
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "sssssi", $name, $username, $contact, $address, $status, $cashier_id);
                }
                
                if (mysqli_stmt_execute($stmt)) {
                    $alert_type = "success";
                    $alert_message = "Cashier updated successfully!";
                } else {
                    $alert_type = "danger";
                    $alert_message = "Error updating cashier: " . mysqli_error($conn);
                }
                
                mysqli_stmt_close($stmt);
            } else {
                $alert_type = "danger";
                $alert_message = "Please correct the following errors:<br>" . implode("<br>", $errors);
            }
        }
        
        // Delete cashier
        if (isset($_POST['delete_cashier'])) {
            $cashier_id = filter_var($_POST['cashier_id'], FILTER_VALIDATE_INT);
            
            if ($cashier_id) {
                $sql = "DELETE FROM cashiers WHERE id = ?";
                
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $cashier_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $alert_type = "success";
                    $alert_message = "Cashier deleted successfully!";
                } else {
                    $alert_type = "danger";
                    $alert_message = "Error deleting cashier: " . mysqli_error($conn);
                }
                
                mysqli_stmt_close($stmt);
            } else {
                $alert_type = "danger";
                $alert_message = "Invalid cashier ID";
            }
        }
    }
    
    // Display alert message if set
    if (!empty($alert_message)) {
        echo '<div class="alert alert-' . $alert_type . '" role="alert">
                <i class="bi bi-' . ($alert_type == 'success' ? 'check-circle' : 'exclamation-triangle') . '"></i>
                ' . $alert_message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
    
    // Get cashier data for edit form if ID is provided
    $editCashier = null;
    if (isset($_GET['edit_id'])) {
        $edit_id = filter_var($_GET['edit_id'], FILTER_VALIDATE_INT);
        
        if ($edit_id) {
            $sql = "SELECT * FROM cashiers WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $edit_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                $editCashier = mysqli_fetch_assoc($result);
            }
            
            mysqli_stmt_close($stmt);
        }
    }
    
    // Get all cashiers for the table
    $sql = "SELECT * FROM cashiers ORDER BY name";
    $cashiers = mysqli_query($conn, $sql);
    ?>
    
    <div class="row">
        <!-- Form Column -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-<?php echo ($editCashier) ? 'edit' : 'plus-circle'; ?>"></i>
                        <?php echo ($editCashier) ? 'Edit Cashier' : 'Add New Cashier'; ?>
                    </h4>
                </div>
                <div class="card-body">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="needs-validation" novalidate>
                        <?php if ($editCashier): ?>
                            <input type="hidden" name="cashier_id" value="<?php echo $editCashier['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                value="<?php echo ($editCashier) ? htmlspecialchars($editCashier['name']) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                value="<?php echo ($editCashier) ? htmlspecialchars($editCashier['username']) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <?php echo ($editCashier) ? 'Password (leave blank to keep current)' : 'Password'; ?>
                            </label>
                            <input type="password" class="form-control" id="password" name="password" 
                                <?php echo ($editCashier) ? '' : 'required'; ?>>
                            <div class="form-text">
                                <?php echo ($editCashier) ? 'Leave blank to keep current password.' : ''; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="contact" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="contact" name="contact" 
                                value="<?php echo ($editCashier) ? htmlspecialchars($editCashier['contact']) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required><?php echo ($editCashier) ? htmlspecialchars($editCashier['address']) : ''; ?></textarea>
                        </div>
                        
                        <?php if ($editCashier): ?>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="Active" <?php echo ($editCashier['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="Inactive" <?php echo ($editCashier['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" name="update_cashier" class="btn btn-primary w-100">
                                    <i class="fas fa-save"></i> UPDATE CASHIER
                                </button>
                            </div>
                            <div class="d-grid mt-2">
                                <a href="manage-cashier.php" class="btn btn-cancel w-100">
                                    CANCEL
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="d-grid">
                                <button type="submit" name="add_cashier" class="btn btn-primary w-100">
                                    <i class="fas fa-plus-circle"></i> ADD CASHIER
                                </button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Table Column -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">
                            <i class="fas fa-users"></i> Cashier List
                        </h4>
                        <input type="text" id="searchInput" class="form-control form-control-sm w-25" placeholder="Search...">
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="cashierTable">
                            <thead>
                                <tr>
                                    <th>NAME</th>
                                    <th>USERNAME</th>
                                    <th>CONTACT</th>
                                    <th>DATE HIRED</th>
                                    <th>STATUS</th>
                                    <th>ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if (mysqli_num_rows($cashiers) > 0) {
                                    while($cashier = mysqli_fetch_assoc($cashiers)) {
                                        echo '<tr>
                                                <td>' . htmlspecialchars($cashier['name']) . '</td>
                                                <td>' . htmlspecialchars($cashier['username']) . '</td>
                                                <td>' . htmlspecialchars($cashier['contact']) . '</td>
                                                <td>' . htmlspecialchars($cashier['date_hired']) . '</td>
                                                <td>
                                                    <span class="badge ' . ($cashier['status'] == 'Active' ? 'bg-success' : 'bg-danger') . '">
                                                        ' . $cashier['status'] . '
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="?edit_id=' . $cashier['id'] . '" class="btn btn-sm btn-edit">
                                                        EDIT
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-delete" 
                                                        data-bs-toggle="modal" data-bs-target="#deleteModal' . $cashier['id'] . '">
                                                        DELETE
                                                    </button>
                                                </td>
                                            </tr>';
                                            
                                        // Delete confirmation modal
                                        echo '<div class="modal fade" id="deleteModal' . $cashier['id'] . '" tabindex="-1" aria-hidden="true">
                                              <div class="modal-dialog">
                                                <div class="modal-content">
                                                  <div class="modal-header">
                                                    <h5 class="modal-title">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                  </div>
                                                  <div class="modal-body">
                                                    <p>Are you sure you want to delete this cashier?</p>
                                                    <div class="text-center">
                                                        <p><strong>NAME</strong><br>' . htmlspecialchars($cashier['name']) . '</p>
                                                        <p><strong>USERNAME</strong><br>' . htmlspecialchars($cashier['username']) . '</p>
                                                    </div>
                                                  </div>
                                                  <div class="modal-footer justify-content-center">
                                                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">CANCEL</button>
                                                    <form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">
                                                        <input type="hidden" name="cashier_id" value="' . $cashier['id'] . '">
                                                        <button type="submit" name="delete_cashier" class="btn btn-confirm-delete">
                                                            CONFIRM DELETE
                                                        </button>
                                                    </form>
                                                  </div>
                                                </div>
                                              </div>
                                            </div>';
                                    }
                                } else {
                                    echo '<tr><td colspan="6" class="text-center">No cashiers found</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Form validation
    (function() {
      'use strict';
      
      // Fetch all forms to apply validation
      var forms = document.querySelectorAll('.needs-validation');
      
      // Loop and prevent submission
      Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          
          form.classList.add('was-validated');
        }, false);
      });
    })();
    
    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
      const searchValue = this.value.toLowerCase();
      const table = document.getElementById('cashierTable');
      const rows = table.getElementsByTagName('tr');
      
      for (let i = 1; i < rows.length; i++) {
        let found = false;
        const cells = rows[i].getElementsByTagName('td');
        
        for (let j = 0; j < cells.length; j++) {
          if (cells[j]) {
            const cellText = cells[j].textContent || cells[j].innerText;
            
            if (cellText.toLowerCase().indexOf(searchValue) > -1) {
              found = true;
              break;
            }
          }
        }
        
        rows[i].style.display = found ? '' : 'none';
      }
    });

    // Refresh button functionality
    document.getElementById('refreshButton').addEventListener('click', function() {
      window.location.href = 'manage-cashier.php';
    });
  </script>
</body>
</html>
