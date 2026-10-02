<?php
include('../head/header.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounting Admin Panel Form</title>
    <link rel="stylesheet" href="../assets/css/customerregis.css">
    <style>
        .document-upload-section {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .document-item {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .file-upload-wrapper {
            margin-top: 10px;
        }
        .file-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .error-message {
            color: red;
            font-size: 12px;
            display: none;
        }
    </style>
</head>
<body>
    <h1>Accounting Admin Panel Form</h1>
    <form action="formhandle.php" method="POST" enctype="multipart/form-data">
        <!-- Personal Details -->
        <div class="ram">
            <label>Title</label>
            <input type="radio" name="title" value="Mr." required> Mr.
            <input type="radio" name="title" value="Miss"> Miss
            <input type="radio" name="title" value="Mrs."> Mrs.
        </div>

        <br>

        <div class="fdf">
            <label>Applicant Type</label>
            <input type="radio" name="applicant_type" value="Minor" onclick="toggleMinorGuardian()" required> Minor
            <input type="radio" name="applicant_type" value="Adult" onclick="toggleMinorGuardian()"> Adult
        </div>

        <br>

        <!-- Applicant's Name -->
        <div class="form-group">
            <label>Applicant's Name</label>
            <input type="text" name="applicant_name_en" required>
        </div>

        <!-- Father's Name -->
        <div class="form-group">
            <label>Father's Name</label>
            <input type="text" name="father_name" required>
        </div>
        <div class="form-group">
            <label>Mother's Name</label>
            <input type="text" name="mother_name" required>
        </div>

        <!-- Grandfather's Name -->
        <div class="form-group">
            <label>Grandfather's Name</label>
            <input type="text" name="grandfather_name">
        </div>

        <div class="form-group">
            <label>Grandmother's Name</label>
            <input type="text" name="grandmother_name">
        </div>

        <!-- Contact Information -->
        <div class="form-group">
            <label>User Phone Number</label>
            <input type="tel" name="phone_number" required>
        </div>

        <div class="form-group">
            <label>User Email Address</label>
            <input type="email" name="email_address" required>
        </div>

        <!-- Marital Status -->
        <div class="form-group">
            <label>Marital Status</label>
            <select name="marital_status" onchange="toggleMarriedChildren()" required>
                <option value="Single">Single</option>
                <option value="Married">Married</option>
            </select>
        </div>

        <!-- Date of Birth -->
        <div class="row">
            <div class="col">
                <label>Date of Birth (BS)</label>
                <input type="text" class="date-picker form-control" data-single="1" name="date1" placeholder="yyyy/mm/dd" required>
            </div>
        
        </div>

        <!-- Identification Document -->
        <div class="form-group">
            <label>Identification Document</label>
            <select name="id_type">
                <option value="Citizenship">Citizenship</option>
                <option value="Birth Certificate">Birth Certificate</option>
                <option value="Passport">Passport</option>
                <option value="License">License</option>
            </select>
        </div>

        <div class="row">
            <div class="col">
                <label>Id Number</label>
                <input type="text" name="id_number">
            </div>
            <div class="col">
                <label>Issued Date</label>
                <input type="text" class="date-picker form-control" data-single="1" name="issued_date" placeholder="yyyy/mm/dd">
            </div>
            <div class="col">
                <label>Issued Place</label>
                <input type="text" name="issued_place" required>
            </div>
        </div>

        <!-- Document Upload Section -->
        <div class="document-upload-section">
            <h3>Document Upload</h3>
            
            <!-- Document Type Selection -->
            <div class="form-group">
                <label>Select Document Type</label>
                <select id="documentType" class="form-control" onchange="showUploadFields()">
                    <option value="">-- Select Document --</option>
                    <option value="citizenship">Citizenship</option>
                    <option value="birth_certificate">Birth Certificate</option>
                    <option value="license">License</option>
                    <option value="national_id">National ID Card</option>
                    <option value="parents_citizenship">Parents Citizenship (For Minor)</option>
                </select>
            </div>
            
            <!-- Dynamic Upload Fields -->
            <div id="uploadFieldsContainer"></div>
            
            <!-- Uploaded Documents List -->
            <div id="uploadedDocuments">
                <h4>Documents to be Uploaded:</h4>
                <ul id="documentsList"></ul>
            </div>
        </div>

        <!-- Permanent Address -->
        <h3>Permanent Address</h3>
        <div class="form-group">
            <label>Country</label>
            <input type="text" name="permanent_country">
        </div>
        <div class="form-group">
            <label>Province</label>
            <select name="permanent_province">
                <option value="Province 1">Province 1</option>
                <option value="Province 2">Province 2</option>
                <option value="Bagmati">Bagmati</option>
                <option value="Gandaki">Gandaki</option>
                <option value="Lumbini">Lumbini</option>
                <option value="Karnali">Karnali</option>
                <option value="Sudurpashchim">Sudurpashchim</option>
            </select>
        </div>

        <div class="form-group">
            <label>District</label>
            <select name="permanent_district">
                <option value="Kavrepalanchok">Kavrepalanchok</option>
                <option value="Sindhuli">Sindhuli</option>
                <option value="Kathmandu">Kathmandu</option>
                <option value="Lalitpur">Lalitpur</option>
                <option value="Chitwan">Chitwan</option>
                <option value="Makwanpur">Makwanpur</option>
                <option value="Nuwakot">Nuwakot</option>
                <option value="Sindhupalchok">Sindhupalchok</option>
                <option value="Ramechhap">Ramechhap</option>
                <option value="Dolakha">Dolakha</option>
                <option value="Rasuwa">Rasuwa</option>
                <option value="Baglung">Baglung</option>
                <option value="Parbat">Parbat</option>
                <option value="Kaski">Kaski</option>
                <option value="Tanahun">Tanahun</option>
            </select>
        </div>

        <div class="form-group">
            <label>Ward No.</label>
            <select name="ward_no">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">10</option>
                <option value="11">11</option>
                <option value="12">12</option>
                <option value="13">13</option>
                <option value="14">14</option>
                <option value="15">15</option>
            </select>
        </div>

        <div class="form-group">
            <label>Tole</label>
            <input type="text" name="permanent_tole">
        </div>

        <!-- Current Address -->
        <h3>Current Address</h3>
        <div class="form-group">
            <label>Country</label>
            <input type="text" name="temporary_country">
        </div>
        <div class="form-group">
            <label>Province</label>
            <select name="temporary_province">
                <option value="Province 1">Province 1</option>
                <option value="Province 2">Province 2</option>
                <option value="Bagmati">Bagmati</option>
                <option value="Gandaki">Gandaki</option>
                <option value="Lumbini">Lumbini</option>
                <option value="Karnali">Karnali</option>
                <option value="Sudurpashchim">Sudurpashchim</option>
            </select>
        </div>

        <div class="form-group">
            <label>District</label>
            <select name="temporary_district">
                <option value="Kavrepalanchok">Kavrepalanchok</option>
                <option value="Bhaktapur">Sindhuli</option>
                <option value="Kathmandu">Kathmandu</option>
                <option value="Lalitpur">Lalitpur</option>
                <option value="Chitwan">Chitwan</option>
                <option value="Makwanpur">Makwanpur</option>
                <option value="Nuwakot">Nuwakot</option>
                <option value="Sindhupalchok">Sindhupalchok</option>
                <option value="Ramechhap">Ramechhap</option>
                <option value="Dolakha">Dolakha</option>
                <option value="Rasuwa">Rasuwa</option>
                <option value="Baglung">Baglung</option>
                <option value="Parbat">Parbat</option>
                <option value="Kaski">Kaski</option>
                <option value="Tanahun">Tanahun</option>
            </select>
        </div>

        <div class="form-group">
            <label>Ward No.</label>
            <select name="temporary_ward_no">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">10</option>
                <option value="11">11</option>
                <option value="12">12</option>
                <option value="13">13</option>
                <option value="14">14</option>
                <option value="15">15</option>
            </select>
        </div>

        <div class="form-group">
            <label>Tole</label>
            <input type="text" name="temporary_tole">
        </div>

        <!-- Minor Guardian Details -->
        <div id="guardianDetails" style="display:none;">
            <h3>Guardian Details</h3>
            <div class="form-group">
                <label>Guardian's Name</label>
                <input type="text" name="guardian_name">
            </div>
            <div class="form-group">
                <label>Relationship</label>
            
            <select name="relationship">
            <option value="">Select</option>
                <option value="father">Father</option>
              
            </select>
 
            </div>
            <div class="form-group">
                <label>Guardian's Phone Number</label>
                <input type="tel" name="guardian_phone" required>
            </div>
            <div class="form-group">
                <label>Guardian's Email Address</label>
                <input type="email" name="guardian_email">
            </div>
      
            
            <div class="form-group">
                <label>Guardian Identification Document</label>
                <select name="iid_type">
                    <option value="Citizenship">Citizenship</option>
                    <option value="Passport">Passport</option>
                    <option value="License">License</option>
                </select>
            </div>

            <div class="row">
                <div class="col">
                    <label>Id Number</label>
                    <input type="text" name="iiissued_number">
                </div>
                <div class="col">
                    <label>Issued Date</label>
                    <input type="text" class="date-picker form-control" data-single="1" name="date1" value=""
                    placeholder="yyyy/mm/dd">
                </div>
                <div class="col">
                    <label>Issued Place</label>
                    <input type="text" name="issued_place">
                </div>
            </div>
        </div>

        <div id="childrenDetails" style="display:none;">
            <h3>Family Details</h3>

            <!-- Spouse Name Section -->
            <div class="form-group">
                <label>Spouse's Name</label>
                <input type="text" name="spouse_name">
            </div>

            <!-- Children Details Container -->
            <div id="childrenContainer">
                <!-- Initial Son Section -->
                <div class="child-section" id="sonSection1">
                    <h4>Son Details</h4>
                    <div class="form-group">
                        <label>Son's Name</label>
                        <input type="text" name="son_name[]">
                    </div>
                </div>

                <!-- Initial Daughter Section -->
                <div class="child-section" id="daughterSection1">
                    <h4>Daughter Details</h4>
                    <div class="form-group">
                        <label>Daughter's Name</label>
                        <input type="text" name="daughter_name[]">
                    </div>
                </div>
            </div>
        </div>

        <!-- Bank Details -->
        <h3>Bank Details</h3>
        <div class="form-group">
            <label>Bank Name</label>
            <select name="bank_name">
                <option value="NIC Asia">NIC Asia</option>
                <option value="Nabil Bank">Nabil Bank</option>
                <option value="Standard Chartered">Standard Chartered</option>
                <option value="Siddhartha Bank">Siddhartha Bank</option>
            </select>
        </div>
        <div class="form-group">
            <label>Branch</label>
            <select name="branch_name">
            <option value="">Select</option>
                <option value="Banepa">Banepa</option>
              
            </select>
        </div>
        <div class="form-group">
            <label>Account Number</label>
            <input type="text" name="bank_account_number">
        </div>
        <div class="form-group">
            <label>Demat Institution</label>
            <select name="bank_name">
                <option value="NIC Asia">NIC Asia</option>
                <option value="Citizen Bank">Citizen bank</option>
                <option value="Vision Securities">Vision Securities</option>
                <option value="Siddhartha Bank">Siddhartha Bank</option>
            </select>
        </div>
        <div class="form-group">
            <label>Branch</label>
            <select name="branch_name">
                <option value="">Select</option>
                <option value="Banepa">Banepa</option>
              
            </select>
        </div>
        <div class="form-group">
            <label>Demat Account Number</label>
            <input type="text" name="demat_account_number">
        </div>

        <div class="form-group">
            <label>Crn Number</label>
            <input type="text" name="crn_number">
        </div>
     
        <div class="form-group">
            <label>Broker</label>
            <select name="broker_name">
                <option value="Vision securities">Vision securities</option>
                <option value="Cbl securities">Cbl securities</option>
                <option value="Broker 3">Broker 3</option>
            </select>
        </div>
        <div class="form-group">
            <label>Branch</label>
            <select name="branch_name">
            <option value="">Select</option>
                <option value="Banepa">Banepa</option>
              
            </select>
        </div>
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="user_number">
        </div>
      
        <!-- Terms and Conditions -->
        <div class="form-group terms">
            <input type="checkbox" name="terms" required>
            <label>I accept the terms and conditions.</label>
        </div>

        <button type="submit">Submit</button>
    </form>

    <script>
        function toggleMinorGuardian() {
            const isMinor = document.querySelector('input[name="applicant_type"]:checked').value === 'Minor';
            document.getElementById('guardianDetails').style.display = isMinor ? 'block' : 'none';
            
            // If minor, make parents citizenship compulsory
            if (isMinor) {
                addDocumentToList('Parents Citizenship Front', true);
                addDocumentToList('Parents Citizenship Back', true);
            }
        }

        function toggleMarriedChildren() {
            const isMarried = document.querySelector('select[name="marital_status"]').value === 'Married';
            document.getElementById('childrenDetails').style.display = isMarried ? 'block' : 'none';
        }

        // Document upload functions
        function showUploadFields() {
            const documentType = document.getElementById('documentType').value;
            const container = document.getElementById('uploadFieldsContainer');
            container.innerHTML = '';
            
            if (!documentType) return;
            
            let html = '';
            const docName = document.getElementById('documentType').options[document.getElementById('documentType').selectedIndex].text;
            
            switch(documentType) {
                case 'citizenship':
                    html = `
                        <div class="document-item">
                            <h4>${docName}</h4>
                            <div class="file-upload-wrapper">
                                <label>Front Side:</label>
                                <input type="file" name="citizenship_front" class="file-upload" data-max-size="2097152" accept="image/*,.pdf">
                                <div class="file-info">Max size: 2MB (JPEG, PNG, PDF)</div>
                                <div class="error-message">File size exceeds limit or invalid format</div>
                            </div>
                            <div class="file-upload-wrapper">
                                <label>Back Side:</label>
                                <input type="file" name="citizenship_back" class="file-upload" data-max-size="2097152" accept="image/*,.pdf">
                                <div class="file-info">Max size: 2MB (JPEG, PNG, PDF)</div>
                                <div class="error-message">File size exceeds limit or invalid format</div>
                            </div>
                            <button type="button" onclick="addDocument('${docName}')">Add Document</button>
                        </div>
                    `;
                    break;
                    
                case 'birth_certificate':
                    html = `
                        <div class="document-item">
                            <h4>${docName}</h4>
                            <div class="file-upload-wrapper">
                                <label>Certificate File:</label>
                                <input type="file" name="birth_certificate" class="file-upload" data-max-size="2097152" accept="image/*,.pdf">
                                <div class="file-info">Max size: 2MB (JPEG, PNG, PDF)</div>
                                <div class="error-message">File size exceeds limit or invalid format</div>
                            </div>
                            <button type="button" onclick="addDocument('${docName}')">Add Document</button>
                        </div>
                    `;
                    break;
                    
                case 'license':
                    html = `
                        <div class="document-item">
                            <h4>${docName}</h4>
                            <div class="file-upload-wrapper">
                                <label>Front Side:</label>
                                <input type="file" name="license_front" class="file-upload" data-max-size="2097152" accept="image/*,.pdf">
                                <div class="file-info">Max size: 2MB (JPEG, PNG, PDF)</div>
                                <div class="error-message">File size exceeds limit or invalid format</div>
                            </div>
                            <div class="file-upload-wrapper">
                                <label>Back Side:</label>
                                <input type="file" name="license_back" class="file-upload" data-max-size="2097152" accept="image/*,.pdf">
                                <div class="file-info">Max size: 2MB (JPEG, PNG, PDF)</div>
                                <div class="error-message">File size exceeds limit or invalid format</div>
                            </div>
                            <button type="button" onclick="addDocument('${docName}')">Add Document</button>
                        </div>
                    `;
                    break;
                    
                case 'national_id':
                    html = `
                        <div class="document-item">
                            <h4>${docName}</h4>
                            <div class="file-upload-wrapper">
                                <label>Front Side:</label>
                                <input type="file" name="national_id_front" class="file-upload" data-max-size="2097152" accept="image/*,.pdf">
                                <div class="file-info">Max size: 2MB (JPEG, PNG, PDF)</div>
                                <div class="error-message">File size exceeds limit or invalid format</div>
                            </div>
                            <div class="file-upload-wrapper">
                                <label>Back Side:</label>
                                <input type="file" name="national_id_back" class="file-upload" data-max-size="2097152" accept="image/*,.pdf">
                                <div class="file-info">Max size: 2MB (JPEG, PNG, PDF)</div>
                                <div class="error-message">File size exceeds limit or invalid format</div>
                            </div>
                            <button type="button" onclick="addDocument('${docName}')">Add Document</button>
                        </div>
                    `;
                    break;
                    
                case 'parents_citizenship':
                    html = `
                        <div class="document-item">
                            <h4>${docName}</h4>
                            <div class="file-upload-wrapper">
                                <label>Father's Citizenship Front:</label>
                                <input type="file" name="father_citizenship_front" class="file-upload" data-max-size="2097152" accept="image/*,.pdf">
                                <div class="file-info">Max size: 2MB (JPEG, PNG, PDF)</div>
                                <div class="error-message">File size exceeds limit or invalid format</div>
                            </div>
                            <div class="file-upload-wrapper">
                                <label>Father's Citizenship Back:</label>
                                <input type="file" name="father_citizenship_back" class="file-upload" data-max-size="2097152" accept="image/*,.pdf">
                                <div class="file-info">Max size: 2MB (JPEG, PNG, PDF)</div>
                                <div class="error-message">File size exceeds limit or invalid format</div>
                            </div>
                           
                            <button type="button" onclick="addDocument('${docName}')">Add Document</button>
                        </div>
                    `;
                    break;
            }
            
            container.innerHTML = html;
            
            // Add event listeners for file upload validation
            const fileInputs = container.querySelectorAll('.file-upload');
            fileInputs.forEach(input => {
                input.addEventListener('change', function() {
                    validateFile(this);
                });
            });
        }
        
        function validateFile(input) {
            const maxSize = parseInt(input.getAttribute('data-max-size'));
            const file = input.files[0];
            const errorElement = input.nextElementSibling.nextElementSibling;
            
            if (file) {
                // Check file size
                if (file.size > maxSize) {
                    errorElement.style.display = 'block';
                    input.value = '';
                    return false;
                }
                
                // Check file type
                const validTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                if (!validTypes.includes(file.type)) {
                    errorElement.style.display = 'block';
                    input.value = '';
                    return false;
                }
                
                errorElement.style.display = 'none';
                return true;
            }
            
            return false;
        }
        
        function addDocument(docName) {
            const container = document.getElementById('uploadFieldsContainer');
            const fileInputs = container.querySelectorAll('.file-upload');
            let allValid = true;
            
            // Validate all files first
            fileInputs.forEach(input => {
                if (!validateFile(input) && input.files.length === 0) {
                    allValid = false;
                }
            });
            
            if (!allValid) {
                alert('Please upload all required files with valid format and size');
                return;
            }
            
            // Add to documents list
            addDocumentToList(docName);
            
            // Reset the selection
            document.getElementById('documentType').value = '';
            container.innerHTML = '';
        }
        
        function addDocumentToList(docName, isCompulsory = false) {
            const list = document.getElementById('documentsList');
            const listItem = document.createElement('li');
            
            listItem.textContent = docName + (isCompulsory ? ' (Required)' : '');
            listItem.setAttribute('data-docname', docName.toLowerCase().replace(/ /g, '_'));
            
            // Add remove button if not compulsory
            if (!isCompulsory) {
                const removeBtn = document.createElement('button');
                removeBtn.textContent = 'Remove';
                removeBtn.type = 'button';
                removeBtn.onclick = function() {
                    listItem.remove();
                };
                listItem.appendChild(removeBtn);
            }
            
            // Check if already exists
            const existingItems = list.querySelectorAll(`li[data-docname="${docName.toLowerCase().replace(/ /g, '_')}"]`);
            if (existingItems.length === 0) {
                list.appendChild(listItem);
            }
        }
        
        // Initialize file upload validation
        document.addEventListener('DOMContentLoaded', function() {
            // Add compulsory documents
            addDocumentToList('Citizenship Front', true);
            addDocumentToList('Citizenship Back', true);
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.20.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.20.0/plugins/autoloader/prism-autoloader.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
     integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="nepali-date-picker.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.20.0/themes/prism.min.css" rel="stylesheet" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="nepali-date-picker.min.js"></script>
    <script src="../assets/css/customer.js"></script>
</body>
</html>