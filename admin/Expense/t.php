
  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
      <img src="assets/dist/img/middlelogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">Global tech</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="assets/dist/img/director.jpg" class="img-circle elevation-2" alt="User Image">
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
            <a href="../admin/index.php" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Dashboard
            
              </p>
            </a>
    
          </li> 
          <!-- Income Input Menu Item -->
<li class="nav-item">
    <a href="Income/income.php" class="nav-link">
        <i class="fas fa-dollar-sign nav-icon"></i>
        <p>
            Income 
        </p>
    </a>
</li>

<!-- Expense Menu Item -->
<li class="nav-item">
    <a href="Expense/expense.php" class="nav-link">
        <i class="fas fa-money-bill-wave nav-icon"></i>
        <p>
            Expense  
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
      <a href="fund/investorprofile.php" class="nav-link">
        <i class="nav-icon fas fa-user-tie"></i>
        <p>Investor Profiles</p>
      </a>
    </li>

    <!-- Funds -->
    <li class="nav-item">
      <a href="fund/fundmanagement.php" class="nav-link">
        <i class="nav-icon fas fa-wallet"></i>
        <p>Fund Management</p>
      </a>
    </li>

    <!-- Returns -->
    <li class="nav-item">
      <a href="fund/returnmanagement.php" class="nav-link">
        <i class="nav-icon fas fa-chart-line"></i>
        <p>Return Management</p>
      </a>
    </li>
  </ul>
</li>
     

<li class="nav-item has-treeview">
  <a href="#" class="nav-link">
  <i class="nav-icon fas fa-users"></i>
    <p>
      Clients
      <i class="fas fa-angle-left right"></i>
      <span class="badge badge-info right">6</span>
    </p>
  </a>
  <ul class="nav nav-treeview">
    <li class="nav-item">
      <a href="#" class="nav-link">
      <i class="nav-icon fas fa-star"></i> 
        <p>
    Special  Registration
          <i class="fas fa-angle-left right"></i>
        </p>
      </a>
      <ul class="nav nav-treeview">
      <li class="nav-item">
          <a href="customer/custmerreg.php" class="nav-link">
          <i class="nav-icon fas fa-user-plus"></i>
            <p>Registration</p>
          </a>
        <li class="nav-item">
          <a href="customer/dscustomer.php" class="nav-link">
          <i class="nav-icon fas fa-database"></i> 
            <p>Details Of Customer</p>
          </a>
        </li>

      </ul>
    </li>

    <li class="nav-item">
      <a href="#" class="nav-link">
      <i class="nav-icon fas fa-user"></i>
        <p>
          Normal Clients
          <i class="fas fa-angle-left right"></i>
        </p>
      </a>
      <ul class="nav nav-treeview">
        <li class="nav-item">
          <a href="customer/normalregis.php" class="nav-link">
          <i class="nav-icon fas fa-user-plus"></i>
            <p>Registration</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="customer/disnormal.php" class="nav-link">
          <i class="nav-icon fas fa-database"></i> 
            <p>Storage</p>
          </a>
        </li>
      </ul>
    </li>

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
                <a href="employee/all.php" class="nav-link">
                <i class="far fas fa-users  nav-icon"></i>
                  <p>  All</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="employee/admin.php" class="nav-link">
                <i class="far fas fa-user-shield  nav-icon"></i>
                  <p>Admin</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="employee/accountant.php" class="nav-link">
                <i class="far fas fa-calculator  nav-icon"></i>
                  <p>Accountant</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="employee/customer_manager.php" class="nav-link">
                <i class="far fas fa-user-cog  nav-icon"></i>
                  <p>Customer Manager</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="employee/research_team.php" class="nav-link">
                <i class="fas fa-flask  nav-icon"></i>

                  <p>Research Team </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="employee/legal_team.php" class="nav-link">
                <i class="fas fa-gavel  nav-icon"></i>

                  <p>Legal Dewpartment </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="employee/hrmanager.php" class="nav-link">
                <i class="fas fa-user-tie  nav-icon"></i>

                  <p>Hr Manager </p>
                </a>
              </li>

           
              <li class="nav-item">
                <a href="employee/itdeveloper.php" class="nav-link">
                <i class="fas fa-desktop  nav-icon"></i>

                  <p>IT Developer </p>
                </a>
              </li>


              <li class="nav-item">
                <a href="employee/dataanalyst.php" class="nav-link">
                <i class="fas fa-chart-line  nav-icon"></i>

                  <p>Data Analyst </p>
                </a>
              </li>
           
              <li class="nav-item">
                <a href="employee/marketing.php" class="nav-link">
                <i class="fas fa-bullhorn  nav-icon"></i>

                  <p>Marketing </p>
                </a>
              </li>

              <li class="nav-item">
                <a href="employee/securityguard.php" class="nav-link">
                <i class="fas fa-shield-alt  nav-icon"></i>

                  <p>security Guard </p>
                </a>
              </li>

          
                <li class="nav-item">
                <a href="employee/intern.php" class="nav-link">
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
          <a href="service/invest_soln/invest_advisor.php" class="nav-link">
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
          <a href="service/invest_soln/realstate.php" class="nav-link">
            <i class="fas fa-building nav-icon"></i>
            <p>Real Estate Investments</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="service/invest_soln/mutalbond.php" class="nav-link">
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
          <a href="service/wealthplan/wealth_manage.php" class="nav-link">
            <i class="fas fa-piggy-bank nav-icon"></i>
            <p>Wealth Management</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="service/wealthplan/financialplan.php" class="nav-link">
            <i class="fas fa-dollar-sign nav-icon"></i>
            <p>Financial Planning</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="service/wealthplan/corporadvisor.php" class="nav-link">
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
          <a href="service/capitalmarket/ipoadvisory.php" class="nav-link">
            <i class="fas fa-newspaper nav-icon"></i>
            <p>IPO Advisory</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="service/capitalmarket/marketresearch.php" class="nav-link">
            <i class="fas fa-search-dollar nav-icon"></i>
            <p>Market Research</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="service/capitalmarket/riskmanage.php" class="nav-link">
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
          <a href="service/accountmanage/accountsetup.php" class="nav-link">
            <i class="fas fa-user-circle nav-icon"></i>
            <p>Account Setup</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="service/accountmanage/kycserv.php" class="nav-link">
            <i class="fas fa-id-card nav-icon"></i>
            <p>KYC Services</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="service/accountmanage/digitalbank.php" class="nav-link">
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
          <a href="service/fineducation/custedu.php" class="nav-link">
            <i class="fas fa-book nav-icon"></i>
            <p>Customer Education Programs</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="service/fineducation/insuranservic.php" class="nav-link">
            <i class="fas fa-shield-alt nav-icon"></i>
            <p>Insurance Services</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="service/fineducation/fintechsolution.php" class="nav-link">
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
            <a href="billing/gbill.php" class="nav-link">
                <i class="fas fa-plus nav-icon"></i>
                <p>Generate Bill</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="billing/bill_history.php" class="nav-link">
                <i class="fas fa-history nav-icon"></i>
                <p>Transaction History</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="billing/report.php" class="nav-link">
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
