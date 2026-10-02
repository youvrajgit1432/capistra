<?php 
 require_once('../../protect/session_check.php');
include('../config/dbcon.php');
include('includes/functions.php');
include('../head/header.php'); 
include('../head/topbar.php'); 
// Fetch all loan investments
$loans = [];
$total_loan_amount = 0;

$query = "SELECT i.id, i.invested_amount, i.investment_date, i.remarks,
                 l.borrower_name, l.loan_type, l.interest_rate, 
                 l.loan_duration, l.repayment_schedule, l.collateral,
                 l.collateral_details, l.agreement_pdf
          FROM investments i
          JOIN loan_investments l ON i.id = l.investment_id
          ORDER BY i.investment_date DESC";

$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $loans[] = $row;
        $total_loan_amount += $row['invested_amount'];
    }
}
?>
<div class="content-wrapper">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Loan Investments</h2>
        <a href="loan.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Loan
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h4 class="mb-0">Loan Summary</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary-light rounded p-3 me-3">
                            <i class="fas fa-dollar-sign fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Total Invested</h6>
                            <h4 class="mb-0"><?= number_format($total_loan_amount, 2) ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-success-light rounded p-3 me-3">
                            <i class="fas fa-list fa-2x text-success"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Total Loans</h6>
                            <h4 class="mb-0"><?= count($loans) ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-info-light rounded p-3 me-3">
                            <i class="fas fa-percentage fa-2x text-info"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Avg Interest Rate</h6>
                            <h4 class="mb-0">
                                <?php 
                                $avg_rate = 0;
                                if (count($loans) > 0) {
                                    $total_rate = 0;
                                    foreach ($loans as $loan) {
                                        $total_rate += $loan['interest_rate'];
                                    }
                                    $avg_rate = $total_rate / count($loans);
                                }
                                echo number_format($avg_rate, 2) . '%';
                                ?>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Loan Details</h4>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                    <li><a class="dropdown-item" href="?filter=active">Active Loans</a></li>
                    <li><a class="dropdown-item" href="?filter=completed">Completed Loans</a></li>
                    <li><a class="dropdown-item" href="?filter=all">All Loans</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Borrower</th>
                            <th>Amount</th>
                            <th>Interest Rate</th>
                            <th>Duration</th>
                            <th>Repayment</th>
                            <th>Date</th>
                            <th>Collateral</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($loans) > 0): ?>
                            <?php foreach ($loans as $loan): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary-light rounded me-3">
                                                <span class="avatar-text text-primary"><?= strtoupper(substr($loan['borrower_name'], 0, 1)) ?></span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($loan['borrower_name']) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($loan['loan_type']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= number_format($loan['invested_amount'], 2) ?></td>
                                    <td><?= $loan['interest_rate'] ?>%</td>
                                    <td><?= $loan['loan_duration'] ?> months</td>
                                    <td><?= ucfirst($loan['repayment_schedule']) ?></td>
                                    <td><?= date('M d, Y', strtotime($loan['investment_date'])) ?></td>
                                    <td>
                                        <?php if ($loan['collateral'] === 'yes'): ?>
                                            <span class="badge bg-success">Yes</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="edit_loan.php?id=<?= $loan['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="view_loan.php?id=<?= $loan['id'] ?>" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger delete-loan" data-id="<?= $loan['id'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php if (!empty($loan['agreement_pdf'])): ?>
                                                <a href="<?= htmlspecialchars($loan['agreement_pdf']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i>
                                        <h5>No loan investments found</h5>
                                        <p class="text-muted">Add your first loan investment to get started</p>
                                        <a href="loan.php" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Add Loan
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
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this loan investment? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>
</div>
<script>
$(document).ready(function() {
    // Delete confirmation
    let loanToDelete = null;
    
    $('.delete-loan').click(function() {
        loanToDelete = $(this).data('id');
        $('#deleteModal').modal('show');
    });
    
    $('#confirmDelete').click(function() {
        if (loanToDelete) {
            window.location.href = 'delete_loan.php?id=' + loanToDelete;
        }
    });
});
</script>

<style>
.avatar-sm {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.avatar-text {
    font-weight: 600;
    font-size: 1rem;
}
.bg-primary-light {
    background-color: rgba(13, 110, 253, 0.1);
}
.bg-success-light {
    background-color: rgba(25, 135, 84, 0.1);
}
.bg-info-light {
    background-color: rgba(13, 202, 240, 0.1);
}
</style>

<?php include('../head/footer.php'); ?>