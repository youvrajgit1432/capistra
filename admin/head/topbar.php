 <!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="index.php" class="nav-link">Home</a>
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

        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>

        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                    <?php 
                    // Display the username from session
                    if (isset($_SESSION['username'])) {
                        echo htmlspecialchars($_SESSION['username']);
                    } else {
                        echo "Guest";
                    }
                    ?>
                </span>
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
?>
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="userDropdown">
                <a class="dropdown-item" href="../profile.php">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="logout.php">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>
        
         
    </ul>
</nav>
<!-- /.navbar -->