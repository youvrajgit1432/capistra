<?php
 require_once('../../protect/session_check.php');
include('../head/header.php');
include('includes/functions.php');
include('../head/topbar.php'); 
?>
<div class="content-wrapper">
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h3 class="mb-0"><i class="fas fa-business-time mr-2"></i>Business Investment</h3>
        </div>
        <div class="card-body">
            <form id="businessForm" method="POST" enctype="multipart/form-data" action="process.php">
                <input type="hidden" name="investment_type" value="business">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="business_name">Business Name</label>
                            <input type="text" id="business_name" name="business_name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="business_type">Business Type</label>
                            <select name="business_type" id="business_type" class="form-control" required>
                                <option value="">-- Select Type --</option>
                                <option value="agriculture">Agriculture & Farming</option>
                                <option value="manufacturing">Manufacturing</option>
                                <option value="retail">Retail Business</option>
                                <option value="service">Service Business</option>
                                <option value="technology">Technology</option>
                                <option value="tourism">Tourism & Hospitality</option>
                                <option value="startup">Startup</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="invested_amount">Investment Amount (₹)</label>
                            <input type="number" id="invested_amount" name="invested_amount" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="investment_date">Investment Date</label>
                            <input type="date" id="investment_date" name="investment_date" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="business_investment_model">Investment Model</label>
                    <select name="business_investment_model" id="business_investment_model" class="form-control" required onchange="toggleInvestmentFields()">
                        <option value="">-- Select Model --</option>
                        <option value="equity">Equity</option>
                        <option value="debt">Debt</option>
                        <option value="profit_sharing">Profit Sharing</option>
                    </select>
                </div>
                
                <!-- Equity Fields -->
                <div id="equity_fields" style="display: none;" class="border p-3 mb-3 rounded">
                    <h5 class="mb-3">Equity Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="equity_percentage">Equity Percentage (%)</label>
                                <input type="number" name="equity_percentage" class="form-control" step="0.01" min="0" max="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="shareholder_rights">Shareholder Rights</label>
                                <textarea name="shareholder_rights" id="shareholder_rights" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Debt Fields -->
                <div id="debt_fields" style="display: none;" class="border p-3 mb-3 rounded">
                    <h5 class="mb-3">Loan Details</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="loan_amount">Loan Amount (₹)</label>
                                <input type="number" name="loan_amount" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="interest_rate">Interest Rate (%)</label>
                                <input type="number" name="interest_rate" class="form-control" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="repayment_period">Repayment Period (Months)</label>
                                <input type="number" name="repayment_period" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Profit Sharing Fields -->
                <div id="profit_sharing_fields" style="display: none;" class="border p-3 mb-3 rounded">
                    <h5 class="mb-3">Profit Sharing Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="profit_share_percentage">Profit Share Percentage (%)</label>
                                <input type="number" name="profit_share_percentage" class="form-control" step="0.01" min="0" max="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="distribution_schedule">Distribution Schedule</label>
                                <input type="text" name="distribution_schedule" class="form-control" placeholder="e.g., Quarterly, Annually">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks" class="form-control" rows="2"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="agreement_pdf">Investment Agreement</label>
                    <input type="file" name="agreement_pdf" class="form-control-file" accept=".pdf,.doc,.docx">
                    <small class="text-muted">Upload PDF or Word document (max 5MB)</small>
                </div>
                
                <div class="form-group text-right mt-4">
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-save mr-2"></i>Save Investment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<script src="assets/js/business.js"></script>
<?php include('../head/footer.php'); ?>