<?php
// Function to sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Function to handle file uploads
function uploadFile($fieldName, $allowedTypes = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']) {
    if(isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../uploads/";
        $fileName = basename($_FILES[$fieldName]["name"]);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Validate file type
        if(!in_array($fileExt, $allowedTypes)) {
            return ['error' => 'Invalid file type. Allowed types: ' . implode(', ', $allowedTypes)];
        }
        
        // Generate unique filename
        $uniqueName = uniqid() . '_' . time() . '.' . $fileExt;
        $targetFilePath = $targetDir . $uniqueName;
        
        if(move_uploaded_file($_FILES[$fieldName]["tmp_name"], $targetFilePath)) {
            return ['success' => $targetFilePath];
        } else {
            return ['error' => 'Failed to upload file'];
        }
    }
    return ['error' => 'No file uploaded'];
}