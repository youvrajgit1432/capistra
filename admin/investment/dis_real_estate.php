<?php 
// Start session and include files
require_once('../../protect/session_check.php');
include('../config/dbcon.php');
include('includes/functions.php');
include('../head/header.php');

include('../head/topbar.php'); 


// Handle delete action
if(isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    deleteRealEstate($id);
}

// Function to delete real estate record
function deleteRealEstate($id) {
    global $conn;
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // First delete property images
        $query = "DELETE FROM property_images WHERE real_estate_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        
        // Then delete the main record
        $query = "DELETE FROM real_estate_investments WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        
        // Finally delete from investments table
        $query = "DELETE FROM investments WHERE id = (SELECT investment_id FROM real_estate_investments WHERE id = ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        
        // Commit transaction
        mysqli_commit($conn);
        
        $_SESSION['success'] = "Real estate record deleted successfully";
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        $_SESSION['error'] = "Error deleting record: " . $e->getMessage();
    }
    
    header("Location: real_estate_list.php");
    exit();
}

// Fetch all real estate records with related data
function getAllRealEstate() {
    global $conn;
    
    $query = "SELECT re.*, i.investment_date, i.amount, i.currency, i.notes 
              FROM real_estate_investments re
              JOIN investments i ON re.investment_id = i.id
              ORDER BY i.investment_date DESC";
    
    $result = mysqli_query($conn, $query);
    
    $properties = [];
    while($row = mysqli_fetch_assoc($result)) {
        // Get property images
        $images = [];
        $imgQuery = "SELECT image_path FROM property_images WHERE real_estate_id = " . $row['id'];
        $imgResult = mysqli_query($conn, $imgQuery);
        while($imgRow = mysqli_fetch_assoc($imgResult)) {
            $images[] = $imgRow['image_path'];
        }
        
        $row['images'] = $images;
        $properties[] = $row;
    }
    
    return $properties;
}

// Calculate summary statistics
function getRealEstateSummary() {
    global $conn;
    
    $summary = [
        'total_properties' => 0,
        'total_investment' => 0,
        'avg_property_size' => 0,
        'property_types' => []
    ];
    
    // Get total count and investment sum
    $query = "SELECT COUNT(re.id) as total_properties, SUM(i.amount) as total_investment
              FROM real_estate_investments re
              JOIN investments i ON re.investment_id = i.id";
    
    $result = mysqli_query($conn, $query);
    if($row = mysqli_fetch_assoc($result)) {
        $summary['total_properties'] = $row['total_properties'];
        $summary['total_investment'] = $row['total_investment'];
    }
    
    // Get average property size
    $query = "SELECT AVG(property_size) as avg_size FROM real_estate_investments";
    $result = mysqli_query($conn, $query);
    if($row = mysqli_fetch_assoc($result)) {
        $summary['avg_property_size'] = round($row['avg_size'], 2);
    }
    
    // Get property type distribution
    $query = "SELECT property_type, COUNT(*) as count 
              FROM real_estate_investments 
              GROUP BY property_type";
    $result = mysqli_query($conn, $query);
    while($row = mysqli_fetch_assoc($result)) {
        $summary['property_types'][$row['property_type']] = $row['count'];
    }
    
    return $summary;
}

$properties = getAllRealEstate();
$summary = getRealEstateSummary();
?>
<div class="content-wrapper">
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mt-4">Real Estate Investments</h2>
            
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-home me-2"></i>
                            <span class="fw-bold">Property Portfolio</span>
                        </div>
                        <a href="add_real_estate.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add Property
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="dataTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Property</th>
                                    <th>Type</th>
                                    <th>Location</th>
                                    <th>Size</th>
                                    <th>Investment</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($properties) > 0): ?>
                                    <?php foreach($properties as $property): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if(!empty($property['images'][0])): ?>
                                                    <img src="<?= htmlspecialchars($property['images'][0]) ?>" class="rounded me-3" width="50" height="50" style="object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                        <i class="fas fa-home text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <h6 class="mb-0"><?= htmlspecialchars($property['property_name'] ?? 'N/A') ?></h6>
                                                    <small class="text-muted"><?= htmlspecialchars($property['ownership_type']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($property['property_type']) ?></td>
                                        <td><?= htmlspecialchars($property['property_location']) ?></td>
                                        <td><?= number_format($property['property_size']) ?> sqft</td>
                                        <td>
                                            <span class="fw-bold"><?= htmlspecialchars($property['currency']) ?> <?= number_format($property['amount'], 2) ?></span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($property['investment_date'])) ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="view_real_estate.php?id=<?= $property['id'] ?>" class="btn btn-sm btn-outline-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit_real_estate.php?id=<?= $property['id'] ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="real_estate_list.php?delete_id=<?= $property['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this property?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="fas fa-home fa-3x text-muted mb-3"></i>
                                                <h5>No properties found</h5>
                                                <p class="text-muted">Add your first real estate investment to get started</p>
                                                <a href="add_real_estate.php" class="btn btn-primary">
                                                    <i class="fas fa-plus me-2"></i>Add Property
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Summary Section -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="fas fa-home fa-lg text-primary"></i>
                            </div>
                            <h3 class="mt-3 mb-0"><?= $summary['total_properties'] ?></h3>
                            <p class="text-muted mb-0">Total Properties</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="fas fa-dollar-sign fa-lg text-success"></i>
                            </div>
                            <h3 class="mt-3 mb-0">$<?= number_format($summary['total_investment'], 2) ?></h3>
                            <p class="text-muted mb-0">Total Investment</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="fas fa-ruler-combined fa-lg text-info"></i>
                            </div>
                            <h3 class="mt-3 mb-0"><?= $summary['avg_property_size'] ?></h3>
                            <p class="text-muted mb-0">Avg. Size (sqft)</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title text-center mb-3">Property Types</h5>
                            <ul class="list-unstyled mb-0">
                                <?php foreach($summary['property_types'] as $type => $count): ?>
                                    <li class="d-flex justify-content-between py-1 border-bottom">
                                        <span><?= htmlspecialchars($type) ?></span>
                                        <span class="badge bg-primary rounded-pill"><?= $count ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>   </div>

<?php include('../head/footer.php'); ?>