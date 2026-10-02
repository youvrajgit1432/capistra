<?php
 require_once('../../protect/session_check.php');
include('../head/header.php');
include('includes/functions.php');
include('../head/topbar.php'); 
include('includes/company_data.php');
?>
<div class="content-wrapper">
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0"><i class="fas fa-chart-line mr-2"></i>Stock Investment</h3>
        </div>
        <div class="card-body">
            <form id="stockForm" method="POST" enctype="multipart/form-data" action="process.php">
                <input type="hidden" name="investment_type" value="stock">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="sector">Sector</label>
                            <select id="sector" name="sector" class="form-control" onchange="filterCompanies()" required>
                                <option value="">Select Sector</option>
                                <option value="commercial_bank">Commercial Bank</option>
                                <option value="development_bank">Development Bank</option>
                                <option value="finance">Finance</option>
                                <option value="hotel">Hotel</option>
                                <option value="insurance">Insurance</option>
                                <option value="micro_insurance_nonlife">Micro Insurance(Non-life)</option>
                                <option value="micro_insurance_life">Micro Insurance(Life)</option>
                                <option value="manufacturing">Manufacturing</option>
                                <option value="microfinance">Microfinance</option>
                                <option value="trading">Trading</option>
                                <option value="others">Others</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="company_name">Company Name</label>
                            <select id="company_name" name="company_name" class="form-control" onchange="updateSymbol()" required>
                                <option value="">Select Company</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="company_symbol">Company Symbol</label>
                            <input type="text" id="company_symbol" name="company_symbol" class="form-control" readonly required>
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
                        
                        <div class="form-group">
                            <label for="stock_investment_type">Investment Type</label>
                            <select name="stock_investment_type" class="form-control" required>
                                <option value="long_term">Long Term</option>
                                <option value="medium_term">Medium Term</option>
                                <option value="short_term">Short Term</option>
                                <option value="trading">Trading</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="stock_base_price">Current Share Price (₹)</label>
                            <input type="number" step="0.01" id="stock_base_price" name="stock_base_price" class="form-control" oninput="calculateStockUnits()" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="stock_total_units">Total Units of Shares</label>
                            <input type="number" step="0.0001" id="stock_total_units" name="stock_total_units" class="form-control" readonly required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks" class="form-control" rows="2"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="agreement_pdf">Investment Proof (Voucher/Receipt)</label>
                    <input type="file" name="agreement_pdf" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
                    <small class="text-muted">Upload PDF, JPG, or PNG file (max 5MB)</small>
                </div>
                
                <div class="form-group text-right mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save mr-2"></i>Save Investment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<script src="assets/js/stock.js"></script>
<?php include('../head/footer.php'); ?>