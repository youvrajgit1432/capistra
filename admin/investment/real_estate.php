<?php 
 require_once('../../protect/session_check.php');
include('../head/header.php');
include('../head/topbar.php'); 
include('includes/functions.php');
?>
<div class="content-wrapper">
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <h3 class="mb-0"><i class="fas fa-home mr-2"></i>Real Estate Investment</h3>
        </div>
        <div class="card-body">
            <form id="realEstateForm" method="POST" enctype="multipart/form-data" action="process.php">
                <input type="hidden" name="investment_type" value="real_estate">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="property_type">Property Type</label>
                            <select name="property_type" class="form-control" required>
                                <option value="">-- Select Property Type --</option>
                                <option value="residential">Residential</option>
                                <option value="commercial">Commercial</option>
                                <option value="land">Land</option>
                                <option value="industrial">Industrial</option>
                                <option value="agricultural">Agricultural</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="property_location">Property Location</label>
                            <input type="text" id="property_location" name="property_location" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="invested_amount">Investment Amount (₹)</label>
                            <input type="number" id="invested_amount" name="invested_amount" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="property_size">Property Size (sq. ft.)</label>
                            <input type="number" id="property_size" name="property_size" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="ownership_type">Ownership Type</label>
                            <select name="ownership_type" class="form-control" required>
                                <option value="freehold">Freehold</option>
                                <option value="leasehold">Leasehold</option>
                                <option value="cooperative">Cooperative</option>
                                <option value="condominium">Condominium</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="investment_date">Purchase Date</label>
                            <input type="date" id="investment_date" name="investment_date" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="property_description">Property Description</label>
                    <textarea id="property_description" name="property_description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="purchase_document">Purchase Documents</label>
                    <input type="file" name="purchase_document" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
                    <small class="text-muted">Upload scanned documents (max 5MB each)</small>
                </div>
                
                <div class="form-group">
                    <label for="property_images">Property Images</label>
                    <input type="file" name="property_images[]" class="form-control-file" multiple accept=".jpg,.jpeg,.png">
                    <small class="text-muted">Upload property images (max 5 images, 2MB each)</small>
                </div>
                
                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks" class="form-control" rows="2"></textarea>
                </div>
                
                <div class="form-group text-right mt-4">
                    <button type="submit" class="btn btn-info px-4 text-white">
                        <i class="fas fa-save mr-2"></i>Save Investment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<script src="assets/js/real_estate.js"></script>
<?php include('../head/footer.php'); ?>