<?php
// Start output buffering
ob_start();

// Verify config file exists and is readable
$configPath = __DIR__ . '/includes/config.ini';
if (!file_exists($configPath) || !is_readable($configPath)) {
    die("Error: Configuration file missing or not readable");
}

// Parse config file
$config = parse_ini_file($configPath, true);
if ($config === false) {
    die("Error: Failed to parse configuration file");
}

// Clean any potential output
ob_end_clean();

// Set timezone
date_default_timezone_set($config['backup']['timezone'] ?? 'UTC');

// List existing backups
$backupFolder = realpath(__DIR__ . '/backups');
if ($backupFolder === false) {
    die("Error: Backup directory not found");
}

$backups = glob($backupFolder . "/capistra_backup_*.sql");
if ($backups === false) {
    die("Error: Could not read backup directory");
}

rsort($backups); // Sort by newest first

// Keep only the latest backup (delete others)
if (count($backups) > 1) {
    $latestBackup = array_shift($backups); // Keep the first (newest) one
    foreach ($backups as $oldBackup) {
        if (file_exists($oldBackup)) {
            @unlink($oldBackup);
        }
    }
    // Refresh the backups list
    $backups = [$latestBackup];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup | Capistra</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="includes/backup.css">
    
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <img src="admin/assets/dist/img/middlelogo.png" alt="Capistra">
                <span class="sidebar-brand-text">Capistra</span>
            </div>
            
            <nav class="sidebar-nav">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="admin/index.php" class="nav-link">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="nav-link-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="admin/Income/income.php" class="nav-link">
                            <i class="fas fa-dollar-sign"></i>
                            <span class="nav-link-text">Income</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="admin/Expense/expense.php" class="nav-link">
                            <i class="fas fa-money-bill-wave"></i>
                            <span class="nav-link-text">Expense</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="admin/investment/index.php" class="nav-link">
                            <i class="fas fa-chart-line"></i>
                            <span class="nav-link-text">Investment</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="admin/clients/index.php" class="nav-link">
                            <i class="fas fa-users"></i>
                            <span class="nav-link-text">Clients Register</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="admin/clients_dis/index.php" class="nav-link">
                            <i class="fas fa-users"></i>
                            <span class="nav-link-text">Clients Data</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="backup_ui.php" class="nav-link active">
                            <i class="fas fa-database"></i>
                            <span class="nav-link-text">Backup</span>
                        </a>
                    </li>
                    <br><br>
                    <li class="nav-item">
                        <a href="admin/logout.php" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="nav-link-text">Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="user-panel">
                <?php
                $profileImage = isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image']) 
                    ? htmlspecialchars($_SESSION['profile_image'])
                    : null;
                
                if ($profileImage && file_exists($profileImage)) {
                    echo '<img src="'.$profileImage.'" class="user-avatar" alt="User Avatar">';
                } else {
                    echo '<div class="user-avatar bg-primary text-white d-flex align-items-center justify-content-center">
                            <i class="fas fa-user"></i>
                          </div>';
                }
                ?>
                <div class="user-info">
                    <div class="user-name">
                        <?php 
                        if (isset($_SESSION['username'])) {
                            echo htmlspecialchars($_SESSION['username']);
                        } else {
                            echo "Guest";
                        }
                        ?>
                    </div>
                    <div class="user-role">Administrator</div>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Topbar -->
            <header class="topbar">
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="search-bar">
                    <input type="text" class="search-input" placeholder="Search...">
                </div>
                
                 
            </header>
            
            <!-- Content Wrapper -->
            <div class="content-wrapper animate-fade-in">
                <div class="container-fluid">
                    <div class="row justify-content-center">
                        <div class="col-lg-10">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h2 class="card-title mb-1">
                                                <i class="fas fa-database me-2"></i> Database Backup
                                            </h2>
                                            <p class="card-subtitle">Only the most recent backup is kept</p>
                                        </div>
                                        <span class="status-indicator"></span>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <button id="backupBtn" class="btn btn-backup mb-4">
                                        <i class="fas fa-database"></i> Create New Backup
                                    </button>
                                    
                                    <div id="result" class="alert" style="display:none;"></div>
                                    
                                    <h3 class="h5 mb-3 d-flex align-items-center">
                                        <i class="fas fa-history me-2 text-primary"></i> Current Backup
                                    </h3>
                                    
                                    <?php if (empty($backups)): ?>
                                        <div class="empty-state">
                                            <i class="fas fa-database empty-icon"></i>
                                            <h4 class="empty-title">No Backup Found</h4>
                                            <p class="empty-text">You haven't created any backups yet. Click the button above to create your first backup.</p>
                                        </div>
                                    <?php else: ?>
                                        <?php $backup = $backups[0]; ?>
                                        <div class="backup-item">
                                            <div class="backup-info">
                                                <i class="fas fa-database backup-icon"></i>
                                                <div class="backup-details">
                                                    <h5><?= basename($backup) ?></h5>
                                                    <div class="backup-meta">
                                                        <span>
                                                            <i class="far fa-calendar-alt"></i>
                                                            <?= date('Y-m-d H:i:s', filemtime($backup)) ?>
                                                        </span>
                                                        <span>
                                                            <i class="fas fa-hdd"></i>
                                                            <?= round(filesize($backup) / 1024, 2) ?> KB
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <a href="backups/<?= basename($backup) ?>" download class="btn btn-sm btn-outline-primary me-2">
                                                    <i class="fas fa-download me-1"></i> Download
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mt-4 pt-3 border-top">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i> 
                                            <strong>Note:</strong> The system automatically maintains only the most recent backup. 
                                            Creating a new backup will replace any existing one.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i> Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this backup? This action cannot be undone.</p>
                    <p class="fw-bold">File: <span id="backupFileName" class="text-danger"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fas fa-trash-alt me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    
    <script>
        // Toggle Sidebar
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            sidebar.classList.toggle('sidebar-collapsed');
            mainContent.classList.toggle('main-collapsed');
            
            // Store preference in localStorage
            const isCollapsed = sidebar.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });
        
        // Check for saved sidebar state
        document.addEventListener('DOMContentLoaded', function() {
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            if (isCollapsed) {
                sidebar.classList.add('sidebar-collapsed');
                mainContent.classList.add('main-collapsed');
            }
            
            // Mobile sidebar toggle
            const mobileToggle = document.createElement('button');
            mobileToggle.className = 'btn btn-primary d-lg-none position-fixed';
            mobileToggle.style.bottom = '20px';
            mobileToggle.style.right = '20px';
            mobileToggle.style.zIndex = '1000';
            mobileToggle.style.width = '50px';
            mobileToggle.style.height = '50px';
            mobileToggle.style.borderRadius = '50%';
            mobileToggle.innerHTML = '<i class="fas fa-bars"></i>';
            mobileToggle.addEventListener('click', function() {
                sidebar.classList.toggle('sidebar-mobile-show');
            });
            document.body.appendChild(mobileToggle);
        });
        
        // Backup button functionality
        document.getElementById('backupBtn').addEventListener('click', function() {
            const resultDiv = document.getElementById('result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = `
                <div class="d-flex align-items-center">
                    <div class="spinner-border spinner-border-sm me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <strong>Creating backup...</strong> Please wait while we secure your data.
                </div>
            `;
            resultDiv.className = 'alert alert-info';
            
            fetch('backup.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.className = 'alert alert-success';
                    resultDiv.innerHTML = `
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle me-2" style="font-size: 1.2rem;"></i>
                            <div>
                                <strong>Backup successful!</strong> ${data.message}
                                <div class="progress mt-2">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 100%"></div>
                                </div>
                            </div>
                        </div>
                    `;
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    resultDiv.className = 'alert alert-danger animate-shake';
                    resultDiv.innerHTML = `
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2" style="font-size: 1.2rem;"></i>
                            <div>
                                <strong>Backup failed!</strong> ${data.message}
                            </div>
                        </div>
                    `;
                }
            })
            .catch(error => {
                resultDiv.className = 'alert alert-danger animate-shake';
                resultDiv.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-2" style="font-size: 1.2rem;"></i>
                        <div>
                            <strong>Error occurred!</strong> ${error.message}
                        </div>
                    </div>
                `;
            });
        });

        // Delete functionality
        let backupToDelete = '';
        
        function confirmDelete(filename) {
            backupToDelete = filename;
            document.getElementById('backupFileName').textContent = filename;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
        
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            const resultDiv = document.getElementById('result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = `
                <div class="d-flex align-items-center">
                    <div class="spinner-border spinner-border-sm me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <strong>Deleting backup...</strong> Please wait while we process your request.
                </div>
            `;
            resultDiv.className = 'alert alert-info';
            
            fetch('delete_backup.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `filename=${encodeURIComponent(backupToDelete)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.className = 'alert alert-success';
                    resultDiv.innerHTML = `
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle me-2" style="font-size: 1.2rem;"></i>
                            <div>
                                <strong>Success!</strong> ${data.message}
                            </div>
                        </div>
                    `;
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    resultDiv.className = 'alert alert-danger animate-shake';
                    resultDiv.innerHTML = `
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2" style="font-size: 1.2rem;"></i>
                            <div>
                                <strong>Error!</strong> ${data.message}
                            </div>
                        </div>
                    `;
                }
            })
            .catch(error => {
                resultDiv.className = 'alert alert-danger animate-shake';
                resultDiv.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-2" style="font-size: 1.2rem;"></i>
                        <div>
                            <strong>Error!</strong> ${error.message}
                        </div>
                    </div>
                `;
            });
            
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
        });
    </script>
</body>
</html>