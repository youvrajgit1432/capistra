<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AdminLTE 3 | Dashboard</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../assets/plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="../../assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="../../assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="../../assets/plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../assets/dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="../../assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="../../assets/plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="../../assets/plugins/summernote/summernote-bs4.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="../../index.php" class="nav-link">Home</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Navbar Search -->
      <li class="nav-item">
        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
          <i class="fas fa-search"></i>
        </a>
        <div class="navbar-search-block">
          <form class="form-inline">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
              <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                  <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
      </li>

      <!-- Messages Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-comments"></i>
          <span class="badge badge-danger navbar-badge">3</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="../assets/dist/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Brad Diesel
                  <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">Call me whenever you can...</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="../assets/dist/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  John Pierce
                  <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">I got your message bro</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="../assets/dist/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Nora Silvester
                  <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">The subject goes here</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
        </div>
      </li>
      <!-- Notifications Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-bell"></i>
          <span class="badge badge-warning navbar-badge">15</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header">15 Notifications</span>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-envelope mr-2"></i> 4 new messages
            <span class="float-right text-muted text-sm">3 mins</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-users mr-2"></i> 8 friend requests
            <span class="float-right text-muted text-sm">12 hours</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-file mr-2"></i> 3 new reports
            <span class="float-right text-muted text-sm">2 days</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
        </div>
      </li>

      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>

      <li class="nav-item dropdown no-arrow">
    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="mr-2 d-none d-lg-inline text-gray-600 small">Demo Administrator </span>
        <img class="img-profile rounded-circle"
             src="../assets/dist/img/undraw_profile.svg" style="width: 30px; height: 30px;">
    </a>
    <!-- Dropdown - User Information -->
    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
         aria-labelledby="userDropdown">
        <a class="dropdown-item" href="#">
            <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
            Profile
        </a>
        <a class="dropdown-item" href="#">
            <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
            Settings
        </a>
        <a class="dropdown-item" href="#">
            <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
            Activity Log
        </a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
            Logout
        </a>
    </div>
</li>


     
      <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-controlsidebar-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li>
      
    </ul>
  </nav>
  <!-- /.navbar -->
   
  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
      <img src="../../assets/dist/img/middlelogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">Global tech</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="../../assets/dist/img/director.jpg" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block">Capistra</a>
        </div>
      </div>

      <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
               <li class="nav-item has-treeview menu-open">
            <a href="../../index.php" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Dashboard
            
              </p>
            </a>
    
          </li> 
           
          <li class="nav-item">
    <a href="../../fund/fundtransfer.php" class="nav-link">
    <i class="fas fa-exchange-alt nav-icon"></i>
        <p>
            Fund Transfer
        </p>
    </a>
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
                <a href="../../employee/all.php" class="nav-link">
                <i class="far fas fa-users  nav-icon"></i>
                  <p>  All</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="../../employee/admin.php" class="nav-link">
                <i class="far fas fa-user-shield  nav-icon"></i>
                  <p>Admin</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="../../employee/accountant.php" class="nav-link">
                <i class="far fas fa-calculator  nav-icon"></i>
                  <p>Accountant</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="../../employee/customer_manager.php" class="nav-link">
                <i class="far fas fa-user-cog  nav-icon"></i>
                  <p>Customer Manager</p>
                </a>
              </li>
              
              <li class="nav-item">
                <a href="../../employee/hrmanager.php" class="nav-link">
                <i class="fas fa-user-tie  nav-icon"></i>

                  <p>Hr Manager </p>
                </a>
              </li>

              <li class="nav-item">
                <a href="../../employee/legal_team.php" class="nav-link">
                <i class="fas fa-gavel  nav-icon"></i>

                  <p>Legal Dewpartment </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="../../employee/itdeveloper.php" class="nav-link">
                <i class="fas fa-desktop  nav-icon"></i>

                  <p>IT Developer </p>
                </a>
              </li>


              <li class="nav-item">
                <a href="../../employee/dataanalyst.php" class="nav-link">
                <i class="fas fa-chart-line  nav-icon"></i>

                  <p>Data Analyst </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="../../employee/research_team.php" class="nav-link">
                <i class="fas fa-flask  nav-icon"></i>

                  <p>Research Team </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="../../employee/marketing.php" class="nav-link">
                <i class="fas fa-bullhorn  nav-icon"></i>

                  <p>Marketing </p>
                </a>
              </li>

              <li class="nav-item">
                <a href="../../employee/securityguard.php" class="nav-link">
                <i class="fas fa-shield-alt  nav-icon"></i>

                  <p>security Guard </p>
                </a>
              </li>

              
                <li class="nav-item">
                <a href="../../employee/intern.php" class="nav-link">
                <i class="fas fa-graduation-cap nav-icon"></i>

                  <p>Intern </p>
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
          <a href="../invest_soln/invest_advisor.php" class="nav-link">
            <i class="fas fa-hand-holding-usd nav-icon"></i>
            <p>Investment Advisory</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="../invest_soln/portfolio-manage.php" class="nav-link">
            <i class="fas fa-chart-pie nav-icon"></i>
            <p>Portfolio Management</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="../invest_soln/realstate.php" class="nav-link">
            <i class="fas fa-building nav-icon"></i>
            <p>Real Estate Investments</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="../invest_soln/mutalbond.php" class="nav-link">
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
          <a href="../wealthplan/wealth_manage.php" class="nav-link">
            <i class="fas fa-piggy-bank nav-icon"></i>
            <p>Wealth Management</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="../wealthplan/financialplan.php" class="nav-link">
            <i class="fas fa-dollar-sign nav-icon"></i>
            <p>Financial Planning</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="../wealthplan/corporadvisor.php" class="nav-link">
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
          <a href="../capitalmarket/ipoadvisory.php" class="nav-link">
            <i class="fas fa-newspaper nav-icon"></i>
            <p>IPO Advisory</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="../capitalmarket/marketresearch.php" class="nav-link">
            <i class="fas fa-search-dollar nav-icon"></i>
            <p>Market Research</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="../capitalmarket/riskmanage.php" class="nav-link">
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
          <a href="../accountmanage/accountsetup.php" class="nav-link">
            <i class="fas fa-user-circle nav-icon"></i>
            <p>Account Setup</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="../accountmanage/kycserv.php" class="nav-link">
            <i class="fas fa-id-card nav-icon"></i>
            <p>KYC Services</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="../accountmanage/digitalbank.php" class="nav-link">
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
          <a href="../fineducation/custedu.php" class="nav-link">
            <i class="fas fa-book nav-icon"></i>
            <p>Customer Education Programs</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="../fineducation/insuranservic.php" class="nav-link">
            <i class="fas fa-shield-alt nav-icon"></i>
            <p>Insurance Services</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="../fineducation/fintechsolution.php" class="nav-link">
            <i class="fas fa-code nav-icon"></i>
            <p>FinTech Solutions</p>
          </a>
        </li>
      </ul>
    </li>
  </ul>
</li>



<li class="nav-item has-treeview">
    <a href="#" class="nav-link">
        <i class="nav-icon fas fa-file-invoice-dollar"></i> 
        <p>
            Billing
            <i class="fas fa-angle-left right"></i>  <span class="badge badge-info right">3</span>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="../../billing/gbill.php" class="nav-link">
                <i class="fas fa-plus nav-icon"></i>
                <p>Generate Bill</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="../../billing/bill_history.php" class="nav-link">
                <i class="fas fa-history nav-icon"></i>
                <p>Transaction History</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="../../billing/report.php" class="nav-link">
                <i class="fas fa-chart-pie nav-icon"></i>
                <p>Reports & Analytics</p>
            </a>
        </li>
    </ul>
</li>


          <li class="nav-header">EXAMPLES</li>
          <li class="nav-item">
            <a href="pages/calendar.html" class="nav-link">
              <i class="nav-icon far fa-calendar-alt"></i>
              <p>
                Calendar
                <span class="badge badge-info right">2</span>
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="pages/gallery.html" class="nav-link">
              <i class="nav-icon far fa-image"></i>
              <p>
                Gallery
              </p>
            </a>
          </li>
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon far fa-envelope"></i>
              <p>
                Mailbox
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/mailbox/mailbox.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Inbox</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/mailbox/compose.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Compose</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/mailbox/read-mail.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Read</p>
                </a>
              </li>
            </ul>
          </li>

              
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>
