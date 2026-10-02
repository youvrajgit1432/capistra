<?php 
 require_once('../../protect/session_check.php');
include('../head/header.php');
include('includes/functions.php');
include('../head/topbar.php'); 
?>
<div class="content-wrapper">
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-warning text-white">
            <h3 class="mb-0"><i class="fas fa-hand-holding-usd mr-2"></i>Loan Investment</h3>
        </div>
        <div class="card-body">
            <form id="loanForm" method="POST" enctype="multipart/form-data" action="process.php">
                <input type="hidden" name="investment_type" value="loan">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="borrower_name">Borrower Name</label>
                            <input type="text" id="borrower_name" name="borrower_name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="loan_type">Loan Type</label>
                            <select name="loan_type" class="form-control" required>
                                <option value="">-- Select Loan Type --</option>
                                <option value="personal">Personal Loan</option>
                                <option value="business">Business Loan</option>
                                <option value="mortgage">Mortgage Loan</option>
                                <option value="education">Education Loan</option>
                                <option value="auto">Auto Loan</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="invested_amount">Loan Amount (₹)</label>
                            <input type="number" id="invested_amount" name="invested_amount" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="interest_rate">Interest Rate (%)</label>
                            <input type="number" step="0.01" id="interest_rate" name="interest_rate" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="loan_duration">Loan Duration (months)</label>
                            <input type="number" id="loan_duration" name="loan_duration" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="investment_date">Loan Date</label>
                            <input type="date" id="investment_date" name="investment_date" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="repayment_schedule">Repayment Schedule</label>
                            <select name="repayment_schedule" class="form-control" required>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="yearly">Yearly</option>
                                <option value="bullet">Bullet Payment</option>
                                <option value="custom">Custom Schedule</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="collateral">Collateral Type</label>
                            <select name="collateral" class="form-control">
                                <option value="">-- Select Collateral --</option>
                                <option value="property">Property</option>
                                <option value="vehicle">Vehicle</option>
                                <option value="equipment">Equipment</option>
                                <option value="inventory">Inventory</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="collateral_details">Collateral Details</label>
                    <textarea id="collateral_details" name="collateral_details" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks" class="form-control" rows="2"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="agreement_pdf">Loan Agreement</label>
                    <input type="file" name="agreement_pdf" class="form-control-file" accept=".pdf,.doc,.docx">
                    <small class="text-muted">Upload PDF or Word document (max 5MB)</small>
                </div>
                
                <div class="form-group text-right mt-4">
                    <button type="submit" class="btn btn-warning px-4 text-white">
                        <i class="fas fa-save mr-2"></i>Save Loan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<script src="assets/js/loan.js"></script>
<?php include('../head/footer.php'); ?>