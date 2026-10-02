<?php
// Real estate-specific validation
$property_type = sanitizeInput($_POST['property_type']);
$property_location = sanitizeInput($_POST['property_location']);
$property_size = filter_var($_POST['property_size'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$ownership_type = sanitizeInput($_POST['ownership_type']);
$property_description = isset($_POST['property_description']) ? sanitizeInput($_POST['property_description']) : null;

// Validate required real estate fields
if(empty($property_type) || empty($property_location) || empty($property_size) || empty($ownership_type)) {
    throw new Exception("All real estate fields must be filled");
}

// Validate property size
if($property_size <= 0) {
    throw new Exception("Property size must be greater than 0");
}

// File upload for purchase document
$purchase_document = null;
if(isset($_FILES['purchase_document'])) {
    $fileUpload = uploadFile('purchase_document');
    if(isset($fileUpload['error'])) {
        throw new Exception($fileUpload['error']);
    }
    $purchase_document = isset($fileUpload['success']) ? $fileUpload['success'] : null;
}

// Handle multiple property images upload
$property_images = [];
if(isset($_FILES['property_images'])) {
    foreach($_FILES['property_images']['tmp_name'] as $key => $tmp_name) {
        if($_FILES['property_images']['error'][$key] === UPLOAD_ERR_OK) {
            $file = [
                'name' => $_FILES['property_images']['name'][$key],
                'type' => $_FILES['property_images']['type'][$key],
                'tmp_name' => $_FILES['property_images']['tmp_name'][$key],
                'error' => $_FILES['property_images']['error'][$key],
                'size' => $_FILES['property_images']['size'][$key]
            ];
            $_FILES['property_image'] = $file;
            
            $fileUpload = uploadFile('property_image', ['jpg', 'jpeg', 'png']);
            if(isset($fileUpload['error'])) {
                throw new Exception($fileUpload['error']);
            }
            $property_images[] = $fileUpload['success'];
            
            // Limit to 5 images
            if(count($property_images) >= 5) break;
        }
    }
}

// Insert into real_estate_investments table
$query = "INSERT INTO real_estate_investments 
          (investment_id, property_type, property_location, 
           property_size, ownership_type, property_description, purchase_document) 
          VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'issssss', $investment_id, $property_type, 
                     $property_location, $property_size, $ownership_type, 
                     $property_description, $purchase_document);

if(!mysqli_stmt_execute($stmt)) {
    throw new Exception("Failed to save real estate investment: " . mysqli_error($conn));
}

$real_estate_id = mysqli_insert_id($conn);

// Save property images if any
if(!empty($property_images)) {
    foreach($property_images as $image_path) {
        $query = "INSERT INTO property_images 
                  (real_estate_id, image_path) 
                  VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'is', $real_estate_id, $image_path);
        mysqli_stmt_execute($stmt);
    }
}