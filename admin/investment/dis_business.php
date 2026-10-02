<?php 
 require_once('../../protect/session_check.php');
include('../config/dbcon.php');
include('includes/functions.php');
include('../head/header.php');
include('../head/topbar.php'); 
// Fetch all business investments
$query = "SELECT * FROM business_investments ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Calculate totals
$total_query = "SELECT 
                COUNT(*) as total_businesses,
                SUM(CASE WHEN investment_model = 'equity' THEN 1 ELSE 0 END) as equity_investments,
                SUM(CASE WHEN investment_model = 'debt' THEN loan_amount ELSE 0 END) as total_loans,
                SUM(CASE WHEN investment_model = 'profit_sharing' THEN 1 ELSE 0 END) as profit_sharing_investments
                FROM business_investments";
$total_result = mysqli_query($conn, $total_query);
$totals = mysqli_fetch_assoc($total_result);
?>
<div class="content-wrapper">
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Business Investments</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Business Name</th>
                            <th>Type</th>
                            <th>Investment Model</th>
                            <th>Details</th>
                            <th>Agreement</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['business_name']) ?></td>
                                <td><?= htmlspecialchars($row['business_type']) ?></td>
                                <td><?= ucfirst(htmlspecialchars($row['investment_model'])) ?></td>
                                <td>
                                    <?php if($row['investment_model'] == 'equity'): ?>
                                        Equity: <?= $row['equity_percentage'] ?>%<br>
                                        Rights: <?= htmlspecialchars($row['shareholder_rights']) ?>
                                    <?php elseif($row['investment_model'] == 'debt'): ?>
                                        Loan: $<?= number_format($row['loan_amount'], 2) ?><br>
                                        Interest: <?= $row['interest_rate'] ?>%<br>
                                        Term: <?= $row['repayment_period'] ?> months
                                    <?php elseif($row['investment_model'] == 'profit_sharing'): ?>
                                        Share: <?= $row['profit_share_percentage'] ?>%<br>
                                        Schedule: <?= htmlspecialchars($row['distribution_schedule']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($row['agreement_pdf']): ?>
                                        <a href="../uploads/<?= htmlspecialchars($row['agreement_pdf']) ?>" target="_blank" class="btn btn-sm btn-info">View PDF</a>
                                    <?php else: ?>
                                        No file
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <a href="edit_business.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <button onclick="confirmDelete(<?= $row['id'] ?>)" class="btn btn-sm btn-danger">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr class="font-weight-bold">
                            <td colspan="3">Total Summary</td>
                            <td>
                                Businesses: <?= $totals['total_businesses'] ?><br>
                                Equity: <?= $totals['equity_investments'] ?><br>
                                Profit Sharing: <?= $totals['profit_sharing_investments'] ?>
                            </td>
                            <td>Total Loans: $<?= number_format($totals['total_loans'], 2) ?></td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
<script>
function confirmDelete(id) {
    if(confirm('Are you sure you want to delete this business investment?')) {
        window.location.href = 'delete_business.php?id=' + id;
    }
}
</script>

<?php include('../head/footer.php'); ?>