<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Capistra</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="../assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="../assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="../assets/plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="../assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="../assets/plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="../assets/plugins/summernote/summernote-bs4.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
 



























  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
      <img src="../assets/dist/img/middlelogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">Capistra</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
        <?php
// Default to icon if no image is set
$profileImage = isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image']) 
    ? htmlspecialchars($_SESSION['profile_image'])
    : null;

// Check if we need to go back one directory level
if ($profileImage) {
    // Adjust path to go back one level if needed
    $adjustedPath = '../' . ltrim($profileImage, '/');
    
    // Verify the file exists with the adjusted path
    if (file_exists($adjustedPath)) {
        // Display the actual image if it exists
        echo '<img class="img-profile rounded-circle" src="'.$adjustedPath.'" 
             style="width: 30px; height: 30px;"
             onerror="this.onerror=null;this.src=\'data:image/svg+xml;charset=UTF-8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'><circle cx=\'50\' cy=\'50\' r=\'40\' fill=\'%23ddd\'/><text x=\'50\' y=\'55\' text-anchor=\'middle\' font-size=\'40\' fill=\'%23666\'>👤</text></svg>\'">';
    } else {
        // Fallback to icon if image doesn't exist
        echo '<i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>';
    }
} else {
    // Display the Font Awesome icon as fallback if no image is set
    echo '<i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>';
}
?> </div>
         <div style="color: white;padding-left:10px;" class="iinfo">
        <?php 
                    // Display the username from session
                    if (isset($_SESSION['username'])) {
                        echo htmlspecialchars($_SESSION['username']);
                    } else {
                        echo "Guest";
                    }
                    ?>
        </div>
      </div>

      <!-- SidebarSearch Form -->
       

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
               <li class="nav-item has-treeview menu-open">
            <a href="../index.php" class="nav-link ">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Dashboard
            
              </p>
            </a>
    
          </li> 
           
          <li class="nav-item">
    <a href="../Income/income.php" class="nav-link">
        <i class="fas fa-dollar-sign nav-icon"></i>
        <p>
            Income 
        </p>
    </a>
</li>

<!-- Expense Menu Item -->
<li class="nav-item">
    <a href="../Expense/expense.php" class="nav-link">
        <i class="fas fa-money-bill-wave nav-icon"></i>
        <p>
            Expense  
        </p>
    </a>
</li>
<!-- Legacy KYC client registration / credential-vault links removed from the
     public build: those modules are not part of Capistra and are excluded from
     the repository. -->

<li class="nav-item">
    <a href="../investment/index.php" class="nav-link">
        <i class="fas fa-chart-line nav-icon"></i>
        <p>
            Investment  
        </p>
    </a>
</li>
<li class="nav-item has-treeview">
  <a href="#" class="nav-link">
    <i class="nav-icon fas fa-hand-holding-usd"></i>
    <p>
      Investors
      <i class="fas fa-angle-left right"></i>
      <span class="badge badge-info right">3</span>
    </p>
  </a>
  <ul class="nav nav-treeview">
    <!-- Investor Profiles -->
    <li class="nav-item">
      <a href="../fund/investorprofile.php" class="nav-link">
        <i class="nav-icon fas fa-user-tie"></i>
        <p>Investor Profiles</p>
      </a>
    </li>

    <!-- Funds -->
    <li class="nav-item">
      <a href="../fund/fundmanagement.php" class="nav-link">
        <i class="nav-icon fas fa-wallet"></i>
        <p>Fund Management</p>
      </a>
    </li>

    <!-- Returns -->
    <li class="nav-item">
      <a href="#" class="nav-link">
        <i class="nav-icon fas fa-chart-line"></i>
        <p>Return Management</p>
      </a>
    </li>
  </ul>
</li>
     
 









<li class="nav-item">
            <a href="pages/widgets.html" class="nav-link">
            <i class="nav-icon fas fa-user-tie"></i> 
              <p>
                Employee
                <i class="fas fa-angle-left right"></i>  <span class="badge badge-info right">12</span>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="../employee/register.php" class="nav-link">
                <i class="far fas fa-users  nav-icon"></i>
                  <p>Register</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="../employee/employee_management.php" class="nav-link">
                <i class="far fas fa-user-shield  nav-icon"></i>
                  <p>Data Storage</p>
                </a>
              </li>
           


            </ul>

          </li>
 <li class="nav-item has-treeview">
  <a href="#" class="nav-link">
    <i class="nav-icon fas fa-cogs"></i> 
    <p>
      Service
      <i class="fas fa-angle-left right"></i>
      <span class="badge badge-info right">6</span>
    </p>
  </a>
  <ul class="nav nav-treeview">
    <li class="nav-item">
      <a href="#" class="nav-link">
        <i class="nav-icon fas fa-briefcase"></i>
        <p>
          Investment Solutions
          <i class="fas fa-angle-left right"></i>
        </p>
      </a>
      <ul class="nav nav-treeview">
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-hand-holding-usd nav-icon"></i>
            <p>Investment Advisory</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="service/invest_soln/portfolio-manage.php" class="nav-link">
            <i class="fas fa-chart-pie nav-icon"></i>
            <p>Portfolio Management</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-building nav-icon"></i>
            <p>Real Estate Investments</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-file-invoice-dollar nav-icon"></i>
            <p>Mutual Funds & Bonds</p>
          </a>
        </li>
      </ul>
    </li>

    <li class="nav-item">
      <a href="#" class="nav-link">
        <i class="nav-icon fas fa-wallet"></i>
        <p>
          Wealth Planning
          <i class="fas fa-angle-left right"></i>
        </p>
      </a>
      <ul class="nav nav-treeview">
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-piggy-bank nav-icon"></i>
            <p>Wealth Management</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-dollar-sign nav-icon"></i>
            <p>Financial Planning</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-briefcase nav-icon"></i>
            <p>Corporate Advisory</p>
          </a>
        </li>
      </ul>
    </li>

    <li class="nav-item">
      <a href="#" class="nav-link">
        <i class="nav-icon fas fa-chart-line"></i>
        <p>
          Capital Market
          <i class="fas fa-angle-left right"></i>
        </p>
      </a>
      <ul class="nav nav-treeview">
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-newspaper nav-icon"></i>
            <p>IPO Advisory</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-search-dollar nav-icon"></i>
            <p>Market Research</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-shield-alt nav-icon"></i>
            <p>Risk Management</p>
          </a>
        </li>
      </ul>
    </li>

    <li class="nav-item">
      <a href="#" class="nav-link">
        <i class="nav-icon fas fa-user-tie"></i>
        <p>
          Account Management
          <i class="fas fa-angle-left right"></i>
        </p>
      </a>
      <ul class="nav nav-treeview">
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-user-circle nav-icon"></i>
            <p>Account Setup</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-id-card nav-icon"></i>
            <p>KYC Services</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-mobile-alt nav-icon"></i>
            <p>Digital Banking</p>
          </a>
        </li>
      </ul>
    </li>

    <li class="nav-item">
      <a href="#" class="nav-link">
        <i class="nav-icon fas fa-graduation-cap"></i>
        <p>
          Financial Education
          <i class="fas fa-angle-left right"></i>
        </p>
      </a>
      <ul class="nav nav-treeview">
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-book nav-icon"></i>
            <p>Customer Education Programs</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-shield-alt nav-icon"></i>
            <p>Insurance Services</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="fas fa-code nav-icon"></i>
            <p>FinTech Solutions</p>
          </a>
        </li>
      </ul>
    </li>
  </ul>
</li>



 
<br><br>
<li class="nav-item">
  <a href="../../backup_ui.php" class="nav-link">
    <i class="nav-icon fas fa-database"></i>
    <p>Backup</p>
  </a>
</li>
<li class="nav-item">
  <a href="../logout.php" class="nav-link">
    <i class="nav-icon fas fa-sign-out-alt"></i>
    <p>Logout</p>
  </a>
</li>


              
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>
